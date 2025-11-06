<?php

declare(strict_types=1);

namespace Tests\Core\Utilities\Image;

use Core\Utilities\Image\Image;
use Core\Utilities\Image\ImageConfig;
use Core\Log;
use Core\Folder\Path;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for Image component
 */
class ImageTest extends TestCase
{
    private string $testImagePath;
    private string $testOutputDir;

    protected function setUp(): void
    {
        // Create a simple test image
        $this->testOutputDir = sys_get_temp_dir() . '/image_tests';
        if (!is_dir($this->testOutputDir)) {
            mkdir($this->testOutputDir, 0755, true);
        }

        // Create a simple 100x100 test image
        $this->testImagePath = $this->testOutputDir . '/test_image.png';
        $image = imagecreate(100, 100);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        // Draw a simple pattern
        imagefilledrectangle($image, 0, 0, 100, 100, $white);
        imagefilledrectangle($image, 10, 10, 90, 90, $black);

        imagepng($image, $this->testImagePath);
        imagedestroy($image);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->testOutputDir)) {
            $this->removeDirectory($this->testOutputDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $dir . '/' . $file;
                if (is_dir($filePath)) {
                    $this->removeDirectory($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }
        rmdir($dir);
    }

    public function testImageCanBeCreated(): void
    {
        $image = new Image();
        $this->assertInstanceOf(Image::class, $image);
    }

    public function testImageCanLoadFromFile(): void
    {
        $image = new Image();
        $image->setImageSource($this->testImagePath);

        $this->assertTrue($image->isLoaded());
        $this->assertEmpty($image->getErrors());

        $dimensions = $image->getCurrentDimensions();
        $this->assertEquals(100, $dimensions['width']);
        $this->assertEquals(100, $dimensions['height']);
    }

    public function testImageResize(): void
    {
        $image = new Image($this->testImagePath);
        $this->assertTrue($image->isLoaded());

        $image->resize(50, 50);
        $this->assertEmpty($image->getErrors());

        $dimensions = $image->getCurrentDimensions();
        $this->assertEquals(50, $dimensions['width']);
        $this->assertEquals(50, $dimensions['height']);
    }

    public function testImageCrop(): void
    {
        $image = new Image($this->testImagePath);
        $this->assertTrue($image->isLoaded());

        $image->crop(10, 10, 50, 50);
        $this->assertEmpty($image->getErrors());

        $dimensions = $image->getCurrentDimensions();
        $this->assertEquals(50, $dimensions['width']);
        $this->assertEquals(50, $dimensions['height']);
    }

    public function testImageRotate(): void
    {
        $image = new Image($this->testImagePath);
        $this->assertTrue($image->isLoaded());

        $image->rotate(90);
        $this->assertEmpty($image->getErrors());

        // After 90 degree rotation, dimensions should be swapped
        $dimensions = $image->getCurrentDimensions();
        $this->assertEquals(100, $dimensions['width']);
        $this->assertEquals(100, $dimensions['height']);
    }

    public function testImageCompress(): void
    {
        $image = new Image($this->testImagePath);
        $this->assertTrue($image->isLoaded());

        $image->compress(80);
        $this->assertEmpty($image->getErrors());

        $dimensions = $image->getCurrentDimensions();
        $this->assertEquals(100, $dimensions['width']);
        $this->assertEquals(100, $dimensions['height']);
    }

    public function testImageSave(): void
    {
        $image = new Image($this->testImagePath);
        $image->resize(80, 80);

        $outputPath = $this->testOutputDir . '/resized_image.jpg';
        $result = $image->save($outputPath);

        $this->assertTrue($result);
        $this->assertFileExists($outputPath);
        $this->assertEmpty($image->getErrors());
    }

    public function testImageWithConfig(): void
    {
        $config = new ImageConfig();
        $config->maxWidth = 200;
        $config->maxHeight = 200;

        $image = new Image($this->testImagePath, $config);
        $this->assertTrue($image->isLoaded());

        $image->resize(150, 150);
        $this->assertEmpty($image->getErrors());
    }

    public function testImageWithLogger(): void
    {
        $logger = new Log();
        $logger->setLogName('image_test');

        $image = new Image($this->testImagePath, null, $logger);

        $this->assertTrue($image->isLoaded());

        // Check that log file was created in the logs directory
        $logFile = Path::LOGS . 'image_test_' . date('Ymd') . '.log';
        $this->assertFileExists($logFile);
    }

    public function testInvalidImagePath(): void
    {
        $image = new Image();
        $image->setImageSource('/nonexistent/image.png');

        $this->assertFalse($image->isLoaded());
        $this->assertNotEmpty($image->getErrors());
    }

    public function testInvalidResizeDimensions(): void
    {
        $image = new Image($this->testImagePath);
        $image->resize(0, 100);

        $this->assertNotEmpty($image->getErrors());
    }

    public function testInvalidCropDimensions(): void
    {
        $image = new Image($this->testImagePath);
        $image->crop(0, 0, 0, 100);

        $this->assertNotEmpty($image->getErrors());
    }
}
