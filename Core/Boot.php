<?php

set_include_path(get_include_path().PATH_SEPARATOR.'./');

spl_autoload_extensions('.php');

spl_autoload_register(function ($namespace_class) {
    // Use a static variable to store the autoload extensions
    static $autoloadExtensions = null;
    if ($autoloadExtensions === null) {
        $autoloadExtensions = explode(',', spl_autoload_extensions());
    }
    // Use a single require statement with a loop
    $files = dirname(__DIR__). DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace_class);
    foreach ($autoloadExtensions as $extension) {
        $file = $files.$extension;
        if (file_exists($file)) {
            require $file;
            break;
        }
    }
});