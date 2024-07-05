<?php
namespace Core\Database\Builders;
	
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

    public function select(string|array $fields = '*'): self
    {
        if (is_string($fields)) {
            $this->querySelect = 'SELECT '.$fields;
        } elseif (is_array($fields)) {
            // Use implode to join array elements
            $this->querySelect = 'SELECT '.implode(',', $fields);
        }

        return $this;
    }

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
    
    public function insertIgnore(array $data): self
    {
        $this->insert($data);

        // Use str_contains to check for substring
        if (str_contains($this->queryInsert, "INTO")) {
            $this->queryInsert = str_replace("INTO", "IGNORE INTO", $this->queryInsert);
        }
        
        return $this;
    }

    public function update(array $data): self
    {
        if ($data) {
            $field_data = '';
            $bind = [];

            foreach ($data as $k => $v) {
                $field_data .= "{$k}=:{$k}".',';
                $this->binds[$k] = $v;
            }

            $field_data = rtrim($field_data, ',');

            $this->queryUpdate = "UPDATE {$this->table} SET {$field_data}";
        }

        return $this;
    }

    public function delete(): self
    {		
		// Prioritize 'WHERE IN' sql statement if found
		$where = !empty($this->queryWhereIn) ? $this->queryWhereIn : $this->queryWhere;

        $this->queryDelete = "DELETE FROM {$this->table} $where";

        return $this;
    }
	
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

    public function count(string $field = '*'): self
    {
        $this->querySelect .= " COUNT({$field})";

        return $this;
    }

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
	
	public function orWhere(string $key, string $operator, string|int $value): self
    {
		if (!empty($this->queryWhere)) {
			$this->where($key,$operator,$value,'OR');
        }
		else
		{
			$this->where($key,$operator,$value);
		}
    }
	
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

    public function offset(int $offset = 0): self
    {
        $this->queryOffset = " OFFSET $offset";
        
        return $this;
    }

    public function limit(int|string $limit = 0): self
    {
        $this->queryLimit = " LIMIT $limit";
        
        return $this;
    }

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

    public function orderBy(string $order_by, string $order): self
    {
        $this->queryOrderBy = " ORDER BY $order_by $order";

        return $this;
    }
    
    public function groupBy(string $groupby): self
    {
        $this->queryGroupBy = " GROUP BY $groupby";
        
        return $this;
    }

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
	
	public function resetQuery(): self
	{
		$this->querySelect = $this->queryInsert = $this->queryUpdate = $this->queryWhere = $this->queryWhereIn = $this->queryGroupBy = $this->queryDelete = $this->queryOffset = $this->queryLimit = $this->queryJoin = $this->queryOrderBy = "";
		
		return $this;
	}
	
}
