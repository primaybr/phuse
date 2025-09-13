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


return $router;
