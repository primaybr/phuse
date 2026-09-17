<?php

declare(strict_types=1);

namespace Core\Http;

final class DatabaseSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
    private ?string $lockedSessionId = null;

    public function __construct(
        private readonly SessionStore $store,
        private readonly int $lifetimeSeconds,
    ) {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        if ($this->lockedSessionId === null) {
            return true;
        }

        try {
            return $this->store->unlock($this->lockedSessionId);
        } catch (\Throwable) {
            return false;
        } finally {
            $this->lockedSessionId = null;
        }
    }

    public function read(string $id): string|false
    {
        try {
            if (!$this->store->lock($id)) {
                return false;
            }

            $this->lockedSessionId = $id;

            return $this->store->read($id) ?? '';
        } catch (\Throwable) {
            $this->close();

            return false;
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            return $this->store->write($id, $data, $this->lifetimeSeconds);
        } catch (\Throwable) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            return $this->store->destroy($id);
        } catch (\Throwable) {
            return false;
        } finally {
            if ($this->lockedSessionId === $id) {
                $this->close();
            }
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            return $this->store->gc($max_lifetime);
        } catch (\Throwable) {
            return false;
        }
    }

    public function validateId(string $id): bool
    {
        try {
            return $this->store->exists($id);
        } catch (\Throwable) {
            return false;
        }
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->write($id, $data);
    }
}
