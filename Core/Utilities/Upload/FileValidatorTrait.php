<?php

declare(strict_types=1);

namespace Core\Utilities\Upload;

/**
 * File Validation Trait
 *
 * Provides comprehensive file validation methods for secure uploads.
 * Includes validation for file size, extension, MIME type, and image dimensions.
 *
 * @package Core\Utilities\Upload
 * @author  Phuse Framework
 */
trait FileValidatorTrait
{
    /**
     * Check if the file is valid according to all configured rules.
     */
    private function isValid(array $file): bool
    {
        // Validate uploaded file
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error = 'File was not uploaded properly';
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->error = 'File size exceeds maximum allowed size of ' . $this->formatBytes($this->maxSize);
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->extensions)) {
            $this->error = 'File extension "' . $extension . '" is not allowed. Allowed types: ' . implode(', ', $this->extensions);
            return false;
        }

        // Check filename length
        if (strlen($file['name']) > $this->maxLength) {
            $this->error = 'Filename is too long (maximum ' . $this->maxLength . ' characters)';
            return false;
        }

        // Check if it's an image and validate dimensions
        if ($this->isImage($file) && !$this->isValidImage($file)) {
            $this->error = 'Image dimensions are invalid. ' . $this->error;
            return false;
        }

        // Check MIME type
        if (!$this->isValidMimeType($file)) {
            $this->error = 'File MIME type is not allowed';
            return false;
        }

        return true;
    }

    /**
     * Check if the file is an image based on extension.
     */
    private function isImage(array $file): bool
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif'];

        return in_array($extension, $imageExtensions);
    }

    /**
     * Check if the image dimensions are within the configured limits.
     */
    private function isValidImage(array $file): bool
    {
        // Get image size information
        $imageInfo = getimagesize($file['tmp_name']);

        if ($imageInfo === false) {
            $this->error = 'Unable to determine image dimensions';
            return false;
        }

        [$width, $height] = $imageInfo;

        // Check width constraints
        if ($width < $this->minWidth || $width > $this->maxWidth) {
            $this->error = "Image width must be between {$this->minWidth}px and {$this->maxWidth}px (current: {$width}px)";
            return false;
        }

        // Check height constraints
        if ($height < $this->minHeight || $height > $this->maxHeight) {
            $this->error = "Image height must be between {$this->minHeight}px and {$this->maxHeight}px (current: {$height}px)";
            return false;
        }

        return true;
    }

    /**
     * Check if the file MIME type is allowed.
     */
    private function isValidMimeType(array $file): bool
    {
        if (empty($this->allowedMimes)) {
            // If no MIME types are configured, skip this check
            return true;
        }

        $fileMime = mime_content_type($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Check if extension exists in allowed mimes
        if (!isset($this->allowedMimes[$extension])) {
            return false;
        }

        $allowedMimesForExtension = $this->allowedMimes[$extension];

        // Handle both string and array formats
        if (is_string($allowedMimesForExtension)) {
            return $fileMime === $allowedMimesForExtension;
        }

        if (is_array($allowedMimesForExtension)) {
            return in_array($fileMime, $allowedMimesForExtension);
        }

        return false;
    }

    /**
     * Format bytes into human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }
}
