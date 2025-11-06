<?php

declare(strict_types=1);

namespace Core\Exception;

/**
 * Common exception handling methods for framework components
 *
 * Provides convenient methods for throwing specific types of exceptions
 * with proper context and logging integration.
 */
trait CommonTrait
{
    /**
     * Throw a validation exception
     *
     * @param string $message Error message
     * @param array $validationErrors Array of validation errors
     * @param array $context Additional context data
     * @throws ValidationException
     */
    public static function throwValidationException(
        string $message = 'Validation failed',
        array $validationErrors = [],
        array $context = []
    ): void {
        throw new ValidationException($message, $validationErrors, $context);
    }

    /**
     * Throw a system exception
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @throws SystemException
     */
    public static function throwSystemException(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ): void {
        throw new SystemException($message, $context, $code, $previous);
    }

    /**
     * Throw a database exception
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @throws DatabaseException
     */
    public static function throwDatabaseException(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ): void {
        throw new DatabaseException($message, $context, $code, $previous);
    }

    /**
     * Throw a filesystem exception
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @throws FilesystemException
     */
    public static function throwFilesystemException(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ): void {
        throw new FilesystemException($message, $context, $code, $previous);
    }

    /**
     * Throw a configuration exception
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @throws ConfigurationException
     */
    public static function throwConfigurationException(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ): void {
        throw new ConfigurationException($message, $context, $code, $previous);
    }

    /**
     * Throw a runtime exception
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @throws RuntimeException
     */
    public static function throwRuntimeException(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ): void {
        throw new RuntimeException($message, $context, $code, $previous);
    }

    /**
     * Check if a condition is met, throw exception if not
     *
     * @param bool $condition Condition to check
     * @param string $message Exception message if condition fails
     * @param string $exceptionType Type of exception to throw
     * @param array $context Additional context data
     * @throws BaseException
     */
    public static function assert(
        bool $condition,
        string $message = 'Assertion failed',
        string $exceptionType = RuntimeException::class,
        array $context = []
    ): void {
        if (!$condition) {
            $exceptionClass = $exceptionType;
            throw new $exceptionClass($message, $context);
        }
    }

    /**
     * Check if a value is not null, throw exception if it is
     *
     * @param mixed $value Value to check
     * @param string $message Exception message if value is null
     * @param array $context Additional context data
     * @return mixed The value if not null
     * @throws RuntimeException
     */
    public static function assertNotNull(
        mixed $value,
        string $message = 'Value cannot be null',
        array $context = []
    ): mixed {
        if ($value === null) {
            throw new RuntimeException($message, $context);
        }
        return $value;
    }

    /**
     * Check if a value is not empty, throw exception if it is
     *
     * @param mixed $value Value to check
     * @param string $message Exception message if value is empty
     * @param array $context Additional context data
     * @return mixed The value if not empty
     * @throws ValidationException
     */
    public static function assertNotEmpty(
        mixed $value,
        string $message = 'Value cannot be empty',
        array $context = []
    ): mixed {
        if (empty($value)) {
            throw new ValidationException($message, [], $context);
        }
        return $value;
    }

    /**
     * Check if a file exists, throw exception if it doesn't
     *
     * @param string $filePath Path to file
     * @param string $message Custom error message
     * @param array $context Additional context data
     * @return string File path if exists
     * @throws FilesystemException
     */
    public static function assertFileExists(
        string $filePath,
        string $message = '',
        array $context = []
    ): string {
        if (!file_exists($filePath)) {
            $message = $message ?: "File not found: {$filePath}";
            $context['file_path'] = $filePath;
            throw new FilesystemException($message, $context, 2001);
        }
        return $filePath;
    }

    /**
     * Check if a directory exists, throw exception if it doesn't
     *
     * @param string $dirPath Path to directory
     * @param string $message Custom error message
     * @param array $context Additional context data
     * @return string Directory path if exists
     * @throws FilesystemException
     */
    public static function assertDirectoryExists(
        string $dirPath,
        string $message = '',
        array $context = []
    ): string {
        if (!is_dir($dirPath)) {
            $message = $message ?: "Directory not found: {$dirPath}";
            $context['directory_path'] = $dirPath;
            throw new FilesystemException($message, $context, 2003);
        }
        return $dirPath;
    }

    /**
     * Try to execute a callable, return result or throw exception on failure
     *
     * @param callable $callback Function to execute
     * @param string $errorMessage Error message if callback fails
     * @param array $context Additional context data
     * @return mixed Result of callback
     * @throws RuntimeException
     */
    public static function tryOrFail(
        callable $callback,
        string $errorMessage = 'Operation failed',
        array $context = []
    ): mixed {
        try {
            return $callback();
        } catch (\Throwable $e) {
            $context['original_exception'] = $e->getMessage();
            throw new RuntimeException($errorMessage, $context, 0, $e);
        }
    }
}
