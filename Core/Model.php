<?php

declare(strict_types=1);

namespace Core;
use Core\Database as Database;
use Core\Config as Config;
use Core\Database\Builders\Builders as Builders;
use Core\Database\ConnectionPool;
use Config\Database as DatabaseConfig;
use Core\Text\Str as Str;
use Core\Exception\DatabaseException;
use Core\Exception\ValidationException;
use Core\Utilities\Validator\Validator;
use Core\Cache\QueryCache;

/**
 * Class Model
 *
 * Represents a database model for interacting with a specific database table.
 *
 * @package Core
 * @author  Prima Yoga
 */
class Model
{
    // Constants for better code maintainability
    private const DEFAULT_RETURN_TYPE = 'array';
    private const DEFAULT_FIELDS = '*';
    private const DEFAULT_PRIMARY_KEY = 'id';
    private const CACHE_LIFETIME = 3600; // 1 hour
    private const CACHE_DIRECTORY = 'database';

    public Database\Connection $db;
    protected string $table;
    protected object $dbconfig;
    protected string $fields = self::DEFAULT_FIELDS;
    protected string $returnType = self::DEFAULT_RETURN_TYPE;
    protected bool $ignoreDuplicate = false;
    protected object $builder;
    protected object $str;
    protected ?QueryCache $queryCache = null;

    public bool $byPassWhere = false;
    public string $primaryKey = self::DEFAULT_PRIMARY_KEY;

	// ORM Features
	protected bool $timestamps = true;
	protected bool $softDeletes = false;
	protected string $createdAtColumn = 'created_at';
	protected string $updatedAtColumn = 'updated_at';
	protected string $deletedAtColumn = 'deleted_at';
	protected array $casts = [];
	protected array $fillable = [];
	protected array $guarded = [];
	protected array $hidden = [];
	protected array $visible = [];
	protected array $relationships = [];
	protected array $events = [];
	protected array $globalScopes = [];

	// Validation properties
	protected array $validationRules = [];
	protected array $validationMessages = [];
	protected bool $validateOnSave = true;
	protected bool $validateOnUpdate = true;

    /**
     * @var ConnectionPool|null Shared connection pool instance
     */
    private static ?ConnectionPool $connectionPool = null;

    /**
     * Initializes the model with the specified table and database.
     *
     * @param string $table The name of the table.
     * @param string $database The name of the database configuration to use.
     */
    public function __construct(string $table, string $database = 'default')
    {
        $this->dbconfig = $this->setDatabase($database);
        if (empty($this->dbconfig->driver) || empty($this->dbconfig->host) || empty($this->dbconfig->port) || empty($this->dbconfig->database) || empty($this->dbconfig->username)) {
            throw DatabaseException::connectionError('Database configuration is incomplete');
        }

        // Initialize connection pool if not already done
        if (self::$connectionPool === null) {
            self::$connectionPool = new ConnectionPool((array) $this->dbconfig);
        }

        // Get connection from pool
        try {
            $this->db = self::$connectionPool->getConnection();
        } catch (\Exception $e) {
            throw DatabaseException::connectionError('Database connection could not be established', [
                'driver' => $this->dbconfig->driver,
                'host' => $this->dbconfig->host,
                'database' => $this->dbconfig->database
            ], 0, $e);
        }

        $this->table = $this->dbconfig->prefix . $table;
        $builders = new Builders($this->dbconfig->driver, $table);
        $this->builder = $builders->builders;
        unset($builders);
        $this->str = new Str;

        //set default primaryKey
        $this->setPrimaryKey();

        // Initialize query cache
        $this->initializeQueryCache();
    }

    /**
     * Destructor - Return connection to pool
     */
    public function __destruct()
    {
        if (self::$connectionPool && $this->db) {
            self::$connectionPool->returnConnection($this->db);
        }
    }

    /**
     * Get connection pool statistics
     *
     * @return array Connection pool statistics
     */
    public static function getConnectionPoolStats(): array
    {
        if (self::$connectionPool) {
            return self::$connectionPool->getStats();
        }
        return [
            'total_connections' => 0,
            'available_connections' => 0,
            'busy_connections' => 0,
            'min_connections' => 0,
            'max_connections' => 0,
        ];
    }
    
    /**
     * Selects the database configuration.
     *
     * @param string $database The name of the database.
     * @return object The database configuration object.
     */
    public function setDatabase(string $database): object
    {
        $config = new DatabaseConfig();
        if (!isset($config->connections[$database])) {
            throw DatabaseException::connectionError("Database configuration '{$database}' not found");
        }
        return (object)$config->connections[$database];
    }

    /**
     * Allows fields to be set before executing get().
     *
     * @param array|string $fields Field name, or an array of field/value pairs.
     * @return self
     * @throws \InvalidArgumentException If fields parameter is invalid.
     */
    public function setFields(array|string $fields): self
    {
        if (is_string($fields)) {
            if (empty(trim($fields))) {
                throw new \InvalidArgumentException('Fields parameter cannot be empty');
            }
            $fields = explode(',', $fields);
        }

        if (empty($fields)) {
            throw new \InvalidArgumentException('Fields array cannot be empty');
        }

        foreach ($fields as $v) {
            $field = trim($v);
            if (empty($field)) {
                throw new \InvalidArgumentException('Field name cannot be empty');
            }

            if (self::DEFAULT_FIELDS == $this->fields) {
                $this->fields = "{$field},";
            } else {
                $this->fields .= "{$field},";
            }
        }

        $this->fields = rtrim($this->fields, ',');

        return $this;
    }
	
	/**
     * Sets the primary key for the model.
     *
     * @param string $primaryKey The primary key field name.
     * @return self
     */
	public function setPrimaryKey(string $primaryKey = ''): self
	{
		$this->primaryKey = empty($primaryKey) ? $this->primaryKey : $primaryKey;
		
		return $this;
	}
	
	/**
     * Sets the SELECT clause for the query.
     *
     * @param string|array $fields The fields to select.
     * @return self
     */
	public function select(string|array $fields = '*'): self
	{
		$this->builder->select($fields);
		
		return $this;
	}
	
    /**
     * Adds a WHERE clause to the query.
     *
     * @param string $key The field to apply the condition to.
     * @param string|int $value The value to compare with.
     * @param string $type The operator to use for the condition.
     * @return self
     */
    public function where(string $key = '', string|int $value = '', string $type = '='): self
    {

        $this->builder->where($key, $value, $type);
        
        return $this;
    }
	
