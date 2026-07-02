# Validator Utilities

The Validator utilities provide a robust, flexible, and secure data validation system for PHP applications. It supports method chaining, multiple validation rules, and comprehensive error reporting.

## Features

### 🔒 Security & Validation
- **Multiple validation rules**: Email, URL, IP, numeric, length, regex validation
- **Custom validation methods**: Extensible trait system for custom validators
- **Secure by default**: All inputs are validated against specified rules
- **Error collection**: Comprehensive error reporting for each field

### 🛠️ Core Functionality
- **Method chaining**: Fluent interface for easy rule definition
- **Multiple rules per field**: Apply multiple validation rules to single fields
- **Array validation**: Validate complex data structures
- **Custom error messages**: Configurable error message templates

### ⚙️ Framework Integration
- **HTML Components integration**: Seamless integration with form components
- **Error handling**: Consistent with framework error handling patterns
- **Type safety**: Full PHP type declarations and validation

## Installation

The Validator utilities are part of the Core framework and are located in:
```
Core/Utilities/Validator/
├── Validator.php               (Main validation class)
├── ValidatorInterface.php      (Validation interface)
└── ValidatorTrait.php          (Validation methods trait)
```

## Basic Usage

```php
<?php
use Core\Utilities\Validator\Validator;

// Create validator instance
$validator = new Validator();

// Add validation rules
$validator
    ->rule('email', 'required')
    ->rule('email', 'email')
    ->rule('password', 'required')
    ->rule('password', 'minLength', 8)
    ->rule('age', 'int')
    ->rule('age', 'range', 18, 120)
    ->rule('website', 'url');

// Validate data
$data = [
    'email' => 'user@example.com',
    'password' => 'securepassword',
    'age' => 25,
    'website' => 'https://example.com'
];

if ($validator->validate($data)) {
    echo "All data is valid!";
} else {
    $errors = $validator->errors();
    foreach ($errors as $field => $fieldErrors) {
        echo "Field '$field' has errors: " . implode(', ', $fieldErrors);
    }
}
?>
```

## Available Validation Rules

### Built-in Validators

```php
// Required field validation
$validator->rule('field', 'required');

// Email validation
$validator->rule('email', 'email');

// URL validation
$validator->rule('website', 'url');

// IP address validation
$validator->rule('ip', 'ip');

// Integer validation
$validator->rule('count', 'int');

// Float validation
$validator->rule('price', 'float');

// Boolean validation
$validator->rule('active', 'bool');

// Range validation (numeric)
$validator->rule('age', 'range', 18, 65);

// Length validation (exact)
$validator->rule('code', 'length', 10);

// Minimum length
$validator->rule('password', 'minLength', 8);

// Maximum length
$validator->rule('username', 'maxLength', 50);

// Regular expression
$validator->rule('custom', 'regex', '/^[A-Z]+$/');

// Value in array
$validator->rule('status', 'in', ['active', 'inactive', 'pending']);

// Password (length check only - hashing/storage is a separate step via Core\Security\Password)
$validator->rule('password', 'password', 8);

// Date / datetime (format is round-trip checked, not just parsed leniently)
$validator->rule('birthdate', 'date', 'Y-m-d');
$validator->rule('published_at', 'datetime', 'Y-m-d H:i:s');

// UUID (v4 shape)
$validator->rule('id', 'uuid');

// Uploaded file extension/size (operates on a $_FILES-shaped array)
$validator->rule('avatar', 'fileType', ['jpg', 'png', 'gif']);
$validator->rule('avatar', 'fileSize', 5 * 1024 * 1024); // 5 MB

// Confirmation field match (e.g. password + password_confirmation)
$validator->rule('password_confirmation', 'confirmed', $data['password'] ?? null);

// Array values must all be unique
$validator->rule('tags', 'distinct');

// Valid JSON string
$validator->rule('settings', 'json');

// Unique in database - no other row already has this value in the given column
$validator->rule('email', 'unique', 'users', 'email');
// ...or, when updating an existing row, exclude its own ID from the check:
$validator->rule('email', 'unique', 'users', 'email', $currentUserId);
```

## Advanced Usage

### Custom Error Messages

```php
<?php
// You can customize error messages by extending the Validator class
class CustomValidator extends Validator
{
    protected function generateErrorMessage(string $field, string $rule, array $args = []): string
    {
        return match($rule) {
            'required' => "The $field field is required",
            'email' => "Please enter a valid email address for $field",
            'minLength' => "The $field must be at least {$args[0]} characters long",
            default => "The $field is invalid"
        };
    }
}
?>
```

### Complex Validation Scenarios

