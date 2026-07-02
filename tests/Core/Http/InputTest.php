<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use Core\Http\Input;

class InputTest extends TestCase
{
    private Input $input;

    protected function setUp(): void
    {
        $this->input = new Input();
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_FILES = [];
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function testGetReturnsSpecificSanitizedValue(): void
    {
        $_GET['name'] = '<script>alert(1)</script>';

        $value = $this->input->get('name');

        $this->assertStringNotContainsString('<script>', $value);
        $this->assertStringContainsString('&lt;script&gt;', $value);
    }

    public function testGetReturnsRawValueWhenSanitizeDisabled(): void
    {
        $_GET['name'] = '<b>bold</b>';

        $this->assertSame('<b>bold</b>', $this->input->get('name', false));
    }

    public function testGetReturnsAllParamsWhenNoNameGiven(): void
    {
        $_GET = ['a' => '1', 'b' => '2'];

        $this->assertSame(['a' => '1', 'b' => '2'], $this->input->get('', false));
    }

    public function testPostReturnsSpecificValue(): void
    {
        $_POST['email'] = 'user@example.com';

        $this->assertSame('user@example.com', $this->input->post('email'));
    }

    public function testHasChecksExistence(): void
    {
        $_GET['q'] = 'search term';

        $this->assertTrue($this->input->has('q', 'GET'));
        $this->assertFalse($this->input->has('missing', 'GET'));
    }

    public function testAllCombinesGetAndPost(): void
    {
        $_GET = ['a' => '1'];
        $_POST = ['b' => '2'];

        $combined = $this->input->all(false);

        $this->assertSame('1', $combined['a']);
        $this->assertSame('2', $combined['b']);
    }

    public function testIsAjaxDetectsHeader(): void
    {
        $this->assertFalse($this->input->isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue($this->input->isAjax());
    }
}
