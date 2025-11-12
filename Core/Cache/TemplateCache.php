<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Exception\Error;
use Core\Folder\Path;
use Core\Log;
use Core\Config\Template;

/**
 * TemplateCache handles the caching of compiled templates
 */
class TemplateCache
{
    /**
     * @var string Cache directory path
     */
    private string $cacheDir;

    /**
     * @var int Cache lifetime in seconds
     */
    private int $cacheLifetime;

    /**
     * @var Log Log instance
     */
    private Log $log;

    /**
     * @var bool Whether to clear cache in development mode
     */
    private bool $autoClearInDevelopment;

    /**
     * Constructor
     * 
     * @throws Error If cache directory is not writable
     */
    public function __construct()
    {
        $config = new \Config\Template();
        
        $this->cacheLifetime = $config->cacheLifetime;
        $this->autoClearInDevelopment = $config->autoClearInDevelopment;
        $this->cacheDir = rtrim(Path::CACHE, DS) . DS . trim($config->cacheDir, DS) . DS;
        $this->log = new Log();
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0755, true)) {
            throw new Error("Unable to create template cache directory: {$this->cacheDir}");
        }
        
        // Check if cache directory is writable
        if (!is_writable($this->cacheDir)) {
            throw new Error("Template cache directory is not writable: {$this->cacheDir}");
        }
        
        // Clear cache in development mode if configured to do so
        if ($this->autoClearInDevelopment) {
            $this->clear();
        }
    }

    /**
     * Generate a cache key for the template
     * 
     * @param string $templatePath Path to the template file
     * @param array $data Template data
     * @return string Cache key
     */
    public function generateKey(string $templatePath, array $data = []): string
    {
        $templateMtime = filemtime($templatePath);
        $dataHash = md5(serialize($data));
        return 'template_' . md5($templatePath . $templateMtime . $dataHash) . '.php';
    }

    /**
     * Get the full path to the cached template
     * 
     * @param string $key Cache key
     * @return string Full path to cached file
     */
    public function getCachePath(string $key): string
    {
        return $this->cacheDir . $key;
    }

    /**
     * Check if a valid cached version exists
     * 
     * @param string $key Cache key
     * @return bool True if valid cache exists, false otherwise
     */
    public function hasValidCache(string $key): bool
    {
        $cacheFile = $this->getCachePath($key);
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        // Check if cache is still valid
        return (time() - filemtime($cacheFile)) < $this->cacheLifetime;
    }

    /**
     * Get the cached template content
     * 
     * @param string $key Cache key
     * @return string Cached content
     * @throws Error If cache file doesn't exist
     */
    public function getCached(string $key): string
    {
        $cacheFile = $this->getCachePath($key);
        
        if (!file_exists($cacheFile)) {
            throw new Error("Cache file not found: {$cacheFile}");
        }
        
        return file_get_contents($cacheFile);
    }

    /**
     * Store the compiled template in cache
     * 
     * @param string $key Cache key
     * @param string $content Compiled template content
     * @return bool True on success, false on failure
     */
    public function store(string $key, string $content): bool
    {
        $cacheFile = $this->getCachePath($key);
        $result = file_put_contents($cacheFile, $content, LOCK_EX);
        
        if ($result === false) {
            $this->log->write("Failed to write template cache: {$cacheFile}", 'error');
            return false;
        }
        
        return true;
    }

    /**
     * Clear all cached templates
     * 
     * @return bool True on success, false on failure
     */
    public function clear(): bool
    {
        $files = glob($this->cacheDir . 'template_*');
        $success = true;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (!unlink($file)) {
                    $this->log->write("Failed to delete cache file: {$file}", 'error');
                    $success = false;
                }
            }
        }
        
        return $success;
    }

    /**
     * Set the cache lifetime
     * 
     * @param int $seconds Cache lifetime in seconds
     * @return self
     */
    public function setCacheLifetime(int $seconds): self
    {
        $this->cacheLifetime = max(0, $seconds);
        return $this;
    }
    
    /**
     * Enable or disable auto-clear in development mode
     * 
     * @param bool $enabled Whether to enable auto-clear in development
     * @return self
     */
    public function setAutoClearInDevelopment(bool $enabled): self
    {
        $this->autoClearInDevelopment = $enabled;
        return $this;
    }
}
