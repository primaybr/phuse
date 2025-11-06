<?php

declare(strict_types=1);

namespace Core\Utilities\Image;

use Core\Log;

/**
 * Image Manipulation Interface
 *
 * Defines the contract for image manipulation classes that can load, process,
 * and save images in various formats. This interface provides a fluent API
 * for image operations like resizing, cropping, rotating, and compression.
 *
 * @package Core\Utilities\Image
 */
interface ImageInterface
{
    /**
     * Sets the source image path.
     *
     * @param string $imagePath The path to the source image file.
     *
     * @return self The instance of the image manipulation class.
     */
    public function setImageSource(string $imagePath): self;

    /**
     * Resizes the image to the specified dimensions.
     *
     * @param int $width  The new width of the image.
     * @param int $height The new height of the image.
     *
     * @return self The instance of the image manipulation class.
     */
    public function resize(int $width, int $height): self;

    /**
     * Crops the image to the specified region.
     *
     * @param int $x      The x-coordinate of the top-left corner of the crop region.
     * @param int $y      The y-coordinate of the top-left corner of the crop region.
     * @param int $width  The width of the crop region.
     * @param int $height The height of the crop region.
     *
     * @return self The instance of the image manipulation class.
     */
    public function crop(int $x, int $y, int $width, int $height): self;

    /**
     * Rotates the image by the specified angle.
     *
     * @param float $angle The angle of rotation in degrees.
     *
     * @return self The instance of the image manipulation class.
     */
    public function rotate(float $angle): self;

    /**
     * Compresses the image to the specified quality level.
     *
     * @param int $quality The quality level of the compressed image (1-100).
     *
     * @return self The instance of the image manipulation class.
     */
    public function compress(int $quality): self;
    /**
     * Adds a watermark to the image.
     *
     * @param string $watermarkPath The path to the watermark image (PNG format recommended).
     *
     * @return self The instance of the image manipulation class.
     */
    public function addWatermark(string $watermarkPath): self;
    /**
     * Saves the processed image to the specified file path.
     *
     * @param string $outputPath The path where the image should be saved.
     *
     * @return bool True if the image was saved successfully, false otherwise.
     */
    public function save(string $outputPath): bool;

    /**
     * Outputs the image directly to the browser.
     *
     * @return bool True if the image was output successfully, false otherwise.
     */
    public function output(): bool;

    // Enhanced methods

    /**
     * Checks if an image is currently loaded and ready for processing.
     *
     * @return bool True if an image is loaded and has no errors, false otherwise.
     */
    public function isLoaded(): bool;

    /**
     * Gets any errors that occurred during image processing.
     *
     * @return array An array of error messages.
     */
    public function getErrors(): array;

    /**
     * Gets the original dimensions of the loaded image.
     *
     * @return array An array containing 'width' and 'height' keys.
     */
    public function getOriginalDimensions(): array;

    /**
     * Gets the current dimensions of the image (after any processing).
     *
     * @return array An array containing 'width' and 'height' keys.
     */
    public function getCurrentDimensions(): array;

    /**
     * Destroys the current image resource and frees memory.
     *
     * @return void
     */
    public function destroy(): void;

    // Configuration and logging

    /**
     * Sets the configuration for the image processing.
     *
     * @param ImageConfig $config The configuration object to use.
     *
     * @return self The instance of the image manipulation class.
     */
    public function setConfig(ImageConfig $config): self;

    /**
     * Gets the current configuration object.
     *
     * @return ?ImageConfig The current configuration object, or null if not set.
     */
    public function getConfig(): ?ImageConfig;

    /**
     * Sets the logger instance for logging operations.
     *
     * @param Log $logger The logger instance to use.
     *
     * @return self The instance of the image manipulation class.
     */
    public function setLogger(Log $logger): self;

    /**
     * Gets the current logger instance.
     *
     * @return ?Log The current logger instance, or null if not set.
     */
    public function getLogger(): ?Log;
}
