# Upload Utilities

The Upload utilities provide a robust, secure, and flexible file upload system for PHP applications. It supports comprehensive validation, XSS protection, multiple file formats, and seamless framework integration.

## Features

### 🔒 Security & Validation
- **Multiple validation rules**: File size, extension, MIME type, image dimensions
- **XSS protection**: Intelligent content sanitization for text-based files
- **Secure filename handling**: Prevents path traversal and injection attacks
- **Binary file protection**: Automatic detection and safe handling of binary formats
- **Comprehensive error reporting**: Detailed error messages for debugging

### 🛠️ Core Functionality
- **Configuration management**: Flexible configuration with preset profiles
- **Framework integration**: Seamless integration with Core\Log and Path systems
- **Method chaining**: Fluent interface for easy configuration
- **Multiple file formats**: Support for images, documents, and custom formats
- **Directory management**: Automatic creation of upload directories

### ⚙️ Framework Integration
- **Core\Log integration**: Consistent logging with framework standards
- **Path system**: Proper directory handling using framework paths
- **Error handling**: Consistent with framework error handling patterns
- **Type safety**: Full PHP type declarations and validation

## Installation

The Upload utilities are part of the Core framework and are located in:
```
Core/Utilities/Upload/
├── Upload.php              (Main upload class)
├── UploadInterface.php     (Upload interface)
├── UploadConfig.php        (Configuration management)
├── FileValidatorTrait.php  (Validation methods trait)
└── FileManipulatorTrait.php (File manipulation methods)
```

## Basic Usage

```php
<?php
use Core\Utilities\Upload\Upload;

// Create upload instance
$upload = new Upload();

// Configure upload settings
$upload->setDir('/path/to/uploads/');
$upload->setMaxSize(2 * 1024 * 1024); // 2MB
$upload->setExtensions(['jpg', 'png', 'gif']);

// Handle file upload
if (isset($_FILES['image'])) {
    if ($upload->upload($_FILES['image'])) {
        echo "File uploaded successfully!";
    } else {
        echo "Upload failed: " . $upload->getError();
    }
}
?>
```

## Configuration Management

### Using Configuration Presets

```php
<?php
use Core\Utilities\Upload\Upload;
use Core\Utilities\Upload\UploadConfig;

// For images
$upload = new Upload();
$config = UploadConfig::forImages();
$upload->configure($config);

// For documents
$upload = new Upload();
$config = UploadConfig::forDocuments();
$upload->configure($config);
?>
```

### Custom Configuration

```php
<?php
use Core\Utilities\Upload\Upload;
use Core\Utilities\Upload\UploadConfig;

$upload = new Upload();

// Create custom configuration
$config = (new UploadConfig())
    ->setMaxSize(5 * 1024 * 1024) // 5MB
    ->setAllowedExtensions(['jpg', 'png', 'pdf', 'doc'])
    ->setMaxFilenameLength(100)
    ->setXssProtection(true)
    ->setImageDimensions(100, 2000, 100, 2000)
    ->setAllowedMimes([
        'jpg' => ['image/jpeg'],
        'png' => ['image/png'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
    ]);

$upload->configure($config);
?>
```

## Configuration Options

### File Size Limits
```php
// Set maximum file size in bytes
$upload->setMaxSize(1048576); // 1MB
$upload->setMaxSize(5 * 1024 * 1024); // 5MB
```

### Allowed File Extensions
```php
// Set allowed file extensions
$upload->setExtensions(['jpg', 'png', 'gif']);
$upload->setExtensions(['pdf', 'doc', 'docx', 'txt']);
```

### Image Dimensions
```php
// Set minimum and maximum image dimensions
$upload->setDimensions(100, 2000, 100, 2000); // Width and height in pixels
```

### Security Settings
```php
// Enable/disable XSS protection
$upload->setXSSProtection(true); // Default: true

// Set maximum filename length
$upload->setMaxLength(64); // Default: 64 characters
```

