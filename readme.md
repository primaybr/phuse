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

### v1.1.3 (2025-11-12)
- **Complete CSS Framework Modernization**: Bootstrap 5+ compatible framework with dark theme optimization
  - **Modern Grid System**: Complete Bootstrap 5+ grid implementation with gap-based spacing
    - **12-Column Responsive Grid**: Full breakpoint support (xs, sm, md, lg, xl, xxl)
    - **Auto-sizing Columns**: `col-auto` classes for all responsive breakpoints
    - **Gap-based Spacing**: CSS variables `--ps-gutter-x` and `--ps-gutter-y` for modern spacing
    - **Row Columns Utilities**: `row-cols-*` classes for automatic column distribution
    - **Fixed 3-Card Layout**: Proper gap spacing ensures correct 3-card per row display
  - **Phuse-Specific Variables**: Renamed all CSS variables from `--bs-*` to `--ps-*` for framework branding
  - **Enhanced Component Library**: Modern components optimized for dark themes
    - **Dark Theme Alerts**: Highly visible alerts with light text, colored backgrounds, and left accent borders
    - **Compact Badges**: Content-width badges using `width: fit-content` instead of full width
    - **Modern Cards**: Enhanced card components with hover effects and proper dark theme styling
    - **Form Controls**: Dark theme optimized form inputs with proper focus states
  - **Extended Utility Classes**: Comprehensive spacing, flexbox, and display utilities
    - **Modern Spacing Scale**: Extended spacing utilities (m-6 through m-8, p-6 through p-8)
    - **Complete Responsive Display**: All breakpoints with flex, block, inline, and none utilities
    - **Enhanced Flexbox**: Complete flexbox control with alignment and justification
    - **Modern Typography**: Extended text utilities and color classes
  - **Dark Theme Optimization**: All components designed for excellent dark background visibility
    - **Color Hierarchy**: Primary, secondary, and tertiary background levels
    - **Text Contrast**: Optimized text colors for readability on dark backgrounds
    - **Component Visibility**: Enhanced contrast for alerts, buttons, and interactive elements
    - **Focus States**: Clear focus indicators for accessibility
  - **Performance Optimized**: Efficient CSS with modern selectors and minimal footprint
  - **Bootstrap 5.3.8 Compatibility**: Full compatibility while maintaining Phuse-specific enhancements
- **Interactive CSS Framework Examples**: Comprehensive demonstration system
  - **CSS Framework Examples Page**: Complete showcase at `/examples/css-framework`
  - **Grid System Demonstrations**: Basic grid, responsive 3-card layout, auto-sizing columns
  - **Component Showcases**: Enhanced alerts, compact badges, modern cards
  - **Utility Examples**: Spacing scales, flexbox utilities, responsive display
  - **Dark Theme Features**: Color hierarchy and contrast demonstrations
  - **CSS Variables Showcase**: Phuse-specific variable system explanation
- **Comprehensive CSS Documentation**: Complete framework guide in `docs/css-framework.md`
  - **Quick Start Guide**: Basic HTML structure and grid usage
  - **Component Documentation**: Cards, alerts, badges, buttons with code examples
  - **Utility Reference**: Spacing, flexbox, display, colors, borders, shadows
  - **Dark Theme Guide**: Optimization strategies and color system
  - **Migration Guide**: Converting from Bootstrap with variable name changes
  - **Browser Support**: Compatibility information and performance notes
- **Framework Integration**: Seamless integration with existing Phuse features
  - **Examples Controller Update**: Added CSS framework to examples system
  - **Routing Integration**: Proper URL routing for CSS framework examples
  - **Template System Compatibility**: Works with existing template rendering
  - **Asset Management**: Proper CSS file serving and caching

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
