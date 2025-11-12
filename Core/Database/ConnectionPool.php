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
    public function __construct(array $config, int $minConnections = 2, int $maxConnections = 10)
    {
        $this->config = $config;
        $this->minConnections = $minConnections;
        $this->maxConnections = $maxConnections;

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
        // Try to get an available connection
        if (!empty($this->availableConnections)) {
            $connection = array_pop($this->availableConnections);
            $this->busyConnections[] = $connection;
            return $connection;
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

        throw new DatabaseException('No database connections available. Maximum connections reached.');
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

        // Check if connection is still valid
        if ($this->isConnectionValid($connection)) {
            $this->availableConnections[] = $connection;
        } else {
            // Connection is invalid, don't reuse it
            $this->currentConnections--;
        }
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
        } catch (\Exception $e) {
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
        try {
            // Simple ping query to test connection
            $connection->query('SELECT 1');
            $connection->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
