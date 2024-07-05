<?php

declare(strict_types=1);

namespace Core\Cache;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Core\Folder\Path as Path;

// Define a trait for caching operations
trait CacheTrait
{
    // Set the cache file with the given name, content and expiration time
    public function set(string $cacheName, string $cache, int $time = 600): void
    {
		$cacheName = md5($cacheName);
		$this->folder->create(Path::CACHE);
		$file = Path::CACHE . $cacheName . '_' . $time . '.cache';
		file_put_contents($file, $cache);
		
		if (file_exists($file)) {
			chmod($file, 0777);
		}
    }
    
    // Get the cache file content with the given name and expiration time
    public function get(string $cacheName, int $time = 600): string
    {
        $cacheName = md5($cacheName);
        $file = Path::CACHE . $cacheName . '_' . $time . '.cache';
	    
        if (file_exists($file)) {
			$part = explode('_', $file);
			$time = str_replace('.cache', '', end($part));
			$timeElapsed = time() - filemtime($file);
			
			if ($timeElapsed < $time) {
				return file_get_contents($file);
			}
			
			unlink($file);
        }
	    
        return '';
    }
    
    // Clear all the cache files in the cache directory
    public function clear(): void
    {
		$directory = new RecursiveDirectoryIterator(Path::CACHE);
		$iterator = new RecursiveIteratorIterator($directory);
		$regex = new RegexIterator($iterator, '/\/*.cache$/i');
	    
		foreach ($regex as $item) {
			unlink($item->getPathname());
        }
    }
}
