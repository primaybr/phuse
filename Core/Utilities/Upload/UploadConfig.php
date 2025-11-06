<?php

declare(strict_types=1);

namespace Core\Utilities\Upload;

/**
 * Upload Configuration Class
 *
 * Provides comprehensive configuration options for the Upload component,
 * including file size limits, extension validation, image dimension constraints,
 * security settings, MIME type checking, and logging preferences. This class
 * centralizes all upload customization options and provides validation.
 *
 * Configuration areas include:
 * - File size limits and validation
 * - Allowed file extensions and formats
 * - Image dimension constraints for security
 * - MIME type validation for additional security
 * - XSS protection and filename sanitization
 * - Logging integration with Core\Log
 * - Security hardening options
 *
 * The class provides convenient factory methods for common use cases:
 * - forImages(): Secure defaults for image uploads
 * - forDocuments(): Secure defaults for document uploads
 *
 * @package Core\Utilities\Upload
 * @author  Phuse Framework
 */
class UploadConfig
{
    /**
     * Maximum file size in bytes (default: 5MB)
     *
     * The maximum allowed size for uploaded files. Files larger than this
     * limit will be rejected during upload validation. This helps prevent
     * server resource exhaustion and provides security against large file attacks.
     *
     * @var int Maximum file size in bytes
     */
    private int $maxSize = 5_000_000; // 5MB default

    /**
     * Allowed file extensions for upload
     *
     * Array of file extensions that are permitted for upload. Extensions are
     * automatically converted to lowercase for case-insensitive validation.
     * This provides the first layer of security against malicious file uploads.
     *
     * @var array<string> List of allowed file extensions
     */
    private array $allowedExtensions = ['jpg', 'png', 'gif', 'webp'];

    /**
     * Maximum filename length
     *
     * The maximum allowed length for uploaded file names in characters.
     * Longer filenames will be truncated or rejected to prevent filesystem issues
     * and maintain consistent naming conventions.
     *
     * @var int Maximum filename length in characters
     */
    private int $maxFilenameLength = 64;

    /**
     * XSS protection enabled
     *
     * Whether to enable Cross-Site Scripting protection for uploaded files.
     * When enabled, performs additional security checks on file contents
     * to detect and prevent potential XSS attacks through file uploads.
     *
     * @var bool Whether XSS protection is enabled
     */
    private bool $xssProtection = true;

    /**
     * Minimum image width constraint
     *
     * The minimum allowed width for uploaded image files in pixels.
     * Images smaller than this dimension will be rejected. Set to 0 to disable.
     *
     * @var int Minimum image width in pixels
     */
    private int $minImageWidth = 50;

    /**
     * Maximum image width constraint
     *
     * The maximum allowed width for uploaded image files in pixels.
     * Images larger than this dimension will be rejected to prevent memory issues.
     *
     * @var int Maximum image width in pixels
     */
    private int $maxImageWidth = 3200;

    /**
     * Minimum image height constraint
     *
     * The minimum allowed height for uploaded image files in pixels.
     * Images smaller than this dimension will be rejected. Set to 0 to disable.
     *
     * @var int Minimum image height in pixels
     */
    private int $minImageHeight = 50;

    /**
     * Maximum image height constraint
     *
     * The maximum allowed height for uploaded image files in pixels.
     * Images larger than this dimension will be rejected to prevent memory issues.
     *
     * @var int Maximum image height in pixels
     */
    private int $maxImageHeight = 2400;

    /**
     * Allowed MIME types for file validation
     *
     * Associative array mapping file extensions to allowed MIME types.
     * This provides an additional layer of security beyond extension checking
     * by validating the actual file content type.
     *
     * @var array<string, string|array> Extension to MIME type mapping
     */
    private array $allowedMimes = [];

    /**
     * Log file name (without extension)
     *
     * The base name for log files created by the upload component.
     * The framework's logging system will handle the full path and extension.
     * Different names help organize logs by upload type (e.g., 'upload/images').
     *
     * @var string Base name for upload log files
     */
    public string $logFileName = 'upload/upload';

    /**
     * Enable logging using Core\Log
     *
     * Controls whether the upload component should log operations and events.
     * When enabled, uses the framework's Core\Log system for consistent logging
     * and provides detailed information about upload attempts and security checks.
     *
     * @var bool Whether to enable upload logging
     */
    public bool $enableLogging = true;

