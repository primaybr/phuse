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
    }

    /**
     * Destructor to clean up database connection
     */
    public function __destruct()
    {
        //disconnect db conn
        $this->handler = null;
    }

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
                $this->bind(param: ":{$k}", value: $v);
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
                return $this->statement->execute($params);
            }

            return $this->statement->execute();
        } catch (PDOException $e) {
            // For debugging, throw the original PDO exception
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
        $this->execute();

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
