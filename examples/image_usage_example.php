<?php

declare(strict_types=1);

/**
 * PHUSE FRAMEWORK - ENHANCED IMAGE COMPONENT EXAMPLES
 *
 * This file contains practical examples demonstrating the improvements
 * made to the Image component and how to use the new features.
 *
 * @package Examples
 * @author  Prima Yoga
 */

namespace Examples;

use Core\Utilities\Image\Image;
use Core\Utilities\Image\ImageConfig;
use Core\Log;
use Core\Utilities\Image\Types\ResizeOptions;
use Exception;

/**
 * EXAMPLE 1: Basic Image component usage
 */
class BasicImageUsage
{
    public function demonstrate(): void
    {
        echo "Basic Image Usage Example\n";
        echo "========================\n\n";

        // Create new Image instance
        $image = new Image();

        // Load image from file
        $image->setImageSource('path/to/your/image.jpg');

        if ($image->isLoaded()) {
            echo "✓ Image loaded successfully\n";
            $dimensions = $image->getCurrentDimensions();
            echo "  Original dimensions: {$dimensions['width']}x{$dimensions['height']}\n";

            // Resize maintaining aspect ratio
            $image->resize(800, 600);
            echo "✓ Image resized to 800x600\n";

            // Save the result
            $result = $image->save('output/resized_image.jpg');
            if ($result) {
                echo "✓ Image saved successfully\n";
            }
        } else {
            echo "✗ Failed to load image:\n";
            foreach ($image->getErrors() as $error) {
                echo "  - $error\n";
            }
        }

        // Always clean up memory
        $image->destroy();
        echo "✓ Memory cleaned up\n\n";
    }
}

/**
 * EXAMPLE 2: Using Image component with configuration
 */
class ConfiguredImageUsage
{
    public function demonstrate(): void
    {
        echo "Image with Configuration Example\n";
        echo "==============================\n\n";

        // Create configuration with custom settings
        $config = new ImageConfig();
        $config->maxWidth = 1920;
        $config->maxHeight = 1080;
        $config->defaultJpegQuality = 85;
        $config->enableLogging = true;
        $config->logFileName = 'image_operations';

        echo "Configuration settings:\n";
        echo "  - Max dimensions: {$config->maxWidth}x{$config->maxHeight}\n";
        echo "  - JPEG quality: {$config->defaultJpegQuality}%\n";
        echo "  - Logging: " . ($config->enableLogging ? 'enabled' : 'disabled') . "\n\n";

        // Create image with configuration
        $image = new Image('path/to/your/image.jpg', $config);

        if ($image->isLoaded()) {
            echo "✓ Image loaded with configuration\n";

            // Process with configured settings
            $image->resize(1200, 900)->save('output/configured_image.jpg');
            echo "✓ Image processed and saved with custom configuration\n";
        } else {
            echo "✗ Failed to load image:\n";
            foreach ($image->getErrors() as $error) {
                echo "  - $error\n";
            }
        }

        $image->destroy();
        echo "✓ Memory cleaned up\n\n";
    }
}

/**
 * EXAMPLE 3: Integration with framework logging
 */
class LoggingIntegrationUsage
{
    public function demonstrate(): void
    {
        echo "Framework Logging Integration Example\n";
        echo "===================================\n\n";

        // Create framework logger
        $log = new Log();
        $log->setLogName('image_operations');
        echo "✓ Framework logger configured\n\n";

        // Create image with logging
        $image = new Image('path/to/your/image.jpg', null, $log);

        if ($image->isLoaded()) {
            echo "✓ Image loaded with logging enabled\n";

            // All operations will be automatically logged
            $image
                ->resize(800, 600)
                ->compress(90)
                ->save('output/logged_image.jpg');

            echo "✓ Image processed with full logging\n";
            echo "  Check logs/image_operations_" . date('Ymd') . ".log for details\n";
        } else {
            echo "✗ Failed to load image:\n";
            foreach ($image->getErrors() as $error) {
                echo "  - $error\n";
            }
        }

        $image->destroy();
        echo "✓ Memory cleaned up\n\n";
    }
}

/**
 * EXAMPLE 4: Advanced image processing pipeline
 */
