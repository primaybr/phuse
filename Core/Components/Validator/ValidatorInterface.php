<?php

declare(strict_types=1);

namespace Core\Components\Validator;

// An interface that defines the contract for a validator class
interface ValidatorInterface
{
    // A method that adds a rule for a given field
    public function rule(string $field, string $method, mixed ...$args): self;

    // A method that validates the data against the rules
    public function validate(array $data): bool;

    // A method that returns the validator errors
    public function errors(): array;
}
