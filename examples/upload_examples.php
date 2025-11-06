<?php

declare(strict_types=1);

/**
 * Upload Utility Usage Examples
 *
 * Comprehensive examples demonstrating the Upload utility functionality.
 * Shows various configuration options, security features, and best practices.
 *
 * @package Examples
 * @author  Prima Yoga
 */

use Core\Utilities\Upload\Upload;
use Core\Utilities\Upload\UploadConfig;

// Example 1: Basic Image Upload
function basicImageUpload(): void
{
    echo "=== Example 1: Basic Image Upload ===\n";

    $upload = new Upload();
    $upload->setDir(__DIR__ . '/uploads/images/');
    $upload->setMaxSize(2 * 1024 * 1024); // 2MB
    $upload->setExtensions(['jpg', 'png', 'gif', 'webp']);

    // Handle file upload from form
    if (isset($_FILES['image'])) {
        if ($upload->upload($_FILES['image'])) {
            echo "✓ Image uploaded successfully!\n";
        } else {
            echo "✗ Upload failed: " . $upload->getError() . "\n";
        }
    }

    echo "Basic image upload configuration complete.\n\n";
}

// Example 2: Document Upload with Configuration
function documentUploadWithConfig(): void
{
    echo "=== Example 2: Document Upload with Configuration ===\n";

    $upload = new Upload();

    // Use pre-configured settings for documents
    $config = UploadConfig::forDocuments();
    $upload->configure($config);
    $upload->setDir(__DIR__ . '/uploads/documents/');

    // Handle file upload from form
    if (isset($_FILES['document'])) {
        if ($upload->upload($_FILES['document'])) {
            echo "✓ Document uploaded successfully!\n";
        } else {
            echo "✗ Upload failed: " . $upload->getError() . "\n";
        }
    }

    echo "Document upload configuration complete.\n\n";
}

// Example 3: Custom Configuration
function customConfigurationExample(): void
{
    echo "=== Example 3: Custom Configuration ===\n";

    $upload = new Upload();

    // Create custom configuration
    $config = (new UploadConfig())
        ->setMaxSize(5 * 1024 * 1024) // 5MB
        ->setAllowedExtensions(['jpg', 'jpeg', 'png', 'pdf'])
        ->setMaxFilenameLength(100)
        ->setXssProtection(true)
        ->setImageDimensions(200, 3000, 200, 3000)
        ->setAllowedMimes([
            'jpg' => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
        ]);

    $upload->configure($config);
    $upload->setDir(__DIR__ . '/uploads/mixed/');

    // Handle file upload from form
    if (isset($_FILES['file'])) {
        if ($upload->upload($_FILES['file'])) {
            echo "✓ File uploaded successfully!\n";
        } else {
            echo "✗ Upload failed: " . $upload->getError() . "\n";
        }
    }

    echo "Custom configuration complete.\n\n";
}

// Example 4: Profile Picture Upload
function profilePictureUpload(): void
{
    echo "=== Example 4: Profile Picture Upload ===\n";

    $upload = new Upload();

    // Configure for profile pictures
    $config = UploadConfig::forImages();
    $config->setMaxSize(1 * 1024 * 1024) // 1MB
           ->setImageDimensions(100, 500, 100, 500) // Square images
           ->setMaxFilenameLength(50);

    $upload->configure($config);
    $upload->setDir(__DIR__ . '/uploads/profiles/');
    $upload->setFileName('profile_' . time()); // Custom filename with timestamp

    // Handle file upload from form
    if (isset($_FILES['profile_picture'])) {
        if ($upload->upload($_FILES['profile_picture'])) {
            echo "✓ Profile picture uploaded successfully!\n";
        } else {
            echo "✗ Upload failed: " . $upload->getError() . "\n";
        }
    }

    echo "Profile picture upload configuration complete.\n\n";
}

// Example 5: Batch Upload Processing
function batchUploadProcessing(): void
{
    echo "=== Example 5: Batch Upload Processing ===\n";

    $upload = new Upload();
    $config = UploadConfig::forImages();
    $upload->configure($config);
    $upload->setDir(__DIR__ . '/uploads/batch/');

    $uploadedFiles = [];
    $errors = [];

    // Process multiple files
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'size' => $_FILES['images']['size'][$i],
                'error' => $_FILES['images']['error'][$i]
            ];

            if ($upload->upload($file)) {
                $uploadedFiles[] = $_FILES['images']['name'][$i];
            } else {
                $errors[] = $_FILES['images']['name'][$i] . ': ' . $upload->getError();
            }
        }
    }

    echo "Batch upload results:\n";
    echo "✓ Successfully uploaded: " . count($uploadedFiles) . " files\n";
    if (!empty($uploadedFiles)) {
        echo "  - " . implode("\n  - ", $uploadedFiles) . "\n";
    }

    if (!empty($errors)) {
        echo "✗ Failed uploads:\n";
        echo "  - " . implode("\n  - ", $errors) . "\n";
    }

    echo "\n";
}

