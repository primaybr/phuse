<?php

declare(strict_types=1);

namespace Tests\Core\Database\Builders;

use PHPUnit\Framework\TestCase;
use Core\Database\Builders\MySQL;
use Core\Database\Builders\PgSQL;
use Core\Database\Builders\BuildersInterface;

class BuildersTest extends TestCase
{
    /**
     * Regression test for a bug where MySQL never implemented compile()/resetQuery()
     * (they existed only as dead, accidentally-commented-out code in BuildersTrait, and
     * as PgSQL-only overrides) - meaning any Phuse install with the default 'mysql' driver
     * fataled with "Class MySQL contains 2 abstract methods" on its first query.
     */
    public function testMySQLImplementsBuildersInterface(): void
    {
        $builder = new MySQL('users');

        $this->assertInstanceOf(BuildersInterface::class, $builder);
    }

    public function testPgSQLImplementsBuildersInterface(): void
    {
        $builder = new PgSQL('users');

        $this->assertInstanceOf(BuildersInterface::class, $builder);
    }

    public function testMySQLCompilesSelectQuery(): void
    {
        $sql = (new MySQL('users'))->select('*')->where('id', '1')->compile();

        $this->assertStringContainsString('SELECT *', $sql);
        $this->assertStringContainsString('FROM users', $sql);
        $this->assertStringContainsString('WHERE id =', $sql);
    }

    public function testPgSQLCompilesSelectQuery(): void
    {
        $sql = (new PgSQL('users'))->select('*')->where('id', '1')->compile();

        $this->assertStringContainsString('SELECT *', $sql);
        $this->assertStringContainsString('FROM users', $sql);
        $this->assertStringContainsString('WHERE id =', $sql);
    }

    public function testCompileResetsStateByDefault(): void
    {
        $builder = new MySQL('users');
        $builder->select('*')->where('id', '1')->compile();

        // A second compile() with no new clauses set should produce an empty string,
        // proving resetQuery() actually cleared the previous state.
        $this->assertSame('', $builder->compile());
    }

    public function testCompileWithoutResetPreservesState(): void
    {
        $builder = new MySQL('users');
        $builder->select('*')->where('id', '1');

        $first = $builder->compile(false);
        $second = $builder->compile(false);

        $this->assertSame($first, $second);
        $this->assertNotSame('', $first);
    }

    public function testCompileInsertQuery(): void
    {
        $sql = (new MySQL('users'))->insert(['name' => 'Bob'])->compile();

        $this->assertStringContainsString('INSERT INTO users', $sql);
    }

    public function testCompileUpdateQuery(): void
    {
        $sql = (new MySQL('users'))->where('id', '1')->update(['name' => 'Bob'])->compile();

        $this->assertStringContainsString('UPDATE users SET', $sql);
        $this->assertStringContainsString('WHERE id =', $sql);
    }

    public function testCompileDeleteQuery(): void
    {
        $sql = (new MySQL('users'))->where('id', '1')->delete()->compile();

        $this->assertStringContainsString('DELETE FROM users', $sql);
    }

    public function testResetQueryClearsBinds(): void
    {
        $builder = new MySQL('users');
        $builder->where('id', '1');

        $this->assertNotEmpty($builder->binds);

        $builder->resetQuery();

        $this->assertEmpty($builder->binds);
    }
}
