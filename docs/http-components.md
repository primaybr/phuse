# HTTP Components

The Phuse framework provides a comprehensive set of HTTP-related classes for handling various aspects of web requests and responses. These components offer a clean, object-oriented interface for common HTTP operations while maintaining compatibility with PHP's native functionality.

## Overview

The HTTP component suite includes:

- **Client**: IP address detection and client information
- **Input**: GET, POST, PUT, DELETE parameter handling
- **Request**: Advanced HTTP client with authentication and token refresh
- **Response**: HTTP status code management and responses
- **Session**: Session management with validation
- **URI**: URL manipulation, cleaning, and generation utilities
- **CSRF**: Cross-Site Request Forgery protection

## Core\Http\Client

Handles client-side HTTP operations, primarily focused on IP address detection.

### Usage

```php
use Core\Http\Client;

$client = new Client();
$ipAddress = $client->getIpAddress();
```

### Methods

#### `getIpAddress(): string`

Retrieves the real IP address of the client by checking multiple proxy headers in order of reliability.

- Checks headers: `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED`, `REMOTE_ADDR`
- Filters out private and reserved IP ranges using `FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE`
- Returns `'UNKNOWN'` if no valid IP address is found

```php
$client = new Client();
$ip = $client->getIpAddress(); // Returns: "192.168.1.100" or "UNKNOWN"
```

## Core\Http\Input

Provides a clean interface for accessing HTTP input data from various request methods.

### Usage

```php
use Core\Http\Input;

$input = new Input();
```

### Methods

#### `get(string $name = ''): string|array`

Retrieves GET parameters from the HTTP request.

```php
// Get all GET parameters
$params = $input->get();

// Get specific GET parameter
$name = $input->get('name');
```

#### `post(string $name = ''): string|array`

Retrieves POST parameters from the HTTP request.

```php
// Get all POST parameters
$data = $input->post();

// Get specific POST parameter
$email = $input->post('email');
```

#### `put(): mixed`

Retrieves raw PUT data from the request body.

```php
$rawData = $input->put();
```

#### `delete(string $name = ''): string|array`

Retrieves DELETE parameters by parsing the request body as a query string.

```php
// Get all DELETE parameters
$params = $input->delete();

// Get specific DELETE parameter
$id = $input->delete('id');
```

## Core\Http\Request

A comprehensive HTTP client for making various types of HTTP requests with authentication support.

### Usage

```php
use Core\Http\Request;

$request = new Request();
```

### Configuration Methods

#### `setHeader(string $header): self`

Sets custom headers for the HTTP request.

```php
$request->setHeader("Authorization: Bearer token123")
        ->setHeader("Content-Type: application/json");
```

#### `setContent(array|string $content): self`

Sets request content with automatic format detection.

```php
// Array data (converted to query string)
$request->setContent(['name' => 'John', 'age' => 30]);

// JSON data
$request->setContent(['json' => json_encode(['key' => 'value'])]);

// Raw string data
$request->setContent('raw data here');
```

#### `setContentType(string $type): self`

Sets the content type for the request.

```php
$request->setContentType('application/json');
```

#### `setSSL(bool $on = true): self`

Configures SSL/TLS settings.

```php
// Disable SSL verification (not recommended for production)
$request->setSSL(false);
```

### Request Execution

#### `request(string $method, string $url, array|string $data = [], bool $refresh = true): self`

Executes an HTTP request with the specified parameters.

```php
$response = $request->setHeader("User-Agent: MyApp/1.0")
                   ->request('GET', 'https://api.example.com/users');

// Check response
$statusCode = $request->getHttpResponseCode();
$content = $request->getContent();
```

### Authentication Features

The Request class includes built-in token refresh functionality for authenticated API calls:

- Automatic token refresh on 401 responses when `useRefresh` is enabled
- JWT token handling and validation
- Session-based token storage

## Core\Http\Response

Represents HTTP responses with status codes and messages.

### Usage

```php
use Core\Http\Response;

$response = new Response(200); // OK
$response = new Response(404); // Not Found
```

### Properties

- `$statusCode`: The HTTP status code (readonly)
- `$statusName`: Human-readable status message (readonly)
- `$wrapper`: Protocol wrapper (readonly)

### Available Status Codes

The class includes a comprehensive collection of HTTP status codes:

- **1×× Informational**: 100-199
- **2×× Success**: 200-299
- **3×× Redirection**: 300-399
- **4×× Client Error**: 400-499
- **5×× Server Error**: 500-599

## Core\Http\Session

Provides session management with validation and cleanup.

### Usage

```php
use Core\Http\Session;

$session = new Session();
```

### Methods

#### `set(string $key, mixed $value): bool`

Sets a session value with validation.

```php
$session->set('user_id', 123);
$session->set('preferences', ['theme' => 'dark']);
```

#### `get(string $key = ''): mixed`

Retrieves session data.

```php
$userId = $session->get('user_id');
$allData = $session->get(); // Returns all session data
```

#### `check(string $key): bool`

Checks if a session key exists and is not empty.

```php
if ($session->check('user_id')) {
    // User is logged in
}
```

#### `flash(string $key): mixed`

Retrieves and removes a session value (flash data).

```php
$message = $session->flash('success_message');
```

