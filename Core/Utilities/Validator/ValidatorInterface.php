<?php

declare(strict_types=1);

namespace Core\Utilities\Validator;

/**
 * Validator Interface
 *
 * Defines the contract for validation classes that can add rules,
 * validate data against those rules, and return validation errors.
 *
 * @package Core\Utilities\Validator
 */
interface ValidatorInterface
{
    /**
     * Add a validation rule for a specific field
     *
     * @param string $field The name of the field to validate
     * @param string $method The validation method name (must exist in implementing class)
     * @param mixed ...$args Additional arguments to pass to the validation method
     * @return self Returns self for method chaining
     * @throws \InvalidArgumentException If the validation method doesn't exist
     */

    /**
     * Validate data against all registered rules
     *
     * @param array $data The data array to validate
     * @return bool True if all validation rules pass, false otherwise
     */

    /**
     * Get all validation errors from the last validation run
     *
     * @return array Array of validation errors, keyed by field name with error messages
     */
}
