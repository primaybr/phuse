# Phuse: A User-Friendly and Intuitive PHP Framework

Phuse is a PHP framework that simplifies web development with conventions and helpers. It follows the convention over configuration principle, which means that it has sensible defaults for most settings and features, reducing the need for manual configuration. It also provides a variety of helpers, which are functions that perform common tasks such as formatting, validation, pagination, and more. Phuse aims to make web development more enjoyable and productive with PHP.

## Features

### Core Features

- **MVC Pattern**: Phuse implements the Model-View-Controller pattern, which separates the application logic from the presentation layer. This makes the code more organized, maintainable, and testable.
- **Advanced Active Record ORM**: Complete ORM system with relationships, eager loading, model events, scopes, soft deletes, attribute casting, accessors/mutators, and automatic validation
- **Database Connection Pooling**: High-performance connection management for concurrent requests with automatic health monitoring
- **Query Result Caching**: Intelligent caching system with automatic invalidation and configurable storage options
- **Routing**: Phuse handles routing efficiently, allowing developers to define clean and understandable URLs for their applications.
- **Modern CSS Framework**: Bootstrap 5.3.8-compatible CSS framework with dark theme optimization, responsive grid system, and comprehensive component library
- **HTML Components**: Secure, fluent HTML generation with automatic XSS protection and modern PHP patterns
- **Pagination Component**: Enterprise-grade pagination system with accessibility support, URL generation, and comprehensive configuration options
- **Text Utilities**: Comprehensive text processing system with string manipulation, number formatting, HTML/CSS/JS minification, and secure UUID generation

### Performance Optimizations

#### Database Query Caching

- **Automatic Caching**: Query results are automatically cached based on configuration
- **Intelligent Cache Invalidation**: Cache is automatically invalidated when data changes
- **Selective Caching**: Control which queries and tables are cached
- **Development Mode**: Automatic cache clearing in development environment

#### Template Caching

- **Compiled Templates**: Templates are compiled and cached for faster rendering
- **Auto-Refresh**: Cache is automatically cleared when templates are modified
- **Development Friendly**: Easy to disable in development mode
- **Configurable**: Control cache lifetime and storage location
- **And more**: Phuse also offers other features such as logging, sessions, security, email, and more.

## Installation
To install Phuse, follow these steps:
1. Clone the repository:
   ```bash
   git clone https://github.com/primaybr/phuse.git
   ```
2. Navigate to the project directory:
   ```bash
   cd phuse
   ```
3. Set up your web server to point to the `Public` directory.

## Basic Usage
To create a new controller, create a new PHP file in the `App/Controllers/Web` directory. For example, to create a `UserController`, create a file named `User.php` and define your controller class.

### Example:
```php
<?php

namespace App\Controllers\Web;

use Core\Controller;

class User extends Controller
{
    public function index()
    {
        // Your logic here
    }
}
```

## Directory Structure
The Phuse framework follows a standard directory structure:
- **App**: Contains controllers, models, and views.
- **Core**: Contains the core framework classes.
- **Config**: Configuration files for the application.
- **Public**: Publicly accessible files (entry point).
- **Logs**: Log files for debugging and error tracking.
- **Cache**: Caching files to improve performance.

## Controllers
Controllers handle incoming requests and contain the business logic for your application. They extend the base `Controller` class found in the `Core` directory.

## Models
Models interact with the database and handle data-related logic. Ensure to create model classes in the `App/Models` directory to manage your data effectively.

## Views
Views are responsible for rendering the user interface. They are located in the `App/Views` directory and can use dynamic data passed from controllers.

## Routing
Routing is managed by the `Router.php` class in the `Core` directory. Define your routes and link them to the appropriate controllers.

## Documentation

Phuse provides comprehensive documentation for all features:

