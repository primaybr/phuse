# Phuse: A User-Friendly and Intuitive PHP Framework

Phuse is a PHP framework that simplifies web development with conventions and helpers. It follows the convention over configuration principle, which means that it has sensible defaults for most settings and features, reducing the need for manual configuration. It also provides a variety of helpers, which are functions that perform common tasks such as formatting, validation, pagination, and more. Phuse aims to make web development more enjoyable and productive with PHP.

## Features

### Core Features

- **MVC Pattern**: Phuse implements the Model-View-Controller pattern, which separates the application logic from the presentation layer. This makes the code more organized, maintainable, and testable.

- **Advanced Active Record ORM**: Complete ORM system with relationships, eager loading, model events, scopes, soft deletes, attribute casting, accessors/mutators, and automatic validation

- **Database Connection Pooling**: High-performance connection management for concurrent requests with automatic health monitoring

- **Query Result Caching**: Intelligent caching system with automatic invalidation and configurable storage options

- **Routing**: Phuse handles routing efficiently with cross-platform compatibility, allowing developers to define clean and understandable URLs for their applications. Supports both domain-based and subdirectory deployments with automatic detection.

- **Modern CSS Framework**: Flat, professional, light-first CSS design system (v1.2.3) with Bootstrap-compatible classes, `--ps-*` design tokens, dark mode via `[data-theme=dark]`, and comprehensive component library

- **Icon System**: 50+ flat hollow SVG icons as CSS classes (`.pi .pi-name`) - no icon fonts, no external files, pure CSS `mask-image` approach, color via `currentColor`

- **Complete JavaScript Components**: Full Bootstrap 5.3.8 JavaScript compatibility with Alert, Button, Carousel, Modal, Offcanvas, Popover, ScrollSpy, Tooltip, Toast, and Accordion components - all implemented as static methods with WeakMap persistent state

- **HTML Components**: Secure, fluent HTML generation with automatic XSS protection and modern PHP patterns

- **Pagination Component**: Enterprise-grade pagination system with accessibility support, URL generation, and comprehensive configuration options

- **Text Utilities**: Comprehensive text processing system with string manipulation, number formatting (bytes, currency, percentage, phone, slug), HTML/CSS/JS minification, and secure UUID generation

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

### Example

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

- **[JavaScript Components](docs/javascript-components.md)**: Alert, Button, Carousel, Modal, Offcanvas, Popover, ScrollSpy, Tooltip, Toast, Accordion - Bootstrap 5.3.8 data-attribute API, WeakMap state, fixed backdrop, color-variant tooltips, top-right toast stack (v1.2.3)

- **[ORM Examples Guide](docs/orm-examples.md)**: Complete ORM setup with database schema, model configuration, and advanced usage examples

- **[HTML Components](docs/html-components.md)**: Secure HTML generation with fluent API

- **[Image Utilities](docs/image-utilities.md)**: Advanced image manipulation with GD library

- **[Pagination Utilities](docs/pagination-utilities.md)**: Enterprise-grade pagination with accessibility support

- **[Upload Utilities](docs/upload-utilities.md)**: Secure file upload system with validation and XSS protection

- **[Cache System](docs/cache-system.md)**: Enhanced caching with multiple drivers

- **[Database Caching](docs/database-caching.md)**: Query result caching

- **[Template System](docs/template-system.md)**: Twig/Blade-inspired template engine - `{{variable}}` double-brace syntax, inline CSS/JS safety, filters, `{# comments #}`, `{!! raw !!}` output, conditionals, loops, and caching (v1.2.1)

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

- Email: <primaybr@gmail.com>

## Contributing

Phuse is an open-source project, and you are welcome to contribute to its development. You can fork the repository, make your changes, and submit a pull request. Please follow the coding standards and guidelines before submitting your code.

## Latest Changes

### v1.2.5 (2026-06-26)

#### Controller Helpers

- **`redirect()`**, **`json()`**, **`isAjax()`**, **`flash()`** shortcut methods added to `Core\Controller` — no need to reach into sub-objects for common actions

#### HTTP

- **`Response::json(array $data, int $status)`** — static JSON terminator; sets header, encodes, and exits
- **`Input::isAjax()`** — checks `HTTP_X_REQUESTED_WITH` header
- **`Client::getIpAddress()` IP-spoofing fix** — defaults to `REMOTE_ADDR`; forwarding headers only trusted when the connecting IP is in `Client::setTrustedProxies([])`

