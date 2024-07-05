<?php

declare(strict_types=1);

namespace Core\Components\Upload;

use Core\Folder\Path as Path;

class Upload implements UploadInterface
{
    use FileValidatorTrait, FileManipulatorTrait;

    // Define the properties with default values
    private string $dir; // The upload directory
    private int $maxSize = 5_000_000; // The maximum file size in bytes = 5MB
    private array $extensions = ['jpg', 'png', 'gif', 'webp']; // The allowed file extensions
    private string $fileName = ''; // The custom file name
    private int $maxLength = 64; // The maximum file name length
    private bool $xssProtection = true; // The XSS protection flag
    private int $minWidth = 50; // The minimum image width
    private int $maxWidth = 3200; // The maximum image width
    private int $minHeight = 50; // The minimum image height
    private int $maxHeight = 2400; // The maximum image height
    private array $allowedMimes; // The allowed MIME types
    private string $error; // The error message

    public function __construct()
    {
        //set the default allowed mimes
        if (file_exists(Path::CONFIG . 'Mimes.php')) {
            $mimes = include(Path::CONFIG . 'Mimes.php');
            $this->setMimes($mimes);
        }
    }

    public function setDir(string $path): void
    {
        $this->dir = $path;
    }

    public function setMaxSize(int $size): void
    {
        $this->maxSize = $size;
    }

    public function setExtensions(array $extensions): void
    {
        $this->extensions = $extensions;
    }

    public function setFileName(string $name): void
    {
        $this->fileName = $name;
    }

    public function setMaxLength(int $length): void
    {
        $this->maxLength = $length;
    }

    public function setXSSProtection(bool $flag): void
    {
        $this->xssProtection = $flag;
    }

    public function setDimensions(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight): void
    {
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
        $this->minHeight = $minHeight;
        $this->maxHeight = $maxHeight;
    }

    public function setMimes(array $allowed): void
    {
        $this->allowedMimes = $allowed;
    }

    public function upload(array $file): bool
    {
        // Apply XSS protection to the file
        $this->protectFile($file);
        // Check if the file is valid
        if ($this->isValid($file)) {
            // Move the file to the upload directory
            return $this->moveFile($file);
        }
        // Return false if any error occurs
        return false;
    }

    public function getError(): string
    {
        return $this->error;
    }
}