- **[CSS Framework](docs/css-framework.md)**: Modern Bootstrap 5.3.8-compatible CSS framework with dark theme optimization, responsive grid system, and comprehensive component library
- **[ORM Examples Guide](docs/orm-examples.md)**: Complete ORM setup with database schema, model configuration, and advanced usage examples
- **[HTML Components](docs/html-components.md)**: Secure HTML generation with fluent API
- **[Image Utilities](docs/image-utilities.md)**: Advanced image manipulation with GD library
- **[Pagination Utilities](docs/pagination-utilities.md)**: Enterprise-grade pagination with accessibility support
- **[Upload Utilities](docs/upload-utilities.md)**: Secure file upload system with validation and XSS protection
- **[Cache System](docs/cache-system.md)**: Enhanced caching with multiple drivers
- **[Database Caching](docs/database-caching.md)**: Query result caching
- **[Template System](docs/template-system.md)**: Powerful template engine with caching, conditionals, loops, filters, and security features
- **[Template Caching](docs/template-caching.md)**: Template compilation caching
- **[CSRF Protection](docs/csrf-protection.md)**: Cross-site request forgery protection
- **[HTTP Components](docs/http-components.md)**: Comprehensive HTTP utilities including Client, Input, Request, Response, Session, URI, and CSRF protection
- **[Validator Utilities](docs/validator-utilities.md)**: Data validation with comprehensive rule system
- **[Text Utilities](docs/text-utilities.md)**: Comprehensive text processing with string manipulation, number formatting, HTML/CSS/JS minification, and secure UUID generation

## Logging
Phuse provides a logging mechanism to track application errors and events. Log files are stored in the `Logs` directory.

## Minimum Requirement

To install Phuse, you need to have PHP 8.2 or higher.

## License

Phuse is licensed under the MIT license, which means that you can use it for any purpose, even for commercial projects, as long as you give credit to the original author.

## Support

If you have any questions, issues, or feedback regarding Phuse, you can contact the developer through the following channels:

- Email: primaybr@gmail.com

## Contributing

Phuse is an open-source project, and you are welcome to contribute to its development. You can fork the repository, make your changes, and submit a pull request. Please follow the coding standards and guidelines before submitting your code.

## Latest Changes

### v1.1.2 (2025-11-12)
- **Text Utilities System Overhaul**: Complete reorganization and enhancement of text processing utilities
  - **Relocated Core Classes**: Moved all text utilities from `Core/Text/` to `Core/Utilities/Text/` for better organization
  - **Enhanced String Utilities (Str)**: Comprehensive improvements to string manipulation and generation
    - **Advanced UUID Generation**: Multi-version UUID support (v1, v3, v4, v5) with maximum uniqueness guarantees
    - **Cryptographically Secure Random Strings**: Enhanced entropy using `random_bytes()` with additional mixing
    - **Improved Pluralization**: Support for irregular plurals and comprehensive linguistic rules
    - **Enhanced Time Formatting**: Better time elapsed strings with proper pluralization
    - **Base64 Validation Fix**: Corrected inverted logic in `isBase64()` method
    - **Meta Keywords Generation**: Improved keyword extraction with frequency-based sorting
    - **RFC 4122 Namespace Support**: Predefined namespaces for DNS, URL, OID, and X.500
    - **UUID Validation**: Built-in format validation for generated UUIDs
  - **Enhanced Number Utilities (Number)**: Improved number formatting and currency handling
    - **Negative Number Support**: Proper handling of negative values in `shortNumber()`
    - **International Phone Formatting**: Support for 10+ countries with automatic country code detection
    - **Improved Type Safety**: Better parameter validation and union types
    - **Enhanced Currency Formatting**: Flexible decimal and thousands separators
  - **HTML Processing Utilities**: Secure HTML minification with XSS protection
  - **CSS Minification Utilities**: Comprehensive CSS optimization and compression
  - **JavaScript Minification Utilities**: Safe JavaScript compression with string/regex handling
- **Framework Architecture Improvement**: Better utility organization and separation of concerns
  - **Namespace Restructuring**: Moved utility classes to dedicated `Core/Utilities/` namespace
  - **Updated Dependencies**: All framework components updated to use new utility namespaces
  - **Backward Compatibility**: Maintained API compatibility where possible
- **Security Enhancements**: Improved security across all text processing utilities
  - **XSS Protection**: Enhanced HTML escaping and validation
  - **Input Sanitization**: Comprehensive input validation and sanitization
  - **Secure Random Generation**: Cryptographically secure random number generation
- **Performance Optimizations**: Enhanced performance for text processing operations
  - **Efficient Algorithms**: Optimized string processing and UUID generation
  - **Memory Management**: Improved memory usage for large text operations
  - **Caching Compatibility**: Better integration with framework caching systems
- **Comprehensive Documentation**: Complete documentation for text utilities system
  - **Text Utilities Guide**: Detailed usage examples and API documentation in `docs/text-utilities.md`
  - **Migration Guide**: Instructions for upgrading from old `Core\Text` namespace
  - **Security Best Practices**: Guidelines for secure text processing
  - **Performance Tips**: Optimization recommendations for production use

