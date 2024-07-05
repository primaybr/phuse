<?php
namespace Core\Database\Builders;
	
interface BuildersInterface {
	
	public function select(string|array $fields = '*'): self;

	public function insert(array $data): self;
	
	public function insertIgnore(array $data): self;

	public function update(array $data): self;

	public function delete(): self;
	
	public function month(string $field, int $value): self;
	
	public function year(string $field, int $value): self;
	
	public function day(string $field, int $value): self;

	public function max(string $field, string $alias = ''): self;

	public function min(string $field, string $alias = ''): self;

	public function count(string $field = '*'): self;

	public function from(string|array $table = ''): self;

	public function where(string $key = '', string|int $value = '', string $operator = '=', string $clause = 'AND'): self;
	
	public function orWhere(string $key, string $operator, string|int $value): self;
	
	public function whereQuery(string $query): self;

	public function whereIn(array $data = [], bool $not = false): self;

	public function offset(int $offset = 0): self;

	public function limit(int $limit = 0): self;

	public function join(string $table, string $cond, string $type): self;

	public function orderBy(string $order_by, string $order): self;
	
	public function groupBy(string $groupby): self;

	public function compile(bool $reset = true): string;
	
	public function resetQuery(): self;
}
	