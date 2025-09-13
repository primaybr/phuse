<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;
use PDOException;
use Core\Cache\QueryCache;
use Config\Database as DatabaseConfig;

class Connection
{
    private $handler;
    private $statement;
    private ?QueryCache $queryCache = null;
    private string $lastQuery = '';
    private array $lastParams = [];

    public function __construct($driver, $host, $port, $dbname, $user, $password, $options = [])
    {
        $connection = "Core\Database\Drivers\\".$this->getDrivers($driver);
        $connect = new $connection($host, $port, $dbname, $user, $password, $options);
        $this->handler = $connect->getDB();
        
        // Initialize query cache if enabled in config
        $config = new DatabaseConfig();
        $cacheConfig = $config->getCacheConfig();
        
        if ($cacheConfig['enabled'] ?? false) {
            $this->queryCache = new QueryCache($cacheConfig);
        }
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
        $this->lastQuery = $query;
        $this->lastParams = [];
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
        
        $this->lastParams[$param] = $value;
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
            $this->lastParams = array_merge($this->lastParams, $params);
            
            // Check if we should use cache
            if ($this->queryCache && $this->queryCache->shouldCacheQuery($this->lastQuery)) {
                $cacheKey = $this->queryCache->generateKey($this->lastQuery, $this->lastParams);
                
                if ($this->queryCache->hasValidCache($cacheKey)) {
                    return $this->queryCache->getCachedResult($cacheKey);
                }
            }
            
            // Execute the query
            $result = !empty($params) 
                ? $this->statement->execute($params)
                : $this->statement->execute();
                
            // Cache the result if needed
            if (isset($cacheKey) && $result !== false) {
                $this->cacheCurrentResult($cacheKey);
            }
            
            return $result;
            
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

        // Check cache first
        if ($this->queryCache && $this->queryCache->shouldCacheQuery($this->lastQuery)) {
            $cacheKey = $this->queryCache->generateKey($this->lastQuery . '_result_' . $type, $this->lastParams);
            
            if ($this->queryCache->hasValidCache($cacheKey)) {
                return $this->queryCache->getCachedResult($cacheKey);
            }
        }

        // Execute and get results
        $this->execute();
        $result = $this->statement->fetchAll($type);
        
        // Cache the result if needed
        if (isset($cacheKey) && $result !== false) {
            $this->queryCache->storeResult($cacheKey, $result);
        }
        
        return $result;
    }

    public function single(): array|false
    {
        // Check cache first
        if ($this->queryCache && $this->queryCache->shouldCacheQuery($this->lastQuery)) {
            $cacheKey = $this->queryCache->generateKey($this->lastQuery . '_single', $this->lastParams);
            
            if ($this->queryCache->hasValidCache($cacheKey)) {
                return $this->queryCache->getCachedResult($cacheKey);
            }
        }
        
        // Execute and get result
        $this->execute();
        $result = $this->statement->fetch(PDO::FETCH_ASSOC);
        
        // Cache the result if needed
        if (isset($cacheKey) && $result !== false) {
            $this->queryCache->storeResult($cacheKey, $result);
        }
        
        return $result;
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * Clear the query cache
     * 
     * @param string|null $table Optional table name to clear specific table cache
     * @return bool True on success, false on failure
     */
    public function clearQueryCache(?string $table = null): bool
    {
        if (!$this->queryCache) {
            return false;
        }
        
        return $table 
            ? $this->queryCache->clearTableCache($table)
            : $this->queryCache->clear();
    }
    
    /**
     * Cache the current statement result
     * 
     * @param string $cacheKey The cache key to use
     * @return void
     */
    private function cacheCurrentResult(string $cacheKey): void
    {
        if (!$this->queryCache) {
            return;
        }
        
        // Get the result based on the query type
        $queryType = strtoupper(strtok($this->lastQuery, ' '));
        $result = null;
        
        switch ($queryType) {
            case 'SELECT':
                $result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'SHOW':
            case 'DESCRIBE':
            case 'EXPLAIN':
                $result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
                break;
        }
        
        if ($result !== null) {
            $this->queryCache->storeResult($cacheKey, $result);
        }
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