### v1.1.1 (2025-11-12)
- **Complete ORM System Overhaul**: Modern Active Record implementation with enterprise features
  - Comprehensive Model class with relationships (hasOne, hasMany, belongsTo, belongsToMany)
  - Eager loading with `with()` method for relationship optimization
  - Model events/hooks system (saving, created, updated, deleted)
  - Scopes for query filtering and reusable query logic
  - Soft deletes with restore functionality and trashed record access
  - Automatic timestamps (created_at, updated_at) management
  - Attribute casting system (boolean, integer, string, array, json)
  - Accessors and mutators for data transformation
  - Mass assignment protection (fillable/guarded attributes)
  - Hidden attributes for API security
  - Global scopes for application-wide query modifications
- **Database Connection Pooling**: Performance optimization with connection reuse
  - ConnectionPool class for managing multiple database connections
  - Automatic connection health monitoring and cleanup
  - Configurable pool size and timeout settings
  - Improved concurrent request handling
- **Enhanced Database Builders**: Advanced query building capabilities
  - Improved BuildersTrait with additional aggregation methods
  - Enhanced MySQL and PostgreSQL driver support
  - Better query compilation and parameter binding
  - Support for complex joins and subqueries
- **Model Validation Integration**: Automatic validation before save operations
  - Integration with existing Validator system
  - Custom validation rules per model
  - Automatic validation error handling
  - Pre-save validation hooks
- **Query Result Caching**: Intelligent caching system for database queries
  - QueryCache integration with Model class
  - Automatic cache invalidation on data changes
  - Configurable cache lifetime and storage
  - Development-friendly cache management
- **Comprehensive ORM Examples**: Complete demonstration system
  - Full CRUD operations example with relationships
  - Model validation examples
  - Advanced query building demonstrations
  - Real-world usage scenarios
- **Database Documentation**: Complete setup and usage guides
  - ORM examples guide with database schema
  - Model configuration and relationship documentation
  - Performance optimization tips
  - Troubleshooting and best practices

### v1.1.0 (2025-11-10)
- **Refactor Exception System**: Complete overhaul with modern PHP practices and framework integration
  - New BaseException class with type categorization, severity levels, and context data
  - Enhanced Handler class with Core\Log integration and improved error categorization
  - Updated Error class with better template handling and logging integration
  - Enhanced CommonTrait with comprehensive exception throwing methods and assertion helpers
  - Updated Base.php with proper exception handling and SystemException usage
  - Updated Container.php to use new exception types for consistency
  - Removed deprecated E_STRICT references for PHP 8.0+ compatibility
  - Comprehensive error context and user-friendly messages throughout
- **Update HTML Components System**: Complete rebuild with enterprise-grade security
  - 30 secure HTML components with automatic XSS protection
  - Factory pattern architecture with fluent API
  - Enhanced ComponentTrait with bulk operations and CSS utilities
  - Comprehensive documentation and usage examples
  - Zero XSS vulnerabilities
- **Refactor Image Component**: Complete overhaul with modern PHP practices
  - Enhanced error handling and validation with comprehensive security checks
  - Integration with framework's Core\Log system for consistent logging
  - Configuration management with ImageConfig class
  - Support for JPEG, PNG, GIF, WebP formats with quality control
  - Advanced operations: resize, crop, rotate, compress, watermark
  - Comprehensive documentation moved to docs/image-utilities.md
  - Unit tests and usage examples
- **Refactor Upload Utility**: Complete security and functionality overhaul
  - Enhanced security with intelligent XSS protection for text files only
  - Integration with framework's Core\Log system for consistent logging
  - UploadConfig class for flexible configuration management with preset profiles
  - Improved validation with detailed error messages and MIME type checking
  - Secure filename handling with sanitization and unique naming
  - Comprehensive documentation moved to docs/upload-utilities.md
  - Unit tests and professional usage examples
  - Enhanced error handling and framework integration
- **Refactor Template System**: Complete overhaul with modern PHP practices and enhanced functionality
  - Enhanced error handling with proper exception throwing and catching capabilities
  - Improved security with safe variable extraction and input validation
  - Fixed condition evaluation logic for better template parsing reliability
  - Added comprehensive PHPDoc documentation throughout the template system
  - Updated ParserInterface with better type declarations and documentation
  - Enhanced template parsing with improved regex patterns for better performance
  - Added proper backward compatibility for Error class constructor
  - Integrated template system with framework's exception handling architecture
