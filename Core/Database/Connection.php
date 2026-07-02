<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;
use PDOException;
use PDOStatement;
use Core\Exception\DatabaseException;

class Connection
{
    private ?PDO $handler;
    private ?PDOStatement $statement;
    private array $boundParams = [];

    public function __construct(
        string $driver,
        string $host,
        int|string $port,
        string $dbname,
        string $user,
        string $password,
        array $options = []
    ) {
		$connection = "Core\Database\Drivers\\".$this->getDrivers($driver);
		$connect	= new $connection($host, $port, $dbname, $user, $password, $options);
		$this->handler = $connect->getDB();

		if (!$this->handler instanceof PDO) {
			throw new DatabaseException('Failed to establish a database connection');
		}
    }

    // No __destruct(): PDO connections close automatically when the handler is garbage
    // collected. A previous version explicitly nulled $handler here, which raced against
    // ConnectionPool's static-held references during PHP shutdown - if this object's
    // destructor ran before the owning Model's, returnConnection()/isConnectionValid()
    // would later call prepare() on the already-nulled handler and fatal.

	/**
     * Get the appropriate driver class name based on the driver type
     *
     * @param string $driver The database driver type (mysql, pgsql)
     * @return string The driver class name
     */
	public function getDrivers(string $driver) : string
	{
		$drivers = ['mysql' => 'MySQL', 'pgsql' => 'PgSQL'];
		$driver = strtolower($driver);

		return $drivers[$driver] ?? '';
	}

    /**
     * Prepare an SQL statement for execution
     *
     * @param string $query The SQL query to prepare
     * @return void
     * @throws DatabaseException If the statement preparation fails
     */
    public function query(string $query): void
    {
		$this->statement = $this->handler->prepare($query);
		if ($this->statement === false) {
			throw new DatabaseException('Failed to prepare SQL statement');
		}
		// Reset bound parameters when preparing a new statement to prevent accumulation
		$this->boundParams = [];
    }

    /**
     * Bind a value to a parameter in the prepared statement
     *
     * @param string $param The parameter name (e.g., ':id')
     * @param mixed $value The value to bind
     * @param mixed $type The PDO parameter type (optional, auto-detected if null)
     * @return void
     */
    public function bind(string $param, mixed $value, mixed $type = null): void
    {
        $type ??= match (true) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };

        $this->statement->bindValue($param, $value, $type);
        // Also add to boundParams for execute()
        $this->boundParams[$param] = $value;
    }

    /**
     * Bind an array of parameters to the prepared statement
     *
     * @param array|null $data The associative array of parameters to bind
     * @return void
     */
    public function arrayBind(array|null $data = []): void
    {
        if ($data) {
            foreach ($data as $k => $v) {
                // Check if key already has colon prefix
                $param = strpos($k, ':') === 0 ? $k : ":{$k}";
                if ($this->statement) {
                    $this->bind(param: $param, value: $v);
                    // Track the bound parameters for execute()
                    $this->boundParams[$param] = $v;
                }
            }
        }
    }

    /**
     * Execute the prepared statement
     *
     * @param array $params Additional parameters to pass to execute (optional)
     * @return mixed The result of the execution
     * @throws PDOException If the execution fails
     */
    public function execute(array $params = []): mixed
    {
        try {
            if (!empty($params)) {
                // Caller passed explicit params - PDO binds them (all as PARAM_STR).
                return $this->statement->execute($params);
            }
            // Parameters were already bound type-correctly via arrayBind() → bindValue().
            // Passing an array to execute() would override those bindings (PDO treats
            // everything as PARAM_STR), breaking BOOLEAN, NULL, and INT columns.
            return $this->statement->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Fetch all results from the executed query
     *
     * @param string $type The fetch type: 'array' (default), 'object', or 'column'
     * @return array|false The result set or false on failure
     */
    public function result(string $type = ''): array|false
    {
         $type = match ($type) {
            'object' => PDO::FETCH_OBJ,
            'column' => PDO::FETCH_COLUMN,
            default  => PDO::FETCH_ASSOC,
        };

        $this->execute();

        return $this->statement->fetchAll($type);
    }

    /**
     * Fetch a single row from the executed query
     *
     * @return array|false The single row result or false if no rows
     */
    public function single() : array|false
    {
        // Only execute if the statement hasn't been executed yet
        // This prevents double execution for INSERT queries with RETURNING clause
        if (!isset($this->statement) || $this->statement->rowCount() === 0) {
            $this->execute();
        }

        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get the number of rows affected by the last executed statement
     *
     * @return int The number of affected rows
     */
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * Get the total number of rows returned by a SELECT query
     *
     * @return int The total number of rows
     */
    public function totalRows(): int
    {
        $this->execute();

        return $this->statement->rowCount();
    }

    /**
     * Get the currently bound parameters (for debugging)
     *
     * @return array The currently bound parameters
     */
    public function getBoundParams(): array
    {
        return $this->boundParams;
    }

    /**
     * Reset bound parameters to empty array
     *
     * @return void
     */
    public function resetBoundParams(): void
    {
        $this->boundParams = [];
    }

    /**
     * Get the last inserted ID from an INSERT operation
     *
     * @return string The last inserted ID
     */
    public function lastInsertId(): string
    {
        return $this->handler->lastInsertId();
    }

    /**
     * Begin a database transaction
     *
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool
    {
        return $this->handler->beginTransaction();
    }

    /**
     * Commit the current database transaction
     *
     * @return bool True on success, false on failure
     */
    public function endTransaction(): bool
    {
        return $this->handler->commit();
    }

    /**
     * Roll back the current database transaction
     *
     * @return bool True on success, false on failure
     */
    public function cancelTransaction(): bool
    {
        return $this->handler->rollBack();
    }

    /**
     * Debug the prepared statement by dumping its parameters
     *
     * @return void
     */
    public function debug(): void
    {
        $this->statement->debugDumpParams();
    }
}
