<?php

declare(strict_types=1);

namespace Core\Database\Builders;

class MySQL implements BuildersInterface
{
	use BuildersTrait;

    public function __construct(string $table)
    {
        $this->from($table);
        $this->table = $table;
    }

    /**
     * Add ORDER BY RAND() for MySQL
     *
     * @return self
     */
    public function orderByRandom(): self
    {
        $this->queryOrderBy = " ORDER BY RAND()";
        return $this;
    }

    /**
     * Add LIMIT with OFFSET for MySQL
     *
     * @param int $limit
     * @param int $offset
     * @return self
     */
    public function limitOffset(int $limit, int $offset = 0): self
    {
        $this->queryLimit = " LIMIT {$limit}";
        if ($offset > 0) {
            $this->queryLimit .= " OFFSET {$offset}";
        }
        return $this;
    }

    /**
     * Add MySQL-specific date functions
     *
     * @param string $field
     * @param string $format
     * @return self
     */
    public function dateFormat(string $field, string $format = '%Y-%m-%d'): self
    {
        $this->querySelect .= ", DATE_FORMAT({$field}, '{$format}')";
        return $this;
    }

    /**
     * Add MySQL full-text search
     *
     * @param string $field
     * @param string $searchTerm
     * @param bool $booleanMode
     * @return self
     */
    public function fullTextSearch(string $field, string $searchTerm, bool $booleanMode = false): self
    {
        $mode = $booleanMode ? ' IN BOOLEAN MODE' : '';
        $this->queryWhere .= " MATCH({$field}) AGAINST('{$searchTerm}'{$mode})";
        return $this;
    }

    /**
     * Add MySQL JSON operations
     *
     * @param string $field
     * @param string $path
     * @return self
     */
    public function jsonExtract(string $field, string $path): self
    {
        $this->querySelect .= ", JSON_EXTRACT({$field}, '$.{$path}')";
        return $this;
    }

    /**
     * Add MySQL JSON_CONTAINS
     *
     * @param string $field
     * @param mixed $value
     * @param string $path
     * @return self
     */
    public function jsonContains(string $field, $value, string $path = '$'): self
    {
        $jsonValue = json_encode($value);
        $this->queryWhere .= " JSON_CONTAINS({$field}, '{$jsonValue}', '$.{$path}')";
        return $this;
    }

    /**
     * Add MySQL GROUP_CONCAT
     *
     * @param string $field
     * @param string $separator
     * @param string $alias
     * @return self
     */
    public function groupConcat(string $field, string $separator = ',', string $alias = ''): self
    {
        $aliasSql = $alias ? " AS {$alias}" : '';
        $this->querySelect .= ", GROUP_CONCAT({$field} SEPARATOR '{$separator}'){$aliasSql}";
        return $this;
    }

    /**
     * Add MySQL IFNULL function
     *
     * @param string $field
     * @param mixed $defaultValue
     * @return self
     */
    public function ifNull(string $field, $defaultValue): self
    {
        $this->querySelect .= ", IFNULL({$field}, '{$defaultValue}')";
        return $this;
    }

    /**
     * Add MySQL CASE statement
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
     * Add MySQL REGEXP search
     *
     * @param string $field
     * @param string $pattern
     * @return self
     */
    public function regexp(string $field, string $pattern): self
    {
        $this->queryWhere .= " {$field} REGEXP '{$pattern}'";
        return $this;
    }

    /**
     * Add MySQL SOUNDEX function
     *
     * @param string $field
     * @return self
     */
    public function soundex(string $field): self
    {
        $this->querySelect .= ", SOUNDEX({$field})";
        return $this;
    }
}
