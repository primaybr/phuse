<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;
use Core\Exception\DatabaseException;

/**
 * Connection Pool for managing multiple database connections
 *
 * Implements connection pooling to improve performance by reusing database connections
 * and maintaining a pool of active connections.
 *
 * @package Core\Database
 * @author Prima Yoga
 */
class ConnectionPool
{
    /**
     * @var array<Connection> Pool of available connections
     */
    private array $availableConnections = [];

    /**
     * @var array<Connection> Pool of busy connections
     */
    private array $busyConnections = [];

    /**
     * @var array Connection configuration
     */
    private array $config;

    /**
     * @var int Minimum number of connections to maintain
     */
    private int $minConnections;

    /**
     * @var int Maximum number of connections allowed
     */
    private int $maxConnections;

    /**
     * @var int Current total number of connections
     */
    private int $currentConnections = 0;

    /**
     * @var int Connection timeout in seconds
     */
    private int $connectionTimeout = 30;

    /**
     * @var int Idle timeout for connections in seconds
     */
    private int $idleTimeout = 300; // 5 minutes

    /**
     * Constructor - Initialize the connection pool
     *
     * @param array $config Database configuration
     * @param int $minConnections Minimum connections to maintain
     * @param int $maxConnections Maximum connections allowed
     */
    public function __construct(array $config, int $minConnections = 1, int $maxConnections = 50)
    {
        $this->config = $config;
        $this->minConnections = $minConnections;
        $maxEnv = getenv('DB_POOL_MAX_CONNECTIONS');
        $this->maxConnections = ($maxEnv !== false && is_numeric($maxEnv)) ? (int) $maxEnv : $maxConnections;

        $this->initializePool();
    }

    /**
     * Initialize the connection pool with minimum connections
     *
     * @return void
     */
    private function initializePool(): void
    {
        for ($i = 0; $i < $this->minConnections; $i++) {
            $connection = $this->createConnection();
            if ($connection) {
                $this->availableConnections[] = $connection;
                $this->currentConnections++;
            }
        }
    }

    /**
     * Get a connection from the pool
     *
     * @return Connection
     * @throws DatabaseException If no connections available and max reached
     */
    public function getConnection(): Connection
    {
        // Try to get an available connection that is confirmed alive
        while (!empty($this->availableConnections)) {
            $connection = array_pop($this->availableConnections);
            if ($this->isConnectionValid($connection)) {
                $this->busyConnections[] = $connection;
                return $connection;
            }

            // Connection is dead - attempt reconnect
            try {
                $connection->reconnect();
                if ($this->isConnectionValid($connection)) {
                    $this->busyConnections[] = $connection;
                    return $connection;
                }
            } catch (\Throwable) {
                // Reconnect failed, discard
            }

            unset($connection);
            $this->currentConnections = max(0, $this->currentConnections - 1);
        }

        // Create new connection if under max limit
        if ($this->currentConnections < $this->maxConnections) {
            $connection = $this->createConnection();
            if ($connection) {
                $this->currentConnections++;
                $this->busyConnections[] = $connection;
                return $connection;
            }
        }

        throw new DatabaseException('No database connections available. Maximum connections reached (' . $this->maxConnections . ').');
    }

    /**
     * Return a connection to the pool
     *
     * @param Connection $connection The connection to return
     * @return void
     */
    public function returnConnection(Connection $connection): void
    {
        // Remove from busy connections
        $key = array_search($connection, $this->busyConnections, true);
        if ($key !== false) {
            unset($this->busyConnections[$key]);
        }

        if (method_exists($connection, 'touchLastUsed')) {
            $connection->touchLastUsed();
        }

        // Return connection to available pool without redundant SELECT 1 roundtrips
        $this->availableConnections[] = $connection;
    }

    /**
     * Create a new database connection
     *
     * @return Connection|null
     */
    private function createConnection(): ?Connection
    {
        try {
            return new Connection(
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['username'],
                $this->config['password'],
                $this->config['options'] ?? []
            );
        } catch (\Throwable $e) {
            // \Throwable, not \Exception - a null/invalid PDO handler surfaces as \Error
            // ("call to a member function on null"), which \Exception alone doesn't catch.
            // Log error but don't throw - pool should handle gracefully
            error_log('Failed to create database connection: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a connection is still valid
     *
     * @param Connection $connection The connection to check
     * @return bool True if connection is valid
     */
    private function isConnectionValid(Connection $connection): bool
    {
        // If connection was used within the last 15 seconds, skip active ping query
        if (method_exists($connection, 'getLastUsedAt') && (microtime(true) - $connection->getLastUsedAt()) < 15.0) {
            return true;
        }

        return $connection->isAlive();
    }

    /**
     * Get the current pool statistics
     *
     * @return array Pool statistics
     */
    public function getStats(): array
    {
        return [
            'total_connections' => $this->currentConnections,
            'available_connections' => count($this->availableConnections),
            'busy_connections' => count($this->busyConnections),
            'min_connections' => $this->minConnections,
            'max_connections' => $this->maxConnections,
        ];
    }

    /**
     * Close all connections in the pool
     *
     * @return void
     */
    public function closeAll(): void
    {
        // Close available connections
        foreach ($this->availableConnections as $connection) {
            unset($connection);
        }

        // Close busy connections (they should be returned first)
        foreach ($this->busyConnections as $connection) {
            unset($connection);
        }

        $this->availableConnections = [];
        $this->busyConnections = [];
        $this->currentConnections = 0;
    }

    /**
     * Clean up idle connections
     *
     * @return void
     */
    public function cleanupIdleConnections(): void
    {
        // For now, this is a placeholder. In a real implementation,
        // you'd track connection creation time and close idle ones
        // that exceed the idle timeout.

        // Keep at least minimum connections
        while (count($this->availableConnections) > $this->minConnections) {
            $connection = array_pop($this->availableConnections);
            unset($connection);
            $this->currentConnections--;
        }
    }

    /**
     * Destructor - Clean up all connections
     */
    public function __destruct()
    {
        $this->closeAll();
    }
}
