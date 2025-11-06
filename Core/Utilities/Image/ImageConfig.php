<?php

declare(strict_types=1);

namespace Core\Utilities\Image;

/**
 * Image Configuration Class
 *
 * Provides comprehensive configuration options for the Image component,
 * including file size limits, dimension constraints, quality settings,
 * supported formats, watermarking options, caching, and logging preferences.
 *
 * This configuration class allows fine-tuning of image processing behavior
 * and provides validation to ensure all settings are within acceptable ranges.
 *
 * @package Core\Utilities\Image
 */
class ImageConfig
{
    /**
     * Maximum file size in bytes (default: 10MB)
     *
     * This setting limits the size of image files that can be processed.
     * Files larger than this limit will be rejected during loading.
     *
     * @var int Maximum file size in bytes
     */
    public int $maxFileSize = 10 * 1024 * 1024;

    /**
     * Maximum width in pixels
     *
     * Images with width exceeding this value will be rejected during loading.
     * This helps prevent memory issues with extremely large images.
     *
     * @var int Maximum allowed width in pixels
     */
    public int $maxWidth = 4096;

    /**
     * Maximum height in pixels
     *
     * Images with height exceeding this value will be rejected during loading.
     * This helps prevent memory issues with extremely large images.
     *
     * @var int Maximum allowed height in pixels
     */
    public int $maxHeight = 4096;

    /**
     * Minimum width in pixels
     *
     * Images with width below this value will be rejected during loading.
     * This ensures a minimum quality standard for processed images.
     *
     * @var int Minimum allowed width in pixels
     */
    public int $minWidth = 1;

    /**
     * Minimum height in pixels
     *
     * Images with height below this value will be rejected during loading.
     * This ensures a minimum quality standard for processed images.
     *
     * @var int Minimum allowed height in pixels
     */
    public int $minHeight = 1;

    /**
     * Default JPEG quality (0-100)
     *
     * Controls the compression quality when saving JPEG images.
     * Higher values result in better quality but larger file sizes.
     *
     * @var int JPEG quality level (0-100)
     */
    public int $defaultJpegQuality = 90;

    /**
     * Default WebP quality (0-100)
     *
     * Controls the compression quality when saving WebP images.
     * Higher values result in better quality but larger file sizes.
     *
     * @var int WebP quality level (0-100)
     */
    public int $defaultWebpQuality = 90;

    /**
     * Default PNG compression level (0-9)
     *
     * Controls the compression level when saving PNG images.
     * Higher values result in better compression but slower processing.
     *
     * @var int PNG compression level (0-9)
     */
    public int $defaultPngCompression = 6;

