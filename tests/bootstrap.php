<?php

declare(strict_types=1);

// Set up testing environment
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__) . DS);

// Set testing environment
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

// Start output buffering to capture any unexpected output
ob_start();

// Include the framework bootstrap
require_once ROOT . 'Core' . DS . 'Boot.php';

// Clean any output from bootstrap
ob_end_clean();

// Set up test database if needed (you can extend this)
if (!defined('TEST_DB_HOST')) {
    define('TEST_DB_HOST', 'localhost');
    define('TEST_DB_NAME', 'phuse_test');
    define('TEST_DB_USER', 'root');
    define('TEST_DB_PASS', '');
}

// Helper function to reset database for tests
function resetTestDatabase(): void
{
    // This is a placeholder - implement based on your database setup
    // You might want to use migrations or fixtures here
}
