<?php

declare(strict_types=1);

// is_uploaded_file() only ever returns true for files that went through PHP's
// real HTTP upload handling - there is no way to produce one of those from a
// CLI test. PHP resolves unqualified function calls from the innermost
// namespace first, so this shim (defined only here, only in this namespace)
// intercepts calls made from Core\Utilities\Upload's FileValidatorTrait
// without touching the real global is_uploaded_file() used in production.
namespace Core\Utilities\Upload {
    function is_uploaded_file(string $filename): bool
    {
        return file_exists($filename);
    }

    // move_uploaded_file() has the same real-upload-only restriction as
    // is_uploaded_file() above - copy() (not rename()) so the test fixture
    // file survives for tests that upload() the same tmp_name more than once.
    function move_uploaded_file(string $from, string $to): bool
    {
        return copy($from, $to);
    }
}

namespace Tests\Core\Utilities {

use Core\Utilities\Upload\Upload;
use Core\Utilities\Upload\UploadConfig;
use PHPUnit\Framework\TestCase;

/**
 * Upload Utility Test Class
 *
 * Comprehensive tests for the Upload utility functionality including
 * validation, security, configuration, and error handling.
 *
 * @package Tests\Core\Utilities
 * @author  Phuse Framework
 */
class UploadTest extends TestCase
{
    private string $testUploadDir;
    private string $testFile;
    private Upload $upload;

    protected function setUp(): void
    {
        $this->testUploadDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'upload_test';
        $this->testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.txt';

        // Create test directory
        if (!is_dir($this->testUploadDir)) {
            mkdir($this->testUploadDir, 0755, true);
        }

        // Create a test file
        file_put_contents($this->testFile, 'Test file content');

        $this->upload = new Upload();
        $this->upload->setDir($this->testUploadDir);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_file($this->testFile)) {
            unlink($this->testFile);
        }

        if (is_dir($this->testUploadDir)) {
            $this->removeDirectory($this->testUploadDir);
        }
    }

    public function testBasicConfiguration(): void
    {
        $this->assertInstanceOf(Upload::class, $this->upload);

        $error = $this->upload->getError();
        $this->assertEmpty($error);
    }

    public function testSetDirectory(): void
    {
        $testDir = $this->testUploadDir . DIRECTORY_SEPARATOR . 'subdir';
        $this->upload->setDir($testDir);

        $this->assertStringEndsWith('subdir' . DIRECTORY_SEPARATOR, $this->getPrivateProperty($this->upload, 'dir'));
    }

    public function testSetMaxSize(): void
    {
        $this->upload->setMaxSize(1000000);
        $this->assertEquals(1000000, $this->getPrivateProperty($this->upload, 'maxSize'));
    }

    public function testSetExtensions(): void
    {
        $extensions = ['jpg', 'png', 'pdf'];
        $this->upload->setExtensions($extensions);
        $this->assertEquals(['jpg', 'png', 'pdf'], $this->getPrivateProperty($this->upload, 'extensions'));
    }

    public function testSetFilename(): void
    {
        $this->upload->setFileName('custom_name');
        $this->assertEquals('custom_name', $this->getPrivateProperty($this->upload, 'fileName'));
    }

    public function testSetMaxLength(): void
    {
        $this->upload->setMaxLength(100);
        $this->assertEquals(100, $this->getPrivateProperty($this->upload, 'maxLength'));
    }

    public function testSetXSSProtection(): void
    {
        $this->upload->setXSSProtection(false);
        $this->assertFalse($this->getPrivateProperty($this->upload, 'xssProtection'));

        $this->upload->setXSSProtection(true);
        $this->assertTrue($this->getPrivateProperty($this->upload, 'xssProtection'));
    }

    public function testSetDimensions(): void
    {
        $this->upload->setDimensions(100, 2000, 100, 2000);
        $this->assertEquals(100, $this->getPrivateProperty($this->upload, 'minWidth'));
        $this->assertEquals(2000, $this->getPrivateProperty($this->upload, 'maxWidth'));
        $this->assertEquals(100, $this->getPrivateProperty($this->upload, 'minHeight'));
        $this->assertEquals(2000, $this->getPrivateProperty($this->upload, 'maxHeight'));
    }

    public function testValidFileUpload(): void
    {
        $file = [
            'name' => 'test.txt',
            'tmp_name' => $this->testFile,
            'size' => 17,
            'error' => UPLOAD_ERR_OK
        ];

        $this->upload->setExtensions(['txt']);
        $result = $this->upload->upload($file);

        $this->assertTrue($result);
        $this->assertEmpty($this->upload->getError());

        // Check if file was uploaded
        $uploadedFiles = glob($this->testUploadDir . DIRECTORY_SEPARATOR . '*');
        $this->assertCount(1, $uploadedFiles);
    }

