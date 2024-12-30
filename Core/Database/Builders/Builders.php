<?php

declare(strict_types=1);

namespace Core\Database\Builders;

class Builders
{
    public $builders;

    public function __construct(string $databaseType, string $table)
    {
        switch (strtolower($databaseType)) {
            case 'mysql':
                $this->builders = new MySQL($table);
                break;
            case 'pgsql':
                $this->builders = new PgSQL($table);
                break;
            default:
                throw new \InvalidArgumentException('Unsupported database type: ' . $databaseType);
        }
    }

    public function __call(string $method, array $args)
    {
        // Delegate method calls to the specific builder instance
        if (method_exists($this->builders, $method)) {
            return $this->builders->$method(...$args);
        }
        throw new \BadMethodCallException('Method ' . $method . ' does not exist on builder.');
    }
}