- **Add Template System Examples**: Comprehensive demonstration system with web interface
  - Created ExamplesController with 8 different example types showcasing all template features
  - Added interactive web interface at `/examples` for easy access to demonstrations
  - Created 5+ example templates in `App/Views/examples/` covering all use cases
  - Added routing configuration for all example endpoints
  - Updated documentation with comprehensive template system guide in `docs/template-system.md`
  - Enhanced README.md with examples section and access instructions
  - Provided real-world scenarios including e-commerce, dashboards, and blog templates

### v1.0.3 (2025-10-21)
- Added comprehensive Dependency Injection (DI) Container system with automatic dependency resolution
- Implemented Middleware System with stack-based processing and request/response modification
- Added unified Cache System with multiple drivers (File, Memory) and advanced features
- Added support for middleware groups in Router class for better organization
- Enhanced type safety with PHP 8.2+ type declarations throughout core classes
- Integrated DI container with middleware system for better dependency management
- Added comprehensive documentation for both DI container and middleware features
- Improved code organization and separation of concerns across the framework

## Credits

Phuse is developed and maintained by Prima Yoga, a passionate web developer and PHP enthusiast.

## Example Usage

### Overview
In this example, we will create a simple web application that allows users to view a list of items. We will set up a controller to handle requests, a model to interact with the data, and a view to display the information.

### Step 1: Create the Model
First, we will create a model class that represents the items. Create a new file named `Item.php` in the `App/Models` directory.

```php
<?php

namespace App\Models;

class Item
{
    private $items = [
        ['id' => 1, 'name' => 'Item 1'],
        ['id' => 2, 'name' => 'Item 2'],
        ['id' => 3, 'name' => 'Item 3'],
    ];

    public function getAllItems()
    {
        return $this->items;
    }
}
```

### Step 2: Create the Controller
Next, we will create a controller that handles the request and uses the model to fetch data. Create a new file named `ItemController.php` in the `App/Controllers/Web` directory.

```php
<?php

namespace App\Controllers\Web;

use Core\Controller;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
    {
        $itemModel = new Item();
        $data['items'] = $itemModel->getAllItems();
        $this->render('items/index', $data);
    }
}
```

### Step 2: Add HTML Components (Alternative to Views)

Instead of using template files, you can generate HTML programmatically with the secure HTML Components system:

```php
<?php

namespace App\Controllers\Web;

use Core\Controller;
use Core\Components\HTML\HTML;

class ItemController extends Controller
{
    public function index()
    {
        $itemModel = new Item();
        $data['items'] = $itemModel->getAllItems();

        // Create HTML components instead of using templates
        $html = new HTML();

        $page = $html->div()
            ->addClass('container')
            ->addComponent($html->heading(1, 'Item List'))
            ->addComponent(
                $html->lists('ul')
                    ->addClass('item-list')
                    ->setAttributes(['id' => 'items', 'data-count' => count($data['items'])])
            );

        // Add items to the list (automatically escaped for security)
        foreach ($data['items'] as $item) {
            $page->components[1]->addItem($item['name']); // Safe from XSS
        }

        // Send HTML response
        $this->response->setContent($page->render());
    }
}
```

This approach provides automatic XSS protection and a fluent API for building HTML structures.

### Step 4: Define the Route
Open the `routes.php` file in the `Config` directory and add the following route:

```php
$router->add('GET', '/items', 'Web\ItemController', 'index');
```

### Step 5: Access the Application
Now you can access your application by navigating to `http://your-domain/items` in your web browser. You should see a list of items displayed on the page.

### Template System Examples
The Phuse framework includes comprehensive template system examples that demonstrate all features:

**Interactive Examples:**
- **Basic Templates**: Variable replacement and simple rendering
- **Conditional Logic**: If/else statements and dynamic content
- **Loops**: Foreach and for loop constructs
- **Nested Data**: Complex data structure access
- **Caching**: Performance optimization techniques
- **Error Handling**: Robust error management

**Access Examples:**
- Visit `/examples` for the complete examples index
- Individual examples: `/examples/basic`, `/examples/conditional`, etc.
- **Documentation**: See `docs/template-system.md` for comprehensive guide

Thank you for using Phuse, and happy coding! 😊
