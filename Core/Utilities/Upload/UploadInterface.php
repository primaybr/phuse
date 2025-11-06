<?php

declare(strict_types=1);

namespace Core\Utilities\Upload;

/**
 * Upload Interface
 *
 * Defines the contract for file upload classes that can handle secure file uploads
 * with validation, security checks, and configuration options. This interface provides
 * a comprehensive API for managing file uploads with features like extension validation,
 * size limits, image dimension checks, XSS protection, and MIME type verification.
 *
 * The upload system supports:
 * - Configurable file size limits and extension validation
 * - Image dimension constraints and validation
 * - MIME type checking for security
 * - XSS protection and filename sanitization
 * - Custom file naming and directory organization
 * - Comprehensive logging integration
 * - Error handling and validation feedback
 * - Fluent interface for easy configuration
 *
 * @package Core\Utilities\Upload
 */
interface UploadInterface
{
    /**
     * Set the upload directory
     *
     * Specifies the directory where uploaded files will be stored.
     *
     * @param string $path The path to the upload directory
     *
     * @return void
     */
    public function setDir(string $path): void;

    /**
     * Set the maximum file size in bytes
     *
     * Defines the maximum allowed size for uploaded files.
     *
     * @param int $size The maximum file size in bytes
     *
     * @return void
     */
    public function setMaxSize(int $size): void;

    /**
     * Set the allowed file extensions
     *
     * Specifies the allowed file extensions for uploaded files.
     *
     * @param array $extensions An array of allowed file extensions
     *
     * @return void
     */
    public function setExtensions(array $extensions): void;

    /**
     * Set the custom file name
     *
     * Allows setting a custom file name for the uploaded file.
     * The file extension will be preserved from the original file.
     *
     * @param string $name The custom file name (without extension)
     * @return void
     */
    public function setFileName(string $name): void;

    /**
     * Set the maximum file name length
     *
     * Defines the maximum allowed length for uploaded file names.
     * Longer names will be truncated or rejected.
     *
     * @param int $length The maximum filename length in characters
     * @return void
     */
    public function setMaxLength(int $length): void;

    /**
     * Set the XSS protection flag
     *
     * Enables or disables XSS protection for uploaded files.
     * When enabled, performs additional security checks on file contents.
     *
     * @param bool $flag Whether to enable XSS protection
     * @return void
     */
    public function setXSSProtection(bool $flag): void;

    /**
     * Set the minimum and maximum width and height for images
     *
     * Defines dimension constraints for uploaded image files.
     * Images outside these dimensions will be rejected.
     *
     * @param int $minWidth Minimum allowed image width in pixels
     * @param int $maxWidth Maximum allowed image width in pixels
     * @param int $minHeight Minimum allowed image height in pixels
     * @param int $maxHeight Maximum allowed image height in pixels
     * @return void
     */
    public function setDimensions(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight): void;

    /**
     * Set the allowed MIME types
     *
     * Specifies the allowed MIME types for uploaded files by extension.
     * This provides an additional layer of security beyond extension checking.
     *
     * @param array $allowed Associative array of extension => MIME type(s)
     * @return void
     */
    public function setMimes(array $allowed): void;

    /**
     * Configure upload with a configuration object
     *
     * Applies all settings from a configuration object to the upload instance.
     * This provides a convenient way to configure multiple settings at once.
     *
     * @param UploadConfig $config Configuration object with upload settings
     * @return self Returns self for method chaining
     */
    public function configure(UploadConfig $config): self;

    /**
     * Set log file name for logging operations
     *
     * Specifies the log file name for upload operation logging.
     * The framework's logging system will handle the full path.
     *
     * @param string $name Base name for the log file
     * @return void
     */
    public function setLogFileName(string $name): void;

    /**
     * Get current log file name
     *
     * Returns the currently configured log file name.
     *
     * @return string Current log file name
     */
    public function getLogFileName(): string;

    /**
     * Set whether logging is enabled
     *
     * Enables or disables logging for upload operations.
     * When disabled, no upload events will be logged.
     *
     * @param bool $enabled Whether to enable logging
     * @return void
     */
    public function setEnableLogging(bool $enabled): void;

    /**
     * Check if logging is enabled
     *
     * Returns whether logging is currently enabled for upload operations.
     *
     * @return bool Whether logging is enabled
     */
    public function isLoggingEnabled(): bool;

    /**
     * Upload the file and return true or false
     *
     * Processes the uploaded file with all configured validations and security checks.
     * If successful, moves the file to the upload directory and returns true.
     * If any validation fails, returns false and sets an error message.
     *
     * @param array $file The $_FILES array element for the uploaded file
     * @return bool True if upload successful, false otherwise
     */
    public function upload(array $file): bool;

    /**
     * Get the error message if any
     *
     * Returns the last error message from upload operations.
     * Returns an empty string if no errors occurred.
     *
     * @return string Error message or empty string
     */
    public function getError(): string;
}
