<?php

declare(strict_types=1);

namespace Core\Components\Validator;

// A trait that provides common validator methods
trait ValidatorTrait
{
    // A method that checks if a value is not empty
    public function required(mixed $value): bool
    {
        return !empty($value);
    }

    // A method that checks if a value matches a regular expression
    public function regex(string $value, string $pattern): bool|int
    {
        return preg_match(pattern: $pattern, subject: $value);
    }

    // A method that checks if a value is a valid email address
    public function email(string $value): bool|string
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_EMAIL);
    }

    // A method that checks if a value is a valid URL
    public function url(string $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_URL);
    }

    // A method that checks if a value is a valid IP address
    public function ip(string $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_IP);
    }

    // A method that checks if a value is a valid integer
    public function int(mixed $value): bool|int
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_INT);
    }

    // A method that checks if a value is a valid float
    public function float(mixed $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_FLOAT);
    }

    // A method that checks if a value is a valid boolean
    public function bool(mixed $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_BOOLEAN);
    }

    // A method that checks if a value is in a given range
    public function range(int|float $value, int|float $min, int|float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    // A method that checks if a value is in a given list of values
    public function in(mixed $value, array $list): bool
    {
        return in_array(needle: $value, haystack: $list);
    }

    // A method that checks if a value has a given length
    public function length(string $value, int $length): bool
    {
        return strlen(string: $value) == $length;
    }

    // A method that checks if a value has a minimum length
    public function minLength(string $value, int $min): bool
    {
        return strlen(string: $value) >= $min;
    }

    // A method that checks if a value has a maximum length
    public function maxLength(string $value, int $max): bool
    {
        return strlen(string: $value) <= $max;
    }
}
