<?php

declare(strict_types=1);

/**
 * Model Validation Example
 *
 * This example demonstrates how to use the validation features integrated
 * into the ORM Model class. The validation system automatically validates
 * data before saving or updating records in the database.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Include the bootstrap file
require_once __DIR__ . '/../Core/Boot.php';

// Example User Model with validation rules
class User extends Core\Model
{
    protected array $fillable = ['name', 'email', 'password', 'age', 'status'];
    protected array $hidden = ['password'];
    protected bool $timestamps = true;

    // Define validation rules
    protected array $validationRules = [
        'name' => 'required|minLength:2|maxLength:100',
        'email' => 'required|email',
        'password' => 'required|minLength:8',
        'age' => 'int|range:18,120',
        'status' => 'in:active,inactive'
    ];

    // Define custom validation messages
    protected array $validationMessages = [
        'name.required' => 'The name field is required.',
        'name.minLength' => 'The name must be at least 2 characters.',
        'name.maxLength' => 'The name cannot exceed 100 characters.',
        'email.required' => 'The email field is required.',
        'email.email' => 'Please provide a valid email address.',
        'password.required' => 'The password field is required.',
        'password.minLength' => 'The password must be at least 8 characters long.',
        'age.int' => 'The age must be a valid number.',
        'age.range' => 'The age must be between 18 and 120.',
        'status.in' => 'The status must be either active or inactive.'
    ];

    public function __construct()
    {
        parent::__construct('users');
    }
}

echo "=== Model Validation Example ===\n\n";

try {
    // Create a new User instance
    $user = new User();

    echo "1. Testing validation with invalid data:\n";
    $invalidData = [
        'name' => '', // Required but empty
        'email' => 'invalid-email', // Invalid email format
        'password' => '123', // Too short
        'age' => 'not-a-number', // Not an integer
        'status' => 'banned' // Not in allowed values
    ];

    try {
        $user->save($invalidData);
        echo "ERROR: Validation should have failed!\n";
    } catch (Core\Exception\ValidationException $e) {
        echo "✓ Validation correctly failed with errors:\n";
        echo $e->getMessage() . "\n\n";
    }

    echo "2. Testing validation with valid data:\n";
    $validData = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'securepassword123',
        'age' => 25,
        'status' => 'active'
    ];

    try {
        $isValid = $user->validate($validData);
        echo "✓ Validation passed for valid data\n\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }

    echo "3. Testing update validation:\n";
    // For updates, we can set different validation rules that don't require all fields
    $user->setValidationRules([
        'name' => 'required|minLength:2|maxLength:100',
        'email' => 'required|email',
        'age' => 'int|range:18,120'
        // Note: password not required for updates
    ]);

    $updateData = [
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
        'age' => 30
    ];

    try {
        $user->validate($updateData);
        echo "✓ Update validation passed\n\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }

    echo "4. Testing validation disabling:\n";
    $user->validateOnSave(false);
    $user->setValidationRules([]); // Temporarily disable all rules
    $invalidDataWithoutValidation = [
        'name' => '', // Would normally fail
        'email' => 'invalid-email',
        'password' => '123'
    ];

    try {
        $isValid = $user->validate($invalidDataWithoutValidation);
        echo "✓ Validation was disabled, no exception thrown\n\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }

    echo "5. Testing custom validation rules:\n";

    // Example of setting custom rules at runtime
    $user->setValidationRules([
        'name' => 'required|minLength:5',
        'email' => 'required|email'
    ]);

    $customData = [
        'name' => 'Bob', // Too short according to new rules
        'email' => 'bob@example.com'
    ];

    try {
        $user->validate($customData);
        echo "ERROR: Custom validation should have failed!\n";
    } catch (Core\Exception\ValidationException $e) {
        echo "✓ Custom validation correctly failed:\n";
        echo $e->getMessage() . "\n\n";
    }

    echo "6. Testing validation methods:\n";

    // Get current validation rules
    $rules = $user->getValidationRules();
    echo "Current validation rules:\n";
    print_r($rules);

    // Get current validation messages
    $messages = $user->getValidationMessages();
    echo "\nCurrent validation messages:\n";
    print_r($messages);

    echo "\n=== Example completed successfully ===\n";

} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
