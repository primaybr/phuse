<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use Core\Http\Session;

class SessionTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->session = new Session();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testSetAndGet(): void
    {
        $this->session->set('user_id', '42');
        $this->assertSame('42', $this->session->get('user_id'));
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $this->assertNull($this->session->get('does_not_exist'));
    }

    public function testCheck(): void
    {
        $this->assertFalse($this->session->check('flag'));
        $this->session->set('flag', true);
        $this->assertTrue($this->session->check('flag'));
    }

    public function testFlashReadsAndRemoves(): void
    {
        $this->session->set('notice', 'saved');

        $this->assertSame('saved', $this->session->flash('notice'));
        $this->assertNull($this->session->get('notice'));
    }

    public function testDestroyDoesNotThrowUnderCli(): void
    {
        // Session::destroy() only clears state when session_status() === PHP_SESSION_ACTIVE,
        // which is never true under CLI (the class intentionally skips real session mechanics
        // there) - so this only verifies it's safe to call, not full destroy semantics.
        $this->session->set('user_id', '42');
        $this->session->destroy();

        $this->assertTrue(true);
    }
}
