<?php

declare(strict_types=1);

namespace Core\Utilities\Image;

/**
 * Image Manipulation Trait
 *
 * Provides core image processing functionality for loading, manipulating, and
 * processing images using PHP's GD extension. This trait contains all the
 * essential methods for image operations and is designed to be used with
 * classes that implement the ImageInterface.
 *
 * Features include:
 * - Image loading with validation and error handling
 * - Resizing with aspect ratio preservation options
 * - Cropping to specific regions
 * - Rotation with angle normalization
 * - Compression and quality control
 * - Watermarking with multiple positioning options
 * - Format conversion and output
 * - Comprehensive error tracking
 * - Memory management and cleanup
 *
 * This trait uses protected properties that should be declared in the using class:
 * - $image: GD image resource
 * - $imageSource: Original image file path
 * - $imageType: Detected image format
 * - $originalWidth: Original image width
 * - $originalHeight: Original image height
 * - $errors: Array of error messages
 *
 * @package Core\Utilities\Image
 */
trait ImageTrait
{
    /**
     * GD image resource
     *
     * The active GD image resource used for all image manipulations.
     * This is the working copy that gets modified by various operations.
     *
     * @var \GdImage|null
     */
    protected $image;

    /**
     * Source image file path
     *
     * The original file path of the loaded image. Used for format detection,
     * logging, and determining output formats when not explicitly specified.
     *
     * @var string|null
     */
    protected $imageSource;

    /**
     * Detected image format/type
     *
     * The format of the original image (e.g., 'jpg', 'png', 'gif').
     * Used for determining appropriate output methods and compression settings.
     *
     * @var string|null
     */
    protected $imageType;

    /**
     * Original image width in pixels
     *
     * The width of the image when it was first loaded. This value remains
     * unchanged throughout processing and is used for logging and calculations.
     *
     * @var int|null
     */
    protected $originalWidth;

    /**
     * Original image height in pixels
     *
     * The height of the image when it was first loaded. This value remains
     * unchanged throughout processing and is used for logging and calculations.
     *
     * @var int|null
     */
    protected $originalHeight;

    /**
     * Array of error messages
     *
     * Stores error messages that occur during image processing operations.
     * Errors are accumulated and can be retrieved using getErrors() method.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Set the image source and validate the file
     *
     * Loads an image from the specified file path with comprehensive validation.
     * This method clears any previous errors, validates file existence, checks
     * file size and dimensions against configuration limits, and loads the image
     * into memory for processing.
     *
     * Validation includes:
     * - File existence and readability
     * - File size limits (configurable)
     * - Image dimension limits (configurable)
     * - Supported format validation
     * - Image integrity checks
     *
     * @param string $imagePath Path to the image file to load
     * @return self Returns self for method chaining
     *
     * @example
     * $image->setImageSource('uploads/photo.jpg')
     *       ->resize(800, 600)
     *       ->save('resized_photo.jpg');
     */
    public function setImageSource(string $imagePath): self
    {
        // Clear previous errors
        $this->errors = [];

        // Validate file exists and is readable
        if (!file_exists($imagePath)) {
            $this->errors[] = 'Image file does not exist: ' . $imagePath;
            $this->log('ERROR', 'Image file does not exist', ['path' => $imagePath]);
            return $this;
        }

        if (!is_readable($imagePath)) {
            $this->errors[] = 'Image file is not readable: ' . $imagePath;
            $this->log('ERROR', 'Image file is not readable', ['path' => $imagePath]);
            return $this;
        }

        // Check file size (use config if available)
        $fileSize = filesize($imagePath);
        $maxSize = $this->getConfigValue('maxFileSize', 10 * 1024 * 1024);
        if ($fileSize > $maxSize) {
            $this->errors[] = 'Image file too large: ' . $fileSize . ' bytes (max: ' . $maxSize . ')';
            $this->log('ERROR', 'Image file too large', ['size' => $fileSize, 'maxSize' => $maxSize]);
            return $this;
        }

        $this->imageSource = $imagePath;
        $this->imageType = $this->getImageType($imagePath);

        // Create image resource based on type
        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            $this->errors[] = 'Failed to read image file: ' . $imagePath;
            $this->log('ERROR', 'Failed to read image file', ['path' => $imagePath]);
            return $this;
        }

        $this->image = imagecreatefromstring($imageData);
        if ($this->image === false) {
            $this->errors[] = 'Invalid image format or corrupted file: ' . $imagePath;
            $this->log('ERROR', 'Invalid image format or corrupted file', ['path' => $imagePath]);
            return $this;
        }

        // Store original dimensions
        $this->originalWidth = imagesx($this->image);
        $this->originalHeight = imagesy($this->image);

        // Validate dimensions if config is available
        $maxWidth = $this->getConfigValue('maxWidth', 4096);
        $maxHeight = $this->getConfigValue('maxHeight', 4096);
        $minWidth = $this->getConfigValue('minWidth', 1);
        $minHeight = $this->getConfigValue('minHeight', 1);

