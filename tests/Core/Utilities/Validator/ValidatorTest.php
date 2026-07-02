<?php

declare(strict_types=1);

namespace Tests\Core\Utilities\Validator;

use PHPUnit\Framework\TestCase;
use Core\Utilities\Validator\Validator;
use Core\Model;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testPasswordRule(): void
    {
        $this->assertTrue($this->validator->password('longenoughpassword'));
        $this->assertFalse($this->validator->password('short'));
        $this->assertFalse($this->validator->password('short', 20));
    }

    public function testDateRule(): void
    {
        $this->assertTrue($this->validator->date('2026-07-02'));
        $this->assertFalse($this->validator->date('2026-13-40'));
        $this->assertFalse($this->validator->date('not-a-date'));
        $this->assertTrue($this->validator->date('02/07/2026', 'd/m/Y'));
    }

    public function testDatetimeRule(): void
    {
        $this->assertTrue($this->validator->datetime('2026-07-02 10:30:00'));
        $this->assertFalse($this->validator->datetime('2026-07-02'));
    }

    public function testUuidRule(): void
    {
        $this->assertTrue($this->validator->uuid('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertFalse($this->validator->uuid('not-a-uuid'));
        $this->assertFalse($this->validator->uuid('550e8400-e29b-41d4-a716'));
    }

    public function testFileTypeRule(): void
    {
        $this->assertTrue($this->validator->fileType(['name' => 'photo.JPG'], ['jpg', 'png']));
        $this->assertFalse($this->validator->fileType(['name' => 'virus.exe'], ['jpg', 'png']));
        $this->assertFalse($this->validator->fileType('not-an-array', ['jpg']));
    }

    public function testFileSizeRule(): void
    {
        $this->assertTrue($this->validator->fileSize(['size' => 1000], 2000));
        $this->assertFalse($this->validator->fileSize(['size' => 3000], 2000));
    }

    public function testConfirmedRule(): void
    {
        $this->assertTrue($this->validator->confirmed('secret', 'secret'));
        $this->assertFalse($this->validator->confirmed('secret', 'nope'));
    }

    public function testDistinctRule(): void
    {
        $this->assertTrue($this->validator->distinct(['a', 'b', 'c']));
        $this->assertFalse($this->validator->distinct(['a', 'b', 'a']));
        $this->assertFalse($this->validator->distinct('not-an-array'));
    }

    public function testJsonRule(): void
    {
        $this->assertTrue($this->validator->json('{"a":1}'));
        $this->assertFalse($this->validator->json('{not json}'));
        $this->assertFalse($this->validator->json(''));
    }

    public function testUniqueRule(): void
    {
        try {
            // Probe with a real query - construction alone can succeed even when the
            // underlying PDO connection never actually came up (no DB server reachable).
            (new Model('test_table'))->get(1);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }

        // No matching row for a value that shouldn't exist -> unique
        $this->assertTrue($this->validator->unique('no-such-value-xyz', 'test_table', 'name'));
    }

    public function testRuleChainingWithNewRules(): void
    {
        $this->validator
            ->rule('birthdate', 'date', 'Y-m-d')
            ->rule('id', 'uuid')
            ->rule('settings', 'json');

        $data = [
            'birthdate' => '1990-05-15',
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'settings' => '{"theme":"dark"}',
        ];

        $this->assertTrue($this->validator->validate($data));
        $this->assertEmpty($this->validator->errors());
    }
}
