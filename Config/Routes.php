<?php

declare(strict_types=1);

namespace Config;

use Core\Router;

$router = new Router();

/*
 *  default route, $this->add($method,$pattern,$controller,$action)
 *  get|post route, $this->get($pattern,$controller,$action) or $this->post($pattern,$controller,$action)
 */

$router->add('GET', '/', 'Web\Welcome', 'index');
$router->get('/items', 'Web\ItemController', 'index');
$router->post('/items', 'Web\ItemController', 'postData');

// Template Examples Routes
$router->get('/examples', 'Web\Examples', 'index');
$router->get('/examples/basic', 'Web\Examples', 'basic');
$router->get('/examples/conditional', 'Web\Examples', 'conditional');
$router->get('/examples/foreach', 'Web\Examples', 'foreach');
$router->get('/examples/nested', 'Web\Examples', 'nested');
$router->get('/examples/blog', 'Web\Examples', 'blog');
$router->get('/examples/dashboard', 'Web\Examples', 'dashboard');
$router->get('/examples/product', 'Web\Examples', 'product');
$router->get('/examples/run/{template}', 'Web\Examples', 'runAll');


return $router;
