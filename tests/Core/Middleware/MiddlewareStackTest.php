<?php

declare(strict_types=1);

namespace Tests\Core\Middleware;

use PHPUnit\Framework\TestCase;
use Core\Middleware\MiddlewareStack;
use Core\Middleware\MiddlewareInterface;

class TestMiddleware implements MiddlewareInterface
{
    public static $executed = false;
    public static $receivedNext = null;

    public function process(callable $next): mixed
    {
        self::$executed = true;
        self::$receivedNext = $next;
        return $next();
    }
}

class MiddlewareStackTest extends TestCase
{
    public function testMiddlewareStackInstantiation(): void
    {
        $handler = function() { return 'handled'; };
        $stack = new MiddlewareStack($handler);

        $this->assertInstanceOf(MiddlewareStack::class, $stack);
    }

    public function testAddMiddleware(): void
    {
        $handler = function() { return 'handled'; };
        $stack = new MiddlewareStack($handler);
        $middleware = new TestMiddleware();

        $stack->add($middleware);

        // Since add uses unshift, the middleware should be first in the internal array
        $reflection = new \ReflectionClass($stack);
        $property = $reflection->getProperty('middlewares');
        $property->setAccessible(true);
        $middlewares = $property->getValue($stack);

        $this->assertCount(1, $middlewares);
        $this->assertSame($middleware, $middlewares[0]);
    }

    public function testProcessWithoutMiddleware(): void
    {
        $handler = function() { return 'handled'; };
        $stack = new MiddlewareStack($handler);

        $result = $stack->process();
        $this->assertEquals('handled', $result);
    }

    public function testProcessWithMiddleware(): void
    {
        $handler = function() { return 'final'; };
        $stack = new MiddlewareStack($handler);
        $middleware = new TestMiddleware();

        $stack->add($middleware);

        $result = $stack->process();

        $this->assertTrue(TestMiddleware::$executed);
        $this->assertEquals('final', $result);
        $this->assertInstanceOf(\Closure::class, TestMiddleware::$receivedNext);
    }

    public function testMultipleMiddleware(): void
    {
        $handler = function() { return 'final'; };
        $stack = new MiddlewareStack($handler);

        $middleware1 = new TestMiddleware();
        $middleware2 = new class implements MiddlewareInterface {
            public static $executed = false;
            public function process(callable $next): mixed {
                self::$executed = true;
                return $next();
            }
        };

        $stack->add($middleware1);
        $stack->add($middleware2);

        $result = $stack->process();

        $this->assertTrue(TestMiddleware::$executed);
        $this->assertTrue($middleware2::$executed);
        $this->assertEquals('final', $result);
    }
}
