<?php
namespace Core\Folder;

// Define constants if not already defined
if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
}
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

class Path{
	
	const CONFIG = ROOT.'Config'.DS;
	const LOGS = ROOT.'Logs'.DS;
	const CACHE = ROOT.'Cache'.DS;
    const CONTROLLERS = 'App'.DS.'Controllers'.DS;
    const MODELS = ROOT.'App'.DS.'Models'.DS;
    const VIEWS  = ROOT.'App'.DS.'Views'.DS;
    const SESSION = ROOT.'Session'.DS;
}