    /**
     * Supported input formats
     *
     * Array of file extensions that are accepted as valid input formats.
     * These formats can be loaded and processed by the image component.
     *
     * @var array<string> List of supported input file extensions
     */
    public array $supportedInputFormats = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'
    ];

    /**
     * Supported output formats
     *
     * Array of file extensions that are supported for saving processed images.
     * The component can convert between compatible formats.
     *
     * @var array<string> List of supported output file extensions
     */
    public array $supportedOutputFormats = [
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    ];

    /**
     * Watermark position options
     *
     * Available positions where watermarks can be placed on images.
     * Used for automatic watermark positioning during processing.
     *
     * @var array<string> List of available watermark positions
     */
    public array $watermarkPositions = [
        'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'
    ];

    /**
     * Default watermark position
     *
     * The default position where watermarks will be placed if not specified.
     * This provides a sensible default for automated watermarking.
     *
     * @var string Default watermark position
     */
    public string $defaultWatermarkPosition = 'bottom-right';

    /**
     * Watermark padding from edges (pixels)
     *
     * The number of pixels to maintain as padding between the watermark
     * and the image edges when using automatic positioning.
     *
     * @var int Padding distance in pixels
     */
    public int $watermarkPadding = 10;

    /**
     * Log file name (without extension)
     *
     * The base name for log files created by the image component.
     * The framework's logging system will handle the full path and extension.
     *
     * @var string Base name for log files
     */
    public string $logFileName = 'image_component';

    /**
     * Enable logging using Core\Log
     *
     * Controls whether the image component should log operations and errors.
     * When enabled, uses the framework's Core\Log system for consistent logging.
     *
     * @var bool Whether to enable logging
     */
    public bool $enableLogging = true;

    /**
     * Allowed memory limit increase for large images
     *
     * PHP memory limit that the component can request when processing
     * large images. This helps prevent memory exhaustion during processing.
     *
     * @var string Memory limit string (e.g., '256M', '512M')
     */
    public string $memoryLimit = '256M';

    /**
     * Cache processed images
     *
     * Whether to enable caching of processed images to improve performance
     * for repeated operations on the same source images.
     *
     * @var bool Whether to enable image caching
     */
    public bool $enableCache = false;

    /**
     * Cache directory path
     *
     * The directory where processed image caches should be stored.
     * If empty, caching will be disabled or use a default location.
     *
     * @var string Path to cache directory
     */
    public string $cachePath = '';

    /**
     * Cache expiration time in seconds (24 hours)
     *
     * How long cached processed images should be considered valid
     * before being regenerated from source images.
     *
     * @var int Cache expiration time in seconds
     */
    public int $cacheExpiration = 86400;

    /**
     * Create configuration from array
     *
     * Creates a new ImageConfig instance and populates it with values from
     * the provided array. Only properties that exist in the class will be set.
     *
     * @param array $config Associative array of configuration options
     * @return self New ImageConfig instance with the provided settings
     *
     * @example
     * $config = ImageConfig::fromArray([
     *     'maxFileSize' => 5 * 1024 * 1024, // 5MB
     *     'defaultJpegQuality' => 85,
     *     'enableLogging' => false
     * ]);
     */
    public static function fromArray(array $config): self
    {
        $instance = new self();

        foreach ($config as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        return $instance;
    }

    /**
     * Convert configuration to array
     *
     * Returns all public properties of the configuration object as an array.
     * This is useful for serialization, debugging, or passing config to other systems.
     *
     * @return array Associative array of all configuration properties
     *
     * @example
     * $configArray = $imageConfig->toArray();
     * // Returns all current configuration as key-value pairs
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Validate configuration
     *
     * Validates all configuration values to ensure they are within acceptable ranges
     * and logically consistent. Returns an array of error messages for any invalid settings.
     *
     * @return array Array of validation error messages. Empty array if all settings are valid.
     *
     * @example
     * $errors = $imageConfig->validate();
     * if (!empty($errors)) {
     *     echo "Configuration errors: " . implode(', ', $errors);
     * }
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->maxFileSize <= 0) {
            $errors[] = 'maxFileSize must be positive';
        }

        if ($this->maxWidth <= 0 || $this->maxHeight <= 0) {
            $errors[] = 'maxWidth and maxHeight must be positive';
        }

        if ($this->minWidth < 0 || $this->minHeight < 0) {
            $errors[] = 'minWidth and minHeight must be non-negative';
        }

        if ($this->minWidth > $this->maxWidth || $this->minHeight > $this->maxHeight) {
            $errors[] = 'minWidth/minHeight cannot be greater than maxWidth/maxHeight';
        }

        if ($this->defaultJpegQuality < 0 || $this->defaultJpegQuality > 100) {
            $errors[] = 'defaultJpegQuality must be between 0 and 100';
        }

        if ($this->defaultWebpQuality < 0 || $this->defaultWebpQuality > 100) {
            $errors[] = 'defaultWebpQuality must be between 0 and 100';
        }

        if ($this->defaultPngCompression < 0 || $this->defaultPngCompression > 9) {
            $errors[] = 'defaultPngCompression must be between 0 and 9';
        }

        if ($this->watermarkPadding < 0) {
            $errors[] = 'watermarkPadding must be non-negative';
        }

        if (!in_array($this->defaultWatermarkPosition, $this->watermarkPositions)) {
            $errors[] = 'Invalid defaultWatermarkPosition';
        }

        return $errors;
    }
}
