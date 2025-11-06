# Image Utilities

The Image utilities provides a robust, secure, and feature-rich solution for image manipulation in PHP applications. It has been completely refactored to include modern PHP practices, comprehensive error handling, configuration support, and logging capabilities.

## Features

### 🔒 Security & Validation
- **File validation**: Checks file existence, readability, and size limits
- **Dimension validation**: Enforces minimum/maximum width and height constraints
- **Format validation**: Supports common image formats with proper validation
- **Memory protection**: Prevents memory exhaustion with file size limits

### 🛠️ Core Functionality
- **Resize**: Scale images while maintaining aspect ratio or exact dimensions
- **Crop**: Extract specific regions from images
- **Rotate**: Rotate images by any angle
- **Compress**: Reduce file size with quality settings
- **Watermark**: Add PNG watermarks with transparency support
- **Save/Output**: Save to files or output directly to browser

### ⚙️ Configuration
- **Flexible settings**: Customizable file size limits, dimensions, quality settings
- **Format support**: Configurable input/output format support
- **Logging control**: Enable/disable logging with configurable levels
- **Caching options**: Built-in support for image caching (future enhancement)

## Installation

The Image utilities is part of the Core framework and is located in:
```
Core/Utilities/Image/
├── Image.php                    (Main class - integrates with Core\Log)
├── ImageInterface.php          (Interface using Core\Log)
├── ImageTrait.php              (Core implementation)
├── ImageConfig.php             (Configuration class)
└── Types/
    └── ResizeOptions.php       (Resize configuration)
```

## Basic Usage

```php
<?php
use Core\Utilities\Image\Image;

// Create and load an image
$image = new Image('path/to/image.jpg');

if ($image->isLoaded()) {
    // Resize the image
    $image->resize(800, 600);

    // Save the result
    $image->save('path/to/resized_image.jpg');

    echo "Image processed successfully!";
} else {
    echo "Failed to load image: " . implode(', ', $image->getErrors());
}

// Clean up memory
$image->destroy();
?>
```

## Advanced Usage

### With Configuration

```php
<?php
use Core\Utilities\Image\Image;
use Core\Utilities\Image\ImageConfig;

$config = new ImageConfig();
$config->maxWidth = 1920;
$config->maxHeight = 1080;
$config->defaultJpegQuality = 85;

$image = new Image('image.jpg', $config);
$image->resize(1200, 900)->save('output.jpg');
?>
```

### With Existing Framework Logging

```php
<?php
use Core\Utilities\Image\Image;
use Core\Log;

$log = new Log();
$log->setLogName('image_operations');

$image = new Image('image.jpg', null, $log);

// All operations will be logged
$image->resize(800, 600)->save('output.jpg');
?>
```

### Processing Pipeline

```php
<?php
$image = new Image('input.jpg', $config, $logger);

// Chain multiple operations
$image
    ->resize(1200, 900)
    ->compress(85)
    ->addWatermark('watermark.png')
    ->save('output.jpg');

// Check for errors
if (!empty($image->getErrors())) {
    echo "Errors: " . implode(', ', $image->getErrors());
}
?>
```

## API Reference

### Image Class

#### Constructor
```php
$image = new Image(string $imagePath = null, ImageConfig $config = null, Log $logger = null)
```

#### Core Methods
- `setImageSource(string $path): self` - Load image from file
- `resize(int $width, int $height): self` - Resize image
- `crop(int $x, int $y, int $width, int $height): self` - Crop image
- `rotate(float $angle): self` - Rotate image
- `compress(int $quality): self` - Compress image (JPEG/WebP only)
- `addWatermark(string $watermarkPath): self` - Add watermark
- `save(string $outputPath): bool` - Save image to file
- `output(): bool` - Output image to browser

#### Status & Information
- `isLoaded(): bool` - Check if image is loaded successfully
- `getErrors(): array` - Get error messages
- `getOriginalDimensions(): array` - Get original image dimensions
- `getCurrentDimensions(): array` - Get current image dimensions

#### Configuration & Logging
- `setConfig(ImageConfig $config): self` - Set configuration
- `getConfig(): ImageConfig` - Get configuration
- `setLogger(Log $logger): self` - Set logger
- `getLogger(): ?Log` - Get logger

#### Memory Management
- `destroy(): void` - Clean up image resources

### ImageConfig Class

#### Key Settings
```php
$config = new ImageConfig();
$config->maxFileSize = 10 * 1024 * 1024;    // 10MB
$config->maxWidth = 4096;                    // Max width in pixels
$config->maxHeight = 4096;                   // Max height in pixels
$config->defaultJpegQuality = 90;            // JPEG quality 0-100
$config->defaultWebpQuality = 90;            // WebP quality 0-100
$config->enableLogging = true;               // Enable framework logging
$config->logFileName = 'image_component';    // Log file name (without extension)
```

## Error Handling

The component provides comprehensive error handling:

```php
$image = new Image('nonexistent.jpg');

if (!$image->isLoaded()) {
    foreach ($image->getErrors() as $error) {
        echo "Error: $error\n";
    }
}
```

Common error types:
- File not found or not readable
- Invalid image format
- File too large
- Dimensions too large/small
- Unsupported format for operation
- Failed to create output directory

## Supported Formats

### Input Formats
- JPEG/JPG
- PNG
- GIF
- WebP
- BMP (with validation)

### Output Formats
- JPEG/JPG (with quality control)
- PNG (with compression levels)
- GIF
- WebP (with quality control)

## Performance Considerations

### Memory Management
- Always call `destroy()` after processing to free memory
- File size limits prevent memory exhaustion
- Automatic cleanup in destructor

### Processing Tips
- Process images in sequence rather than parallel for memory efficiency
- Use appropriate quality settings to balance file size vs. quality
- Consider using caching for frequently processed images

## Integration with Framework

The Image component is designed to integrate seamlessly with the Phuse framework:

### Logging Integration
The component uses the framework's existing `Core\Log` class for all logging operations. The deprecated `LoggerInterface.php` and `Logger.php` files are kept for backward compatibility but should not be used in new code.

```php
// Recommended approach
use Core\Log;

$log = new Log();
$log->setLogName('image_operations');

$image = new Image('image.jpg', null, $log);
```

### Configuration
The `ImageConfig` class provides comprehensive configuration options that integrate with the framework's conventions and security requirements.

## Security

- File validation prevents malicious uploads
- Size limits prevent DoS attacks
- Proper error handling avoids information disclosure
- Safe memory management prevents resource exhaustion

## Migration from Old Version

The refactored component is backward compatible but includes many enhancements:

### Before (Old)
```php
$image = new Image('image.jpg');
$image->resize(800, 600);
imagejpeg($image->image, 'output.jpg'); // Direct GD access
```

### After (New)
```php
$image = new Image('image.jpg');
$image->resize(800, 600)->save('output.jpg'); // Safe, validated, logged
```

## Testing

Run the test suite:
```bash
php phpunit tests/Core/Utilities/Image/ImageTest.php
```

## Examples

See `examples/image_usage_example.php` for comprehensive usage examples.

## License

This component is part of the Phuse framework and follows the same license terms.
