<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Cache\BaseCache;
use Core\Cache\CacheConfig;
use Core\Cache\CacheException;

/**
 * File-based cache implementation
 */
class FileCache extends BaseCache
{
    /**
     * Cache directory path
     */
    private string $cacheDir;

    /**
     * Initialize the file cache
     */
    protected function initialize(): void
    {
        $this->cacheDir = $this->ensureCacheDirectory();
    }

    /**
     * Get a value from cache
     */
    public function get(string $key): mixed
    {
        $this->validateKey($key);
        $cacheKey = $this->generateKey($key);
        $filePath = $this->getCacheFilePath($cacheKey);

        try {
            if (!file_exists($filePath)) {
                $this->incrementStat('misses');
                return null;
            }

            // Check if cache is still valid
            if (!$this->isCacheValid($filePath)) {
                $this->delete($key); // Delete expired cache
                $this->incrementStat('misses');
                return null;
            }

            // Read and unserialize cache data
            $data = $this->readCacheFile($filePath);
            if ($data === false) {
                $this->incrementStat('misses');
                return null;
            }

            $this->incrementStat('hits');
            return $data['value'];

        } catch (\Throwable $e) {
            $this->incrementStat('errors');
            $this->log('Cache read error', 'error', [
                'key' => $key,
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Set a value in cache
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $this->validateKey($key);
        $cacheKey = $this->generateKey($key);
        $filePath = $this->getCacheFilePath($cacheKey);
        $ttl = $ttl ?? $this->config->defaultTtl;

        try {
            // Prepare cache data
            $cacheData = [
                'value' => $value,
                'ttl' => $ttl,
                'created_at' => time(),
                'expires_at' => time() + $ttl
            ];

            // Write to file with locking
            $success = $this->writeCacheFile($filePath, $cacheData);

            if ($success) {
                $this->incrementStat('sets');
                $this->log('Cache set successfully', 'debug', [
                    'key' => $key,
                    'ttl' => $ttl
                ]);
            } else {
                $this->incrementStat('errors');
                $this->log('Cache set failed', 'error', [
                    'key' => $key,
                    'file' => $filePath
                ]);
            }

            return $success;

        } catch (\Throwable $e) {
            $this->incrementStat('errors');
            $this->log('Cache write error', 'error', [
                'key' => $key,
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete a value from cache
     */
    public function delete(string $key): bool
    {
        $this->validateKey($key);
        $cacheKey = $this->generateKey($key);
        $filePath = $this->getCacheFilePath($cacheKey);

        try {
            if (file_exists($filePath) && unlink($filePath)) {
                $this->incrementStat('deletes');
                $this->log('Cache deleted', 'debug', ['key' => $key]);
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            $this->incrementStat('errors');
            $this->log('Cache delete error', 'error', [
                'key' => $key,
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear all cache files
     */
    public function clear(): bool
    {
        try {
            $files = glob($this->cacheDir . '*');
            $success = true;

            if ($files === false) {
                return false;
            }

            foreach ($files as $file) {
                if (is_file($file) && !unlink($file)) {
                    $success = false;
                    $this->log('Failed to delete cache file', 'warning', [
                        'file' => $file
                    ]);
                }
            }

            $this->resetStats();
            $this->log('Cache cleared', 'info', [
                'files_deleted' => count($files)
            ]);

            return $success;
        } catch (\Throwable $e) {
            $this->incrementStat('errors');
            $this->log('Cache clear error', 'error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get the full path to cache file
     */
    private function getCacheFilePath(string $key): string
    {
        return $this->cacheDir . md5($key) . '.cache';
    }

    /**
     * Check if cache file is still valid
     */
    private function isCacheValid(string $filePath): bool
    {
        $cacheData = $this->readCacheFile($filePath, false);

        if ($cacheData === false) {
            return false;
        }

        return ($cacheData['expires_at'] ?? 0) > time();
    }

    /**
     * Read cache file with error handling
     */
    private function readCacheFile(string $filePath, bool $unserialize = true)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = false;

        if ($this->config->useFileLocking) {
            $handle = fopen($filePath, 'rb');
            if ($handle && flock($handle, LOCK_SH)) {
                $content = stream_get_contents($handle);
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        } else {
            $content = file_get_contents($filePath);
        }

        if ($content === false) {
            return false;
        }

        if (!$unserialize) {
            return $content;
        }

        $data = unserialize($content);

        if ($data === false) {
            // Corrupted cache file, delete it
            unlink($filePath);
            return false;
        }

        return $data;
    }

    /**
     * Write cache file with locking
     */
    private function writeCacheFile(string $filePath, array $data): bool
    {
        $content = serialize($data);

        if ($this->config->useFileLocking) {
            $handle = fopen($filePath, 'wb');
            if (!$handle) {
                return false;
            }

            if (flock($handle, LOCK_EX)) {
                $result = fwrite($handle, $content) !== false;
                flock($handle, LOCK_UN);
                fclose($handle);
                return $result;
            }

            fclose($handle);
            return false;
        } else {
            return file_put_contents($filePath, $content) !== false;
        }
    }

    /**
     * Get cache directory
     */
    public function getCacheDirectory(): string
    {
        return $this->cacheDir;
    }

    /**
     * Set cache directory (for testing or special cases)
     */
    public function setCacheDirectory(string $directory): void
    {
        $this->cacheDir = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