#### Database

- **`Connection::execute()` type-safe fix** — no longer passes already-bound params through `execute($array)` (which resets them all to `PARAM_STR`); calls bare `execute()` when params were bound via `arrayBind()`

#### Router

- **FQCN module support** — controller strings starting with `App\` used as-is, enabling module controllers
- Route captures cast to `string` before dispatch (UUID-safe)
- `is_dir()` guard for cache directory creation

#### Template Parser

- **`findTopLevelElse()`** — nesting-aware `{% else %}` locator; fixes corrupted output for nested `{% if %}{% else %}{% endif %}` blocks
- Off-by-one fix in `findTopLevelIfBlocks()` (`{% endif %}` = 11 chars, not 12)
- Condition regex: trailing space removed from identifier character class

#### Upload

- `imageProfile()` default max size raised 2 MB → 5 MB

#### CSS

- New icons: `.pi-bars`, `.pi-chart-bar`, `.pi-video`, `.pi-images`, `.pi-inbox`, `.pi-cog`
- `.vtx-loading` utility class (opacity + pointer-events for async states)
- Date/time input styling with dark-mode `color-scheme` support

### v1.2.4 (2026-06-22)

#### Database Builder — SQL Injection Security Fixes

- **`quoteIdentifier()`** — new protected helper in `BuildersTrait`; sanitizes column/table identifiers (strips non-`[a-zA-Z0-9_]` chars, wraps in backticks for MySQL, double-quotes for PostgreSQL)

- **`bindValue()`** — new private helper; registers values as `:qb_N` PDO bound parameters instead of interpolating them into the SQL string

- **Identifier + value injection fixed** across all advanced query methods in `BuildersTrait`, `MySQL`, and `PgSQL`: `month()`, `year()`, `day()`, `whereJsonContains()`, `dateFormat()`, `jsonExtract()`, `jsonExtractPath()`, `jsonContains()`, `caseWhen()`, `regexp()`, `fullTextSearch()`, `stringAgg()`, `groupConcat()`, `ifNull()`, `coalesce()`, `arrayContains()`, `ilike()`

- **`PgSQL::quoteIdentifier()`** — overrides trait default to use double-quote style required by PostgreSQL

- **`PgSQL::insertIgnore()`** — overrides trait's MySQL-only `INSERT IGNORE` with PostgreSQL's `ON CONFLICT DO NOTHING`

- **`orderBy()` accumulation** — multiple `->orderBy()` calls now append `, field dir` instead of overwriting the clause

#### ORM / Model

- **`get()` single-record fix** — detection no longer relies on `isset($result['id'])`; now correctly handles any primary key name

- **`Model::on(Connection, table)`** — static factory to share a connection across models inside a transaction

- **`withoutTimestamps()`** — disable auto-stamping per query

- **`whereRaw(sql, binds)`** — raw parameterized WHERE fragment for complex conditions

- **`distinct()`** — now deferred via `$isDistinct` flag to prevent double-DISTINCT when combined with `select()`

- **`orderBy()` default direction** — second argument now defaults to `'DESC'`

- **`clearQueryCache()`** — automatically called after every successful write (`save`, `update`, `delete`)

#### CSS

- **New icons**: `.pi-pencil`, `.pi-archive`, `.pi-message`, `.pi-sparkle`

- **Dark mode palette** — refined to a darker, less blue-shifted tone set for improved visual comfort

### v1.2.3 (2026-05-24)

#### JavaScript Components - Complete Overhaul

- **WeakMap state store** (`Phuse._store`) - persistent per-element state prevents carousel index reset and stale closures across handler calls

- **Carousel**: persistent slide index, `next()` / `prev()` / `goTo(i)` API, indicator dots in sync; arrow SVG icons added to CSS

- **Modal** (new full implementation): dynamic backdrop with fade, body scroll lock, click-outside close, `Escape` key, `data-backdrop="static"` to disable both; three sizes (`modal-sm`, `modal-lg`, `modal-xl`)

- **Offcanvas**: dynamic backdrop created/removed on show/hide, body scroll lock

- **Popover**: `stopPropagation()` prevents self-close race; `data-popover-open` state tracking

- **Tooltip**: fixed `mouseenter` delegation (switched to `mouseover`/`mouseout` which bubble); color variants (`tooltip-danger`, `tooltip-success`, etc.) auto-applied from trigger button class; `data-placement` support

- **Toast**: fixed top-right container (`position:fixed; top:1rem; right:1rem`); four types with pi icons and white text; slide-in animation; auto-dismiss; `Phuse.toast(message, type, duration)`

- **Accordion**: correct DOM traversal via `.closest('.accordion-item')`; padding animated alongside `max-height` to prevent border-box bleed-through; `overflow:hidden` + CSS transition

- **Tabs**: deprecated global `event` object removed - trigger element passed directly

- **ScrollSpy**: `new` keyword removed (it's a static method); listens on the scrollable element, not `window`

- **`.btn-close`**: global CSS rule with `inline-flex`, hover background; all instances use `<i class="pi pi-x"></i>`

- **Template parser**: guards against `Array to string conversion` in `parseRawOutput()`, `parseNestedProperties()`, `resolveExpressionValue()`

### v1.2.2 (2026-05-24)

#### CSS Framework - Flat/Modern Rewrite + Icon System

- Complete overhaul of `styles.css` - flat, professional, light-first with `--ps-*` tokens and `[data-theme=dark]`

- **50+ pi icons** - pure CSS `mask-image` SVGs, no icon fonts, `currentColor` inheritance

- New example page: `/examples/icons`

### v1.2.1 (2026-05-22)

#### Template System - Double-Brace Syntax Overhaul

- **`{{variable}}`** (was `{variable}`) - eliminates conflicts with inline CSS and JS

- **`{!! raw !!}`**, **`{# comment #}`**, **`@{{escaped}}`** - Twig/Blade parity

- All `{% if %}`, `{% foreach %}`, `{% for %}` control-flow tags unchanged

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

### Cross-Platform Deployment

Phuse supports flexible deployment options for different environments:

#### Domain-Based Deployment

For production or domain-based access (e.g., `https://phuse.test`):

- Routes work directly: `/examples`, `/items`, etc.

- Assets load from root: `/assets/css/styles.css`

- No additional configuration required

#### Subdirectory Deployment  

For local development or subdirectory access (e.g., `http://localhost/phuse/`):

- Routes include directory: `/phuse/examples`, `/phuse/items`, etc.

- Assets load from subdirectory: `/phuse/assets/css/styles.css`

- Automatic detection based on HTTP_HOST

#### Automatic Detection

The framework automatically detects the deployment type and adjusts:

- URL generation for routes and links

- Asset path generation for CSS, JS, and images

- Template variables (`baseUrl`, `assetsUrl`)

- Router pattern matching for clean URLs

#### Windows/Linux Compatibility

- Uses proper directory separators for file paths

- Consistent forward slashes for URLs across platforms

- No manual configuration required

### Template System (v1.2.1)

PHUSE uses a **Twig/Blade-inspired** double-brace syntax. Single `{ }` are never parsed, so inline CSS and JavaScript are completely safe inside templates.

```html
{# comment - stripped from output #}
<style>
  .btn { color: red; }          /* single { } untouched */
  .hero { background: {{bg}}; } /* {{var}} still works  */
</style>

<h1>{{title}}</h1>

{% if logged_in %}
  Welcome, {{user.name|capitalize}}!
{% else %}
  Please <a href="/login">login</a>.
{% endif %}

<script>
  var cfg = { debug: false };   /* plain JS - safe */
  var api = "{{apiUrl}}";       /* dynamic value    */
</script>

```

**Interactive examples** - visit these URLs in the browser:

| URL | Description |
| --- | --- |
| `/examples` | Example index |
| `/examples/basic` | Variable replacement |
| `/examples/conditional` | If/else logic |
| `/examples/foreach` | Array iteration |
| `/examples/nested` | Nested data access |
| `/examples/inline-assets` | Inline CSS/JS safety demo |
| `/examples/components` | **JS Components demo** (Modal, Toast, Accordion, Carousel …) |
| `/examples/icons` | Pi icon system reference |
| `/examples/dashboard` | Admin dashboard |
| `/examples/product` | E-commerce page |

📖 Full documentation: [`docs/template-system.md`](docs/template-system.md)

Thank you for using Phuse, and happy coding! 😊