	/**
     * Adds a WHERE IN clause to the query.
     *
     * @param array $data The values to check against.
     * @param bool $not Indicates whether to use NOT IN instead of IN.
     * @return self
     */
	public function whereIn(array $data = [], bool $not = false): self
    {
        $this->builder->whereIn($data,$not);
        
        return $this;
    }
	
	/**
     * Adds an OR WHERE clause to the query.
     *
     * @param string $key The field to apply the condition to.
     * @param string $value The value to compare with.
     * @param string $type The operator to use for the condition.
     * @return self
     */
	public function orWhere(string $key = '', string $value = '', string $type = '='): self
    {
        $this->builder->orWhere($key, $value, $type);
        
        return $this;
    }
	
	/**
     * Adds a raw WHERE query to the query.
     *
     * @param string $query The raw query to use.
     * @return self
     */
	public function whereQuery(string $query): self
    {
        $this->builder->whereQuery($query);
        
        return $this;
    }
	
	/**
     * Adds a JOIN clause to the query.
     *
     * @param string $table The table to join with.
     * @param string $cond The condition for the join.
     * @param string $type The type of join (e.g., INNER, LEFT, RIGHT).
     * @return self
     */
	public function join(string $table, string $cond, string $type): self
	{
		$this->builder->join($table,$cond,$type);
		
		return $this;
	}
	
	/**
     * Adds an ORDER BY clause to the query.
     *
     * @param string $key The field to order by.
     * @param string $order The order direction (ASC or DESC).
     * @return self
     */
	public function orderBy(string $key, string $order): self
	{
		$this->builder->orderBy($key,$order);
		
		return $this;
	}
	
	/**
     * Adds a GROUP BY clause to the query.
     *
     * @param string $groupby The field to group by.
     * @return self
     */
	public function groupBy(string $groupby): self
	{
		$this->builder->groupBy($groupby);

		return $this;
	}



    /**
     * Sets the return type to array.
     *
     * @return self
     */
    public function asArray(): self
    {
        $this->returnType = 'array';

        return $this;
    }

    /**
     * Sets the return type to object.
     *
     * @return self
     */
    public function asObject(): self
    {
        $this->returnType = 'object';

        return $this;
    }
    
    /**
     * Indicates that duplicate records should be ignored during insertion.
     *
     * @return self
     */
    public function ignoreDuplicate(): self
    {
        $this->ignoreDuplicate = true;
        
        return $this;
    }



    /**
     * Retrieves the total number of rows in the result set.
     *
     * @param bool $reset Indicates whether to reset the query after counting.
     * @return int The total number of rows.
     */
    public function totalRows(bool $reset = false): int|bool
    {
        $query = $this->builder->select('')->count()->compile($reset);
        $this->db->query($query);
        $this->db->arrayBind($this->builder->binds);
        
        if ($reset) {
            $this->builder->binds = [];
        }
        
        if ($this->db->execute()) {
            $count = $this->db->result('column');
            return reset($count);
        }

        return false;
    }

    /**
     * Retrieves the last inserted ID.
     *
     * @param string $id The primary key field name.
     * @return mixed The last inserted ID.
     */
    public function getLastId(string $id = 'id'): mixed
    {
        $query = $this->builder->select('')->max($id)->compile();
        $this->db->query($query);

        $result = $this->db->single();

        if ($result) {
            return $result['id'];
        }

        return false;
    }
	
	/**
     * Resets the query parameters.
     *
     * @return self
     */
	public function resetQuery() :self
	{
		$this->builder->resetQuery();
		
		return $this;
	}

    /**
     * Adds a SUM aggregation to the query.
     *
     * @param string $field The field to sum.
     * @param string $alias The alias for the sum result.
     * @return self
     */
    public function sum(string $field, string $alias = ''): self
    {
        $this->builder->sum($field, $alias);
        return $this;
    }

    /**
     * Adds an AVG aggregation to the query.
     *
     * @param string $field The field to average.
     * @param string $alias The alias for the average result.
     * @return self
     */
    public function avg(string $field, string $alias = ''): self
    {
        $this->builder->avg($field, $alias);
        return $this;
    }

    /**
     * Adds multiple WHERE conditions to the query.
     *
     * @param array $conditions The conditions to apply.
     * @return self
     */
    public function whereArray(array $conditions): self
    {
        $this->builder->whereArray($conditions);
        return $this;
    }

    /**
     * Sets the DISTINCT keyword for the query.
     *
     * @return self
     */
    public function distinct(): self
    {
        $this->builder->distinct();
        return $this;
    }

    /**
     * Adds a JSON_CONTAINS condition to the query.
     *
     * @param string $field The field to check.
     * @param mixed $value The value to check for.
     * @return self
     */
    public function whereJsonContains(string $field, $value): self
    {
        $this->builder->whereJsonContains($field, $value);
        return $this;
    }

    /**
     * Logs the executed query.
     *
     * @return void
     */
    public function logQuery(): void
    {
        $this->builder->logQuery();
    }

    /**
     * Adds multiple conditions to the query.
     *
     * @param array $conditions The conditions to apply.
     * @param string $clause The clause to use (AND/OR).
     * @return self
     */
    public function whereMultiple(array $conditions, string $clause = 'AND'): self
    {
        $this->builder->whereMultiple($conditions, $clause);
        return $this;
    }