// Example 6: Secure File Upload with Validation
function secureFileUpload(): void
{
    echo "=== Example 6: Secure File Upload with Validation ===\n";

    $upload = new Upload();

    // Strict security configuration
    $config = (new UploadConfig())
        ->setMaxSize(500 * 1024) // 500KB
        ->setAllowedExtensions(['txt', 'pdf', 'jpg', 'png'])
        ->setMaxFilenameLength(30)
        ->setXssProtection(true)
        ->setImageDimensions(50, 1000, 50, 1000);

    $upload->configure($config);
    $upload->setDir(__DIR__ . '/uploads/secure/');

    // Handle file upload from form
    if (isset($_FILES['secure_file'])) {
        // Additional validation before upload
        if ($_FILES['secure_file']['size'] > 0) {
            if ($upload->upload($_FILES['secure_file'])) {
                echo "✓ Secure file uploaded successfully!\n";
                echo "File uploaded to: " . $upload->getError() . " (check error logs for details)\n";
            } else {
                echo "✗ Upload failed: " . $upload->getError() . "\n";
            }
        } else {
            echo "✗ No file provided or file is empty.\n";
        }
    }

    echo "Secure file upload configuration complete.\n\n";
}

// Example 7: Error Handling and Logging
function errorHandlingExample(): void
{
    echo "=== Example 7: Error Handling and Logging ===\n";

    $upload = new Upload();
    $upload->setDir(__DIR__ . '/uploads/logs/');
    $upload->setExtensions(['jpg']); // Only allow JPG files

    // Test with various error conditions
    $testFiles = [
        [
            'name' => 'test.txt',
            'tmp_name' => '/tmp/nonexistent.txt',
            'size' => 1000,
            'error' => UPLOAD_ERR_OK
        ],
        [
            'name' => 'large_file.jpg',
            'tmp_name' => __FILE__, // Use current file as test
            'size' => 10 * 1024 * 1024, // 10MB
            'error' => UPLOAD_ERR_OK
        ]
    ];

    foreach ($testFiles as $index => $file) {
        echo "Testing file " . ($index + 1) . ": {$file['name']}\n";

        if ($upload->upload($file)) {
            echo "✓ Upload successful\n";
        } else {
            echo "✗ Upload failed: " . $upload->getError() . "\n";
        }
        echo "\n";
    }

    echo "Error handling example complete.\n\n";
}

// Example 8: Integration with Framework Controllers
function controllerIntegrationExample(): void
{
    echo "=== Example 8: Integration with Framework Controllers ===\n";

    // This would typically be in a controller method
    $upload = new Upload();

    // Configure based on upload type (from route parameter or form field)
    $uploadType = $_POST['upload_type'] ?? 'images';

    switch ($uploadType) {
        case 'documents':
            $config = UploadConfig::forDocuments();
            $upload->setDir(__DIR__ . '/uploads/user_documents/');
            break;

        case 'profile':
            $config = UploadConfig::forImages();
            $config->setMaxSize(1 * 1024 * 1024) // 1MB
                   ->setImageDimensions(100, 400, 100, 400);
            $upload->setDir(__DIR__ . '/uploads/profiles/');
            $upload->setFileName('user_' . $_SESSION['user_id'] ?? 'anonymous');
            break;

        default:
            $config = UploadConfig::forImages();
            $upload->setDir(__DIR__ . '/uploads/general/');
    }

    $upload->configure($config);

    // Handle upload
    if (isset($_FILES['user_file'])) {
        if ($upload->upload($_FILES['user_file'])) {
            echo "✓ File uploaded successfully in controller context!\n";
            // Here you would typically save file info to database
            // $this->saveFileInfo($upload, $_FILES['user_file']);
        } else {
            echo "✗ Controller upload failed: " . $upload->getError() . "\n";
            // Here you would typically add error to view data
            // $this->data['upload_error'] = $upload->getError();
        }
    }

    echo "Controller integration example complete.\n\n";
}

