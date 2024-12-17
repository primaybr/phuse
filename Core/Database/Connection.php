<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;
use PDOException;

class Connection
{
    private $handler;
    private $statement;

    public function __construct($driver, $host, $port, $dbname, $user, $password, $options = [])
    {
		$connection = "Core\Database\Drivers\\".$this->getDrivers($driver);
		$connect	= new $connection($host, $port, $dbname, $user, $password, $options);
		$this->handler = $connect->getDB();
    }

    public function __destruct()
    {
        //disconnect db conn
        $this->handler = null;
    }
	
	public function getDrivers(string $driver) : string
	{
		$drivers = ['mysql' => 'MySQL', 'pgsql' => 'PgSQL'];
		$driver = strtolower($driver);
		
		return $drivers[$driver] ?? '';
	}

    public function query(string $query): void
    {
		$this->statement = $this->handler->prepare($query);
    }

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

    public function arrayBind(array|null $data = []): void
    {
        if ($data) {
            foreach ($data as $k => $v) {
                $this->bind(param: ":{$k}", value: $v);
            }
        }
    }

   public function execute(array $params = []): mixed 
    {
        try {
            if (!empty($params)) {
                return $this->statement->execute($params);
            } 
            
            return $this->statement->execute();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode()); 
        }
    }

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

    public function single() : array|false
    {
        $this->execute();

        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    public function totalRows(): int
    {
        $this->execute();

        return $this->statement->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->handler->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->handler->beginTransaction();
    }

    public function endTransaction(): bool
    {
        return $this->handler->commit();
    }

    public function cancelTransaction(): bool
    {
        return $this->handler->rollBack();
    }

    public function debug(): void
    {
        $this->statement->debugDumpParams();
    }
}
