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
     * Override to use PostgreSQL double-quote identifier quoting.
     */
    protected function quoteIdentifier(string $field): string
    {
        $parts = explode('.', $field);
        return implode('.', array_map(
            fn($p) => '"' . preg_replace('/[^a-zA-Z0-9_]/', '', $p) . '"',
            $parts
        ));
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
     * Override insertIgnore to use PostgreSQL ON CONFLICT DO NOTHING syntax.
     * The trait default generates MySQL INSERT IGNORE which is not valid in PostgreSQL.
     */
    public function insertIgnore(array $data): self
    {
        $this->insert($data);
        $this->queryInsert .= ' ON CONFLICT DO NOTHING';
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
        $qf = $this->quoteIdentifier($field);
        $ph = $this->bindValue($format);
        $this->querySelect .= ", TO_CHAR({$qf}, {$ph})";
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
        $qf = $this->quoteIdentifier($field);
        $ph = $this->bindValue($searchTerm);
        $this->queryWhere .= " {$qf} @@ plainto_tsquery('english', {$ph})";
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
        $qf = $this->quoteIdentifier($field);
        $safePath = preg_replace('/[^a-zA-Z0-9_]/', '', $path);
        $this->querySelect .= ", {$qf} -> '{$safePath}'";
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
        $qf = $this->quoteIdentifier($field);
        $pathParts = explode('.', $path);
        $sanitizedParts = array_map(fn($p) => preg_replace('/[^a-zA-Z0-9_]/', '', $p), $pathParts);
        $jsonPath = "'" . implode("','", $sanitizedParts) . "'";
        $this->querySelect .= ", {$qf} #> ARRAY[{$jsonPath}]";
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
        $qf = $this->quoteIdentifier($field);
        $ph = $this->bindValue($value);
        if ($path) {
            $safePath = preg_replace('/[^a-zA-Z0-9_]/', '', $path);
            $this->queryWhere .= " {$qf} -> '{$safePath}' ? {$ph}";
        } else {
            $this->queryWhere .= " {$qf} ? {$ph}";
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
        $qf = $this->quoteIdentifier($field);
        $safeSep = preg_replace('/[^a-zA-Z0-9\s,.\-_|\/]/', '', $separator);
        $aliasSql = $alias ? " AS {$alias}" : '';
        $this->querySelect .= ", STRING_AGG({$qf}, '{$safeSep}'){$aliasSql}";
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
        $qf = $this->quoteIdentifier($field);
        $ph = $this->bindValue($defaultValue);
        $this->querySelect .= ", COALESCE({$qf}, {$ph})";
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
        $qf = $this->quoteIdentifier($field);
        $caseSql = " CASE {$qf}";
        foreach ($cases as $value => $result) {
            $phVal = $this->bindValue($value);
            $phRes = $this->bindValue($result);
            $caseSql .= " WHEN {$phVal} THEN {$phRes}";
        }
        if ($default !== null) {
            $phDef = $this->bindValue($default);
            $caseSql .= " ELSE {$phDef}";
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
        $qf = $this->quoteIdentifier($field);
        $ph = $this->bindValue($pattern);
        $this->queryWhere .= " {$qf} ~ {$ph}";
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
        $qf = $this->quoteIdentifier($field);
        $ph = $this->bindValue($value);
        $this->queryWhere .= " {$ph} = ANY({$qf})";
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
        $qf = $this->quoteIdentifier($field);
        $ph = $this->bindValue($value);
        $this->queryWhere .= " {$qf} ILIKE {$ph}";
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

    // compile()/resetQuery() are dialect-agnostic and now shared via BuildersTrait.
}
