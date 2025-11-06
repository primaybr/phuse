<?php

declare(strict_types=1);

namespace Core\Utilities\Image;

use Core\Log;

/**
 * Image Manipulation Class
 *
 * A comprehensive image processing class that provides a fluent interface for
 * loading, manipulating, and saving images in various formats. This class implements
 * the ImageInterface and uses the ImageTrait to provide core image processing functionality.
 *
 * Features include:
 * - Loading images from files with validation
 * - Resizing, cropping, and rotating images
 * - Compression with quality control
 * - Watermarking support
 * - Multiple output format support (JPEG, PNG, GIF, WebP)
 * - Direct browser output capability
 * - Comprehensive error handling and logging
 * - Memory management and cleanup
 *
 * Example usage:
 * ```php
 * $image = new Image();
 * $image->setImageSource('input.jpg')
 *       ->resize(800, 600)
 *       ->compress(85)
 *       ->save('output.jpg');
 * ```
 *
 * @package Core\Utilities\Image
 */
class Image implements ImageInterface
{
    use ImageTrait;

    /**
     * Image configuration instance
     *
     * @var ImageConfig
     */
    protected ImageConfig $config;

    /**
     * Logger instance for logging image processing events
     *
     * @var Log|null
     */
    protected ?Log $logger;

    /**
     * GD image resource from ImageTrait
     *
     * The active GD image resource used for all image manipulations.
     * This is the working copy that gets modified by various operations.
     *
     * @var \GdImage|null
     */
    protected $image;

    /**
     * Source image file path from ImageTrait
     *
     * @var string|null
     */
    protected $imageSource;

    /**
     * Detected image format/type from ImageTrait
     *
     * @var string|null
     */
    protected $imageType;

    /**
     * Original image width in pixels from ImageTrait
     *
     * @var int|null
     */
    protected $originalWidth;

    /**
     * Original image height in pixels from ImageTrait
     *
     * @var int|null
     */
    protected $originalHeight;

    /**
     * Array of error messages from ImageTrait
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Image constructor
     *
     * Initializes a new Image instance with optional configuration, logging,
     * and automatic image loading. If an image path is provided, the image
     * will be loaded immediately during construction.
     *
     * @param string|null $imagePath Path to an image file to load immediately (optional)
     * @param ImageConfig|null $config Custom configuration object (optional, uses defaults if null)
     * @param Log|null $logger Custom logger instance (optional, creates new logger if null)
     *
     * @example
     * // Create instance without loading image
     * $image = new Image();
     *
     * // Create instance with immediate image loading
     * $image = new Image('path/to/image.jpg');
     *
     * // Create instance with custom configuration
     * $config = new ImageConfig();
     * $config->maxWidth = 1920;
     * $image = new Image('image.jpg', $config);
     */
    public function __construct(?string $imagePath = null, ?ImageConfig $config = null, ?Log $logger = null)
    {
        $this->config = $config ?? new ImageConfig();
        $this->logger = $logger ?? $this->createLogger();

        if ($imagePath !== null) {
            $this->setImageSource($imagePath);
        }
    }

    /**
     * Create logger instance using existing Core\Log
     *
     * Creates a new logger instance based on the current configuration settings.
     * If logging is disabled in the configuration, returns null.
     *
     * @return Log|null New logger instance or null if logging is disabled
     */
    protected function createLogger(): ?Log
    {
        if ($this->config->enableLogging) {
            $log = new Log();
            $log->setLogName($this->config->logFileName);
            return $log;
        }
        return null;
    }

    /**
     * Set configuration
     *
     * Updates the image configuration and recreates the logger if necessary.
     * This allows changing configuration settings after the object is created.
     *
     * @param ImageConfig $config The new configuration object to use
     * @return self Returns self for method chaining
     *
     * @example
     * $image->setConfig($customConfig)->resize(800, 600);
     */
    public function setConfig(ImageConfig $config): self
    {
        $this->config = $config;
        // Recreate logger if config changed
        $this->logger = $this->createLogger();
        return $this;
    }

    /**
     * Get configuration
     *
     * Returns the current image configuration object. This allows inspection
     * of current settings and can be used to create modified configurations.
     *
     * @return ImageConfig The current configuration object
     *
     * @example
     * $config = $image->getConfig();
     * $newConfig = ImageConfig::fromArray($config->toArray());
     * $newConfig->maxWidth = 1200;
     */
    public function getConfig(): ImageConfig
    {
        return $this->config;
    }

    /**
     * Set logger
     *
     * Sets a custom logger instance to use for logging image processing events.
     * Pass null to disable logging. The logger will be used for all subsequent operations.
     *
     * @param Log|null $logger The logger instance to use, or null to disable logging
     * @return self Returns self for method chaining
     *
     * @example
     * $customLogger = new Log();
     * $image->setLogger($customLogger)->resize(800, 600);
     */
    public function setLogger(?Log $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get logger
     *
     * Returns the current logger instance used by the image component.
     * This can be used to inspect logging configuration or attach additional handlers.
     *
     * @return Log|null The current logger instance, or null if logging is disabled
     *
     * @example
     * $logger = $image->getLogger();
     * if ($logger) {
     *     $logger->setLogName('custom_image_log');
     * }
     */
    public function getLogger(): ?Log
    {
        return $this->logger;
    }

    /**
     * Log a message using Core\Log
     *
     * Internal method for logging messages with different levels and context.
     * Only logs if a logger is available. This method is used throughout the
     * image processing operations to track events and errors.
     *
     * @param string $level The log level (INFO, ERROR, WARNING, DEBUG)
     * @param string $message The log message
     * @param array $context Additional context data to include with the log entry
     *
     * @example
     * $this->log('ERROR', 'Failed to resize image', ['width' => 800, 'height' => 600]);
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $contextString = empty($context) ? '' : ' ' . json_encode($context);
            $this->logger->write("[$level] $message$contextString");
        }
    }

    /**
     * Clean up memory by destroying the image resource
     *
     * Destroys the current GD image resource and clears any errors to free up memory.
     * This method should be called when you're done processing an image to prevent
     * memory leaks, especially when processing multiple images in a loop.
     *
     * @return void
     *
     * @example
     * $image = new Image('input.jpg');
     * $image->resize(800, 600)->save('output.jpg');
     * $image->destroy(); // Clean up memory
     */
    public function destroy(): void
    {
        // Use reflection or just implement the logic directly
        // Since the trait is used in this class, the method should be available
        if (isset($this->image) && $this->image !== null) {
            imagedestroy($this->image);
            unset($this->image);
        }
        if (isset($this->errors)) {
            $this->errors = [];
        }
    }
}
