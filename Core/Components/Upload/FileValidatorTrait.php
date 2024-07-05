<?php

declare(strict_types=1);

namespace Core\Components\Upload;

trait FileValidatorTrait
{
    // Check if the file is valid
    private function isValid(array $file): bool
    {
        // Use match expression instead of multiple if statements
        // Use str_ends_with function instead of pathinfo
        // Use null coalescing operator instead of assigning default values
        return match (true) {
            !is_uploaded_file($file['tmp_name']) => ($this->error = 'File not uploaded') && false,
            $file['size'] > ($this->maxSize ?? PHP_INT_MAX) => ($this->error = 'File too large') && false,
            !in_array(str_ends_with($file['name'], '.') ? '' : '.' . $file['name'], $this->extensions ?? []) => ($this->error = 'Invalid file extension') && false,
            strlen($file['name']) > ($this->maxLength ?? PHP_INT_MAX) => ($this->error = 'File name too long') && false,
            $this->isImage($file) && !$this->isValidImage($file) => ($this->error = 'Invalid image dimensions') && false,
            !in_array(mime_content_type($file['tmp_name']), $this->allowedMimes ?? []) => ($this->error = 'Invalid file MIME type') && false,
            default => true,
        };
    }

    // Check if the file is an image
    private function isImage(array $file): bool
    {
        // Use match expression instead of array for image extensions
        return match (str_ends_with($file['name'], '.') ? '' : '.' . $file['name']) {
            '.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp' => true,
            default => false,
        };
    }

    // Check if the image dimensions are within the limits
    private function isValidImage(array $file): bool
    {
        // Use null coalescing operator instead of assigning default values
        // Use list unpacking instead of list function
        [$width, $height] = getimagesize($file['tmp_name']);
        return $width >= ($this->minWidth ?? 0)
            && $width <= ($this->maxWidth ?? PHP_INT_MAX)
            && $height >= ($this->minHeight ?? 0)
            && $height <= ($this->maxHeight ?? PHP_INT_MAX);
    }
}
