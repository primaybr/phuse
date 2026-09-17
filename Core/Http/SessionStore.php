<?php

declare(strict_types=1);

namespace Core\Http;

interface SessionStore
{
    public function lock(string $sessionId): bool;

    public function unlock(string $sessionId): bool;

    public function read(string $sessionId): ?string;

    public function write(string $sessionId, string $payload, int $lifetimeSeconds): bool;

    public function destroy(string $sessionId): bool;

    public function gc(int $maxLifetime): int|false;

    public function exists(string $sessionId): bool;
}