    public function testInvalidFileExtension(): void
    {
        $file = [
            'name' => 'test.exe',
            'tmp_name' => $this->testFile,
            'size' => 17,
            'error' => UPLOAD_ERR_OK
        ];

        $result = $this->upload->upload($file);

        $this->assertFalse($result);
        $this->assertStringContainsString('extension', $this->upload->getError());
    }

    public function testFileTooLarge(): void
    {
        $file = [
            'name' => 'test.txt',
            'tmp_name' => $this->testFile,
            'size' => 1000000, // 1MB
            'error' => UPLOAD_ERR_OK
        ];

        $this->upload->setMaxSize(1000); // 1KB limit
        $result = $this->upload->upload($file);

        $this->assertFalse($result);
        $this->assertStringContainsString('size', $this->upload->getError());
    }

    public function testInvalidImageDimensions(): void
    {
        // Create a fake image file for testing
        $imageFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_image.jpg';
        $this->createTestImage($imageFile, 10, 10); // Very small image

        $file = [
            'name' => 'test.jpg',
            'tmp_name' => $imageFile,
            'size' => filesize($imageFile),
            'error' => UPLOAD_ERR_OK
        ];

        $this->upload->setExtensions(['jpg']);
        $this->upload->setDimensions(100, 2000, 100, 2000); // Require minimum 100px

        $result = $this->upload->upload($file);

        $this->assertFalse($result);
        $this->assertStringContainsString('dimensions', $this->upload->getError());

        // Clean up
        unlink($imageFile);
    }

    public function testConfigurationObject(): void
    {
        $config = new UploadConfig();
        $config->setMaxSize(2000000)
               ->setAllowedExtensions(['jpg', 'png'])
               ->setMaxFilenameLength(50);

        $this->upload->configure($config);

        $this->assertEquals(2000000, $this->getPrivateProperty($this->upload, 'maxSize'));
        $this->assertEquals(['jpg', 'png'], $this->getPrivateProperty($this->upload, 'extensions'));
        $this->assertEquals(50, $this->getPrivateProperty($this->upload, 'maxLength'));
    }

    public function testImagePresetConfiguration(): void
    {
        $config = UploadConfig::forImages();
        $this->upload->configure($config);

        // 5 MB, not 2 MB - forImages()'s "5 MB CMS default" (Core/Utilities/Upload/UploadConfig.php)
        // has been the documented default since v1.2.5 ("raised from 2 MB to 5 MB to better
        // suit CMS image uploads").
        $this->assertEquals(5000000, $this->getPrivateProperty($this->upload, 'maxSize'));
        $this->assertEquals(['jpg', 'jpeg', 'png', 'gif', 'webp'], $this->getPrivateProperty($this->upload, 'extensions'));
        $this->assertTrue($this->getPrivateProperty($this->upload, 'xssProtection'));
    }

    public function testDocumentPresetConfiguration(): void
    {
        $config = UploadConfig::forDocuments();
        $this->upload->configure($config);

        $this->assertEquals(10000000, $this->getPrivateProperty($this->upload, 'maxSize'));
        $this->assertEquals(['pdf', 'doc', 'docx', 'txt'], $this->getPrivateProperty($this->upload, 'extensions'));
        $this->assertFalse($this->getPrivateProperty($this->upload, 'xssProtection'));
    }

    public function testCustomFilename(): void
    {
        $file = [
            'name' => 'test.txt',
            'tmp_name' => $this->testFile,
            'size' => 17,
            'error' => UPLOAD_ERR_OK
        ];

        $this->upload->setFileName('my_custom_file');
        $this->upload->setExtensions(['txt']);

        $result = $this->upload->upload($file);

        $this->assertTrue($result);

        // Check if file was uploaded with custom name
        $uploadedFiles = glob($this->testUploadDir . DIRECTORY_SEPARATOR . '*');
        $this->assertCount(1, $uploadedFiles);

        $uploadedFile = basename($uploadedFiles[0]);
        $this->assertStringStartsWith('my_custom_file', $uploadedFile);
        $this->assertStringEndsWith('.txt', $uploadedFile);
    }

    public function testErrorReset(): void
    {
        // First upload should fail
        $file = [
            'name' => 'test.exe',
            'tmp_name' => $this->testFile,
            'size' => 17,
            'error' => UPLOAD_ERR_OK
        ];

        $this->upload->upload($file);
        $this->assertNotEmpty($this->upload->getError());

        // Second upload should reset error
        $file['name'] = 'test.txt';
        $this->upload->setExtensions(['txt']);
        $this->upload->upload($file);
        $this->assertEmpty($this->upload->getError());
    }

    /**
     * Helper method to access private properties for testing.
     */
    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    /**
     * Helper method to remove directory recursively.
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Helper method to create a test image.
     */
    private function createTestImage(string $filename, int $width, int $height): void
    {
        $image = imagecreate($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagejpeg($image, $filename);
        imagedestroy($image);
    }
}

}
