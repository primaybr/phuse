# CSRF Protection in Phuse

Cross-Site Request Forgery (CSRF) is a security vulnerability that allows an attacker to trick users into performing unintended actions on a web application in which they are authenticated. Phuse provides a built-in CSRF protection system to mitigate this risk.

## Overview

The CSRF protection system in Phuse generates unique tokens for each user session, which must be included in forms and validated upon submission. This ensures that requests originate from your application and not from malicious external sites.

## Features

- **Secure Token Generation**: Uses cryptographically secure random bytes for token creation.
- **Session-Based Storage**: Tokens are stored in the user session with automatic expiration.
- **Easy Integration**: Seamlessly integrated into the Controller class for straightforward use.
- **Automatic Expiration**: Tokens expire after a configurable time (default: 1 hour) for enhanced security.
- **Timing Attack Protection**: Uses `hash_equals()` for secure token comparison.

## Usage

### Basic Implementation

The CSRF service is automatically available in all controllers through the `$this->csrf` property.

#### Generating a Token

```php
// In your controller action that renders a form
public function create()
{
    $data['csrf_token'] = $this->csrf->getTokenInput();
    $this->render('form/create', $data);
}
```

This will generate an HTML input field:
```html
<input type="hidden" name="csrf_token" value="your_secure_token_here">
```

#### Validating a Token

```php
// In your controller action that processes the form
public function store()
{
    $submittedToken = $this->input->post('csrf_token');

    if (!$this->csrf->validateToken($submittedToken)) {
        $this->log->error('Invalid CSRF token submitted');
        $this->error->show('Invalid request. Please try again.');
        return;
    }

    // Process the form data
    // Your form processing logic here
}
```

### Manual Token Management

If you need more control, you can use the CSRF methods directly:

```php
// Generate a new token
$token = $this->csrf->generateToken();

// Get the current token (generates if needed)
$currentToken = $this->csrf->getToken();

// Validate a token
$isValid = $this->csrf->validateToken($userToken);

// Remove the current token
$this->csrf->removeToken();
```

## Configuration

The CSRF system uses the following default settings, which can be customized by extending the `CSRF` class:

- **Token Length**: 32 bytes (256 bits) for high entropy.
- **Token Expiry**: 3600 seconds (1 hour).
- **Token Name**: `csrf_token` (used as the form field name).

To customize these, create a new class in your application:

```php
<?php

namespace App\Security;

use Core\Security\CSRF;

class CustomCSRF extends CSRF
{
    protected const TOKEN_LENGTH = 64; // Increase token length
    protected const TOKEN_EXPIRY = 1800; // 30 minutes
    protected const TOKEN_NAME = 'custom_csrf'; // Custom field name
}
```

## Best Practices

1. **Always Validate**: Never skip CSRF validation for state-changing operations (POST, PUT, DELETE).

2. **Use HTTPS**: Ensure your application runs over HTTPS to prevent token interception.

3. **Token Regeneration**: For sensitive operations, regenerate tokens after successful validation.

4. **AJAX Requests**: For AJAX forms, include the token in the request headers or data.

5. **Error Handling**: Log invalid token attempts for security monitoring.

## Security Considerations

- Tokens are tied to the user session, so users must be logged in or have an active session.
- Expired or invalid tokens are automatically cleaned up.
- The system uses secure random generation and constant-time comparison to prevent timing attacks.

## Troubleshooting

### Common Issues

1. **Token Not Validating**: Ensure the token is being sent correctly in the form and that sessions are properly configured.

2. **Session Issues**: CSRF tokens require sessions to be enabled. Check your session configuration.

3. **AJAX Problems**: For AJAX requests, manually include the token in the request data:

```javascript
const token = document.querySelector('input[name="csrf_token"]').value;
fetch('/submit', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ csrf_token: token, ...formData })
});
```

## Integration with Forms

When using Phuse's template system, you can easily include the CSRF token in your views:

```php
// In your controller
$data['csrf_field'] = $this->csrf->getTokenInput();
$this->render('user/form', $data);
```

```html
<!-- In your view template -->
<form method="POST" action="/user/store">
    <?php echo $csrf_field; ?>
    <!-- Your other form fields -->
    <button type="submit">Submit</button>
</form>
```

This CSRF protection system adds a robust layer of security to your Phuse application, helping prevent unauthorized actions and protecting your users from CSRF attacks.
