<?php

declare(strict_types=1);

namespace Core\Utilities\Upload;

use Core\Folder\Path as Path;
use Core\Log;

/**
 * Upload Utility Class
 *
 * Handles secure file uploads with validation, XSS protection, and comprehensive error handling.
 * Integrates with the framework's logging system for consistent error tracking and debugging.
 *
 * @package Core\Utilities\Upload
 * @author  Phuse Framework
 */
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
    private string $error = ''; // The error message
    private Log $logger; // Framework logger instance

    private string $logFileName = 'upload/upload';
    private bool $enableLogging = true;

    /**
     * Upload constructor.
     * Initializes the upload handler with default MIME types and logging.
     */
    public function __construct()
    {
        // Initialize logger
        $this->logger = new Log();

        // Ensure log directory exists
        $this->ensureLogDirectoryExists($this->logFileName);

        // Set the default allowed mimes from configuration
        $this->loadDefaultMimes();
    }

    /**
     * Configure the upload handler with a configuration object.
     */
    public function configure(UploadConfig $config): self
    {
        $settings = $config->toArray();

        $this->setMaxSize($settings['maxSize']);
        $this->setExtensions($settings['extensions']);
        $this->setMaxLength($settings['maxLength']);
        $this->setXSSProtection($settings['xssProtection']);
        $this->setDimensions(
            $settings['minWidth'],
            $settings['maxWidth'],
            $settings['minHeight'],
            $settings['maxHeight']
        );
        $this->setMimes($settings['allowedMimes']);
        $this->setLogFileName($settings['logFileName']);
        $this->setEnableLogging($settings['enableLogging']);

        $this->logger->setLogName($this->logFileName)->write('Upload configuration applied successfully');

        return $this;
    }

    /**
     * Load default MIME types from configuration file.
     */
    private function loadDefaultMimes(): void
    {
        try {
            if (file_exists(Path::CONFIG . 'Mimes.php')) {
                $mimes = include(Path::CONFIG . 'Mimes.php');
                $this->setMimes($mimes);
                if ($this->enableLogging) {
                    $this->logger->setLogName($this->logFileName)->write('MIME types loaded successfully from configuration');
                }
            } else {
                // Fallback MIME types for common file types
                $this->setMimes([
                    'jpg' => ['image/jpeg', 'image/pjpeg'],
                    'jpeg' => ['image/jpeg', 'image/pjpeg'],
                    'png' => ['image/png', 'image/x-png'],
                    'gif' => ['image/gif'],
                    'webp' => ['image/webp'],
                ]);
                if ($this->enableLogging) {
                    $this->logger->setLogName($this->logFileName)->write('Using fallback MIME types - configuration file not found');
                }
            }
        } catch (\Exception $e) {
            if ($this->enableLogging) {
                $this->logger->setLogName($this->logFileName)->write('Error loading MIME configuration: ' . $e->getMessage());
            }
            // Continue with empty mimes array
            $this->allowedMimes = [];
        }
    }

    public function setDir(string $path): void
    {
        $this->dir = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write("Upload directory set to: {$this->dir}");
        }
    }

    public function setMaxSize(int $size): void
    {
        $this->maxSize = $size;
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write("Maximum file size set to: {$size} bytes");
        }
    }

    public function setExtensions(array $extensions): void
    {
        $this->extensions = array_map('strtolower', $extensions);
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write('Allowed extensions updated: ' . implode(', ', $this->extensions));
        }
    }

    public function setFileName(string $name): void
    {
        $this->fileName = $name;
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write("Custom filename set to: {$name}");
        }
    }

    public function setMaxLength(int $length): void
    {
        $this->maxLength = $length;
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write("Maximum filename length set to: {$length}");
        }
    }

    public function setXSSProtection(bool $flag): void
    {
        $this->xssProtection = $flag;
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write("XSS protection " . ($flag ? 'enabled' : 'disabled'));
        }
    }

    public function setDimensions(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight): void
    {
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
        $this->minHeight = $minHeight;
        $this->maxHeight = $maxHeight;
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write("Image dimensions set: min {$minWidth}x{$minHeight}, max {$maxWidth}x{$maxHeight}");
        }
    }

    public function setMimes(array $allowed): void
    {
        $this->allowedMimes = $allowed;
        if ($this->enableLogging) {
            $this->logger->setLogName($this->logFileName)->write('MIME types configuration updated');
        }
    }

    /**
     * Set log file name for logging operations.
     */
    public function setLogFileName(string $name): void
    {
        $this->logFileName = $name;
        $this->ensureLogDirectoryExists($name);
    }

    /**
     * Get current log file name.
     */
    public function getLogFileName(): string
    {
        return $this->logFileName;
    }

    /**
     * Set whether logging is enabled.
     */
    public function setEnableLogging(bool $enabled): void
    {
        $this->enableLogging = $enabled;
    }

    /**
     * Check if logging is enabled.
     */
    public function isLoggingEnabled(): bool
    {
        return $this->enableLogging;
    }

    /**
     * Ensure the log directory exists for subdirectory logging.
     */
    private function ensureLogDirectoryExists(string $logName): void
    {
        if (str_contains($logName, '/')) {
            $logDir = Path::LOGS . dirname($logName);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
        }
    }

    public function upload(array $file): bool
    {
        $this->error = ''; // Reset error message

        try {
            if ($this->enableLogging) {
                $this->logger->setLogName($this->logFileName)->write('Starting file upload process');
            }

            // Apply XSS protection to the file if enabled
            if ($this->xssProtection && !$this->isBinaryFile($file)) {
                $this->protectFile($file);
            }

            // Check if the file is valid
            if ($this->isValid($file)) {
                // Move the file to the upload directory
                $success = $this->moveFile($file);

                if ($success) {
                    if ($this->enableLogging) {
                        $this->logger->setLogName($this->logFileName)->write('File uploaded successfully: ' . basename($file['name']));
                    }
                    return true;
                } else {
                    if ($this->enableLogging) {
                        $this->logger->setLogName($this->logFileName)->write('Failed to move uploaded file: ' . $this->error);
                    }
                    return false;
                }
            } else {
                if ($this->enableLogging) {
                    $this->logger->setLogName($this->logFileName)->write('File validation failed: ' . $this->error);
                }
                return false;
            }
        } catch (\Exception $e) {
            $this->error = 'Upload error: ' . $e->getMessage();
            if ($this->enableLogging) {
                $this->logger->setLogName($this->logFileName)->write('Upload exception: ' . $e->getMessage());
            }
            return false;
        }
    }

    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Check if file is a binary file that shouldn't have XSS protection applied.
     */
    private function isBinaryFile(array $file): bool
    {
        $binaryExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'ico', 'svg'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        return in_array($extension, $binaryExtensions);
    }
}
