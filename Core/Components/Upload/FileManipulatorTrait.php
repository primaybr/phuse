<?php

declare(strict_types=1);

namespace Core\Components\Upload;

trait FileManipulatorTrait
{
    // Move the file to the upload directory
    private function moveFile(array $file): bool
    {
        
		$this->fileName ??= uniqid() . (str_ends_with($file['name'], '.') ? '' : '.') . $file['name'];
	    $destination = $this->dir?->trim(DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->fileName;
		
        if (!is_dir(dirname($destination))) {
            chmod(dirname($destination), 0777, true);
        }
       
        return @move_uploaded_file($file['tmp_name'], $destination) ?: ($this->error = 'Failed to move file') && false;
    }

    // Apply XSS protection to the file
    private function protectFile(array $file): void
    {
        // Use early return instead of nested if
        if (!$this->xssProtection) {
            return;
        }
        // Use file_get_contents and file_put_contents with LOCK_EX flag
        $content = file_get_contents($file['tmp_name']);
        $content = htmlentities($content, ENT_QUOTES, 'UTF-8');
        file_put_contents($file['tmp_name'], $content, LOCK_EX);
    }
}