## Advanced Usage

### Custom Filename Generation

```php
<?php
$upload = new Upload();
$upload->setDir('/uploads/');
$upload->setFileName('profile_' . time()); // Custom filename with timestamp

if ($upload->upload($_FILES['avatar'])) {
    echo "Profile picture uploaded with custom name!";
}
?>
```

### Batch File Processing

```php
<?php
$upload = new Upload();
$upload->setDir('/uploads/batch/');
$upload->setExtensions(['jpg', 'png']);

$uploaded = [];
$errors = [];

if (isset($_FILES['images'])) {
    foreach ($_FILES['images']['name'] as $key => $name) {
        $file = [
            'name' => $name,
            'tmp_name' => $_FILES['images']['tmp_name'][$key],
            'size' => $_FILES['images']['size'][$key],
            'error' => $_FILES['images']['error'][$key]
        ];

        if ($upload->upload($file)) {
            $uploaded[] = $name;
        } else {
            $errors[] = $name . ': ' . $upload->getError();
        }
    }
}

echo "Uploaded: " . count($uploaded) . " files\n";
echo "Errors: " . count($errors) . " files\n";
?>
```

### Integration with Framework Controllers

```php
<?php
namespace App\Controllers\Web;

use Core\Controller;
use Core\Utilities\Upload\Upload;
use Core\Utilities\Upload\UploadConfig;

class MediaController extends Controller
{
    public function upload()
    {
        $upload = new Upload();

        // Configure based on upload type
        if ($this->request->getPost('type') === 'profile') {
            $config = UploadConfig::forImages();
            $config->setMaxSize(1 * 1024 * 1024) // 1MB for profiles
                   ->setImageDimensions(100, 400, 100, 400); // Square images
            $upload->setDir('/uploads/profiles/');
        } else {
            $config = UploadConfig::forImages();
            $upload->setDir('/uploads/gallery/');
        }

        $upload->configure($config);

        if ($this->request->isPost() && isset($_FILES['media'])) {
            if ($upload->upload($_FILES['media'])) {
                // Save to database, redirect, etc.
                $this->redirect('/success');
            } else {
                $this->data['error'] = $upload->getError();
            }
        }

        $this->render('media/upload', $this->data);
    }
}
?>
```

## Security Features

### XSS Protection

The upload utility provides intelligent XSS protection:

```php
// XSS protection is enabled by default
$upload->setXSSProtection(true);

// Only text-based files are sanitized
// Binary files (images, PDFs) are left untouched
```

**Protected file types**: Text files, HTML, XML, CSS, JavaScript
**Excluded file types**: Images (JPG, PNG, GIF, WebP), PDFs, binary documents

### Secure Filename Handling

```php
// Automatic filename sanitization
$upload->setFileName('user_file.txt'); // Becomes: user_file.txt

// Dangerous characters are replaced with underscores
$upload->setFileName('../../../etc/passwd'); // Becomes: _____etc_passwd
```

### Path Traversal Protection

```php
// Directory traversal attempts are blocked
$upload->setDir('/uploads/');

// Files with ../ in names are sanitized
// Malicious: ../../../etc/passwd
// Result: _____etc_passwd
```

## Validation Rules

### File Size Validation

```php
// Maximum file size in bytes
$upload->setMaxSize(2097152); // 2MB

// Human-readable error messages
// "File size exceeds maximum allowed size of 2 MB"
```

### Extension Validation

```php
// Case-insensitive extension checking
$upload->setExtensions(['jpg', 'png', 'gif']);

// Validates against actual file extension
// "File extension 'exe' is not allowed. Allowed types: jpg, png, gif"
```

### Image Dimension Validation

```php
// Only for image files
$upload->setDimensions(100, 2000, 100, 2000);

// Checks actual image dimensions
// "Image width must be between 100px and 2000px (current: 50px)"
```

### MIME Type Validation