class ImageProcessingPipeline
{
    public function demonstrate(): void
    {
        echo "Image Processing Pipeline Example\n";
        echo "==============================\n\n";

        // Setup configuration and logging
        $config = new ImageConfig();
        $config->defaultJpegQuality = 85;
        $config->enableLogging = true;

        $log = new Log();
        $log->setLogName('image_pipeline');

        echo "Pipeline configuration:\n";
        echo "  - Quality: {$config->defaultJpegQuality}%\n";
        echo "  - Logging: enabled\n\n";

        // Create image for processing
        $image = new Image('path/to/your/image.jpg', $config, $log);

        if ($image->isLoaded()) {
            echo "✓ Image loaded for pipeline processing\n";
            $originalDimensions = $image->getOriginalDimensions();
            echo "  Original size: {$originalDimensions['width']}x{$originalDimensions['height']}\n\n";

            // Process image in a pipeline (method chaining)
            $image
                ->resize(1200, 900)
                ->compress(85)
                ->addWatermark('path/to/watermark.png')
                ->save('output/pipeline_processed.jpg');

            echo "✓ Pipeline processing completed:\n";
            echo "  - Resized to 1200x900\n";
            echo "  - Compressed to 85% quality\n";
            echo "  - Watermark applied\n";
            echo "  - Saved to output/pipeline_processed.jpg\n\n";

            // Check for any processing errors
            if (!empty($image->getErrors())) {
                echo "⚠ Processing warnings:\n";
                foreach ($image->getErrors() as $error) {
                    echo "  - $error\n";
                }
            }
        } else {
            echo "✗ Failed to load image:\n";
            foreach ($image->getErrors() as $error) {
                echo "  - $error\n";
            }
        }

        $image->destroy();
        echo "✓ Memory cleaned up\n\n";
    }
}

/**
 * EXAMPLE 5: Batch processing multiple images
 */
class BatchProcessingUsage
{
    public function demonstrate(): void
    {
        echo "Batch Processing Example\n";
        echo "======================\n\n";

        // Configuration for batch processing
        $config = new ImageConfig();
        $config->defaultJpegQuality = 80;
        $config->enableLogging = true;

        $log = new Log();
        $log->setLogName('batch_processing');

        echo "Batch configuration:\n";
        echo "  - Quality: {$config->defaultJpegQuality}%\n";
        echo "  - Target size: 800x600\n\n";

        // List of images to process
        $images = [
            'image1.jpg',
            'image2.png',
            'image3.gif',
            'image4.webp'
        ];

        echo "Processing " . count($images) . " images...\n\n";

        foreach ($images as $index => $inputImage) {
            echo "Processing image " . ($index + 1) . ": $inputImage\n";

            // Create new instance for each image
            $image = new Image($inputImage, $config, $log);

            if ($image->isLoaded()) {
                // Process and save
                $image->resize(800, 600)->save('output/batch_' . basename($inputImage));
                echo "  ✓ Processed successfully\n";

                // Show dimensions
                $dimensions = $image->getCurrentDimensions();
                echo "  ✓ Final dimensions: {$dimensions['width']}x{$dimensions['height']}\n";
            } else {
                echo "  ✗ Failed to process:\n";
                foreach ($image->getErrors() as $error) {
                    echo "    - $error\n";
                }
            }

            // Clean up after each image
            $image->destroy();
            echo "  ✓ Memory cleaned up\n\n";
        }

        echo "✓ Batch processing completed!\n\n";
    }
}

/**
 * EXAMPLE 6: Error handling and recovery
 */