    /**
     * Clears the query cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->builder->clearCache();
    }

    /**
     * Inserts multiple records into the database with improved performance and error handling.
     *
     * @param array $data The data to insert. Each element should be an associative array representing a record.
     * @param int $chunkSize The number of records to insert in each chunk (default: 1000).
     * @return array Returns array with 'success' count and 'errors' array.
     * @throws DatabaseException If the insert operation fails.
     */
    public function insertBatch(array $data, int $chunkSize = 1000): array
    {
        if (empty($data)) {
            throw DatabaseException::queryError('', 'No data provided for batch insert');
        }

        $results = ['success' => 0, 'errors' => []];
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            try {
                $this->beginTransaction();

                // Validate and prepare data for the chunk
                $processedChunk = [];
                foreach ($chunk as $row) {
                    // Apply fillable/guarded attributes
                    $row = $this->fillableAttributes($row);
                    $row = $this->guardedAttributes($row);

                    // Set mutator values
                    foreach ($row as $key => $value) {
                        $row[$key] = $this->setMutatorValue($key, $value);
                    }

                    // Set timestamps
                    $row = $this->setTimestamps($row);

                    $processedChunk[] = $row;
                }

                $this->builder->insertBatch($processedChunk);

                $query = $this->builder->compile();
                $this->db->query($query);
                $this->db->arrayBind($this->builder->binds);

                if ($this->db->execute()) {
                    $results['success'] += count($chunk);
                    $this->commit();
                } else {
                    $this->rollback();
                    $results['errors'][] = 'Failed to insert chunk: ' . json_encode($chunk);
                }

                $this->builder->binds = [];

            } catch (\Exception $e) {
                $this->rollback();
                $results['errors'][] = 'Exception in chunk: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Updates multiple records in batches for better performance.
     *
     * @param array $data Array of records to update, each containing conditions and update data.
     * @param int $chunkSize The number of records to update in each chunk (default: 500).
     * @return array Returns array with 'success' count and 'errors' array.
     */
    public function updateBatch(array $data, int $chunkSize = 500): array
    {
        if (empty($data)) {
            throw DatabaseException::queryError('', 'No data provided for batch update');
        }

        $results = ['success' => 0, 'errors' => []];
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            try {
                $this->beginTransaction();

                foreach ($chunk as $record) {
                    if (!isset($record['data']) || !isset($record['where'])) {
                        $results['errors'][] = 'Invalid record format. Must contain "data" and "where" keys.';
                        continue;
                    }

                    $updateData = $record['data'];
                    $conditions = $record['where'];

                    // Apply fillable/guarded attributes
                    $updateData = $this->fillableAttributes($updateData);
                    $updateData = $this->guardedAttributes($updateData);

                    // Set mutator values
                    foreach ($updateData as $key => $value) {
                        $updateData[$key] = $this->setMutatorValue($key, $value);
                    }

                    // Set timestamps
                    $updateData = $this->setTimestamps($updateData, true);

                    // Include soft delete condition
                    if ($this->softDeletes) {
                        $this->whereNull($this->deletedAtColumn);
                    }

                    // Apply where conditions
                    foreach ($conditions as $field => $value) {
                        $this->where($field, $value);
                    }

                    $this->builder->update($updateData);
                    $query = $this->builder->compile();

                    $this->db->query($query);
                    $this->db->arrayBind($this->builder->binds);

                    if ($this->db->execute()) {
                        $results['success']++;
                    } else {
                        $results['errors'][] = 'Failed to update record with conditions: ' . json_encode($conditions);
                    }

                    $this->builder->binds = [];
                    $this->resetQuery();
                }

                $this->commit();

            } catch (\Exception $e) {
                $this->rollback();
                $results['errors'][] = 'Exception in chunk: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Deletes multiple records in batches for better performance.
     *
     * @param array $conditions Array of condition arrays for records to delete.
     * @param int $chunkSize The number of records to delete in each chunk (default: 500).
     * @return array Returns array with 'success' count and 'errors' array.
     */
    public function deleteBatch(array $conditions, int $chunkSize = 500): array
    {
        if (empty($conditions)) {
            throw DatabaseException::queryError('', 'No conditions provided for batch delete');
        }

        $results = ['success' => 0, 'errors' => []];
        $chunks = array_chunk($conditions, $chunkSize);

        foreach ($chunks as $chunk) {
            try {
                $this->beginTransaction();

                foreach ($chunk as $condition) {
                    // Apply where conditions
                    foreach ($condition as $field => $value) {
                        $this->where($field, $value);
                    }

                    // Include soft delete condition for hard deletes
                    if ($this->softDeletes) {
                        $this->whereNull($this->deletedAtColumn);
                    }

                    $this->builder->delete();
                    $query = $this->builder->compile();

                    $this->db->query($query);
                    $this->db->arrayBind($this->builder->binds);

                    if ($this->db->execute()) {
                        $results['success']++;
                    } else {
                        $results['errors'][] = 'Failed to delete record with conditions: ' . json_encode($condition);
                    }

                    $this->builder->binds = [];
                    $this->resetQuery();
                }

                $this->commit();

            } catch (\Exception $e) {
                $this->rollback();
                $results['errors'][] = 'Exception in chunk: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * The "builder" function.
     *
     * get builder instance.
     *
     * @return object
     */
    public function builder(): object
    {
        return $this->builder;
    }

    /**
     * Adds a raw query to the query.
     *
     * @param string $query The raw query to use.
     * @return self
     */
    public function raw(string $query): self
    {
        $this->builder->raw($query);
        return $this;
    }

    /**
     * Adds random ordering to the query.
     *
     * @return self
     */
    public function orderByRandom(): self
    {
        $this->builder->orderByRandom();
        return $this;
    }

    /**
     * Adds date formatting to the query.
     *
     * @param string $field The field to format.
     * @param string $format The date format.
     * @return self
     */
    public function dateFormat(string $field, string $format = 'Y-m-d'): self
    {
        $this->builder->dateFormat($field, $format);
        return $this;
    }

    /**
     * Adds JSON extraction to the query.
     *
     * @param string $field The JSON field.
     * @param string $path The JSON path.
     * @return self
     */
    public function jsonExtract(string $field, string $path): self
    {
        $this->builder->jsonExtract($field, $path);
        return $this;
    }

    /**
     * Adds JSON containment check to the query.
     *
     * @param string $field The JSON field.
     * @param mixed $value The value to check for.
     * @param string $path The JSON path.
     * @return self
     */
    public function jsonContains(string $field, $value, string $path = '$'): self
    {
        $this->builder->jsonContains($field, $value, $path);
        return $this;
    }

    /**
     * Adds full-text search to the query.
     *
     * @param string $field The field to search.
     * @param string $searchTerm The search term.
     * @return self
     */
    public function fullTextSearch(string $field, string $searchTerm): self
    {
        $this->builder->fullTextSearch($field, $searchTerm);
        return $this;
    }

    /**
     * Adds string aggregation to the query.
     *
     * @param string $field The field to aggregate.
     * @param string $separator The separator.
     * @param string $alias The alias for the result.
     * @return self
     */
    public function stringAgg(string $field, string $separator = ',', string $alias = ''): self
    {
        $this->builder->stringAgg($field, $separator, $alias);
        return $this;
    }

    /**
     * Adds null coalescing to the query.
     *
     * @param string $field The field to check.
     * @param mixed $defaultValue The default value.
     * @return self
     */
    public function coalesce(string $field, $defaultValue): self
    {
        $this->builder->coalesce($field, $defaultValue);
        return $this;
    }

    /**
     * Adds case-when statement to the query.
     *
     * @param string $field The field to evaluate.
     * @param array $cases The cases array.
     * @param mixed $default The default value.
     * @return self
     */
    public function caseWhen(string $field, array $cases, $default = null): self
    {
        $this->builder->caseWhen($field, $cases, $default);
        return $this;
    }

    /**
     * Adds regex matching to the query.
     *
     * @param string $field The field to match.
     * @param string $pattern The regex pattern.
     * @return self
     */
    public function regexp(string $field, string $pattern): self
    {
        $this->builder->regexp($field, $pattern);
        return $this;
    }

    /**
     * Adds combined limit and offset to the query.
     *
     * @param int $limit The limit.
     * @param int $offset The offset.
     * @return self
     */
    public function limitOffset(int $limit, int $offset = 0): self
    {
        $this->builder->limitOffset($limit, $offset);
        return $this;
    }

    /**
     * Sets the LIMIT clause for the query.
     *
     * @param int|string $limit The limit value.
     * @return self
     */
    public function limit(int|string $limit): self
    {
        $this->builder->limit($limit);
        return $this;
    }

    /**
     * Begins a database transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function beginTransaction(): bool
    {
        try {
            return $this->db->beginTransaction();
        } catch (\PDOException $e) {
            throw DatabaseException::queryError('', 'Failed to begin transaction', [], $e);
        }
    }

    /**
     * Commits the current database transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function commit(): bool
    {
        try {
            return $this->db->endTransaction();
        } catch (\PDOException $e) {
            throw DatabaseException::queryError('', 'Failed to commit transaction', [], $e);
        }
    }

    /**
     * Rolls back the current database transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function rollback(): bool
    {
        try {
            return $this->db->cancelTransaction();
        } catch (\PDOException $e) {
            throw DatabaseException::queryError('', 'Failed to rollback transaction', [], $e);
        }
    }

    /**
     * Executes a callback within a database transaction.
     *
     * @param callable $callback The callback to execute within the transaction.
     * @return mixed The result of the callback.
     * @throws \Exception If the transaction fails.
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Initializes the query cache.
     *
     * @return void
     */
    protected function initializeQueryCache(): void
    {
        try {
            $cacheConfig = [
                'enabled' => true,
                'lifetime' => self::CACHE_LIFETIME,
                'directory' => self::CACHE_DIRECTORY,
                'cacheable_queries' => ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN'],
                'exclude_tables' => [],
                'ignore_on_calc_found_rows' => true
            ];

            $this->queryCache = new QueryCache($cacheConfig);
        } catch (\Exception $e) {
            // Cache initialization failed, continue without caching
            $this->queryCache = null;
        }
    }

    /**
     * Enables or disables query caching for this model instance.
     *
     * @param bool $enabled Whether to enable caching.
     * @return self
     */
    public function enableCache(bool $enabled = true): self
    {
        if ($this->queryCache) {
            // This would require modifying QueryCache to have a disable method
            // For now, we'll just set the instance to null
            if (!$enabled) {
                $this->queryCache = null;
            }
        } elseif ($enabled) {
            $this->initializeQueryCache();
        }

        return $this;
    }

    /**
     * Clears the query cache for this table.
     *
     * @return bool True on success, false on failure.
     */
    public function clearTableCache(): bool
    {
        if ($this->queryCache) {
            return $this->queryCache->clearTableCache($this->table);
        }
        return false;
    }

    /**
     * Clears all cached queries.
     *
     * @return bool True on success, false on failure.
     */
    public function clearAllCache(): bool
    {
        if ($this->queryCache) {
            return $this->queryCache->clear();
        }
        return false;
    }

    // ===== ORM FEATURES =====

    /**
     * Define a hasOne relationship.
     *
     * @param string $relatedModel The related model class.
     * @param string $foreignKey The foreign key in the related table.
     * @param string $localKey The local key in this table.
     * @return mixed The relationship result.
     */
    public function hasOne(string $relatedModel, string $foreignKey = '', string $localKey = ''): mixed
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        $related = new $relatedModel($this->getTableNameFromModel($relatedModel));
        return $related->where($foreignKey, $this->{$localKey})->get(1);
    }

    /**
     * Define a hasMany relationship.
     *
     * @param string $relatedModel The related model class.
     * @param string $foreignKey The foreign key in the related table.
     * @param string $localKey The local key in this table.
     * @return mixed The relationship result.
     */
    public function hasMany(string $relatedModel, string $foreignKey = '', string $localKey = ''): mixed
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        $related = new $relatedModel($this->getTableNameFromModel($relatedModel));
        return $related->where($foreignKey, $this->{$localKey})->get();
    }

    /**
     * Define a belongsTo relationship.
     *
     * @param string $relatedModel The related model class.
     * @param string $foreignKey The foreign key in this table.
     * @param string $ownerKey The owner key in the related table.
     * @return mixed The relationship result.
     */
    public function belongsTo(string $relatedModel, string $foreignKey = '', string $ownerKey = ''): mixed
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $ownerKey = $ownerKey ?: 'id';

        $related = new $relatedModel($this->getTableNameFromModel($relatedModel));
        return $related->where($ownerKey, $this->{$foreignKey})->get(1);
    }

    /**
     * Define a belongsToMany relationship.
     *
     * @param string $relatedModel The related model class.
     * @param string $pivotTable The pivot table name.
     * @param string $foreignKey The foreign key for this model.
     * @param string $relatedKey The foreign key for the related model.
     * @return mixed The relationship result.
     */
    public function belongsToMany(string $relatedModel, string $pivotTable = '', string $foreignKey = '', string $relatedKey = ''): mixed
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $relatedKey = $relatedKey ?: (new $relatedModel($this->getTableNameFromModel($relatedModel)))->getForeignKey();
        $pivotTable = $pivotTable ?: $this->getPivotTableName($relatedModel);

        $related = new $relatedModel($this->getTableNameFromModel($relatedModel));

        // Complex join query for many-to-many
        $query = $related->select("{$related->table}.*")
            ->join($pivotTable, "{$pivotTable}.{$relatedKey} = {$related->table}.{$related->primaryKey}")
            ->where("{$pivotTable}.{$foreignKey}", $this->{$this->primaryKey});

        return $query->get();
    }

    /**
     * Eager load relationships.
     *
     * @param array $relations The relationships to eager load.
     * @return self
     */
    public function with(array $relations): self
    {
        foreach ($relations as $relation) {
            $this->relationships[$relation] = true; // Simple constraint, can be expanded later
        }
        return $this;
    }

    /**
     * Load relationships for the given results.
     *
     * @param array $results The query results.
     * @return array The results with loaded relationships.
     */
    protected function loadRelationships(array $results): array
    {
        if (empty($this->relationships)) {
            return $results;
        }

        foreach ($this->relationships as $relation => $constraints) {
            $results = $this->loadRelationship($results, $relation, $constraints);
        }

        return $results;
    }

    /**
     * Load a specific relationship.
     *
     * @param array $results The query results.
     * @param string $relation The relationship name.
     * @param mixed $constraints The relationship constraints.
     * @return array The results with loaded relationship.
     */
    protected function loadRelationship(array $results, string $relation, $constraints): array
    {
        if (method_exists($this, $relation)) {
            // For now, skip eager loading and just set a placeholder
            // This would need a more sophisticated implementation
            foreach ($results as &$result) {
                if (is_array($result)) {
                    $result[$relation] = 'Relationship loaded (placeholder)';
                }
            }
        }

        return $results;
    }

    /**
     * Add a global scope.
     *
     * @param callable $scope The scope callback.
     * @return void
     */
    public function addGlobalScope(callable $scope): void
    {
        $this->globalScopes[] = $scope;
    }

    /**
     * Apply global scopes to the query.
     *
     * @return self
     */
    protected function applyGlobalScopes(): self
    {
        foreach ($this->globalScopes as $scope) {
            $scope($this);
        }
        return $this;
    }

    /**
     * Apply a scope to the query.
     *
     * @param string $scope The scope method name.
     * @param mixed ...$parameters The scope parameters.
     * @return self
     */
    public function scope(string $scope, ...$parameters): self
    {
        if (method_exists($this, 'scope' . ucfirst($scope))) {
            return $this->{'scope' . ucfirst($scope)}(...$parameters);
        }
        return $this;
    }

    /**
     * Example scope: Active records only.
     *
     * @return self
     */
    public function scopeActive(): self
    {
        return $this->where('active', 1);
    }

    /**
     * Enable soft deletes for this model.
     *
     * @return void
     */
    public function enableSoftDeletes(): void
    {
        $this->softDeletes = true;
    }

    /**
     * Disable soft deletes for this model.
     *
     * @return void
     */
    public function disableSoftDeletes(): void
    {
        $this->softDeletes = false;
    }

    /**
     * Soft delete a record.
     *
     * @return bool True on success, false on failure.
     */
    public function softDelete(): bool
    {
        if (!$this->softDeletes) {
            return $this->delete() !== false;
        }

        return $this->update([$this->deletedAtColumn => date('Y-m-d H:i:s')]) !== false;
    }

    /**
     * Restore a soft deleted record.
     *
     * @return bool True on success, false on failure.
     */
    public function restore(): bool
    {
        if (!$this->softDeletes) {
            return false;
        }

        return $this->update([$this->deletedAtColumn => null]);
    }

    /**
     * Include soft deleted records in queries.
     *
     * @return self
     */
    public function withTrashed(): self
    {
        $this->softDeletes = false;
        return $this;
    }

    /**
     * Only get soft deleted records.
     *
     * @return self
     */
    public function onlyTrashed(): self
    {
        if ($this->softDeletes) {
            return $this->whereNotNull($this->deletedAtColumn);
        }
        return $this;
    }

    /**
     * Cast attributes to specific types.
     *
     * @param array $attributes The attributes to cast.
     * @return array The cast attributes.
     */
    protected function castAttributes(array $attributes): array
    {
        foreach ($this->casts as $attribute => $type) {
            if (isset($attributes[$attribute])) {
                $attributes[$attribute] = $this->castAttribute($attributes[$attribute], $type);
            }
        }
        return $attributes;
    }

    /**
     * Cast a single attribute.
     *
     * @param mixed $value The value to cast.
     * @param string $type The type to cast to.
     * @return mixed The cast value.
     */
    protected function castAttribute($value, string $type)
    {
        return match ($type) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array' => json_decode($value, true),
            'object' => json_decode($value),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get fillable attributes.
     *
     * @param array $attributes The input attributes.
     * @return array The fillable attributes.
     */
    protected function fillableAttributes(array $attributes): array
    {
        if (empty($this->fillable)) {
            return $attributes;
        }

        return array_intersect_key($attributes, array_flip($this->fillable));
    }

    /**
     * Get guarded attributes.
     *
     * @param array $attributes The input attributes.
     * @return array The non-guarded attributes.
     */
    protected function guardedAttributes(array $attributes): array
    {
        if (empty($this->guarded)) {
            return $attributes;
        }

        return array_diff_key($attributes, array_flip($this->guarded));
    }

    /**
     * Hide attributes from serialization.
     *
     * @param array $attributes The attributes to hide from.
     * @return array The visible attributes.
     */
    protected function hideAttributes(array $attributes): array
    {
        if (!empty($this->hidden)) {
            $attributes = array_diff_key($attributes, array_flip($this->hidden));
        }

        if (!empty($this->visible)) {
            $attributes = array_intersect_key($attributes, array_flip($this->visible));
        }

        return $attributes;
    }

    /**
     * Set timestamps on the data.
     *
     * @param array $data The data to set timestamps on.
     * @param bool $update Whether this is an update operation.
     * @return array The data with timestamps.
     */
    protected function setTimestamps(array $data, bool $update = false): array
    {
        if (!$this->timestamps) {
            return $data;
        }

        $now = date('Y-m-d H:i:s');

        if (!$update) {
            $data[$this->createdAtColumn] = $now;
        }

        $data[$this->updatedAtColumn] = $now;

        return $data;
    }

    /**
     * Register a model event.
     *
     * @param string $event The event name.
     * @param callable $callback The event callback.
     * @return void
     */
    public function registerEvent(string $event, callable $callback): void
    {
        $this->events[$event][] = $callback;
    }

    /**
     * Fire a model event.
     *
     * @param string $event The event name.
     * @param mixed $data The event data.
     * @return void
     */
    protected function fireEvent(string $event, $data = null): void
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $callback) {
                $callback($data, $this);
            }
        }
    }

    /**
     * Get accessor value.
     *
     * @param string $key The attribute key.
     * @param mixed $value The attribute value.
     * @return mixed The accessor value.
     */
    protected function getAccessorValue(string $key, $value)
    {
        $method = 'get' . $this->str->studly($key) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }
        return $value;
    }

