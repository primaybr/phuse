<?php

declare(strict_types=1);

namespace Core\Exception;

/**
 * ValidationException class for session validation errors
 */
class ValidationException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
