<?php

declare(strict_types=1);

namespace Core;
use Core\Database as Database;
use Core\Config as Config;
use Core\Database\Builders\Builders as Builders;

/**
 * Class Model
 *
 * Represents a database model for interacting with a specific database table.
 *
 * @package Core
 * @author  Prima Yoga
 * @version 1.0.1
 */
class Model
{
    public Database\Connection $db;
    protected string $table;
    protected object $dbconfig;
    protected string $fields = '*';
    protected string $returnType = 'array';
    protected bool $ignoreDuplicate = false;
	protected object $builder;
	protected object $str;
	
	public bool $byPassWhere = false;
	public string $primaryKey = 'id';

    /**
     * Initializes the model with the specified table and database.
     *
     * @param string $table The name of the table.
     * @param string $database The name of the database configuration to use.
     */
    public function __construct(string $table, string $database = 'default')
    {
        $this->dbconfig = $this->setDatabase($database);
        
        if (empty($this->dbconfig->driver) || empty($this->dbconfig->host) || empty($this->dbconfig->port) || empty($this->dbconfig->dbname) || empty($this->dbconfig->user) || empty($this->dbconfig->password)) {
            return false;
        }

		try{
			// set error_reporting off to prevent leaked database information regardless of environment
			error_reporting(0);
			
			$this->db = new Database\Connection(
				$this->dbconfig->driver,
				$this->dbconfig->host,
				$this->dbconfig->port,
				$this->dbconfig->dbname,
				$this->dbconfig->user,
				$this->dbconfig->password
			);
		}
		catch(\Exception $e)
		{
			exit('Database connection couldn\'t be estabilished.');
        }
		finally{
			error_reporting(-1);
			$this->table = $this->dbconfig->prefix . $table;
			
			if(isset($this->db)) // make sure the object exists
			{
                $builders = new Builders($this->dbconfig->driver, $table);
                $this->builder = $builders->builders;
                unset($builders);
				$this->str = new Text\Str;
				
				//set default primaryKey
				$this->setPrimaryKey();
			}
			else
			{
				return '';
			}
		}		
    }
    
    /**
     * Selects the database configuration.
     *
     * @param string $database The name of the database.
     * @return object The database configuration object.
     */
    public function setDatabase(string $database): object
    {
		$config = new Config;
        $config = $config->get()->database;

        return $config->{$database};
    }

    /**
     * Allows fields to be set before executing get().
     *
     * @param array|string $fields Field name, or an array of field/value pairs.
     * @return self
     */
    public function setFields(array|string $fields): self
    {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        foreach ($fields as $v) {
            if ('*' == $this->fields) {
                $this->fields = "{$v},";
            } else {
                $this->fields .= "{$v},";
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
     * Executes the query and retrieves the results.
     *
     * @param int|string $limit The maximum number of records to return or 'all' for no limit.
     * @param int $offset The number of records to skip.
     * @return array|object|bool The retrieved records, or false on failure.
     */
    public function get(int|string $limit = 'all', int $offset = 0): array|object|bool
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
        
        $this->db->query($query);
        $this->db->arrayBind($this->builder->binds);
        $this->builder->binds = [];
		$this->fields = '*';
        
        if ($limit == 1) {
            return $this->db->single();
        }
        
        return $this->db->result($this->returnType);
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
     * Saves a record to the database.
     *
     * @param array $data The data to save.
     * @param bool $update Indicates whether to update an existing record.
     * @return int|bool The last inserted ID or the number of affected rows, or false on failure.
     */
    public function save(array $data, bool $update = false): int|bool
    {
        if ($update) {
            if (!$this->builder->queryWhere) {
                die('Where is not defined, process terminated.');
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
			$this->primaryKey = '';
			$this->db->query($query);
		}

        $this->db->arrayBind($this->builder->binds);

        $this->builder->binds = [];
        
        if ($lastId = $this->db->execute()) {
            if ($update) {
                return $this->db->rowCount();
            }
			
			return $lastId;
        }

        return false;
    }
	
	/**
     * Updates a record in the database.
     *
     * @param array $data The data to update.
     * @return int|bool The number of affected rows, or false on failure.
     */
	public function update(array $data): int|bool
	{
		if (!$this->builder->queryWhere && !$this->builder->queryWhereIn && !$this->byPassWhere) {
                die('Where is not defined, process terminated.');
		}
		
		$query = $this->builder->update($data)->compile();
		
		$this->db->query($query);
        $this->db->arrayBind($this->builder->binds);
		
		$this->builder->binds = [];
		
		if ($this->db->execute()) {
            return $this->db->rowCount();
        }
		
		return false;
	}

    /**
     * Deletes a record from the database.
     *
     * @return int|bool The number of affected rows, or false on failure.
     */
    public function delete(): int|bool
    {
		if (!$this->builder->queryWhere && !$this->builder->queryWhereIn) {
                die('Where is not defined, process terminated.');
		}
		
        $query = $this->builder->delete()->compile();
        $this->db->query($query);
        $this->db->arrayBind($this->builder->binds);
        $this->builder->binds = [];
            
        if ($this->db->execute()) {
            return $this->db->rowCount();
        }

		return false;
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
     * Inserts multiple records into the database.
     *
     * @param array $data The data to insert. Each element should be an associative array representing a record.
     * @return self
     * @throws \Exception If the insert operation fails.
     */
    public function insertBatch(array $data): self 
    {
        if (empty($data)) {
            throw new \Exception("No data provided for batch insert.");
        }

        return $this->builder->insertBatch($data);
    }

    /**
     * The "builder" function.
     *
     * get builder instance.
     *
     * @return Database\Builder
     */
    public function builder() : Builders
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
}	
