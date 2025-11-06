<?php

declare(strict_types=1);

namespace Core\Utilities\Validator;

/**
 * Validator Trait
 *
 * Provides common validation methods for checking various data types and formats.
 * This trait can be used by validator classes to implement standard validation rules.
 *
 * @package Core\Utilities\Validator
 */
trait ValidatorTrait
{
    /**
     * Check if a value is not empty
     *
     * Validates that a value is not empty, null, false, or an empty string.
     * Arrays and objects are considered valid if they are not empty.
     *
     * @param mixed $value The value to check
     * @return bool True if the value is not empty, false otherwise
     */
    public function required(mixed $value): bool
    {
        return !empty($value);
    }

    /**
     * Check if a value matches a regular expression pattern
     *
     * @param string $value The string value to validate
     * @param string $pattern The regular expression pattern to match against
     * @return bool|int Returns 1 if the pattern matches, 0 if it doesn't, or false on error
     */
    public function regex(string $value, string $pattern): bool|int
    {
        return preg_match(pattern: $pattern, subject: $value);
    }

    /**
     * Validate an email address format
     *
     * @param string $value The email address to validate
     * @return bool|string Returns the filtered email if valid, false otherwise
     */
    public function email(string $value): bool|string
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate a URL format
     *
     * @param string $value The URL to validate
     * @return bool True if the URL is valid, false otherwise
     */
    public function url(string $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_URL);
    }

    /**
     * Validate an IP address format
     *
     * @param string $value The IP address to validate
     * @return bool True if the IP address is valid, false otherwise
     */
    public function ip(string $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_IP);
    }

    /**
     * Validate an integer value
     *
     * @param mixed $value The value to validate as an integer
     * @return bool|int Returns the filtered integer if valid, false otherwise
     */
    public function int(mixed $value): bool|int
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_INT);
    }

    /**
     * Validate a float value
     *
     * @param mixed $value The value to validate as a float
     * @return bool True if the value is a valid float, false otherwise
     */
    public function float(mixed $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_FLOAT);
    }

    /**
     * Validate a boolean value
     *
     * @param mixed $value The value to validate as a boolean
     * @return bool True if the value is a valid boolean, false otherwise
     */
    public function bool(mixed $value): bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if a value is within a specified range
     *
     * @param int|float $value The value to check
     * @param int|float $min The minimum value (inclusive)
     * @param int|float $max The maximum value (inclusive)
     * @return bool True if the value is within the range, false otherwise
     */
    public function range(int|float $value, int|float $min, int|float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Check if a value exists in a given array of allowed values
     *
     * @param mixed $value The value to check for
     * @param array $list The array of allowed values
     * @return bool True if the value exists in the array, false otherwise
     */
    public function in(mixed $value, array $list): bool
    {
        return in_array(needle: $value, haystack: $list);
    }

    /**
     * Check if a string has an exact length
     *
     * @param string $value The string to check
     * @param int $length The exact length required
     * @return bool True if the string length matches exactly, false otherwise
     */
    public function length(string $value, int $length): bool
    {
        return strlen(string: $value) == $length;
    }

    /**
     * Check if a string has a minimum length
     *
     * @param string $value The string to check
     * @param int $min The minimum length required (inclusive)
     * @return bool True if the string length is greater than or equal to minimum, false otherwise
     */
    public function minLength(string $value, int $min): bool
    {
        return strlen(string: $value) >= $min;
    }

    /**
     * Check if a string has a maximum length
     *
     * @param string $value The string to check
     * @param int $max The maximum length allowed (inclusive)
     * @return bool True if the string length is less than or equal to maximum, false otherwise
     */
    public function maxLength(string $value, int $max): bool
    {
        return strlen(string: $value) <= $max;
    }
}
