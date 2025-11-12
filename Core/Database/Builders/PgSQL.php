<?php

declare(strict_types=1);

namespace Core\Database\Builders;

class PgSQL implements BuildersInterface
{
	use BuildersTrait;

    public function __construct(string $table)
    {
        $this->from($table);
        $this->table = $table;
    }

    /**
     * Add ORDER BY RANDOM() for PostgreSQL
     *
     * @return self
     */
    public function orderByRandom(): self
    {
        $this->queryOrderBy = " ORDER BY RANDOM()";
        return $this;
    }

    /**
     * Add LIMIT with OFFSET for PostgreSQL
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
     * Add PostgreSQL-specific date functions
     *
     * @param string $field
     * @param string $format
     * @return self
     */
    public function dateFormat(string $field, string $format = 'YYYY-MM-DD'): self
    {
        $this->querySelect .= ", TO_CHAR({$field}, '{$format}')";
        return $this;
    }

    /**
     * Add PostgreSQL full-text search
     *
     * @param string $field
     * @param string $searchTerm
     * @return self
     */
    public function fullTextSearch(string $field, string $searchTerm): self
    {
        $this->queryWhere .= " {$field} @@ plainto_tsquery('english', '{$searchTerm}')";
        return $this;
    }

    /**
     * Add PostgreSQL JSON operations
     *
     * @param string $field
     * @param string $path
     * @return self
     */
    public function jsonExtract(string $field, string $path): self
    {
        $this->querySelect .= ", {$field} -> '{$path}'";
        return $this;
    }

    /**
     * Add PostgreSQL JSON path extraction
     *
     * @param string $field
     * @param string $path
     * @return self
     */
    public function jsonExtractPath(string $field, string $path): self
    {
        $pathParts = explode('.', $path);
        $jsonPath = "'" . implode("','", $pathParts) . "'";
        $this->querySelect .= ", {$field} #> ARRAY[{$jsonPath}]";
        return $this;
    }

    /**
     * Add PostgreSQL JSON_CONTAINS equivalent
     *
     * @param string $field
     * @param mixed $value
     * @param string $path
     * @return self
     */
    public function jsonContains(string $field, $value, string $path = ''): self
    {
        if ($path) {
            $this->queryWhere .= " {$field} -> '{$path}' ? '{$value}'";
        } else {
            $this->queryWhere .= " {$field} ? '{$value}'";
        }
        return $this;
    }

    /**
     * Add PostgreSQL STRING_AGG (equivalent to GROUP_CONCAT)
     *
     * @param string $field
     * @param string $separator
     * @param string $alias
     * @return self
     */
    public function stringAgg(string $field, string $separator = ',', string $alias = ''): self
    {
        $aliasSql = $alias ? " AS {$alias}" : '';
        $this->querySelect .= ", STRING_AGG({$field}, '{$separator}'){$aliasSql}";
        return $this;
    }

    /**
     * Add PostgreSQL COALESCE function (equivalent to IFNULL)
     *
     * @param string $field
     * @param mixed $defaultValue
     * @return self
     */
    public function coalesce(string $field, $defaultValue): self
    {
        $this->querySelect .= ", COALESCE({$field}, '{$defaultValue}')";
        return $this;
    }

    /**
     * Add PostgreSQL CASE statement
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
     * Add PostgreSQL ~ (tilde) regex operator
     *
     * @param string $field
     * @param string $pattern
     * @return self
     */
    public function regexp(string $field, string $pattern): self
    {
        $this->queryWhere .= " {$field} ~ '{$pattern}'";
        return $this;
    }

    /**
     * Add PostgreSQL array operations
     *
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function arrayContains(string $field, $value): self
    {
        $this->queryWhere .= " '{$value}' = ANY({$field})";
        return $this;
    }

    /**
     * Add PostgreSQL ILIKE for case-insensitive search
     *
     * @param string $field
     * @param string $value
     * @return self
     */
    public function ilike(string $field, string $value): self
    {
        $this->queryWhere .= " {$field} ILIKE '{$value}'";
        return $this;
    }

    /**
     * Add PostgreSQL DISTINCT ON
     *
     * @param array $fields
     * @return self
     */
    public function distinctOn(array $fields): self
    {
        $fieldList = implode(', ', $fields);
        $this->querySelect = str_replace('SELECT ', "SELECT DISTINCT ON ({$fieldList}) ", $this->querySelect);
        return $this;
    }

    /**
     * Add PostgreSQL RETURNING clause for INSERT/UPDATE/DELETE
     *
     * @param array $fields
     * @return self
     */
    public function returning(array $fields): self
    {
        $fieldList = implode(', ', $fields);
        $this->querySelect = " RETURNING {$fieldList}";
        return $this;
    }

    /**
     * Add PostgreSQL window functions
     *
     * @param string $function
     * @param string $partitionBy
     * @param string $orderBy
     * @param string $alias
     * @return self
     */
    public function windowFunction(string $function, string $partitionBy = '', string $orderBy = '', string $alias = ''): self
    {
        $over = ' OVER (';
        if ($partitionBy) {
            $over .= "PARTITION BY {$partitionBy}";
        }
        if ($orderBy) {
            $over .= ($partitionBy ? ' ' : '') . "ORDER BY {$orderBy}";
        }
        $over .= ')';

        $aliasSql = $alias ? " AS {$alias}" : '';
        $this->querySelect .= ", {$function}{$over}{$aliasSql}";
        return $this;
    }
}
