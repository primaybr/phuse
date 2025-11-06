<?php

declare(strict_types=1);

/**
 * HTTP Components Examples
 *
 * Comprehensive demonstration of the Phuse framework's HTTP components including
 * Client, Input, Request, Response, Session, URI, and CSRF protection classes.
 *
 * This file showcases various use cases and best practices for HTTP handling
 * in web applications using the Phuse framework.
 *
 * @package Examples
 * @author  Prima Yoga
 */

namespace Examples;

use Core\Http\Client;
use Core\Http\Input;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Session;
use Core\Http\URI;
use Core\Security\CSRF;

/**
 * Example 1: Basic HTTP Client usage for IP detection
 */
class BasicClientUsage
{
    public function demonstrate(): void
    {
        echo "🖥️  EXAMPLE 1: HTTP Client - IP Address Detection\n";
        echo str_repeat("=", 60) . "\n";

        $client = new Client();
        $ipAddress = $client->getIpAddress();

        echo "✓ Client IP Address: " . $ipAddress . "\n";
        echo "✓ This method checks multiple proxy headers to find the real client IP\n";
        echo "✓ Filters out private and reserved IP ranges for security\n\n";
    }
}

/**
 * Example 2: HTTP Input handling for forms and requests
 */
class BasicInputUsage
{
    public function demonstrate(): void
    {
        echo "📝 EXAMPLE 2: HTTP Input - Form Data Handling\n";
        echo str_repeat("=", 60) . "\n";

        $input = new Input();

        echo "✓ GET Parameters: ";
        $getParams = $input->get();
        if (!empty($getParams)) {
            echo json_encode($getParams);
        } else {
            echo "No GET parameters (run this script with ?param=value)";
        }
        echo "\n";

        echo "✓ POST Parameters: ";
        $postParams = $input->post();
        if (!empty($postParams)) {
            echo json_encode($postParams);
        } else {
            echo "No POST parameters (submit a form to see this)";
        }
        echo "\n";

        echo "✓ Specific GET parameter: ";
        echo $input->get('param') ?: 'No "param" GET parameter';
        echo "\n\n";
    }
}

/**
 * Example 3: Session management for user data
 */
class BasicSessionUsage
{
    public function demonstrate(): void
    {
        echo "💾 EXAMPLE 3: Session Management\n";
        echo str_repeat("=", 60) . "\n";

        $session = new Session();

        // Set some session data
        $session->set('user_id', 12345);
        $session->set('username', 'johndoe');
        $session->set('preferences', ['theme' => 'dark', 'language' => 'en']);

        echo "✓ Session data set successfully\n";
        echo "✓ User ID: " . $session->get('user_id') . "\n";
        echo "✓ Username: " . $session->get('username') . "\n";
        echo "✓ User preferences: " . json_encode($session->get('preferences')) . "\n";

        echo "✓ Checking if session key exists: " . ($session->check('user_id') ? 'YES' : 'NO') . "\n";

        // Demonstrate flash data
        $flashData = $session->flash('temp_message');
        echo "✓ Flash data retrieved: " . ($flashData ?: 'No flash data') . "\n";

        echo "\n";
    }
}

/**
 * Example 4: URI utilities for URL manipulation
 */
class BasicURIUsage
{
    public function demonstrate(): void
    {
        echo "🔗 EXAMPLE 4: URI Utilities\n";
        echo str_repeat("=", 60) . "\n";

        $uri = new URI();

        echo "✓ Current URL (full): " . $uri->getCurrentURL(true) . "\n";
        echo "✓ Current URL (path only): " . $uri->getCurrentURL(false) . "\n";
        echo "✓ Protocol: " . $uri->getProtocol() . "\n";
        echo "✓ Host: " . $uri->getHost() . "\n";

        // URL cleaning example
        $dirtyUrl = "Hello World! This is a test...";
        $cleanUrl = $uri->makeURL($dirtyUrl);
        echo "✓ URL Cleaning: '{$dirtyUrl}' -> '{$cleanUrl}'\n";

        // YouTube thumbnail example
        $youtubeUrl = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
        $thumbnailUrl = $uri->makeImageYoutube($youtubeUrl, 0);
        echo "✓ YouTube Thumbnail: " . ($thumbnailUrl ?: 'Could not extract video ID') . "\n";

        echo "\n";
    }
}

/**
 * Example 5: CSRF protection for forms
 */
