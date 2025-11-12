<?php
namespace Core\Database\Builders;
	
/**
 * Trait BuildersTrait
 *
 * This trait provides methods for building SQL queries in a fluent interface style.
 */
trait BuildersTrait {
	
	//SQL default operators
	protected array $operators = ['+','-','*','/','%','&','|','^', // Arithmetic & Bitwise 
								  '=','>','<','>=','<=','<>','+=','-=','*=','/=','%=','&=','^-=','|*=', // Comparison & Compound
								  'ALL','AND','ANY','BETWEEN','EXISTS','IN','LIKE','NOT','OR','SOME','IS' // Logical
								 ];
	
	public array $binds = [];
    public string $querySelect = '';
    public string $queryFrom = '';
    public string $queryWhere = '';
    public string $queryLimit = '';
    public string $queryOffset = '';
    public string $queryWhereIn = '';
    public string $queryJoin = '';
    public string $queryInsert = '';
    public string $queryUpdate = '';
    public string $queryDelete = '';
    public string $queryOrderBy = '';
    public string $queryGroupBy = '';
    protected string $table = '';
    protected array $queryCache = []; // Array to store cached queries

    /**
     * Generates a unique cache key based on the query and parameters.
     *
     * @param string $query The SQL query.
     * @param array $params The query parameters.
     * @return string The cache key.
     */
    protected function generateCacheKey(string $query, array $params): string {
        return md5($query . serialize($params)); // Create a unique key based on the query and parameters
    }