```php
<?php
// Validate user registration data
$validator = new Validator();

$validator
    ->rule('username', 'required')
    ->rule('username', 'minLength', 3)
    ->rule('username', 'maxLength', 50)
    ->rule('username', 'regex', '/^[a-zA-Z0-9_]+$/')
    ->rule('email', 'required')
    ->rule('email', 'email')
    ->rule('password', 'required')
    ->rule('password', 'minLength', 8)
    ->rule('password_confirm', 'required')
    ->rule('age', 'int')
    ->rule('age', 'range', 13, 120)
    ->rule('country', 'in', ['US', 'CA', 'UK', 'AU']);

$data = $_POST;

if ($validator->validate($data)) {
    // Additional validation - passwords match
    if ($data['password'] !== $data['password_confirm']) {
        $validator->errors()['password_confirm'][] = 'Passwords do not match';
    }
}

if ($validator->validate($data) && $data['password'] === $data['password_confirm']) {
    // Registration logic here
    echo "Registration successful!";
} else {
    // Display errors
    foreach ($validator->errors() as $field => $errors) {
        echo "Field '$field': " . implode(', ', $errors) . "\n";
    }
}
?>
```

## Integration with HTML Components

The Validator utilities integrate seamlessly with the HTML Components system:

```php
<?php
use Core\Utilities\Validator\Validator;
use Core\Components\HTML\HTML;

// Create validator
$validator = new Validator();
$validator
    ->rule('name', 'required')
    ->rule('email', 'required')
    ->rule('email', 'email')
    ->rule('message', 'required')
    ->rule('message', 'minLength', 10);

// Create HTML factory
$html = new HTML();

// Create form with validator
$form = $html->form('/contact', 'post', $validator)
    ->addClass('contact-form')
    ->setAttribute('novalidate', 'true');

// Add form fields
$form->addComponent(
    $html->div()
        ->addClass('form-group')
        ->addComponent($html->label('Name')->setAttribute('for', 'name'))
        ->addComponent(
            $html->input('text', 'name', '')
                ->addClass('form-control')
                ->setAttribute('id', 'name')
                ->setAttribute('required', 'true')
        )
);

$form->addComponent(
    $html->div()
        ->addClass('form-group')
        ->addComponent($html->label('Email')->setAttribute('for', 'email'))
        ->addComponent(
            $html->input('email', 'email', '')
                ->addClass('form-control')
                ->setAttribute('id', 'email')
                ->setAttribute('required', 'true')
        )
);

// Render the form
echo $form->render();
?>
```

## Custom Validation Methods

You can extend the ValidatorTrait to add custom validation methods:

```php
<?php
use Core\Utilities\Validator\ValidatorTrait;

class CustomValidatorTrait
{
    use ValidatorTrait;

    // Custom validation: strong password
    public function strongPassword(string $value): bool
    {
        // Must contain uppercase, lowercase, number, and special character
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $value)
               && strlen($value) >= 8;
    }

    // Custom validation: valid phone number
    public function phone(string $value): bool
    {
        // Simple phone validation (customize as needed)
        return preg_match('/^\+?[\d\s\-\(\)]+$/', $value);
    }

    // Custom validation: valid postal code
    public function postalCode(string $value, string $country = 'US'): bool
    {
        return match($country) {
            'US' => preg_match('/^\d{5}(-\d{4})?$/', $value),
            'CA' => preg_match('/^[A-Za-z]\d[A-Za-z]\s?\d[A-Za-z]\d$/', $value),
            'UK' => preg_match('/^[A-Za-z]{1,2}\d[A-Za-z\d]?\s?\d[A-Za-z]{2}$/', $value),
            default => true
        };
    }
}
?>
```

## Error Handling

### Accessing Validation Errors

```php
<?php
$validator = new Validator();
$validator->rule('email', 'required')->rule('email', 'email');

$data = ['email' => 'invalid-email'];

if (!$validator->validate($data)) {
    $errors = $validator->errors();

    // $errors structure:
    // [
    //     'email' => [
    //         'The email is invalid for email rule'
    //     ]
    // ]

    foreach ($errors as $field => $fieldErrors) {
        echo "Field: $field\n";
        foreach ($fieldErrors as $error) {
            echo "  - $error\n";
        }
    }
}
?>
```

### Custom Error Message Generation

```php
<?php
class UserValidator extends Validator
{
    protected function generateErrorMessage(string $field, string $rule, array $args = []): string
    {
        return match($rule) {
            'required' => "The {$field} field is required.",
            'email' => "Please enter a valid email address.",
            'minLength' => "The {$field} must be at least {$args[0]} characters long.",
            'maxLength' => "The {$field} cannot be longer than {$args[0]} characters.",
            'range' => "The {$field} must be between {$args[0]} and {$args[1]}.",
            'in' => "The {$field} must be one of: " . implode(', ', $args[0]),
            default => "The {$field} field is invalid."
        };
    }
}
?>
```

## API Reference

