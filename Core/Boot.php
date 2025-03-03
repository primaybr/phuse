<?php

/**
 * Bootstraps the application by setting up autoloading for classes.
 * This file configures the autoloading mechanism to dynamically load class files based on their namespace.
 * @package Core
 * @author  Prima Yoga
 * @version 1.0.1
 */

set_include_path(get_include_path().PATH_SEPARATOR.'./');

spl_autoload_extensions('.php');

spl_autoload_register(function ($namespace_class) {
    /**
     * Autoloads the class files based on the given namespace class.
     * This function replaces the namespace separators with directory separators and appends the appropriate file extension.
     * 
     * @param string $namespace_class The fully qualified class name.
     */
    static $autoloadExtensions = null;
    if ($autoloadExtensions === null) {
        $autoloadExtensions = explode(',', spl_autoload_extensions());
    }
    $baseDir = dirname(__DIR__). DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace_class);
    $fileFound = false;

    try {
        foreach ($autoloadExtensions as $extension) {
            $filePath = $baseDir . $extension;
            if (file_exists($filePath)) {
                require $filePath;
                $fileFound = true;
                break;
            }
        }

        if (!$fileFound) {
            throw new Exception("Class not found: $namespace_class");
        }
    } catch (Exception $e) {
        error_log("Autoload failed for class: $namespace_class - " . $e->getMessage());
        throw $e;
    }
});