        if ($this->originalWidth > $maxWidth || $this->originalHeight > $maxHeight) {
            $this->errors[] = 'Image dimensions too large: ' . $this->originalWidth . 'x' . $this->originalHeight .
                             ' (max: ' . $maxWidth . 'x' . $maxHeight . ')';
            $this->log('ERROR', 'Image dimensions too large', [
                'width' => $this->originalWidth,
                'height' => $this->originalHeight,
                'maxWidth' => $maxWidth,
                'maxHeight' => $maxHeight
            ]);
        }

        if ($this->originalWidth < $minWidth || $this->originalHeight < $minHeight) {
            $this->errors[] = 'Image dimensions too small: ' . $this->originalWidth . 'x' . $this->originalHeight .
                             ' (min: ' . $minWidth . 'x' . $minHeight . ')';
            $this->log('ERROR', 'Image dimensions too small', [
                'width' => $this->originalWidth,
                'height' => $this->originalHeight,
                'minWidth' => $minWidth,
                'minHeight' => $minHeight
            ]);
        }

        $this->log('INFO', 'Image loaded successfully', [
            'path' => $imagePath,
            'type' => $this->imageType,
            'width' => $this->originalWidth,
            'height' => $this->originalHeight,
            'size' => $fileSize
        ]);

        return $this;
    }

    /**
     * Get configuration value with fallback
     */
    protected function getConfigValue(string $key, $default = null)
    {
        // Check if the class has a config property
        if (property_exists($this, 'config') && isset($this->config) && isset($this->config->$key)) {
            return $this->config->$key;
        }
        return $default;
    }

    /**
     * Log a message using Core\Log
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        // Check if the class has a logger (from the main Image class)
        if (property_exists($this, 'logger') && isset($this->logger) && method_exists($this->logger, 'write')) {
            $contextString = empty($context) ? '' : ' ' . json_encode($context);
            $this->logger->write("[$level] $message$contextString");
        }
    }

    /**
     * Check if image is loaded successfully
     */
    public function isLoaded(): bool
    {
        return $this->image !== null && empty($this->errors);
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get original dimensions
     */
    public function getOriginalDimensions(): array
    {
        return [
            'width' => $this->originalWidth,
            'height' => $this->originalHeight
        ];
    }

    /**
     * Get current dimensions
     */
    public function getCurrentDimensions(): array
    {
        if (!$this->isLoaded()) {
            return ['width' => 0, 'height' => 0];
        }

        return [
            'width' => imagesx($this->image),
            'height' => imagesy($this->image)
        ];
    }

    public function resize(int $width, int $height): self
    {
        if (!$this->isLoaded()) {
            $this->errors[] = 'No image loaded for resize operation';
            return $this;
        }

        if ($width <= 0 || $height <= 0) {
            $this->errors[] = 'Invalid dimensions for resize: width and height must be positive integers';
            return $this;
        }

        // Get original dimensions for logging
        $originalDimensions = $this->getCurrentDimensions();

        // Store current image before resize for potential rollback
        $originalImage = $this->image;

        $resizedImage = imagescale($this->image, $width, $height);
        if ($resizedImage === false) {
            $this->errors[] = 'Failed to resize image to ' . $width . 'x' . $height;
            $this->image = $originalImage; // Rollback
            return $this;
        }

        // Clean up original image resource
        if ($originalImage !== $resizedImage) {
            imagedestroy($originalImage);
        }

        $this->image = $resizedImage;
        $this->log('INFO', 'Image resized successfully', [
            'from' => $originalDimensions['width'] . 'x' . $originalDimensions['height'],
            'to' => $width . 'x' . $height
        ]);
        return $this;
    }

    public function crop(int $x, int $y, int $width, int $height): self
    {
        if (!$this->isLoaded()) {
            $this->errors[] = 'No image loaded for crop operation';
            return $this;
        }

        if ($width <= 0 || $height <= 0) {
            $this->errors[] = 'Invalid crop dimensions: width and height must be positive integers';
            return $this;
        }

        $currentDimensions = $this->getCurrentDimensions();
        if ($x + $width > $currentDimensions['width'] || $y + $height > $currentDimensions['height']) {
            $this->errors[] = 'Crop area exceeds image boundaries';
            return $this;
        }

        $croppedImage = imagecrop($this->image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        if ($croppedImage === false) {
            $this->errors[] = 'Failed to crop image';
            return $this;
        }

        imagedestroy($this->image);
        $this->image = $croppedImage;
        return $this;
    }

    public function rotate(float $angle): self
    {
        if (!$this->isLoaded()) {
            $this->errors[] = 'No image loaded for rotate operation';
            return $this;
        }

        // Normalize angle
        $angle = fmod($angle, 360);
        if ($angle < 0) {
            $angle += 360;
        }

        $rotatedImage = imagerotate($this->image, $angle, 0);
        if ($rotatedImage === false) {
            $this->errors[] = 'Failed to rotate image by ' . $angle . ' degrees';
            return $this;
        }

        imagedestroy($this->image);
        $this->image = $rotatedImage;
        return $this;
    }

    public function compress(int $quality): self
    {
        if (!$this->isLoaded()) {
            $this->errors[] = 'No image loaded for compress operation';
            return $this;
        }

        if ($quality < 0 || $quality > 100) {
            $this->errors[] = 'Invalid quality value: must be between 0 and 100';
            return $this;
        }

        // For formats that don't support quality, skip compression
        if (!in_array($this->imageType, ['jpg', 'jpeg', 'webp'])) {
            return $this; // No compression needed for PNG/GIF
        }

        ob_start();
        $result = imagejpeg($this->image, null, $quality);
        if ($result === false) {
            $this->errors[] = 'Failed to compress image';
            ob_end_clean();
            return $this;
        }

        $compressedData = ob_get_clean();
        $newImage = imagecreatefromstring($compressedData);
        if ($newImage === false) {
            $this->errors[] = 'Failed to create image from compressed data';
            return $this;
        }

        imagedestroy($this->image);
        $this->image = $newImage;
        return $this;
    }

    public function addWatermark(string $watermarkPath): self
    {
        if (!$this->isLoaded()) {
            $this->errors[] = 'No image loaded for watermark operation';
            return $this;
        }

        if (!file_exists($watermarkPath) || !is_readable($watermarkPath)) {
            $this->errors[] = 'Watermark file does not exist or is not readable: ' . $watermarkPath;
            return $this;
        }

        // Only support PNG watermarks for transparency
        $watermarkInfo = getimagesize($watermarkPath);
        if ($watermarkInfo === false || $watermarkInfo['mime'] !== 'image/png') {
            $this->errors[] = 'Watermark must be a PNG image: ' . $watermarkPath;
            return $this;
        }

        $watermark = imagecreatefrompng($watermarkPath);
        if ($watermark === false) {
            $this->errors[] = 'Failed to load watermark image: ' . $watermarkPath;
            return $this;
        }

        $mainWidth = imagesx($this->image);
        $mainHeight = imagesy($this->image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        // Position watermark at bottom right corner
        $x = $mainWidth - $watermarkWidth - 10;
        $y = $mainHeight - $watermarkHeight - 10;

        $result = imagecopy($this->image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
        if ($result === false) {
            $this->errors[] = 'Failed to apply watermark';
        }

        imagedestroy($watermark);
        return $this;
    }

    public function save(string $outputPath): bool
    {
        if (!$this->isLoaded()) {
            $this->errors[] = 'No image loaded for save operation';
            $this->log('ERROR', 'Save operation failed - no image loaded');
            return false;
        }

        // Ensure output directory exists
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            $this->errors[] = 'Failed to create output directory: ' . $outputDir;
            $this->log('ERROR', 'Failed to create output directory', ['directory' => $outputDir]);
            return false;
        }

        if (!is_writable($outputDir)) {
            $this->errors[] = 'Output directory is not writable: ' . $outputDir;
            $this->log('ERROR', 'Output directory is not writable', ['directory' => $outputDir]);
            return false;
        }

        // Determine image type and save accordingly
        $extension = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));
        $supportedFormats = $this->getConfigValue('supportedOutputFormats', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        if (!in_array($extension, $supportedFormats)) {
            $this->errors[] = 'Unsupported image format: ' . $extension;
            $this->log('ERROR', 'Unsupported image format for save', ['format' => $extension, 'path' => $outputPath]);
            return false;
        }

        $result = false;
        $quality = $this->getConfigValue('defaultJpegQuality', 90);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($this->image, $outputPath, $quality);
                break;
            case 'png':
                $compression = $this->getConfigValue('defaultPngCompression', 6);
                $result = imagepng($this->image, $outputPath, $compression);
                break;
            case 'gif':
                $result = imagegif($this->image, $outputPath);
                break;
            case 'webp':
                $webpQuality = $this->getConfigValue('defaultWebpQuality', 90);
                $result = imagewebp($this->image, $outputPath, $webpQuality);
                break;
        }

        if ($result === false) {
            $this->errors[] = 'Failed to save image to: ' . $outputPath;
            $this->log('ERROR', 'Failed to save image', ['path' => $outputPath, 'format' => $extension]);
        } else {
            $fileSize = filesize($outputPath);
            $this->log('INFO', 'Image saved successfully', [
                'path' => $outputPath,
                'format' => $extension,
                'size' => $fileSize
            ]);
        }

        return $result;
    }

    public function output(): bool
    {
        if (!$this->isLoaded()) {
            $this->errors[] = 'No image loaded for output operation';
            return false;
        }

        if (!$this->imageSource) {
            $this->errors[] = 'No source image specified for output';
            return false;
        }

        // Determine image type and output accordingly
        $extension = strtolower(pathinfo($this->imageSource, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                ob_start();
                imagejpeg($this->image);
                $imageData = ob_get_clean();
                header('Content-Length: ' . strlen($imageData));
                echo $imageData;
                return true;
            case 'png':
                header('Content-Type: image/png');
                return imagepng($this->image);
            case 'gif':
                header('Content-Type: image/gif');
                return imagegif($this->image);
            case 'webp':
                header('Content-Type: image/webp');
                return imagewebp($this->image);
            default:
                $this->errors[] = 'Unsupported image format for output: ' . $extension;
                return false;
        }
    }

}
