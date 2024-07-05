<?php
declare(strict_types=1);

namespace Core\Components\Image;

// Interface for Image Manipulation
interface ImageInterface
{
    public function setImageSource(string $imagePath): self;
    public function resize(int $width, int $height): self;
    public function crop(int $x, int $y, int $width, int $height): self;
    public function rotate(float $angle): self;
    public function compress(int $quality): self;
    public function addWatermark(string $watermarkPath): self;
    public function save(string $outputPath): bool;
    public function output(): bool;
}
