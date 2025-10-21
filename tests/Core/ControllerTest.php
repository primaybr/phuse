<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Controller;
use Core\Security\CSRF;
use Core\Container;
use Core\Middleware\MiddlewareStack;
use Core\Middleware\MiddlewareInterface;

class ControllerTest extends TestCase
{
    private Controller $controller;

    protected function setUp(): void
    {
        $this->controller = new Controller();
    }

    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(Controller::class, $this->controller);
        $this->assertInstanceOf(CSRF::class, $this->controller->csrf);
    }

    public function testControllerHasRequiredServices(): void
    {
        $this->assertTrue(property_exists($this->controller, 'config'));
        $this->assertTrue(property_exists($this->controller, 'log'));
        $this->assertTrue(property_exists($this->controller, 'session'));
        $this->assertTrue(property_exists($this->controller, 'template'));
        $this->assertTrue(property_exists($this->controller, 'csrf'));
    }

    public function testModelCreation(): void
    {
        $this->controller->model('TestTable');
        $this->assertTrue(property_exists($this->controller, 'TestTable'));
        $this->assertInstanceOf(\Core\Model::class, $this->controller->TestTable);
    }

    public function testModelAlias(): void
    {
        $this->controller->model('TestTable');
        $originalModel = $this->controller->TestTable;
        $this->controller->modelAlias('TestTable', 'TestAlias');

        $this->assertTrue(property_exists($this->controller, 'TestAlias'));
        $this->assertFalse(property_exists($this->controller, 'TestTable'));
        $this->assertSame($originalModel, $this->controller->TestAlias);
    }
}