    /**
     * Sets the SELECT clause for the query.
     *
     * @param string|array $fields The fields to select.
     * @return self
     */
    public function select(string|array $fields = '*'): self
    {
        if (is_string($fields)) {
            $this->querySelect = 'SELECT '.$fields;
        } elseif (is_array($fields)) {
            // Use implode to join array elements
            $this->querySelect = 'SELECT '.implode(',', $fields);
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($this->querySelect, $this->binds);

        // Check if result is cached
        if (isset($this->queryCache[$cacheKey])) {
            return $this->queryCache[$cacheKey]; // Return cached result
        }

        return $this;
    }

    /**
     * Sets the INSERT clause for the query.
     *
     * @param array $data The data to insert.
     * @return self
     */
    public function insert(array $data): self
    {
        if ($data) {
            $field_data = '';
            $value_data = '';

            foreach ($data as $k => $v) {
                $field_data .= $k.',';
                $value_data .= ':'.$k.''.',';
                $this->binds[$k] = $v;
            }

            $field_data = rtrim($field_data, ',');
            $value_data = rtrim($value_data, ',');

            $this->queryInsert = "INSERT INTO {$this->table} ({$field_data}) VALUES ({$value_data})";
        }

        return $this;
    }
    
    /**
     * Sets the INSERT IGNORE clause for the query.
     *
     * @param array $data The data to insert.
     * @return self
     */
    public function insertIgnore(array $data): self
    {
        $this->insert($data);

        // Use str_contains to check for substring
        if (str_contains($this->queryInsert, "INTO")) {
            $this->queryInsert = str_replace("INTO", "IGNORE INTO", $this->queryInsert);
        }
        
        return $this;
    }

    /**
     * Sets the UPDATE clause for the query.
     *
     * @param array $data The data to update.
     * @return self
     */
    public function update(array $data): self
    {
        if ($data) {
            $field_data = '';
            $count = 1;
            foreach ($data as $k => $v) {
                if(isset($this->binds[$k]))
                {
                    $field_data .= "{$k}=:{$k}{$count}".',';
                    $this->binds[$k.$count] = $v;
                    $count++;
                }
                else
                {
                    $field_data .= "{$k}=:{$k}".',';
                    $this->binds[$k] = $v;
                }
               
            }

            $field_data = rtrim($field_data, ',');

            $this->queryUpdate = "UPDATE {$this->table} SET {$field_data}";
        }

        return $this;
    }

    /**
     * Sets the DELETE clause for the query.
     *
     * @return self
     */
    public function delete(): self
    {		
		// Prioritize 'WHERE IN' sql statement if found
		$where = !empty($this->queryWhereIn) ? $this->queryWhereIn : $this->queryWhere;

        $this->queryDelete = "DELETE FROM {$this->table} $where";

        return $this;
    }
	
	/**
     * Sets the MONTH condition for the query.
     *
     * @param string $field The field to apply the condition to.
     * @param int $value The value to compare with.
     * @return self
     */
	public function month(string $field, int $value): self
	{

		if (!empty($this->queryWhere)) {
            $this->queryWhere = $this->queryWhere." AND MONTH({$field}) = {$value}";
        }
		else{
			$this->queryWhere = " WHERE MONTH({$field}) = '{$value}'";
		}

        return $this;
	}
	
	/**
     * Sets the YEAR condition for the query.
     *
     * @param string $field The field to apply the condition to.
     * @param int $value The value to compare with.
     * @return self
     */
	public function year(string $field, int $value): self
	{
		if (!empty($this->queryWhere)) {
            $this->queryWhere = $this->queryWhere." AND YEAR({$field}) = {$value}";
        }
		else{
			$this->queryWhere = " WHERE YEAR({$field}) = '{$value}'";
		}

        return $this;
	}
	
	/**
     * Sets the DAY condition for the query.
     *
     * @param string $field The field to apply the condition to.
     * @param int $value The value to compare with.
     * @return self
     */
	public function day(string $field, int $value): self
	{
		if (!empty($this->queryWhere)) {
            $this->queryWhere = $this->queryWhere." AND DAY({$field}) = {$value}";
        }
		else{
			$this->queryWhere = " WHERE DAY({$field}) = '{$value}'";
		}

        return $this;
	}

    /**
     * Sets the MAX aggregation for the query.
     *
     * @param string $field The field to apply the aggregation to.
     * @param string $alias The alias for the aggregated field.
     * @return self
     */
    public function max(string $field, string $alias = ''): self
    {
        if (!empty($alias)) {
            $alias = " AS {$alias}";
        } else {
            $alias = " AS {$field}";
        }

        $this->querySelect .= " MAX({$field}) {$alias}";

        return $this;
    }

    /**
     * Sets the MIN aggregation for the query.
     *
     * @param string $field The field to apply the aggregation to.
     * @param string $alias The alias for the aggregated field.
     * @return self
     */
    public function min(string $field, string $alias = ''): self
    {
        if (!empty($alias)) {
            $alias = " AS {$alias}";
        } else {
            $alias = " AS {$field}";
        }

        $this->querySelect .= " MIN({$field}) {$alias}";

        return $this;
    }

    /**
     * Sets the COUNT aggregation for the query.
     *
     * @param string $field The field to apply the aggregation to.
     * @return self
     */
    public function count(string $field = '*'): self
    {
        $this->querySelect .= " COUNT({$field})";

        return $this;
    }

    /**
     * Sets the FROM clause for the query.
     *
     * @param string|array $table The table(s) to select from.
     * @return self
     */
    public function from(string|array $table = ''): self
    {
        $from = ' FROM ';

        if (is_array($table)) {
            // Use implode to join array elements
            $result = $from.implode(',', $table);
        } else {
            $result = $from.$table;
        }

        $this->queryFrom = $result;

        return $this;
    }

    /**
     * Sets the WHERE clause for the query.
     *
     * @param string $key The field to apply the condition to.
     * @param string|int $value The value to compare with.
     * @param string $operator The operator to use for the condition.
     * @param string $clause The clause to use for the condition (AND or OR).
     * @return self
     */
    public function where(string $key = '', string|int $value = '', string $operator = '=', string $clause = 'AND'): self
    {
		if(in_array($value,$this->operators))
		{
			$newValue = $operator;
			$operator = $value;
			$value = $newValue;
		}
		
        $where = ' WHERE ';
        $operator = strtoupper(trim($operator));
		
        $replacer = str_replace('.', '_', $key);
        
        if ($operator == "BETWEEN" || $operator == "IS") {
            $query = "{$key} {$operator} {$value}";
        } else {
            $query = "{$key} {$operator} :{$replacer}";
            $this->binds[$replacer] = $value;
        }
        
        $result = $where.$query;

        if (!empty($this->queryWhere)) {
            $result = $this->queryWhere." $clause ".$query;
        }

        $this->queryWhere = $result;

        return $this;
    }
	
	/**
     * Sets the OR WHERE clause for the query.
     *
     * @param string $key The field to apply the condition to.
     * @param string $operator The operator to use for the condition.
     * @param string|int $value The value to compare with.
     * @return self
     */
	public function orWhere(string $key, string $operator, string|int $value): self
    {
		if (!empty($this->queryWhere)) {
			$this->where($key,$operator,$value,'OR');
        }
		else
		{
			$this->where($key,$operator,$value);
		}
		return $this;
    }
	
	/**
     * Sets the WHERE clause for the query using a raw query.
     *
     * @param string $query The raw query to use.
     * @return self
     */
	public function whereQuery(string $query): self
	{
		$where = ' WHERE ';
		$result = $where.$query;

        if (!empty($this->queryWhere)) {
            $result = $this->queryWhere." AND ".$query;
        }
		
		$this->queryWhere = $result;
		
		return $this;
	}

    /**
     * Sets the WHERE IN clause for the query.
     *
     * @param array $data The data to use for the IN condition.
     * @param bool $not Indicates whether to use NOT IN instead of IN.
     * @return self
     */
    public function whereIn(array $data = [], bool $not = false): self
    {
        $whereIn = ' WHERE ';
        if ($data) {
            $inString = $result = '';

            foreach ($data as $key => $value) {
				
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
				
                $inValue = '';
				$replacer = str_replace('.', '_', $key);
                foreach ($value as $val) {
                    $inValue .= ":{$replacer}{$val},";
                    $this->binds[$replacer.$val] = $val;
                }

                $inValue = rtrim($inValue, ',');

                if ($not) {
                    $inString .= "{$key} NOT IN ({$inValue}) AND ";
                } else {
                    $inString .= "{$key} IN ({$inValue}) AND ";
                }
            }

            $result = $inString;

            if (!empty($this->queryWhereIn)) {
                $result = $inString.$this->queryWhereIn;
            }

            $result = $whereIn.$result;
            $this->queryWhereIn = rtrim($result, ' AND ');
        }

        return $this;
    }

    /**
     * Sets the OFFSET clause for the query.
     *
     * @param int $offset The number of records to skip before starting to return records.
     * @return self
     */
    public function offset(int $offset = 0): self
    {
        $this->queryOffset = " OFFSET $offset";
        
        return $this;
    }

    /**
     * Sets the LIMIT clause for the query.
     *
     * @param int|string $limit The maximum number of records to return.
     * @return self
     */
    public function limit(int|string $limit = 0): self
    {
        $this->queryLimit = ' LIMIT ' . (int)$limit; // Ensure limit is an integer
        return $this;
    }

    /**
     * Sets the JOIN clause for the query.
     *
     * @param string $table The table to join with.
     * @param string $cond The condition for the join.
     * @param string $type The type of join (e.g. INNER, LEFT, RIGHT).
     * @return self
     */
    public function join(string $table, string $cond, string $type): self
    {
        if ('' !== $type) {
            $type = strtoupper(trim($type));

            if (!in_array($type, ['LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'], true)) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }
		
		if (!empty($this->queryJoin)) {
			$this->queryJoin = $this->queryJoin.' '.$type.' JOIN '.$table.' ON '.$cond;
		}
		else{
			 $this->queryJoin = ' '.$type.' JOIN '.$table.' ON '.$cond;
		}

        return $this;
    }

    /**
     * Sets the ORDER BY clause for the query.
     *
     * @param string $order_by The field to order by.
     * @param string $order The order direction (ASC or DESC).
     * @return self
     */
    public function orderBy(string $order_by, string $order): self
    {
        $this->queryOrderBy = " ORDER BY $order_by $order";

        return $this;
    }
    
    /**
     * Sets the GROUP BY clause for the query.
     *
     * @param string $groupby The field to group by.
     * @return self
     */
    public function groupBy(string $groupby): self
    {
        $this->queryGroupBy = " GROUP BY $groupby";
        
        return $this;
    }

    /**
     * Sets the SUM aggregation for the query.
     *
     * @param string $field The field to apply the aggregation to.
     * @param string $alias The alias for the aggregated field.
     * @return self
     */
    public function sum(string $field, string $alias = ''): self
    {
        if (!empty($alias)) {
            $alias = " AS {$alias}";
        } else {
            $alias = " AS {$field}";
        }

        $this->querySelect .= ", SUM({$field}) {$alias}";

        return $this;
    }

    /**
     * Sets the AVG aggregation for the query.
     *
     * @param string $field The field to apply the aggregation to.
     * @param string $alias The alias for the aggregated field.
     * @return self
     */
    public function avg(string $field, string $alias = ''): self
    {
        if (!empty($alias)) {
            $alias = " AS {$alias}";
        } else {
            $alias = " AS {$field}";
        }

        $this->querySelect .= ", AVG({$field}) {$alias}";

        return $this;
    }

    /**
     * Sets the HAVING clause for the query.
     *
     * @param string $condition The condition for the HAVING clause.
     * @return self
     */
    public function having(string $condition): self
    {
        $this->queryWhere .= " HAVING $condition";

        return $this;
    }

    /**
     * Sets a subquery for the query.
     *
     * @param string $query The subquery to use.
     * @param string $alias The alias for the subquery.
     * @return self
     */
    public function subquery(string $query, string $alias): self
    {
        $this->querySelect .= ", ($query) AS $alias";

        return $this;
    }

    /**
     * Resets the query parameters based on the specified part.
     *
     * @param string $part The part of the query to reset. Options include 'select', 'where', 'insert', etc.
     * @return self
     */
    public function resetQuery(string $part = ''): self {
        match ($part) {
            'select' => $this->querySelect = '',
            'where' => $this->queryWhere = '',
            'wherein' => $this->queryWhereIn = '',
            'from' => $this->queryFrom = '',
            'join' => $this->queryJoin = '',
            'insert' => $this->queryInsert = '',
            'update' => $this->queryUpdate = '',
            'delete' => $this->queryDelete = '',
            'orderby' => $this->queryOrderBy = '',
            'groupby' => $this->queryGroupBy = '',
            'limit' => $this->queryLimit = '',
            'offset' => $this->queryOffset = '',
            default => 
                // Reset all
                $this->querySelect = 
                $this->queryWhere = 
                $this->queryInsert = 
                $this->queryUpdate = 
                $this->queryDelete = 
                $this->queryOrderBy = 
                $this->queryGroupBy = 
                $this->queryLimit = 
                $this->queryOffset = 
                $this->queryJoin = 
                $this->queryWhereIn = ''
            
        };

        return $this;
    }

    /**
     * Compiles the SQL query based on the set parameters.
     *
     * @param bool $reset Indicates whether to reset the query after compilation.
     * @return string The compiled SQL query.
     */
    public function compile(bool $reset = true): string
	{
        if (!empty($this->querySelect)) {
			if(!empty($this->queryWhere) && !empty($this->queryWhereIn))
			{
				$this->queryWhereIn = str_replace('WHERE', 'AND',$this->queryWhereIn);
			}
			
            $sql = $this->querySelect.$this->queryFrom.$this->queryJoin.$this->queryWhere.$this->queryWhereIn.$this->queryGroupBy.$this->queryOrderBy.$this->queryLimit.$this->queryOffset;
        } elseif (!empty($this->queryInsert)) {
            $sql = $this->queryInsert;
        } elseif (!empty($this->queryUpdate)) {
            $sql = $this->queryUpdate.$this->queryWhere;
        } elseif (!empty($this->queryDelete)) {
            $sql = $this->queryDelete;
        }
		else
		{
			$sql = '';
		}
        
        if ($reset) {
            $this->resetQuery();
        }
        
        return str_replace("''", "'", $sql);
	}

    /**
     * Inserts multiple records into the database.
     *
     * @param array $data The data to insert.
     * @return self
     */
    public function insertBatch(array $data): self
    {
        if (!empty($data)) {
            $columns = implode(',', array_keys($data[0]));
            $valuePlaceholders = [];

            foreach ($data as $rowIndex => $row) {
                $placeholders = [];
                foreach ($row as $column => $value) {
                    $placeholder = ":{$column}_{$rowIndex}";
                    $placeholders[] = $placeholder;
                    $this->binds[$placeholder] = $value;
                }
                $valuePlaceholders[] = '(' . implode(',', $placeholders) . ')';
            }

            $this->queryInsert = "INSERT INTO {$this->table} ({$columns}) VALUES " . implode(',', $valuePlaceholders);
        }
        return $this;
    }

    /**
     * Sets multiple WHERE conditions for the query.
     *
     * @param array $conditions The conditions to apply.
     * @return self
     */
    public function whereArray(array $conditions): self {
        foreach ($conditions as $key => $value) {
            $this->where($key, $value);
        }
        return $this;
    }

    /**
     * Sets the DISTINCT keyword for the query.
     *
     * @return self
     */
    public function distinct(): self {
        $this->querySelect = str_replace('SELECT ', 'SELECT DISTINCT ', $this->querySelect);
        return $this;
    }

    /**
     * Sets a JSON_CONTAINS condition for the query.
     *
     * @param string $field The field to apply the condition to.
     * @param mixed $value The value to compare with.
     * @return self
     */
    public function whereJsonContains(string $field, $value): self {
        $this->queryWhere .= " JSON_CONTAINS($field, '" . json_encode($value) . "')";
        return $this;
    }

    /**
     * Logs the query.
     *
     * @return void
     */
    public function logQuery(): void {
        $query = $this->compile(); // Get the compiled query
        $timestamp = date('Y-m-d H:i:s'); // Current timestamp
        $operation = ''; // Determine the operation type based on the query

        // Basic operation detection based on the query
        if (stripos($query, 'SELECT') === 0) {
            $operation = 'SELECT';
        } elseif (stripos($query, 'INSERT') === 0) {
            $operation = 'INSERT';
        } elseif (stripos($query, 'UPDATE') === 0) {
            $operation = 'UPDATE';
        } elseif (stripos($query, 'DELETE') === 0) {
            $operation = 'DELETE';
        }

        // Log message
        $logMessage = "[$timestamp] $operation Query: $query";

        // Use the Log class to write the log message
        $log = new \Core\Log();
        $log->setLogName('database_queries')->write($logMessage);
    }

    /**
     * Sets multiple conditions for the query.
     *
     * @param array $conditions The conditions to apply.
     * @param string $clause The clause to use for the conditions (AND or OR).
     * @return self
     */
    public function whereMultiple(array $conditions, string $clause = 'AND'): self {
        foreach ($conditions as $condition) {
            $this->queryWhere .= " $clause " . $condition;
        }
        return $this;
    }

    /**
     * Clears the query cache.
     *
     * @return void
     */
    public function clearCache(): void {
        $this->queryCache = []; // Clear the cache
    }

    /**
     * Add database-agnostic random ordering (to be overridden by specific implementations)
     *
     * @return self
     */
    public function orderByRandom(): self
    {
        // Default implementation - can be overridden
        $this->queryOrderBy = " ORDER BY RAND()";
        return $this;
    }

    /**
     * Add database-agnostic limit with offset
     *
     * @param int $limit
     * @param int $offset
     * @return self
     */
    public function limitOffset(int $limit, int $offset = 0): self
    {
        $this->queryLimit = " LIMIT {$limit}";
        if ($offset > 0) {
            $this->queryOffset = " OFFSET {$offset}";
        }
        return $this;
    }

    /**
     * Add database-agnostic date formatting (to be overridden)
     *
     * @param string $field
     * @param string $format
     * @return self
     */
    public function dateFormat(string $field, string $format = 'Y-m-d'): self
    {
        // Default implementation - can be overridden
        $this->querySelect .= ", DATE_FORMAT({$field}, '{$format}')";
        return $this;
    }

    /**
     * Add database-agnostic JSON extraction (to be overridden)
     *
     * @param string $field
     * @param string $path
     * @return self
     */
    public function jsonExtract(string $field, string $path): self
    {
        // Default implementation - can be overridden
        $this->querySelect .= ", JSON_EXTRACT({$field}, '$.{$path}')";
        return $this;
    }

    /**
     * Add database-agnostic JSON contains (to be overridden)
     *
     * @param string $field
     * @param mixed $value
     * @param string $path
     * @return self
     */
    public function jsonContains(string $field, $value, string $path = '$'): self
    {
        // Default implementation - can be overridden
        $jsonValue = json_encode($value);
        $this->queryWhere .= " JSON_CONTAINS({$field}, '{$jsonValue}', '$.{$path}')";
        return $this;
    }

    /**
     * Add database-agnostic null coalescing (to be overridden)
     *
     * @param string $field
     * @param mixed $defaultValue
     * @return self
     */
    public function coalesce(string $field, $defaultValue): self
    {
        // Default implementation - can be overridden
        $this->querySelect .= ", COALESCE({$field}, '{$defaultValue}')";
        return $this;
    }

    /**
     * Add database-agnostic case-when statement
     *
     * @param string $field
     * @param array $cases [value => result]
     * @param mixed $default
     * @return self
     */
    public function caseWhen(string $field, array $cases, $default = null): self
    {
        $caseSql = " CASE {$field}";
        foreach ($cases as $value => $result) {
            $caseSql .= " WHEN '{$value}' THEN '{$result}'";
        }
        if ($default !== null) {
            $caseSql .= " ELSE '{$default}'";
        }
        $caseSql .= " END";
        $this->querySelect .= ", {$caseSql}";
        return $this;
    }

    /**
     * Add database-agnostic regex search (to be overridden)
     *
     * @param string $field
     * @param string $pattern
     * @return self
     */
    public function regexp(string $field, string $pattern): self
    {
        // Default implementation - can be overridden
        $this->queryWhere .= " {$field} REGEXP '{$pattern}'";
        return $this;
    }

    /**
     * Add database-agnostic full-text search (to be overridden)
     *
     * @param string $field
     * @param string $searchTerm
     * @return self
     */
    public function fullTextSearch(string $field, string $searchTerm): self
    {
        // Default implementation - can be overridden
        $this->queryWhere .= " MATCH({$field}) AGAINST('{$searchTerm}')";
        return $this;
    }

    /**
     * Add database-agnostic string aggregation (to be overridden)
     *
     * @param string $field
     * @param string $separator
     * @param string $alias
     * @return self
     */
    public function stringAgg(string $field, string $separator = ',', string $alias = ''): self
    {
        // Default implementation - can be overridden
        $aliasSql = $alias ? " AS {$alias}" : '';
        $this->querySelect .= ", GROUP_CONCAT({$field} SEPARATOR '{$separator}'){$aliasSql}";
        return $this;
    }

}
