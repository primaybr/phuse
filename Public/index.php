<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)).DS);

require_once '../Core/Boot.php';

$app = new Core\Base();

$app->run();