    /**
     * Set maximum file size in bytes
     *
     * Configures the maximum allowed size for uploaded files. Files exceeding
     * this limit will be rejected during validation. This helps prevent server
     * resource exhaustion and provides security against large file attacks.
     *
     * @param int $size The maximum file size in bytes (must be positive)
     * @return self Returns self for method chaining
     * @throws \InvalidArgumentException If size is not positive
     *
     * @example
     * $config->setMaxSize(2_000_000); // 2MB limit
     * $config->setMaxSize(10 * 1024 * 1024); // 10MB limit
     */
    public function setMaxSize(int $size): self
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Maximum file size must be positive');
        }

        $this->maxSize = $size;
        return $this;
    }

    /**
     * Get maximum file size in bytes
     *
     * Returns the currently configured maximum file size limit.
     *
     * @return int Current maximum file size in bytes
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Set allowed file extensions
     *
     * Configures the file extensions that are permitted for upload.
     * Extensions are automatically converted to lowercase and validated
     * for security. This provides the primary security layer against
     * malicious file uploads.
     *
     * @param array $extensions Array of allowed file extensions (without dots)
     * @return self Returns self for method chaining
     * @throws \InvalidArgumentException If any extension contains invalid characters
     *
     * @example
     * $config->setAllowedExtensions(['jpg', 'png', 'gif']);
     * $config->setAllowedExtensions(['pdf', 'doc', 'docx', 'txt']);
     */
    public function setAllowedExtensions(array $extensions): self
    {
        // Convert to lowercase and validate
        $extensions = array_map('strtolower', $extensions);

        foreach ($extensions as $extension) {
            if (!preg_match('/^[a-z0-9]+$/', $extension)) {
                throw new \InvalidArgumentException("Invalid file extension: {$extension}");
            }
        }

        $this->allowedExtensions = $extensions;
        return $this;
    }

    /**
     * Get allowed file extensions.
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Set maximum filename length.
     */
    public function setMaxFilenameLength(int $length): self
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('Maximum filename length must be positive');
        }

        $this->maxFilenameLength = $length;
        return $this;
    }

    /**
     * Get maximum filename length.
     */
    public function getMaxFilenameLength(): int
    {
        return $this->maxFilenameLength;
    }

    /**
     * Enable or disable XSS protection.
     */
    public function setXssProtection(bool $enabled): self
    {
        $this->xssProtection = $enabled;
        return $this;
    }

    /**
     * Check if XSS protection is enabled.
     */
    public function isXssProtectionEnabled(): bool
    {
        return $this->xssProtection;
    }

    /**
     * Set image dimension constraints.
     */
    public function setImageDimensions(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight): self
    {
        if ($minWidth <= 0 || $maxWidth <= 0 || $minHeight <= 0 || $maxHeight <= 0) {
            throw new \InvalidArgumentException('Image dimensions must be positive');
        }

        if ($minWidth > $maxWidth || $minHeight > $maxHeight) {
            throw new \InvalidArgumentException('Minimum dimensions cannot exceed maximum dimensions');
        }

        $this->minImageWidth = $minWidth;
        $this->maxImageWidth = $maxWidth;
        $this->minImageHeight = $minHeight;
        $this->maxImageHeight = $maxHeight;

        return $this;
    }

    /**
     * Get minimum image width.
     */
    public function getMinImageWidth(): int
    {
        return $this->minImageWidth;
    }

    /**
     * Get maximum image width.
     */
    public function getMaxImageWidth(): int
    {
        return $this->maxImageWidth;
    }

    /**
     * Get minimum image height.
     */
    public function getMinImageHeight(): int
    {
        return $this->minImageHeight;
    }

    /**
     * Get maximum image height.
     */
    public function getMaxImageHeight(): int
    {
        return $this->maxImageHeight;
    }

    /**
     * Set allowed MIME types.
     */
    public function setAllowedMimes(array $mimes): self
    {
        // Validate MIME type format
        foreach ($mimes as $extension => $mimeTypes) {
            if (!is_string($extension) || !preg_match('/^[a-z0-9]+$/', $extension)) {
                throw new \InvalidArgumentException("Invalid file extension: {$extension}");
            }

            if (is_string($mimeTypes)) {
                $mimeTypes = [$mimeTypes];
            }

            if (!is_array($mimeTypes)) {
                throw new \InvalidArgumentException("MIME types must be string or array for extension: {$extension}");
            }

            foreach ($mimeTypes as $mimeType) {
                if (!is_string($mimeType) || !preg_match('/^[a-zA-Z][a-zA-Z0-9][a-zA-Z0-9\!\#\$\&\-\^]*\/[a-zA-Z0-9][a-zA-Z0-9\!\#\$\&\-\^]*$/', $mimeType)) {
                    throw new \InvalidArgumentException("Invalid MIME type format: {$mimeType}");
                }
            }
        }

        $this->allowedMimes = $mimes;
        return $this;
    }

    /**
     * Get allowed MIME types.
     */
    public function getAllowedMimes(): array
    {
        return $this->allowedMimes;
    }

    /**
     * Set log file name.
     */
    public function setLogFileName(string $name): self
    {
        $this->logFileName = $name;
        return $this;
    }

    /**
     * Get log file name.
     */
    public function getLogFileName(): string
    {
        return $this->logFileName;
    }

    /**
     * Enable or disable logging.
     */
    public function setEnableLogging(bool $enabled): self
    {
        $this->enableLogging = $enabled;
        return $this;
    }

    /**
     * Check if logging is enabled.
     */
    public function isLoggingEnabled(): bool
    {
        return $this->enableLogging;
    }

    /**
     * Get configuration as array for easy application to Upload instance
     *
     * Returns all configuration settings as an associative array that can be
     * used to configure an Upload instance. This method provides a convenient
     * way to export configuration for use with upload components.
     *
     * @return array Associative array of configuration settings
     *
     * @example
     * $configArray = $uploadConfig->toArray();
     * $uploader = new Upload();
     * foreach ($configArray as $method => $value) {
     *     if (method_exists($uploader, 'set' . ucfirst($method))) {
     *         $uploader->{'set' . ucfirst($method)}($value);
     *     }
     * }
     */
    public function toArray(): array
    {
        return [
            'maxSize' => $this->maxSize,
            'extensions' => $this->allowedExtensions,
            'maxLength' => $this->maxFilenameLength,
            'xssProtection' => $this->xssProtection,
            'minWidth' => $this->minImageWidth,
            'maxWidth' => $this->maxImageWidth,
            'minHeight' => $this->minImageHeight,
            'maxHeight' => $this->maxImageHeight,
            'allowedMimes' => $this->allowedMimes,
            'logFileName' => $this->logFileName,
            'enableLogging' => $this->enableLogging,
        ];
    }

    /**
     * Create configuration with secure defaults for images
     *
     * Returns a pre-configured UploadConfig instance optimized for image uploads.
     * This factory method provides secure defaults including appropriate file size
     * limits, image dimension constraints, and MIME type validation specifically
     * designed for image files.
     *
     * Security features included:
     * - 2MB file size limit (appropriate for images)
     * - Common image format extensions
     * - MIME type validation for security
     * - XSS protection enabled
     * - Reasonable image dimension limits
     *
     * @return self New UploadConfig instance configured for images
     *
     * @example
     * $imageConfig = UploadConfig::forImages();
     * $uploader = new Upload($imageConfig);
     * $uploader->upload($_FILES['image']);
     */
    public static function forImages(): self
    {
        return (new self())
            ->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'webp'])
            ->setAllowedMimes([
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png', 'image/x-png'],
                'gif' => ['image/gif'],
                'webp' => ['image/webp'],
            ])
            ->setImageDimensions(100, 2048, 100, 2048)
            ->setMaxSize(2_000_000) // 2MB for images
            ->setLogFileName('upload/images'); // Use subdirectory for clean organization
    }

    /**
     * Create configuration with secure defaults for documents
     *
     * Returns a pre-configured UploadConfig instance optimized for document uploads.
     * This factory method provides secure defaults including larger file size
     * limits appropriate for documents, disabled XSS protection (since documents
     * are typically binary), and MIME type validation for common document formats.
     *
     * Security features included:
     * - 10MB file size limit (appropriate for documents)
     * - Common document format extensions
     * - MIME type validation for security
     * - XSS protection disabled (binary formats)
     * - No image dimension validation (for documents)
     * - Organized logging in documents subdirectory
     *
     * @return self New UploadConfig instance configured for documents
     *
     * @example
     * $docConfig = UploadConfig::forDocuments();
     * $uploader = new Upload($docConfig);
     * $uploader->upload($_FILES['document']);
     */
    public static function forDocuments(): self
    {
        return (new self())
            ->setAllowedExtensions(['pdf', 'doc', 'docx', 'txt'])
            ->setAllowedMimes([
                'pdf' => ['application/pdf'],
                'doc' => ['application/msword'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'txt' => ['text/plain'],
            ])
            ->setImageDimensions(0, 0, 0, 0) // No image validation for documents
            ->setMaxSize(10_000_000) // 10MB for documents
            ->setXssProtection(false) // Disable XSS protection for binary formats
            ->setLogFileName('upload/documents'); // Use subdirectory for clean organization
    }
}
