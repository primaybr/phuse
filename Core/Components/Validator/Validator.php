<?php

declare(strict_types=1);

namespace Core\Components\Validator;

// A class that implements the validator interface using the validator trait
class Validator implements ValidatorInterface
{
    use ValidatorTrait;

    // An array that stores the rules for each field
    protected array $rules = [];

    // An array that stores the validator errors
    protected array $errors = [];

    // A method that adds a rule for a given field
    public function rule(string $field, string $method, mixed ...$args): self
    {
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Invalid validator method: $method");
        }
        
        $this->rules[$field][] = [$method, $args];

        return $this;
    }

    // A method that validates the data against the rules
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

    // A method that returns the validator errors
    public function errors(): array
    {
        return $this->errors;
    }
}