```php
// Validates against actual file content
$upload->setMimes([
    'jpg' => ['image/jpeg', 'image/pjpeg'],
    'png' => ['image/png', 'image/x-png'],
    'pdf' => ['application/pdf']
]);

// "File MIME type is not allowed"
```

## Error Handling

### Accessing Validation Errors

```php
<?php
$upload = new Upload();

if (!$upload->upload($_FILES['file'])) {
    $error = $upload->getError();

    // Error messages are user-friendly and specific
    echo "Upload failed: " . $error;
}
?>
```

### Common Error Messages

- **File not uploaded properly**: `is_uploaded_file()` check failed
- **File size exceeds maximum**: File larger than `maxSize` setting
- **File extension not allowed**: Extension not in allowed list
- **Filename too long**: Name longer than `maxLength` setting
- **Image dimensions invalid**: Image outside dimension constraints
- **File MIME type not allowed**: MIME type validation failed
- **Failed to create directory**: Directory creation failed
- **Failed to move file**: File move operation failed

### Logging Integration

All upload operations are logged using the framework's Core\Log system:

```
[2025-10-23 14:30:15] (upload/upload) Starting file upload process
[2025-10-23 14:30:15] (upload/upload) MIME types loaded successfully from configuration
[2025-10-23 14:30:15] (upload/upload) Upload directory set to: /uploads/
[2025-10-23 14:30:15] (upload/upload) File uploaded successfully: profile_1234567890.jpg
```

For different upload types, separate log files are created:
- **General uploads**: `Logs/upload/upload.log`
- **Image uploads**: `Logs/upload/images.log` 
- **Document uploads**: `Logs/upload/documents.log`

## Configuration Presets

### For Images

```php
<?php
$config = UploadConfig::forImages();
// Settings:
// - Max size: 2MB
// - Extensions: jpg, jpeg, png, gif, webp
// - Image dimensions: 100x100 to 2048x2048
// - XSS protection: enabled
// - Logging: logs/upload/images.log
?>
```

### For Documents

```php
<?php
$config = UploadConfig::forDocuments();
// Settings:
// - Max size: 10MB
// - Extensions: pdf, doc, docx, txt
// - Image dimensions: disabled (0x0 to 0x0)
// - XSS protection: disabled (binary formats)
// - Logging: logs/upload/documents.log
?>
```

## Best Practices

### 1. Validate Before Processing

```php
<?php
// ✅ Good - validate before processing
if ($upload->upload($file)) {
    processUploadedFile($file);
    saveToDatabase($file);
} else {
    showError($upload->getError());
}
?>
```

### 2. Use Appropriate Configuration

```php
<?php
// ✅ Good - specific configuration for each use case
$imageUpload = new Upload();
$imageUpload->configure(UploadConfig::forImages());

$documentUpload = new Upload();
$documentUpload->configure(UploadConfig::forDocuments());
?>
```

### 3. Handle Errors Gracefully

```php
<?php
// ✅ Good - comprehensive error handling
try {
    if (!$upload->upload($file)) {
        $this->data['error'] = $upload->getError();
        $this->render('upload/form', $this->data);
        return;
    }
} catch (Exception $e) {
    $this->logError($e);
    $this->redirect('/error');
}
?>
```

### 4. Secure Directory Setup

```php
<?php
// ✅ Good - proper directory permissions
$uploadDir = '/uploads/';

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Ensure proper permissions
chmod($uploadDir, 0755);

$upload->setDir($uploadDir);
?>
```

## Performance Tips

- **Reuse instances**: Create upload instances once and reuse them
- **Pre-configure**: Set all configuration before handling uploads
- **Batch processing**: Use single instance for multiple uploads in loops
- **Directory caching**: Set upload directory once for multiple operations

```php
<?php
// ✅ Good - efficient batch processing
$upload = new Upload();
$upload->configure($config);
$upload->setDir('/uploads/');

foreach ($files as $file) {
    $upload->upload($file); // Reuses configuration
}
?>
```

