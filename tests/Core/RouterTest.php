<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Router;
use Core\Log;
use Core\Exception\Error;
use Core\Cache\Cache;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        // Create a basic Router instance without complex mocking
        $this->router = new Router();
    }

    public function testRouterInstantiation(): void
    {
        $router = new Router();
        $this->assertInstanceOf(Router::class, $router);
    }

    public function testAddRoute(): void
    {
        $this->router->add('GET', '/test', 'TestController', 'index');

        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($this->router);

        $expectedKey = '~^\/phuse\/test$~@GET';
        $this->assertArrayHasKey($expectedKey, $routes);
        $this->assertEquals('TestController', $routes[$expectedKey]);
    }

    public function testGetRoute(): void
    {
        $this->router->get('/test', 'TestController', 'index');

        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($this->router);

        $expectedKey = '~^\/phuse\/test$~@GET';
        $this->assertArrayHasKey($expectedKey, $routes);
        $this->assertEquals('TestController', $routes[$expectedKey]);
    }

    public function testPostRoute(): void
    {
        $this->router->post('/test', 'TestController', 'store');

        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($this->router);

        $expectedKey = '~^\/phuse\/test$~@POST';
        $this->assertArrayHasKey($expectedKey, $routes);
        $this->assertEquals('TestController', $routes[$expectedKey]);
    }

    public function testPutRoute(): void
    {
        $this->router->put('/test', 'TestController', 'update');

        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($this->router);

        $expectedKey = '~^\/phuse\/test$~@PUT';
        $this->assertArrayHasKey($expectedKey, $routes);
        $this->assertEquals('TestController', $routes[$expectedKey]);
    }

    public function testDeleteRoute(): void
    {
        $this->router->delete('/test', 'TestController', 'destroy');

        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($this->router);

        $expectedKey = '~^\/phuse\/test$~@DELETE';
        $this->assertArrayHasKey($expectedKey, $routes);
        $this->assertEquals('TestController', $routes[$expectedKey]);
    }

    public function testGroupMiddleware(): void
    {
        $executed = false;
        $this->router->group(['auth'], function() use (&$executed) {
            $executed = true;
            // Just mark as executed, don't try to add routes in test
        });

        $this->assertTrue($executed);
    }

    public function testPreparePattern(): void
    {
        $reflection = new \ReflectionClass($this->router);
        $preparePatternMethod = $reflection->getMethod('preparePattern');
        $preparePatternMethod->setAccessible(true);

        $pattern = $preparePatternMethod->invoke($this->router, '/test');
        $this->assertStringContainsString('phuse', $pattern); // Should contain the root directory name
        $this->assertStringContainsString('test', $pattern);
    }

    public function testGetRouteKey(): void
    {
        $reflection = new \ReflectionClass($this->router);
        $getRouteKeyMethod = $reflection->getMethod('getRouteKey');
        $getRouteKeyMethod->setAccessible(true);

        $key = $getRouteKeyMethod->invoke($this->router, '/test', 'GET');
        $this->assertEquals('/test@GET', $key);
    }

    public function testRouteCaching(): void
    {
        $this->router->add('GET', '/test', 'TestController');

        $reflection = new \ReflectionClass($this->router);
        $cachedRoutesProperty = $reflection->getProperty('cachedRoutes');
        $cachedRoutesProperty->setAccessible(true);
        $cachedRoutes = $cachedRoutesProperty->getValue($this->router);

        $this->assertIsArray($cachedRoutes);
        $this->assertArrayHasKey('routes', $cachedRoutes);
        $this->assertArrayHasKey('actions', $cachedRoutes);
        $this->assertArrayHasKey('methods', $cachedRoutes);
        $this->assertArrayHasKey('middlewares', $cachedRoutes);
    }
}
