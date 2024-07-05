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
	
}