// Example 9: Advanced XSS Protection
function advancedXssProtectionExample(): void
{
    echo "=== Example 9: Advanced XSS Protection ===\n";

    $upload = new Upload();

    // Create configuration with strict XSS protection
    $config = (new UploadConfig())
        ->setMaxSize(100 * 1024) // 100KB for text files
        ->setAllowedExtensions(['txt', 'html', 'htm', 'xml'])
        ->setXssProtection(true) // Enable XSS protection
        ->setMaxFilenameLength(50);

    $upload->configure($config);
    $upload->setDir(__DIR__ . '/uploads/sanitized/');

    // Create a test file with potential XSS content
    $maliciousContent = '<script>alert("XSS")</script><p>Legitimate content</p>';
    $testFile = sys_get_temp_dir() . '/xss_test.html';
    file_put_contents($testFile, $maliciousContent);

    $file = [
        'name' => 'xss_test.html',
        'tmp_name' => $testFile,
        'size' => strlen($maliciousContent),
        'error' => UPLOAD_ERR_OK
    ];

    if ($upload->upload($file)) {
        echo "✓ File uploaded and sanitized successfully!\n";

        // Check if XSS content was neutralized
        $uploadedFiles = glob(__DIR__ . '/uploads/sanitized/*');
        $uploadedFile = $uploadedFiles[0] ?? '';
        if ($uploadedFile && is_file($uploadedFile)) {
            $content = file_get_contents($uploadedFile);
            echo "Original content length: " . strlen($maliciousContent) . "\n";
            echo "Sanitized content length: " . strlen($content) . "\n";
            echo "XSS script neutralized: " . (strpos($content, '<script>') === false ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "✗ Upload failed: " . $upload->getError() . "\n";
    }

    // Clean up test file
    if (is_file($testFile)) {
        unlink($testFile);
    }

    echo "Advanced XSS protection example complete.\n\n";
}

// Example 10: Performance and Best Practices
function performanceBestPracticesExample(): void
{
    echo "=== Example 10: Performance and Best Practices ===\n";

    // Reuse upload instance for multiple uploads
    $upload = new Upload();
    $config = UploadConfig::forImages();
    $upload->configure($config);

    // Set directory once
    $upload->setDir(__DIR__ . '/uploads/performance/');

    echo "Processing multiple uploads efficiently...\n";

    // Simulate multiple file uploads
    for ($i = 1; $i <= 3; $i++) {
        // Create a test file for each iteration
        $testFile = sys_get_temp_dir() . "/performance_test_{$i}.jpg";
        createTestImage($testFile, 800, 600);

        $file = [
            'name' => "test_image_{$i}.jpg",
            'tmp_name' => $testFile,
            'size' => filesize($testFile),
            'error' => UPLOAD_ERR_OK
        ];

        $startTime = microtime(true);

        if ($upload->upload($file)) {
            $endTime = microtime(true);
            echo "✓ Upload {$i} successful in " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
        } else {
            echo "✗ Upload {$i} failed: " . $upload->getError() . "\n";
        }

        // Clean up test file
        unlink($testFile);
    }

    echo "\nPerformance best practices demonstrated:\n";
    echo "- Reused upload instance\n";
    echo "- Pre-configured settings\n";
    echo "- Proper cleanup of temporary files\n";
    echo "- Error handling without breaking the loop\n\n";
}

// Helper function to create test images
function createTestImage(string $filename, int $width, int $height): void
{
    $image = imagecreate($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    // Add some content
    imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $white);
    imagestring($image, 5, 10, 10, "Test Image", $black);
    imagestring($image, 2, 10, 30, "{$width}x{$height}", $black);

    imagejpeg($image, $filename, 90);
    imagedestroy($image);
}

// Main execution - run examples
echo "Upload Utility Examples\n";
echo "======================\n\n";

// Check if running from command line or web
if (php_sapi_name() === 'cli') {
    echo "Running examples in CLI mode...\n\n";

    // For CLI, just show configuration examples
    basicImageUpload();
    documentUploadWithConfig();
    customConfigurationExample();
    profilePictureUpload();
    errorHandlingExample();
    controllerIntegrationExample();
    advancedXssProtectionExample();
    performanceBestPracticesExample();

    echo "All examples completed successfully!\n";
    echo "Check the upload directories for uploaded files.\n";
    echo "Check the logs/upload/upload.log file for detailed logging information.\n";
} else {
    echo "Running examples in web mode...\n\n";

    // For web, show usage instructions
    echo "To use these examples:\n";
    echo "1. Create the upload directories: uploads/images/, uploads/documents/, etc.\n";
    echo "2. Set proper permissions (755) on the directories\n";
    echo "3. Submit forms with appropriate file inputs\n";
    echo "4. Check browser console and log files (logs/upload/upload.log) for details\n\n";

    // Show current configuration
    $upload = new Upload();
    echo "Current upload configuration:\n";
    echo "- Max size: 5MB (default)\n";
    echo "- Extensions: jpg, png, gif, webp (default)\n";
    echo "- XSS Protection: Enabled (default)\n";
    echo "- Image dimensions: 50x50 to 3200x2400 (default)\n";
}

echo "\nUpload utility examples completed!\n";