    /**
     * Set mutator value.
     *
     * @param string $key The attribute key.
     * @param mixed $value The attribute value.
     * @return mixed The mutator value.
     */
    protected function setMutatorValue(string $key, $value)
    {
        $method = 'set' . $this->str->studly($key) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }
        return $value;
    }

    // ===== HELPER METHODS =====

    /**
     * Get the foreign key name for this model.
     *
     * @return string The foreign key name.
     */
    protected function getForeignKey(): string
    {
        return $this->str->snake(basename(str_replace('\\', '/', get_class($this)))) . '_id';
    }

    /**
     * Get table name from model class.
     *
     * @param string $model The model class name.
     * @return string The table name.
     */
    protected function getTableNameFromModel(string $model): string
    {
        return $this->str->snake($this->str->plural(basename(str_replace('\\', '/', $model))));
    }

    /**
     * Get pivot table name for many-to-many relationship.
     *
     * @param string $relatedModel The related model class.
     * @return string The pivot table name.
     */
    protected function getPivotTableName(string $relatedModel): string
    {
        $models = [$this->getTableNameFromModel(get_class($this)), $this->getTableNameFromModel($relatedModel)];
        sort($models);
        return implode('_', $models);
    }

    /**
     * Add a WHERE NOT NULL condition.
     *
     * @param string $field The field to check.
     * @return self
     */
    public function whereNotNull(string $field): self
    {
        return $this->where($field . ' IS NOT NULL');
    }

    /**
     * Override save method to include ORM features.
     *
     * @param array $data The data to save.
     * @param bool $update Whether this is an update operation.
     * @return int|bool The result.
     */
    public function save(array $data, bool $update = false): int|bool
    {
        // Fire before save event
        $this->fireEvent('saving', $data);

        // Validate data before saving if validation is enabled
        if (($update && $this->validateOnUpdate) || (!$update && $this->validateOnSave)) {
            $this->validate($data);
        }

        // Apply fillable/guarded attributes
        $data = $this->fillableAttributes($data);
        $data = $this->guardedAttributes($data);

        // Set mutator values
        foreach ($data as $key => $value) {
            $data[$key] = $this->setMutatorValue($key, $value);
        }

        // Set timestamps
        $data = $this->setTimestamps($data, $update);

        // Include soft delete condition for updates
        if ($update && $this->softDeletes) {
            $this->whereNull($this->deletedAtColumn);
        }

        // Basic save implementation
        if ($update) {
            if (!$this->builder->queryWhere) {
                throw DatabaseException::queryError('', 'WHERE clause is required for update operations');
            }
            $query = $this->builder->update($data)->compile();
        } else {
            if ($this->ignoreDuplicate) {
                $query = $this->builder->insertIgnore($data)->compile();
            } else {
                $query = $this->builder->insert($data)->compile();
            }
        }

        if(strtolower($this->dbconfig->driver) === 'pgsql')
        {
            $this->db->query($query.' RETURNING '. $this->primaryKey);
        }
        else
        {
            $this->db->query($query);
        }

        $this->db->arrayBind($this->builder->binds);

        $this->builder->binds = [];

        try {
            if ($lastId = $this->db->execute()) {
                if ($update) {
                    return $this->db->rowCount();
                }

                return $lastId;
            }
        } catch (\PDOException $e) {
            throw DatabaseException::queryError($query, 'Database save operation failed', [], $e);
        }

        // Fire after save event
        $this->fireEvent($update ? 'updated' : 'created', $data);

        return false;
    }

    /**
     * Override update method to include ORM features.
     *
     * @param array $data The data to update.
     * @return int|bool The result.
     */
    public function update(array $data): int|bool
    {
        // Fire before update event
        $this->fireEvent('updating', $data);

        // Validate data before updating if validation is enabled
        if ($this->validateOnUpdate) {
            $this->validate($data);
        }

        // Apply fillable/guarded attributes
        $data = $this->fillableAttributes($data);
        $data = $this->guardedAttributes($data);

        // Set mutator values
        foreach ($data as $key => $value) {
            $data[$key] = $this->setMutatorValue($key, $value);
        }

        // Set timestamps
        $data = $this->setTimestamps($data, true);

        // Include soft delete condition
        if ($this->softDeletes) {
            $this->whereNull($this->deletedAtColumn);
        }

        // Basic update implementation
        if (!$this->builder->queryWhere && !$this->builder->queryWhereIn && !$this->byPassWhere) {
            throw DatabaseException::queryError('', 'WHERE clause is required for update operations');
        }

        $query = $this->builder->update($data)->compile();

        $this->db->query($query);
        $this->db->arrayBind($this->builder->binds);

        $this->builder->binds = [];

        try {
            if ($this->db->execute()) {
                return $this->db->rowCount();
            }
        } catch (\PDOException $e) {
            throw DatabaseException::queryError($query, 'Database update operation failed', [], $e);
        }

        // Fire after update event
        $this->fireEvent('updated', $data);

        return false;
    }

    /**
     * Override delete method to include ORM features.
     *
     * @return int|bool The result.
     */
    public function delete(): int|bool
    {
        // Fire before delete event
        $this->fireEvent('deleting');

        if ($this->softDeletes) {
            $result = $this->softDelete();
            $event = 'softDeleted';
        } else {
            // Basic delete implementation
            if (!$this->builder->queryWhere && !$this->builder->queryWhereIn) {
                throw DatabaseException::queryError('', 'WHERE clause is required for delete operations');
            }

            $query = $this->builder->delete()->compile();
            $this->db->query($query);
            $this->db->arrayBind($this->builder->binds);
            $this->builder->binds = [];

            try {
                if ($this->db->execute()) {
                    $result = $this->db->rowCount();
                }
            } catch (\PDOException $e) {
                throw DatabaseException::queryError($query, 'Database delete operation failed', [], $e);
            }
            $event = 'deleted';
        }

        // Fire after delete event
        $this->fireEvent($event);

        return $result;
    }

    /**
     * Override get method to include ORM features.
     *
     * @param int|string $limit The limit.
     * @param int $offset The offset.
     * @return array|object|bool The result.
     */
    public function get(int|string $limit = 'all', int $offset = 0): array|object|bool
    {
        // Apply global scopes
        $this->applyGlobalScopes();

        // Include soft delete condition
        if ($this->softDeletes) {
            $this->whereNull($this->deletedAtColumn);
        }

        // Call the original get method implementation
        $result = $this->executeGet($limit, $offset);

        if ($result !== false && $result !== null) {
            if ($limit == 1 && is_array($result) && isset($result['id'])) {
                // Single record returned as associative array
                $result = $this->processSingleRecord($result);
            } elseif (is_array($result)) {
                // Multiple records returned as array of arrays
                $result = $this->processMultipleRecords($result);
            }
        }

        return $result;
    }

    /**
     * Execute the basic get query without ORM features.
     *
     * @param int|string $limit The limit.
     * @param int $offset The offset.
     * @return array|object|bool The result.
     */
    protected function executeGet(int|string $limit = 'all', int $offset = 0): array|object|bool
    {
		if(!empty($limit) || !empty($offset)){
			if ($limit != 'all'){
				$this->builder->limit($limit);
				if ($offset >= 0){
					$this->builder->offset($offset);
				}
			}
		}

		if(!empty($this->builder->querySelect))	{
			$this->fields = str_ireplace('select','',$this->builder->querySelect);
		}

        $query = $this->builder->select($this->fields)->compile(true);

        // Check cache first for SELECT queries
        if ($this->queryCache && $this->queryCache->shouldCacheQuery($query)) {
            $cacheKey = $this->queryCache->generateKey($query, $this->builder->binds);

            if ($this->queryCache->hasValidCache($cacheKey)) {
                $cachedResult = $this->queryCache->getCachedResult($cacheKey);
                // Cache exists and was successfully retrieved
                $this->builder->binds = [];
                $this->fields = '*';

                if ($cachedResult !== null && $cachedResult !== false) {
                    if ($limit == 1) {
                        // Check if cached result is already a single record (has 'id' key)
                        if (is_array($cachedResult) && isset($cachedResult['id'])) {
                            return $cachedResult;
                        }
                        // Otherwise, assume it's an array of records and take the first one
                        return is_array($cachedResult) && !empty($cachedResult) ? $cachedResult[0] : $cachedResult;
                    }

                    return $this->returnType === 'object' ? (object)$cachedResult : $cachedResult;
                }
                // Cache is invalid or empty, continue with database query
            }
        }

        $this->db->query($query);
        $this->db->arrayBind($this->builder->binds);

        try {
            if ($limit == 1) {
                $result = $this->db->single();
            } else {
                $result = $this->db->result($this->returnType);
            }

        } catch (\PDOException $e) {
            // Cache the failed result (false) for failed queries too
            if ($this->queryCache && $this->queryCache->shouldCacheQuery($query)) {
                $cacheKey = $this->queryCache->generateKey($query, $this->builder->binds);
                $this->queryCache->storeResult($cacheKey, false);
            }
            throw DatabaseException::queryError($query, 'Database query execution failed', [], $e);
        }

        // Cache the result if caching is enabled and this is a cacheable query
        if ($this->queryCache && $this->queryCache->shouldCacheQuery($query)) {
            $cacheKey = $this->queryCache->generateKey($query, $this->builder->binds);
            $this->queryCache->storeResult($cacheKey, $result);
        }

        $this->builder->binds = [];
		$this->fields = '*';

        return $result;
    }

    /**
     * Process a single record with ORM features.
     *
     * @param array $record The record to process.
     * @return array The processed record.
     */
    protected function processSingleRecord(array $record): array
    {
        // Cast attributes
        $record = $this->castAttributes($record);

        // Apply accessors
        foreach ($record as $key => $value) {
            $record[$key] = $this->getAccessorValue($key, $value);
        }

        // Hide attributes
        $record = $this->hideAttributes($record);

        // Load relationships
        $record = $this->loadRelationships([$record])[0];

        return $record;
    }

    /**
     * Process multiple records with ORM features.
     *
     * @param array $records The records to process.
     * @return array The processed records.
     */
    protected function processMultipleRecords(array $records): array
    {
        // Cast attributes
        $records = array_map([$this, 'castAttributes'], $records);

        // Apply accessors
        $records = array_map(function ($item) {
            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    $item[$key] = $this->getAccessorValue($key, $value);
                }
            }
            return $item;
        }, $records);

        // Hide attributes
        $records = array_map([$this, 'hideAttributes'], $records);

        // Load relationships
        $records = $this->loadRelationships($records);

        return $records;
    }

    /**
     * Add a WHERE NULL condition.
     *
     * @param string $field The field to check.
     * @return self
     */
    public function whereNull(string $field): self
    {
        return $this->where($field . ' IS NULL');
    }

    // ===== VALIDATION METHODS =====

    /**
     * Set validation rules for the model.
     *
     * @param array $rules The validation rules.
     * @return self
     */
    public function setValidationRules(array $rules): self
    {
        $this->validationRules = $rules;
        return $this;
    }

    /**
     * Set custom validation messages for the model.
     *
     * @param array $messages The custom validation messages.
     * @return self
     */
    public function setValidationMessages(array $messages): self
    {
        $this->validationMessages = $messages;
        return $this;
    }

    /**
     * Enable or disable validation on save operations.
     *
     * @param bool $enable Whether to enable validation on save.
     * @return self
     */
    public function validateOnSave(bool $enable = true): self
    {
        $this->validateOnSave = $enable;
        return $this;
    }

    /**
     * Enable or disable validation on update operations.
     *
     * @param bool $enable Whether to enable validation on update.
     * @return self
     */
    public function validateOnUpdate(bool $enable = true): self
    {
        $this->validateOnUpdate = $enable;
        return $this;
    }

    /**
     * Validate data against the model's validation rules.
     *
     * @param array $data The data to validate.
     * @return bool True if validation passes, false otherwise.
     * @throws ValidationException If validation fails.
     */
    public function validate(array $data): bool
    {
        if (empty($this->validationRules)) {
            return true; // No rules defined, validation passes
        }

        $validator = new Validator();
        $this->addValidationRulesToValidator($validator, $data);

        return $this->performValidation($validator, $data);
    }

    /**
     * Add validation rules to the validator instance.
     *
     * @param Validator $validator The validator instance.
     * @param array $data The data being validated.
     * @return void
     */
    protected function addValidationRulesToValidator(Validator $validator, array $data): void
    {
        foreach ($this->validationRules as $field => $rules) {
            if (!$this->shouldValidateField($field, $rules, $data)) {
                continue;
            }

            $this->addFieldRulesToValidator($validator, $field, $rules);
        }
    }

    /**
     * Determine if a field should be validated.
     *
     * @param string $field The field name.
     * @param string|array $rules The validation rules for the field.
     * @param array $data The data being validated.
     * @return bool True if the field should be validated.
     */
    protected function shouldValidateField(string $field, string|array $rules, array $data): bool
    {
        $hasRequiredRule = $this->fieldHasRequiredRule($rules);

        // Skip validation for this field if it's not in data and not required
        return array_key_exists($field, $data) || $hasRequiredRule;
    }

    /**
     * Check if a field has a required rule.
     *
     * @param string|array $rules The validation rules.
     * @return bool True if the field has a required rule.
     */
    protected function fieldHasRequiredRule(string|array $rules): bool
    {
        $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;

        foreach ($fieldRules as $rule) {
            $ruleName = is_string($rule) ? explode(':', $rule, 2)[0] : $rule[0];
            if ($ruleName === 'required') {
                return true;
            }
        }

        return false;
    }

    /**
     * Add rules for a specific field to the validator.
     *
     * @param Validator $validator The validator instance.
     * @param string $field The field name.
     * @param string|array $rules The validation rules.
     * @return void
     */
    protected function addFieldRulesToValidator(Validator $validator, string $field, string|array $rules): void
    {
        $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;

        foreach ($fieldRules as $rule) {
            if (is_string($rule)) {
                $this->addStringRuleToValidator($validator, $field, $rule);
            } elseif (is_array($rule)) {
                $validator->rule($field, $rule[0], ...($rule[1] ?? []));
            }
        }
    }

    /**
     * Add a string-based rule to the validator.
     *
     * @param Validator $validator The validator instance.
     * @param string $field The field name.
     * @param string $rule The rule string (e.g., 'required', 'minLength:8').
     * @return void
     */
    protected function addStringRuleToValidator(Validator $validator, string $field, string $rule): void
    {
        // Parse rule like 'required', 'email', 'minLength:8', 'range:18:65'
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $ruleArgs = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

        // Convert string arguments to appropriate types
        $ruleArgs = $this->castRuleArguments($ruleName, $ruleArgs);

        // Special handling for 'in' rule which expects an array as second argument
        if ($ruleName === 'in') {
            $validator->rule($field, $ruleName, $ruleArgs);
        } else {
            $validator->rule($field, $ruleName, ...$ruleArgs);
        }
    }

    /**
     * Perform the actual validation and handle errors.
     *
     * @param Validator $validator The validator instance.
     * @param array $data The data being validated.
     * @return bool True if validation passes.
     * @throws ValidationException If validation fails.
     */
    protected function performValidation(Validator $validator, array $data): bool
    {
        try {
            if (!$validator->validate($data)) {
                $errors = $validator->errors();
                $errors = $this->applyCustomValidationMessages($errors);
                throw new ValidationException('Validation failed: ' . json_encode($errors));
            }
        } catch (\TypeError $e) {
            throw new ValidationException('Validation failed: Type error during validation - ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Apply custom validation messages to errors.
     *
     * @param array $errors The validation errors.
     * @return array The errors with custom messages applied.
     */
    protected function applyCustomValidationMessages(array $errors): array
    {
        if (empty($this->validationMessages)) {
            return $errors;
        }

        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $key => $error) {
                $ruleKey = $this->extractRuleFromError($error);
                $customKey = $field . '.' . $ruleKey;

                if (isset($this->validationMessages[$customKey])) {
                    $errors[$field][$key] = $this->validationMessages[$customKey];
                } elseif (isset($this->validationMessages[$field])) {
                    $errors[$field][$key] = $this->validationMessages[$field];
                }
            }
        }

        return $errors;
    }

    /**
     * Cast rule arguments to appropriate types based on the rule name.
     *
     * @param string $ruleName The name of the validation rule.
     * @param array $args The arguments to cast.
     * @return array The cast arguments.
     */
    protected function castRuleArguments(string $ruleName, array $args): array
    {
        // Define which rules expect integer arguments
        $intRules = ['length', 'minLength', 'maxLength', 'range'];
        $floatRules = ['range']; // range can accept floats too

        if (in_array($ruleName, $intRules)) {
            return array_map('intval', $args);
        }

        if (in_array($ruleName, $floatRules)) {
            // For range rule, convert to appropriate numeric types
            return array_map(function($arg) {
                return is_numeric($arg) ? (strpos($arg, '.') !== false ? floatval($arg) : intval($arg)) : $arg;
            }, $args);
        }

        // For 'in' rule, keep as strings but trim whitespace
        if ($ruleName === 'in') {
            return array_map('trim', $args);
        }

        // Default: return as strings
        return $args;
    }

    /**
     * Extract the validation rule name from an error message.
     *
     * @param string $error The error message.
     * @return string The rule name.
     */
    protected function extractRuleFromError(string $error): string
    {
        // Error format: "The field is invalid for rule_name rule"
        if (preg_match('/invalid for (\w+) rule/', $error, $matches)) {
            return $matches[1];
        }
        return 'unknown';
    }

    /**
     * Get the model's validation rules.
     *
     * @return array The validation rules.
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * Get the model's validation messages.
     *
     * @return array The validation messages.
     */
    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }
}
