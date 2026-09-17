<?php

declare(strict_types=1);

namespace Core\Http;

use PDO;

final class PostgresSessionStore implements SessionStore
{
    private const LOCK_TIMEOUT_SECONDS = 2.0;
    private const RETRY_SLEEP_MICROSECONDS = 40000; // 40ms

    private ?string $lockedSessionId = null;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function __destruct()
    {
        if ($this->lockedSessionId !== null) {
            $this->unlock($this->lockedSessionId);
        }
    }

    public function lock(string $sessionId): bool
    {
        try {
            $statement = $this->pdo->prepare('SELECT pg_try_advisory_lock(hashtext(:session_id))');
            $start = microtime(true);

            do {
                $statement->execute([':session_id' => $sessionId]);
                $acquired = (bool) $statement->fetchColumn();

                if ($acquired) {
                    $this->lockedSessionId = $sessionId;

                    return true;
                }

                usleep(self::RETRY_SLEEP_MICROSECONDS);
            } while ((microtime(true) - $start) < self::LOCK_TIMEOUT_SECONDS);
        } catch (\Throwable) {
            return true;
        }

        return true;
    }

    public function unlock(string $sessionId): bool
    {
        if ($this->lockedSessionId !== $sessionId) {
            return true;
        }

        try {
            $statement = $this->pdo->prepare('SELECT pg_advisory_unlock(hashtext(:session_id))');
            $statement->execute([':session_id' => $sessionId]);
        } catch (\Throwable) {
            return false;
        } finally {
            $this->lockedSessionId = null;
        }

        return true;
    }

    public function read(string $sessionId): ?string
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT encode(payload, :encoding) AS payload FROM http_sessions WHERE session_id = :session_id AND expires_at > NOW()'
            );
            $statement->execute([
                ':encoding' => 'base64',
                ':session_id' => $sessionId,
            ]);
            $encodedPayload = $statement->fetchColumn();

            if (!is_string($encodedPayload)) {
                return null;
            }

            $payload = base64_decode($encodedPayload, true);

            return $payload === false ? null : $payload;
        } catch (\Throwable) {
            return null;
        }
    }

    public function write(string $sessionId, string $payload, int $lifetimeSeconds): bool
    {
        try {
            $statement = $this->pdo->prepare(
                "INSERT INTO http_sessions (session_id, payload, expires_at, updated_at)\n             VALUES (:session_id, decode(:payload, 'base64'), NOW() + (:lifetime_seconds * INTERVAL '1 second'), NOW())\n             ON CONFLICT (session_id) DO UPDATE\n             SET payload = EXCLUDED.payload, expires_at = EXCLUDED.expires_at, updated_at = EXCLUDED.updated_at"
            );

            return $statement->execute([
                ':session_id' => $sessionId,
                ':payload' => base64_encode($payload),
                ':lifetime_seconds' => $lifetimeSeconds,
            ]);
        } catch (\Throwable) {
            return false;
        }
    }

    public function destroy(string $sessionId): bool
    {
        try {
            $statement = $this->pdo->prepare('DELETE FROM http_sessions WHERE session_id = :session_id');

            return $statement->execute([':session_id' => $sessionId]);
        } catch (\Throwable) {
            return false;
        }
    }

    public function gc(int $maxLifetime): int|false
    {
        try {
            $statement = $this->pdo->prepare('DELETE FROM http_sessions WHERE expires_at <= NOW()');
            $statement->execute();

            return $statement->rowCount();
        } catch (\Throwable) {
            return false;
        }
    }

    public function exists(string $sessionId): bool
    {
        try {
            $statement = $this->pdo->prepare('SELECT 1 FROM http_sessions WHERE session_id = :session_id AND expires_at > NOW()');
            $statement->execute([':session_id' => $sessionId]);

            return $statement->fetchColumn() !== false;
        } catch (\Throwable) {
            return false;
        }
    }
}