### Validator Class

#### Constructor
```php
new Validator()
```

#### Methods

- `rule(string $field, string $method, mixed ...$args): self` - Add validation rule
- `validate(array $data): bool` - Validate data against all rules
- `errors(): array` - Get validation errors

### ValidatorInterface

#### Methods

- `rule(string $field, string $method, mixed ...$args): self` - Add validation rule
- `validate(array $data): bool` - Validate data against rules
- `errors(): array` - Get validation errors

### ValidatorTrait

#### Validation Methods

- `required(mixed $value): bool` - Check if value is not empty
- `email(string $value): bool|string` - Validate email address
- `url(string $value): bool` - Validate URL
- `ip(string $value): bool` - Validate IP address
- `int(mixed $value): bool|int` - Validate integer
- `float(mixed $value): bool` - Validate float
- `bool(mixed $value): bool` - Validate boolean
- `range(int|float $value, int|float $min, int|float $max): bool` - Validate range
- `in(mixed $value, array $list): bool` - Validate value in array
- `length(string $value, int $length): bool` - Validate exact length
- `minLength(string $value, int $min): bool` - Validate minimum length
- `maxLength(string $value, int $max): bool` - Validate maximum length
- `regex(string $value, string $pattern): bool|int` - Validate against regex
- `password(mixed $value, int $minLength = 8): bool` - Validate plaintext password length (hashing is separate, via `Core\Security\Password`)
- `date(mixed $value, string $format = 'Y-m-d'): bool` - Validate a date string against a format (round-trip checked)
- `datetime(mixed $value, string $format = 'Y-m-d H:i:s'): bool` - Validate a date-time string against a format
- `uuid(mixed $value): bool` - Validate a UUID (v4 shape)
- `fileType(mixed $value, array $allowed): bool` - Validate an uploaded file's extension against an allow-list
- `fileSize(mixed $value, int $maxBytes): bool` - Validate an uploaded file's size against a maximum
- `confirmed(mixed $value, mixed $confirmationValue): bool` - Validate that two values match
- `distinct(mixed $value): bool` - Validate that all values in an array are unique
- `json(mixed $value): bool` - Validate that a string is valid JSON
- `unique(mixed $value, string $table, string $column, mixed $ignoreId = null, string $idColumn = 'id'): bool` - Validate that no other row in `$table` already has this value in `$column` (queries via `Core\Model`)

## Best Practices

### 1. Validate Early and Often
```php
<?php
// ✅ Good - validate before processing
if ($validator->validate($data)) {
    processUserData($data);
} else {
    showValidationErrors($validator->errors());
}
?>
```

### 2. Use Appropriate Validation Rules
```php
<?php
// ✅ Good - specific validation for each field
$validator
    ->rule('email', 'required')
    ->rule('email', 'email')
    ->rule('password', 'required')
    ->rule('password', 'minLength', 8)
    ->rule('age', 'int')
    ->rule('age', 'range', 13, 120);
?>
```

### 3. Provide Clear Error Messages
```php
<?php
// ✅ Good - descriptive error messages
$validator->rule('username', 'minLength', 3);
$validator->rule('email', 'email');
$validator->rule('age', 'range', 18, 65);
?>
```

## Security Considerations

- **Never trust user input**: Always validate all user-provided data
- **Use appropriate validation rules**: Choose the most restrictive validation possible
- **Sanitize output**: Even validated data should be escaped when displayed
- **Validate file uploads**: Use additional validation for file uploads
- **Consider validation timing**: Validate as close to use as possible

## Integration Examples

### With Framework Controllers

```php
<?php
namespace App\Controllers\Web;

use Core\Controller;
use Core\Utilities\Validator\Validator;

class UserController extends Controller
{
    public function register()
    {
        $validator = new Validator();
        $validator
            ->rule('username', 'required')
            ->rule('username', 'minLength', 3)
            ->rule('email', 'required')
            ->rule('email', 'email')
            ->rule('password', 'required')
            ->rule('password', 'minLength', 8);

        if ($this->request->isPost()) {
            if ($validator->validate($this->request->getPost())) {
                // Process registration
                $this->redirect('/welcome');
            } else {
                // Show validation errors
                $this->data['errors'] = $validator->errors();
            }
        }

        $this->render('user/register', $this->data);
    }
}
?>
```

## Performance Tips

- **Cache validators**: Reuse validator instances when possible
- **Validate only necessary fields**: Don't over-validate
- **Use efficient validation methods**: Prefer built-in validators over regex when possible
- **Batch validation**: Validate multiple fields together for better performance

## Testing

```bash
# Run validator tests
phpunit tests/Core/Utilities/Validator/
```

The Validator utilities provide a comprehensive, secure, and easy-to-use validation system that integrates perfectly with your Phuse framework applications! 🎯
