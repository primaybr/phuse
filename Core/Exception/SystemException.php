<?php

declare(strict_types=1);

namespace Core\Exception;

/**
 * System-level exceptions for core framework errors
 */
class SystemException extends BaseException
{
    public function __construct(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::TYPE_SYSTEM_ERROR,
            self::SEVERITY_HIGH,
            $context,
            'A system error occurred. Please contact support.',
            $code,
            $previous
        );
    }
}