## Security Considerations

- **Never trust user input**: Always validate file types and content
- **Use appropriate limits**: Set reasonable file size and dimension limits
- **Monitor uploads**: Log all upload attempts for security monitoring
- **Regular cleanup**: Implement automatic cleanup of old uploaded files
- **Access control**: Restrict upload directories from web access

### File Type Security

```php
<?php
// ✅ Good - strict file type validation
$config = (new UploadConfig())
    ->setAllowedExtensions(['jpg', 'png']) // Only images
    ->setAllowedMimes([
        'jpg' => ['image/jpeg'],
        'png' => ['image/png']
    ])
    ->setXssProtection(true); // Enable for text files
?>
```

## Testing

```bash
# Run upload utility tests
phpunit tests/Core/Utilities/UploadTest.php
```

The test suite covers:
- Basic upload functionality
- Configuration management
- Security validation
- Error handling
- Integration testing

## Integration Examples

### With HTML Forms

```html
<form action="/upload" method="post" enctype="multipart/form-data">
    <input type="file" name="image" accept="image/*" required>
    <input type="hidden" name="type" value="profile">
    <button type="submit">Upload</button>
</form>
```

### With JavaScript

```javascript
// File validation before upload
function validateFile(file) {
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (file.size > maxSize) {
        return 'File too large (max 2MB)';
    }

    if (!allowedTypes.includes(file.type)) {
        return 'Invalid file type';
    }

    return null;
}
```

## API Reference

### Upload Class

#### Constructor
```php
new Upload()
```

#### Configuration Methods
- `setDir(string $path): void` - Set upload directory
- `setMaxSize(int $size): void` - Set maximum file size in bytes
- `setExtensions(array $extensions): void` - Set allowed extensions
- `setFileName(string $name): void` - Set custom filename
- `setMaxLength(int $length): void` - Set maximum filename length
- `setXSSProtection(bool $flag): void` - Enable/disable XSS protection
- `setDimensions(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight): void` - Set image dimensions
- `setMimes(array $mimes): void` - Set allowed MIME types
- `configure(UploadConfig $config): self` - Apply configuration object

#### Upload Methods
- `upload(array $file): bool` - Process file upload
- `getError(): string` - Get last error message

### UploadConfig Class

#### Static Methods
- `forImages(): self` - Get configuration preset for images
- `forDocuments(): self` - Get configuration preset for documents

#### Configuration Methods
- `setMaxSize(int $size): self` - Set maximum file size
- `setAllowedExtensions(array $extensions): self` - Set allowed extensions
- `setMaxFilenameLength(int $length): self` - Set filename length limit
- `setXssProtection(bool $enabled): self` - Enable/disable XSS protection
- `setImageDimensions(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight): self` - Set image dimensions
- `setAllowedMimes(array $mimes): self` - Set allowed MIME types
- `toArray(): array` - Get configuration as array

## Troubleshooting

### Common Issues

**Files not uploading**
- Check directory permissions (755)
- Verify directory exists and is writable
- Check PHP upload limits in php.ini

**MIME type validation failing**
- Verify file is not corrupted
- Check actual file content with `mime_content_type()`
- Update MIME configuration if needed

**Image dimension validation failing**
- Ensure file is a valid image
- Check if image has valid dimensions
- Verify GD extension is enabled

**XSS protection corrupting files**
- Disable XSS protection for binary files
- Use `UploadConfig::forDocuments()` for non-text files
- Check file extension detection

### Debug Information

Enable detailed logging to troubleshoot issues:

```php
<?php
$upload = new Upload();
// Upload operations are automatically logged
// Check logs/upload/upload.log (or logs/upload/images.log, logs/upload/documents.log) for detailed information
?>
```

The Upload utilities provide a comprehensive, secure, and easy-to-use file upload system that integrates perfectly with your Phuse framework applications! 🎯