class ErrorHandlingUsage
{
    public function demonstrate(): void
    {
        echo "Error Handling Example\n";
        echo "====================\n\n";

        echo "Testing various error conditions...\n\n";

        // Test 1: Non-existent file
        echo "1. Testing non-existent file:\n";
        $image = new Image();
        $image->setImageSource('nonexistent.jpg');

        if (!$image->isLoaded()) {
            echo "  ✓ Properly detected missing file\n";
            echo "  Errors:\n";
            foreach ($image->getErrors() as $error) {
                echo "    - $error\n";
            }
        }
        $image->destroy();

        echo "\n2. Testing invalid dimensions:\n";
        $image = new Image('path/to/your/image.jpg');
        if ($image->isLoaded()) {
            // Try invalid resize dimensions
            $image->resize(0, 100);
            if (!empty($image->getErrors())) {
                echo "  ✓ Properly validated dimensions\n";
                foreach ($image->getErrors() as $error) {
                    echo "    - $error\n";
                }
            }
        }
        $image->destroy();

        echo "\n3. Testing crop boundaries:\n";
        $image = new Image('path/to/your/image.jpg');
        if ($image->isLoaded()) {
            // Try crop outside image boundaries
            $image->crop(10000, 10000, 100, 100);
            if (!empty($image->getErrors())) {
                echo "  ✓ Properly validated crop boundaries\n";
                foreach ($image->getErrors() as $error) {
                    echo "    - $error\n";
                }
            }
        }
        $image->destroy();

        echo "\n✓ Error handling demonstration completed\n\n";
    }
}

/**
 * EXAMPLE 7: Advanced operations and features
 */
class AdvancedOperationsUsage
{
    public function demonstrate(): void
    {
        echo "Advanced Operations Example\n";
        echo "========================\n\n";

        $config = new ImageConfig();
        $config->defaultJpegQuality = 90;
        $config->enableLogging = true;

        $log = new Log();
        $log->setLogName('advanced_operations');

        $image = new Image('path/to/your/image.jpg', $config, $log);

        if ($image->isLoaded()) {
            echo "✓ Image loaded for advanced operations\n";
            $originalDimensions = $image->getOriginalDimensions();
            echo "  Original: {$originalDimensions['width']}x{$originalDimensions['height']}\n\n";

            // Test rotation
            echo "Testing rotation...\n";
            $image->rotate(45);
            $currentDimensions = $image->getCurrentDimensions();
            echo "  ✓ Rotated 45 degrees: {$currentDimensions['width']}x{$currentDimensions['height']}\n";

            // Test cropping
            echo "Testing cropping...\n";
            $image->crop(50, 50, 200, 200);
            $croppedDimensions = $image->getCurrentDimensions();
            echo "  ✓ Cropped to 200x200: {$croppedDimensions['width']}x{$croppedDimensions['height']}\n";

            // Save final result
            $image->save('output/advanced_operations.jpg');
            echo "  ✓ Saved advanced operations result\n";
        } else {
            echo "✗ Failed to load image for advanced operations\n";
        }

        $image->destroy();
        echo "✓ Memory cleaned up\n\n";
    }
}

// Example usage demonstration
if (php_sapi_name() === 'cli') {
    echo "Image Component Examples\n";
    echo "======================\n\n";

    // Ensure output directory exists
    if (!is_dir('output')) {
        mkdir('output', 0755, true);
        echo "Created output directory\n\n";
    }

    try {
        $basicExample = new BasicImageUsage();
        $basicExample->demonstrate();
        echo "✓ Basic image usage example completed\n\n";

        $configExample = new ConfiguredImageUsage();
        $configExample->demonstrate();
        echo "✓ Configuration example completed\n\n";

        $loggingExample = new LoggingIntegrationUsage();
        $loggingExample->demonstrate();
        echo "✓ Logging integration example completed\n\n";

        $pipelineExample = new ImageProcessingPipeline();
        $pipelineExample->demonstrate();
        echo "✓ Pipeline processing example completed\n\n";

        $batchExample = new BatchProcessingUsage();
        $batchExample->demonstrate();
        echo "✓ Batch processing example completed\n\n";

        $errorExample = new ErrorHandlingUsage();
        $errorExample->demonstrate();
        echo "✓ Error handling example completed\n\n";

        $advancedExample = new AdvancedOperationsUsage();
        $advancedExample->demonstrate();
        echo "✓ Advanced operations example completed\n\n";

        echo "🎉 All Image component examples completed successfully!\n";
        echo "📖 Check the logs directory for detailed operation logs.\n";
        echo "📁 Check the output directory for processed images.\n";

    } catch (Exception $e) {
        echo "❌ Error running examples: " . $e->getMessage() . "\n";
        echo "Make sure you have valid image files in the path/to/your/ directory.\n";
    }
}
