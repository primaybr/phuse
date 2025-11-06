<?php

declare(strict_types=1);

namespace Core\Utilities\Upload;

use Core\Log;

/**
 * File Manipulation Trait
 *
 * Provides secure file manipulation methods for uploads.
 * Includes XSS protection for text files and secure file movement.
 *
 * @package Core\Utilities\Upload
 * @author  Phuse Framework
 */
trait FileManipulatorTrait
{
    /**
     * Move the file to the upload directory with secure naming.
     */
    private function moveFile(array $file): bool
    {
        // Generate filename with proper extension handling
        if (empty($this->fileName)) {
            $fileName = $this->generateSecureFilename($file['name']);
        } else {
            $fileName = $this->sanitizeFilename($this->fileName);
            // Ensure filename has extension if not provided
            if (pathinfo($fileName, PATHINFO_EXTENSION) === '') {
                $originalExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName .= '.' . $originalExtension;
            }
        }

        $destination = $this->dir . $fileName;

        // Ensure destination directory exists and is writable
        if (!$this->ensureDirectoryExists($destination)) {
            $this->error = 'Failed to create destination directory: ' . dirname($destination);
            return false;
        }

        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Set proper permissions (readable and writable by owner, readable by others)
            chmod($destination, 0644);
            return true;
        } else {
            $this->error = 'Failed to move uploaded file to destination';
            return false;
        }
    }

    /**
     * Apply XSS protection to text-based files only.
     */
    private function protectFile(array $file): void
    {
        // Only apply XSS protection to text-based files
        if ($this->isBinaryFile($file)) {
            return; // Skip binary files
        }

        try {
            $content = file_get_contents($file['tmp_name']);

            if ($content === false) {
                return; // Skip if we can't read the file
            }

            // Basic XSS protection - escape HTML entities
            $protectedContent = $this->sanitizeContent($content);

            if (file_put_contents($file['tmp_name'], $protectedContent, LOCK_EX) === false) {
                // Log the error but don't fail the upload
                $this->logXssWarning('Failed to apply XSS protection to file: ' . $file['name']);
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the upload
            $this->logXssWarning('XSS protection error for file ' . $file['name'] . ': ' . $e->getMessage());
        }
    }

    /**
     * Log XSS protection warnings if logging is enabled.
     */
    private function logXssWarning(string $message): void
    {
        if ($this->enableLogging) {
            $logger = new Log();
            $logger->setLogName($this->logFileName)->write('Warning: ' . $message);
        }
    }

    /**
     * Generate a secure filename to prevent conflicts and security issues.
     */
    private function generateSecureFilename(string $originalName): string
    {
        // Get the original extension
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Sanitize the base filename
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = $this->sanitizeFilename($baseName);

        // Generate unique filename with timestamp and random component
        $uniqueId = uniqid() . '_' . substr(md5(microtime()), 0, 8);

        return $baseName . '_' . $uniqueId . '.' . $extension;
    }

    /**
     * Sanitize filename to prevent security issues.
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove or replace dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove multiple consecutive underscores/dots
        $filename = preg_replace('/[._-]{2,}/', '_', $filename);

        // Remove leading/trailing underscores and dots
        $filename = trim($filename, '._-');

        // Ensure filename is not empty and has reasonable length
        if (empty($filename)) {
            $filename = 'file';
        }

        if (strlen($filename) > $this->maxLength - 10) { // Leave room for unique suffix
            $filename = substr($filename, 0, $this->maxLength - 10);
        }

        return $filename;
    }

    /**
     * Sanitize content for XSS protection.
     */
    private function sanitizeContent(string $content): string
    {
        // Basic HTML entity encoding for XSS protection
        // This is a simplified approach - in production, consider using more sophisticated sanitization
        return htmlentities($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Ensure the destination directory exists and is writable.
     */
    private function ensureDirectoryExists(string $destination): bool
    {
        $directory = dirname($destination);

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return false;
            }
        }

        // Ensure directory is writable
        if (!is_writable($directory)) {
            if (!chmod($directory, 0755)) {
                return false;
            }
        }

        return true;
    }
}
