<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Container;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testSetAndGetService(): void
    {
        $this->container->set('testService', function() {
            return new \stdClass();
        });

        $service = $this->container->get('testService');
        $this->assertInstanceOf(\stdClass::class, $service);
    }

    public function testSharedService(): void
    {
        $this->container->set('sharedService', function() {
            return new \stdClass();
        }, true);

        $service1 = $this->container->get('sharedService');
        $service2 = $this->container->get('sharedService');

        $this->assertSame($service1, $service2);
    }

    public function testClassResolution(): void
    {
        $this->container->set('testClass', \stdClass::class);

        $instance = $this->container->get('testClass');
        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    public function testDependencyInjection(): void
    {
        $this->container->set('dependency', function() {
            return new \stdClass();
        });

        $this->container->set('dependentService', function($container) {
            $obj = new \stdClass();
            $obj->dependency = $container->get('dependency');
            return $obj;
        });

        $service = $this->container->get('dependentService');
        $this->assertInstanceOf(\stdClass::class, $service->dependency);
    }

    public function testHasService(): void
    {
        $this->assertFalse($this->container->has('nonExistent'));

        $this->container->set('existent', \stdClass::class);
        $this->assertTrue($this->container->has('existent'));
    }

    public function testGetNonExistentService(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Service 'nonExistent' not registered in container.");

        $this->container->get('nonExistent');
    }
}