#### `destroy(): void`

Completely destroys the session.

```php
$session->destroy();
```

## Core\Http\URI

Comprehensive URI utilities for URL manipulation and generation.

### Usage

```php
use Core\Http\URI;

$uri = new URI();
```

### Methods

#### `makeURL(string $string): string`

Creates URL-friendly strings by cleaning and normalizing text.

```php
$cleanUrl = $uri->makeURL("Hello World! This is a test...");
// Returns: "hello-world-this-is-a-test"
```

#### `makeFullURL(string $string): string`

Prepends the site base URL to a string.

```php
$fullUrl = $uri->makeFullURL("about-us");
// Returns: "https://example.com/about-us"
```

#### `makeImagePath(string $image, string $size): string`

Creates responsive image paths with size directories.

```php
$imagePath = $uri->makeImagePath("uploads/photo.jpg", "thumb");
// Returns: "https://example.com/uploads/thumb/photo.jpg"
```

#### `makeImageYoutube(string $url, int $type = 0): string|false`

Generates YouTube thumbnail URLs.

```php
$thumbnail = $uri->makeImageYoutube("https://youtu.be/dQw4w9WgXcQ", 0);
// Returns: "https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg"
```

#### `getCurrentURL(bool $full = false): string`

Gets the current request URL.

```php
$currentUrl = $uri->getCurrentURL(true);  // Full URL with protocol
$pathOnly = $uri->getCurrentURL(false);  // Path only
```

#### `getProtocol(): string`

Detects the current request protocol.

```php
$protocol = $uri->getProtocol(); // Returns: "https://" or "http://"
```

#### `redirect(string $url): void`

Performs HTTP redirects.

```php
$uri->redirect("https://example.com/new-location");
```

## Core\Security\CSRF

Provides CSRF protection for form submissions.

### Usage

```php
use Core\Security\CSRF;

$csrf = new CSRF();
```

### Methods

#### `generateToken(): string`

Generates a new CSRF token.

```php
$token = $csrf->generateToken();
```

#### `validateToken(string $token): bool`

Validates a submitted CSRF token.

```php
$isValid = $csrf->validateToken($_POST['csrf_token']);
```

#### `getToken(): string`

Gets the current token, generating one if needed.

```php
$token = $csrf->getToken();
```

#### `getTokenInput(): string`

Generates HTML input field for forms.

```php
echo $csrf->getTokenInput();
// Outputs: <input type="hidden" name="csrf_token" value="...">
```

## Integration Examples

### Basic Form with CSRF Protection

```php
use Core\Http\Input;
use Core\Security\CSRF;

$input = new Input();
$csrf = new CSRF();

// In your form template
<form method="POST">
    <?php echo $csrf->getTokenInput(); ?>
    <input type="text" name="username" required>
    <button type="submit">Login</button>
</form>

// In your form handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validateToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    $username = $input->post('username');
    // Process form data...
}
```

### API Request with Authentication

```php
use Core\Http\Request;
use Core\Http\Session;

$request = new Request();
$session = new Session();

// Set up authenticated request
$token = $session->get('auth_token');
$response = $request->setHeader("Authorization: Bearer {$token}")
                   ->setContentType('application/json')
                   ->request('POST', 'https://api.example.com/data', [
                       'name' => 'John Doe',
                       'email' => 'john@example.com'
                   ]);

// Handle response
if ($request->getHttpResponseCode() === 200) {
    $data = json_decode($request->getContent());
    echo "Success: " . $data->message;
} else {
    echo "Error: " . $request->getHttpResponseCode();
}
```

### URL Generation and Cleaning

```php
use Core\Http\URI;

$uri = new URI();

// Clean user input for URL use
$title = "User's Blog Post: Getting Started!";
$slug = $uri->makeURL($title); // "users-blog-post-getting-started"

// Generate full URLs
$fullUrl = $uri->makeFullURL($slug); // "https://example.com/users-blog-post-getting-started"

// Handle YouTube URLs
$youtubeUrl = "https://www.youtube.com/watch?v=VIDEO_ID";
$thumbnail = $uri->makeImageYoutube($youtubeUrl, 0);
```

## Best Practices

1. **Always validate CSRF tokens** on form submissions
2. **Use HTTPS** in production environments
3. **Validate and sanitize** all input data
4. **Handle exceptions** properly when making HTTP requests
5. **Use sessions securely** with proper cleanup
6. **Validate URLs** before processing or redirecting
7. **Log errors** and suspicious activities

## Error Handling

All HTTP components include proper error handling:

```php
try {
    $response = $request->request('GET', 'https://api.example.com/data');
    $data = $request->getContent();
} catch (\Exception $e) {
    // Log error and handle gracefully
    error_log("HTTP Request failed: " . $e->getMessage());
    // Return error response or redirect
}
```

## Security Considerations

- CSRF tokens expire after 1 hour by default
- Session data is properly validated and cleaned
- IP detection filters out private ranges for security
- SSL verification is enabled by default
- Input data should always be validated and sanitized
- Use HTTPS for all production traffic

## Testing

The HTTP components can be tested using the provided examples:

```bash
php examples/http_examples.php
```

This will demonstrate all HTTP component functionality with sample data and outputs.
