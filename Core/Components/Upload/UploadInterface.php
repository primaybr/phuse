<?php
declare(strict_types=1);

namespace Core\Components\Upload;

interface UploadInterface {
    // Set the upload directory
    public function setDir(string $path): void;
    // Set the maximum file size in bytes
    public function setMaxSize(int $size): void;
    // Set the allowed file extensions
    public function setExtensions(array $extensions): void;
    // Set the custom file name
    public function setFileName(string $name): void;
    // Set the maximum file name length
    public function setMaxLength(int $length): void;
    // Set the XSS protection flag
    public function setXSSProtection(bool $flag): void;
    // Set the minimum and maximum width and height for images
    public function setDimensions(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight): void;
    // Set the allowed and default MIME types
    public function setMimes(array $allowed): void;
    // Upload the file and return true or false
    public function upload(array $file): bool;
    // Get the error message if any
    public function getError();
}
