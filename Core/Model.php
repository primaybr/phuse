<?php

declare(strict_types=1);

namespace Core;
use Core\Database as Database;
use Core\Config as Config;

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

    public function __construct(string $table, string $database = 'default')
    {
        $this->dbconfig = $this->setDatabase($database);
		
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
			exit($e->getMessage());
        }
		finally{
			error_reporting(-1);
			$this->table = $this->dbconfig->prefix . $table;
		
			$builder = "Core\Database\Builders\\" . $this->db->getDrivers($this->dbconfig->driver);
			
			$this->builder = new $builder($this->table);
			
			$this->str = new Text\Str;
			
			//set default primaryKey
			$this->setPrimaryKey();
		
		}		
    }
    
    /**
     * The "setDatabase" function.
     *
     * select database configuration
     *
     * @param string $database Database name
     *
     * @return object
     */
    public function setDatabase(string $database): object
    {
		$config = new Config;
        $config = $config->get()->database;

        return $config->{$database};
    }

    /**
     * The "setFields" function.
     *
     * Allows fields to be set before executing get()
     *
     * @param array|string $fields Field name, or an array of field/value pairs
     *
     * @return Model
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
	
	public function setPrimaryKey(string $primaryKey = ''): self
	{
		$this->primaryKey = empty($primaryKey) ? $this->primaryKey : $primaryKey;
		
		return $this;
	}
	
	public function select(string|array $fields = '*'): self
	{
		$this->builder->select($fields);
		
		return $this;
	}
	
    
    public function where(string $key = '', string|int $value = '', string $type = '='): self
    {
        $this->builder->where($key, $value, $type);
        
        return $this;
    }
	
	public function whereIn(array $data = [], bool $not = false): self
    {
        $this->builder->whereIn($data,$not);
        
        return $this;
    }
	
	public function orWhere(string $key = '', string $value = '', string $type = '='): self
    {
        $this->builder->orWhere($key, $value, $type);
        
        return $this;
    }
	
	public function whereQuery(string $query): self
    {
        $this->builder->whereQuery($query);
        
        return $this;
    }
	
	public function join(string $table, string $cond, string $type): self
	{
		$this->builder->join($table,$cond,$type);
		
		return $this;
	}
	
	public function orderBy(string $key, string $order): self
	{
		$this->builder->orderBy($key,$order);
		
		return $this;
	}
	
	public function groupBy(string $groupby): self
	{
		$this->builder->groupBy($groupby);
		
		return $this;
	}		

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

    public function asArray(): self
    {
        $this->returnType = 'array';

        return $this;
    }

    public function asObject(): self
    {
        $this->returnType = 'object';

        return $this;
    }
    
    public function ignoreDuplicate(): self
    {
        $this->ignoreDuplicate = true;
        
        return $this;
    }

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
	
	public function resetQuery() :self
	{
		$this->builder->resetQuery();
		
		return $this;
	}

    /**
     * The "builder" function.
     *
     * get builder instance.
     *
     * @return Database\Builder
     */
    public function builder() : Builder
    {
        return $this->builder ??= Builder($this->table);
    }

}
