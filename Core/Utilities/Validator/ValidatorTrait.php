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

    /**
     * Check if a value is a plausible plaintext password (length only - hashing
     * and storage are the caller's responsibility via Core\Security\Password)
     *
     * @param mixed $value The plaintext password to check
     * @param int $minLength The minimum length required (default 8)
     * @return bool True if the value is a non-empty string meeting the minimum length
     */
    public function password(mixed $value, int $minLength = 8): bool
    {
        return is_string($value) && strlen($value) >= $minLength;
    }

    /**
     * Check if a value is a valid date matching the given format
     *
     * @param mixed $value The value to check
     * @param string $format The expected date format (default 'Y-m-d')
     * @return bool True if the value parses as a date and round-trips to the same format
     */
    public function date(mixed $value, string $format = 'Y-m-d'): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        $date = \DateTime::createFromFormat($format, $value);
        return $date !== false && $date->format($format) === $value;
    }

    /**
     * Check if a value is a valid date-time matching the given format
     *
     * @param mixed $value The value to check
     * @param string $format The expected date-time format (default 'Y-m-d H:i:s')
     * @return bool True if the value parses as a date-time and round-trips to the same format
     */
    public function datetime(mixed $value, string $format = 'Y-m-d H:i:s'): bool
    {
        return $this->date($value, $format);
    }

    /**
     * Check if a value is a UUID (v4 shape)
     *
     * @param mixed $value The value to check
     * @return bool True if the value matches the standard UUID format
     */
    public function uuid(mixed $value): bool
    {
        return is_string($value) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }

    /**
     * Check if an uploaded file's extension is in the allowed list
     *
     * @param mixed $value A $_FILES-shaped array (must contain a 'name' key)
     * @param array $allowed Allowed extensions, without the leading dot (e.g. ['jpg', 'png'])
     * @return bool True if the file's extension is allowed
     */
    public function fileType(mixed $value, array $allowed): bool
    {
        if (!is_array($value) || empty($value['name'])) {
            return false;
        }

        $extension = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        $allowed = array_map('strtolower', $allowed);

        return in_array($extension, $allowed, true);
    }

    /**
     * Check if an uploaded file's size is within the allowed maximum
     *
     * @param mixed $value A $_FILES-shaped array (must contain a 'size' key)
     * @param int $maxBytes The maximum allowed size in bytes
     * @return bool True if the file's size does not exceed the maximum
     */
    public function fileSize(mixed $value, int $maxBytes): bool
    {
        if (!is_array($value) || !isset($value['size'])) {
            return false;
        }

        return (int) $value['size'] <= $maxBytes;
    }

    /**
     * Check if a value matches a confirmation value (e.g. password + password_confirmation)
     *
     * @param mixed $value The value to check
     * @param mixed $confirmationValue The value it must match
     * @return bool True if both values are identical
     */
    public function confirmed(mixed $value, mixed $confirmationValue): bool
    {
        return $value === $confirmationValue;
    }

    /**
     * Check if all values in an array are unique
     *
     * @param mixed $value The array to check
     * @return bool True if the array contains no duplicate values
     */
    public function distinct(mixed $value): bool
    {
        return is_array($value) && count($value) === count(array_unique($value, SORT_REGULAR));
    }

    /**
     * Check if a value is a valid JSON string
     *
     * @param mixed $value The value to check
     * @return bool True if the value decodes as valid JSON
     */
    public function json(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Check that no other row in a table already has this value in the given column
     *
     * @param mixed $value The value to check for uniqueness
     * @param string $table The table to check against
     * @param string $column The column the value must be unique in
     * @param mixed $ignoreId When updating an existing row, its ID to exclude from the check
     * @param string $idColumn The primary key column name (default 'id')
     * @return bool True if no other row already has this value
     */
    public function unique(mixed $value, string $table, string $column, mixed $ignoreId = null, string $idColumn = 'id'): bool
    {
        $model = new \Core\Model($table);
        $model->where($column, (string) $value);

        if ($ignoreId !== null) {
            $model->where($idColumn, (string) $ignoreId, '!=');
        }

        $result = $model->get();

        return empty($result);
    }
}