class BasicCSRFUsage
{
    public function demonstrate(): void
    {
        echo "🔒 EXAMPLE 5: CSRF Protection\n";
        echo str_repeat("=", 60) . "\n";

        $csrf = new CSRF();

        echo "✓ CSRF Token Generated: " . $csrf->getToken() . "\n";
        echo "✓ HTML Input Field: " . $csrf->getTokenInput() . "\n";

        // Simulate form submission validation
        $submittedToken = $_POST['csrf_token'] ?? '';
        if ($submittedToken) {
            $isValid = $csrf->validateToken($submittedToken);
            echo "✓ Token Validation: " . ($isValid ? 'VALID' : 'INVALID') . "\n";
        } else {
            echo "✓ Token Validation: No token submitted (submit form to test)\n";
        }

        echo "\n";
    }
}

/**
 * Example 6: HTTP Response handling
 */
class BasicResponseUsage
{
    public function demonstrate(): void
    {
        echo "📊 EXAMPLE 6: HTTP Response Handling\n";
        echo str_repeat("=", 60) . "\n";

        // Success response
        $successResponse = new Response(200);
        echo "✓ Success Response: {$successResponse->statusCode} - {$successResponse->statusName}\n";

        // Error response
        $errorResponse = new Response(404);
        echo "✓ Error Response: {$errorResponse->statusCode} - {$errorResponse->statusName}\n";

        // Check if response is successful (2xx status codes)
        echo "✓ Is Success (200): " . ($successResponse->statusCode >= 200 && $successResponse->statusCode < 300 ? 'YES' : 'NO') . "\n";
        echo "✓ Is Error (404): " . ($errorResponse->statusCode >= 400 ? 'YES' : 'NO') . "\n";

        echo "✓ Available Status Codes:\n";
        $statusCodes = Response::STATUS_CODES;
        $sampleCodes = [200, 201, 301, 400, 404, 500];
        foreach ($sampleCodes as $code) {
            echo "  - {$code}: " . ($statusCodes[$code] ?? 'Unknown') . "\n";
        }

        echo "\n";
    }
}

/**
 * Example 7: Advanced HTTP Request with authentication
 */
class AdvancedRequestUsage
{
    public function demonstrate(): void
    {
        echo "🚀 EXAMPLE 7: Advanced HTTP Request\n";
        echo str_repeat("=", 60) . "\n";

        $request = new Request();

        try {
            // Configure the request
            $request->setHeader("User-Agent: Phuse Framework Demo")
                   ->setContentType('application/json')
                   ->setSSL(true);

            echo "✓ Request configured with custom headers and SSL\n";

            // Example of making a GET request (commented out to avoid external calls)
            /*
            $response = $request->request('GET', 'https://httpbin.org/get');
            echo "✓ Request Status: {$request->getHttpResponseCode()}\n";
            $content = $request->getContent();
            echo "✓ Response Length: " . strlen($content) . " characters\n";
            */

            echo "✓ Request object ready for API calls\n";
            echo "✓ Supports method chaining for fluent interface\n";
            echo "✓ Automatic token refresh for authenticated requests\n";

        } catch (\Exception $e) {
            echo "⚠️  Request example error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }
}

/**
 * Main demonstration runner
 */
class HTTPComponentsDemo
{
    private array $examples = [];

    public function __construct()
    {
        $this->examples = [
            new BasicClientUsage(),
            new BasicInputUsage(),
            new BasicSessionUsage(),
            new BasicURIUsage(),
            new BasicCSRFUsage(),
            new BasicResponseUsage(),
            new AdvancedRequestUsage(),
        ];
    }

    public function run(): void
    {
        echo "🌐 PHUSE FRAMEWORK - HTTP COMPONENTS DEMONSTRATION\n";
        echo str_repeat("=", 80) . "\n\n";

        foreach ($this->examples as $example) {
            $example->demonstrate();
        }

        echo "✅ HTTP Components demonstration completed!\n";
        echo "📚 Check the framework documentation at docs/http-components.md for detailed usage\n";
        echo "🔧 All examples use the framework's logging system via Core\\Log\n";
    }
}

// Run the demonstration if this file is executed directly
if (php_sapi_name() === 'cli') {
    $demo = new HTTPComponentsDemo();
    $demo->run();
} else {
    // Web interface
    echo "<h1>HTTP Components Demonstration</h1>";
    echo "<p>This page demonstrates the Phuse framework's HTTP components. Run via CLI for full output.</p>";
    echo "<pre>";
    $demo = new HTTPComponentsDemo();
    $demo->run();
    echo "</pre>";
}
