<?php

declare(strict_types=1);

namespace Core\Utilities\Validator;

/**
 * Validator Class
 *
 * A comprehensive validation class that implements the ValidatorInterface
 * and uses the ValidatorTrait to provide a fluent interface for data validation.
 *
 * This class allows you to add validation rules for specific fields and then
 * validate data against those rules. It supports method chaining for easy use.
 *
 * Example usage:
 * ```php
 * $validator = new Validator();
 * $validator->rule('email', 'required')
 *           ->rule('email', 'email')
 *           ->rule('age', 'int')
 *           ->rule('age', 'range', 18, 65);
 *
 * if ($validator->validate($data)) {
 *     // Validation passed
 * } else {
 *     $errors = $validator->errors();
 * }
 * ```
 *
 * @package Core\Utilities\Validator
 */
class Validator implements ValidatorInterface
{
    use ValidatorTrait;

    /**
     * Storage for validation rules organized by field name
     *
     * @var array<string, array> Array where keys are field names and values are arrays of rules
     */
    protected array $rules = [];

    /**
     * Storage for validation errors from the last validation run
     *
     * @var array<string, array> Array where keys are field names and values are arrays of error messages
     */
    protected array $errors = [];

    /**
     * Add a validation rule for a specific field
     *
     * This method allows you to add validation rules to specific fields. The validation
     * method must exist in this class (usually provided by the ValidatorTrait).
     * Multiple rules can be added to the same field by calling this method multiple times.
     *
     * @param string $field The name of the field to validate
     * @param string $method The name of the validation method to apply
     * @param mixed ...$args Additional arguments to pass to the validation method
     * @return self Returns self for method chaining
     * @throws \InvalidArgumentException If the validation method doesn't exist in this class
     *
     * @example
     * $validator->rule('email', 'required')
     *           ->rule('email', 'email')
     *           ->rule('password', 'minLength', 8);
     */
    public function rule(string $field, string $method, mixed ...$args): self
    {
        $this->rules[$field][] = [$method, $args];
        return $this;
    }

    /**
     * Validate data against all registered rules
     *
     * This method runs validation on the provided data array against all rules
     * that have been added via the rule() method. It clears any previous errors
     * and collects new ones during validation.
     *
     * @param array $data The data array to validate, where keys are field names
     * @return bool True if all validation rules pass, false if any fail
     *
     * @example
     * $data = ['email' => 'user@example.com', 'age' => 25];
     * $isValid = $validator->validate($data); // Returns true or false
     */
    public function validate(array $data): bool
    {
        // Loop through the rules for each field
        foreach ($this->rules as $field => $rules) {
            // Get the value of the field from the data
            $value = $data[$field] ?? null;
            // Loop through the rules for the field
            foreach ($rules as $rule) {
                // Get the method and the arguments for the rule
                [$method, $args] = $rule;
                // Call the method with the value and the arguments
                if (!$this->$method($value, ...$args)) {
                    $this->errors[$field][] = "The $field is invalid for $method rule";
                }
            }
        }
        // Return true if there are no errors, false otherwise
        return empty($this->errors);
    }

    /**
     * Get all validation errors from the last validation run
     *
     * Returns an array of validation errors where the keys are field names
     * and the values are arrays of error messages for each field.
     *
     * @return array Array of validation errors organized by field name
     *
     * @example
     * $errors = $validator->errors();
     * // Returns: ['email' => ['The email is invalid for email rule']]
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
