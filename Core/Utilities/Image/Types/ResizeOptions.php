<?php

declare(strict_types=1);

namespace Core\Utilities\Image\Types;

/**
 * Resize options for image manipulation
 */
class ResizeOptions
{
    /**
     * Resize modes
     */
    public const RESIZE_EXACT = 'exact';
    public const RESIZE_FIT = 'fit';
    public const RESIZE_FILL = 'fill';
    public const RESIZE_CROP = 'crop';
    public const RESIZE_AUTO = 'auto';

    /**
     * Target width
     */
    public int $width;

    /**
     * Target height
     */
    public int $height;

    /**
     * Resize mode
     */
    public string $mode = self::RESIZE_AUTO;

    /**
     * Background color for fill mode (hex format without #)
     */
    public string $backgroundColor = 'ffffff';

    /**
     * Maintain aspect ratio
     */
    public bool $maintainAspectRatio = true;

    /**
     * Allow upscaling
     */
    public bool $allowUpscale = false;

    /**
     * Quality for lossy formats (0-100)
     */
    public int $quality = 90;

    /**
     * Filter for resizing
     */
    public int $filter = IMG_BILINEAR_FIXED;

    /**
     * Create resize options with width and height
     */
    public function __construct(int $width = 0, int $height = 0)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Set exact dimensions (stretches image to fit exactly)
     */
    public function exact(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;
        $this->mode = self::RESIZE_EXACT;
        $this->maintainAspectRatio = false;
        return $this;
    }

    /**
     * Fit image within dimensions (maintains aspect ratio, may leave empty space)
     */
    public function fit(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;
        $this->mode = self::RESIZE_FIT;
        $this->maintainAspectRatio = true;
        return $this;
    }

    /**
     * Fill dimensions (maintains aspect ratio, crops if necessary)
     */
    public function fill(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;
        $this->mode = self::RESIZE_FILL;
        $this->maintainAspectRatio = true;
        return $this;
    }

    /**
     * Auto resize (chooses best fit based on aspect ratio)
     */
    public function auto(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;
        $this->mode = self::RESIZE_AUTO;
        $this->maintainAspectRatio = true;
        return $this;
    }

    /**
     * Set background color for fill operations
     */
    public function background(string $color): self
    {
        $this->backgroundColor = ltrim($color, '#');
        return $this;
    }

    /**
     * Set quality for lossy formats
     */
    public function quality(int $quality): self
    {
        $this->quality = max(0, min(100, $quality));
        return $this;
    }

    /**
     * Enable or disable aspect ratio maintenance
     */
    public function maintainAspectRatio(bool $maintain): self
    {
        $this->maintainAspectRatio = $maintain;
        return $this;
    }

    /**
     * Enable or disable upscaling
     */
    public function allowUpscale(bool $allow): self
    {
        $this->allowUpscale = $allow;
        return $this;
    }

    /**
     * Set resize filter
     */
    public function filter(int $filter): self
    {
        $validFilters = [
            IMG_NEAREST_NEIGHBOUR,
            IMG_BILINEAR_FIXED,
            IMG_BICUBIC,
            IMG_BICUBIC_FIXED
        ];

        if (in_array($filter, $validFilters)) {
            $this->filter = $filter;
        }

        return $this;
    }

    /**
     * Validate options
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->width <= 0) {
            $errors[] = 'Width must be positive';
        }

        if ($this->height <= 0) {
            $errors[] = 'Height must be positive';
        }

        if ($this->quality < 0 || $this->quality > 100) {
            $errors[] = 'Quality must be between 0 and 100';
        }

        $validModes = [
            self::RESIZE_EXACT,
            self::RESIZE_FIT,
            self::RESIZE_FILL,
            self::RESIZE_CROP,
            self::RESIZE_AUTO
        ];

        if (!in_array($this->mode, $validModes)) {
            $errors[] = 'Invalid resize mode';
        }

        // Validate hex color
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $this->backgroundColor)) {
            $errors[] = 'Background color must be a valid 6-digit hex color';
        }

        return $errors;
    }

    /**
     * Convert RGB hex to GD color array
     */
    public function getBackgroundColorArray(): array
    {
        $r = hexdec(substr($this->backgroundColor, 0, 2));
        $g = hexdec(substr($this->backgroundColor, 2, 2));
        $b = hexdec(substr($this->backgroundColor, 4, 2));

        return [$r, $g, $b];
    }

    /**
     * Get configuration as array
     */
    public function toArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'mode' => $this->mode,
            'backgroundColor' => $this->backgroundColor,
            'maintainAspectRatio' => $this->maintainAspectRatio,
            'allowUpscale' => $this->allowUpscale,
            'quality' => $this->quality,
            'filter' => $this->filter
        ];
    }
}
