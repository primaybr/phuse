<?php
declare(strict_types=1);

namespace Core\Components\Image;

// Trait for Image Manipulation
trait ImageTrait
{
    protected $image;
    protected $imageSource;

    public function setImageSource(string $imagePath): self
    {
        $this->imageSource = $imagePath;
        $this->image = imagecreatefromstring(file_get_contents($imagePath));
        return $this;
    }

    public function resize(int $width, int $height): self
    {
        imagescale($this->image, $width, $height);
        return $this;
    }

    public function crop(int $x, int $y, int $width, int $height): self
    {
        $this->image = imagecrop($this->image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        return $this;
    }

    public function rotate(float $angle): self
    {
        $this->image = imagerotate($this->image, $angle, 0);
        return $this;
    }

    public function compress(int $quality): self
    {
        ob_start();
        imagejpeg($this->image, null, $quality);
        $this->image = imagecreatefromstring(ob_get_clean());
        return $this;
    }

    public function addWatermark(string $watermarkPath): self
    {
        $watermark = imagecreatefrompng($watermarkPath);
        imagecopy($this->image, $watermark, 0, 0, 0, 0, imagesx($watermark), imagesy($watermark));
        return $this;
    }

    public function save(string $outputPath): bool
    {
        // Determine image type and save accordingly
        $extension = pathinfo($outputPath, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($this->image, $outputPath);
            case 'png':
                return imagepng($this->image, $outputPath);
            case 'gif':
                return imagegif($this->image, $outputPath);
			case 'webp':
                return imagewebp($this->image, $outputPath);
            default:
                return false;
        }
    }

    public function output(): bool
    {
        // Determine image type and output accordingly
        $extension = pathinfo($this->imageSource, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                return imagejpeg($this->image);
            case 'png':
                header('Content-Type: image/png');
                return imagepng($this->image);
            case 'gif':
                header('Content-Type: image/gif');
                return imagegif($this->image);
			case 'webp':
                header('Content-Type: image/webp');
                return imagegif($this->image);
            default:
                return false;
        }
    }
	
}
