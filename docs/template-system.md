# Template System in Phuse

The Phuse framework provides a powerful and flexible template system that allows you to separate your application logic from presentation. The template system supports variable replacement, conditional statements, loops, caching, and more.

## Overview

The template system consists of several key components:

- **Parser Class** (`Core/Template/Parser.php`) - Main template rendering engine
- **ParserInterface** (`Core/Template/ParserInterface.php`) - Defines the contract for template operations
- **ParserTrait** (`Core/Template/ParserTrait.php`) - Contains template parsing logic
- **TemplateCache** (`Core/Cache/TemplateCache.php`) - Handles template caching
- **Configuration** (`Config/Template.php`) - Template system settings

## Basic Usage

### Setting Up Templates

```php
// In your controller
$this->template->setTemplate('welcome'); // Loads App/Views/welcome.php
$data = ['name' => 'John Doe', 'title' => 'Welcome'];
$this->template->setData($data);
$output = $this->template->render('', [], true); // Return as string
```

### Method Chaining

The template system supports method chaining for cleaner code:

```php
$output = $this->template
    ->setTemplate('user/profile')
    ->setData([
        'user' => $userData,
        'posts' => $userPosts
    ])
    ->render('', [], true);
```

## Template Syntax

### Variable Replacement

Use double curly braces to output variables:

```php
<!-- Template file -->
<h1>Hello {name}!</h1>
<p>Your age is {age} years old.</p>
<p>You live in {city}.</p>
```

```php
// PHP code
$data = [
    'name' => 'Jane Doe',
    'age' => 28,
    'city' => 'New York'
];
$this->template->render('user/profile', $data, true);
```

### Filters

Apply filters to variables using the pipe syntax:

```php
<!-- Template file -->
<h1>{title|upper}</h1>
<p>Length: {items|length}</p>
<p>Rating: {product.rating|round} stars</p>
<p>Name: {name|capitalize}</p>
```

#### Available filters

| Filter | Description | Example |
|---|---|---|
| `length` / `count` | Array length or string character count | `{items\|length}` |
| `upper` / `uppercase` | Convert to uppercase | `{name\|upper}` |
| `lower` / `lowercase` | Convert to lowercase | `{name\|lower}` |
| `capitalize` | Capitalize first letter of each word | `{name\|capitalize}` |
| `trim` | Remove surrounding whitespace | `{value\|trim}` |
| `title` | Convert to title case | `{title\|title}` |
| `round` | Round to nearest integer | `{rating\|round}` |
| `stars` | Convert numeric rating to star symbols (★☆) | `{score\|stars}` |
| `substr` | Extract substring — `substr:start` or `substr:start:length` | `{name\|substr:0:1}` |
| `date` | Format a date string or Unix timestamp | `{created_at\|date:'M d, Y'}` |

#### Filter chaining (v1.2.0)

Filters can be chained with `|` — they are applied left to right:

```php
<!-- Get first letter of name, uppercased -->
{name|substr:0:1|upper}

<!-- Trim whitespace then capitalize -->
{title|trim|capitalize}
```

#### Parameterized filters (v1.2.0)

Pass colon-delimited arguments after the filter name. Quoted strings (single or double) preserve spaces and commas:

```php
<!-- Substring: start 0, length 50 -->
{description|substr:0:50}

<!-- Date format with spaces and commas inside quotes -->
{created_at|date:'F j, Y'}
{updated_at|date:'Y-m-d H:i'}

<!-- Chained: format date then uppercase -->
{event_date|date:'M d, Y'|upper}
```

```php
// PHP code
$data = [
    'title' => 'welcome to our site',
    'items' => ['item1', 'item2', 'item3'],
    'product' => ['rating' => 4.7],
    'name' => 'john doe',
    'created_at' => '2024-12-25 09:00:00'
];
// Outputs: "WELCOME TO OUR SITE", "3", "5 stars", "John Doe", "December 25, 2024"
```

### Conditional Statements

Use `{% if %}...{% endif %}` for conditional logic. Supports boolean variables, comparisons, and the `not` keyword.

The conditional parser supports **proper nesting** (v1.2.0) — inner `{% if %}` blocks no longer break the outer block.

```php
<!-- Template file -->
{% if logged_in %}
    <p>Welcome back, {username}!</p>
    {% if user.role == 'admin' %}
        <p>You have admin privileges.</p>
    {% endif %}
    <a href="/logout">Logout</a>
{% else %}
    <p>Please <a href="/login">login</a> to continue.</p>
{% endif %}
```

```php
// PHP code
$data = [
    'logged_in' => true,
    'username' => 'johndoe',
    'user' => ['role' => 'admin']
];
```

Array variables evaluate as truthy when non-empty, falsy when empty:

```php
{% if items %}
    <p>Found {items|count} items.</p>
{% else %}
    <p>No items found.</p>
{% endif %}
```

### Foreach Loops

Iterate over arrays with `{% foreach %}...{% endforeach %}`. Filters and `{% if/else/endif %}` blocks are fully supported inside loops (v1.2.0):

```php
<!-- Template file -->
<h2>Your Posts:</h2>
<ul>
{% foreach posts as post %}
    <li>
        {post.title|capitalize} — {post.created_at|date:'M d, Y'}
        {% if post.published %}
            <span class="badge">Published</span>
        {% else %}
            <span class="badge">Draft</span>
        {% endif %}
    </li>
{% endforeach %}
</ul>
```

```php
// PHP code
$data = [
    'posts' => [
        ['title' => 'First Post', 'date' => '2023-01-01'],
        ['title' => 'Second Post', 'date' => '2023-01-02']
    ]
];
```

### Nested Data Access

Access nested array/object properties:

```php
<!-- Template file -->
{% foreach users as user %}
    <div class="user">
        <h3>{user.name}</h3>
        <p>Age: {user.profile.age}</p>
        <p>City: {user.profile.city}</p>
    </div>
{% endforeach %}
```

```php
// PHP code
$data = [
    'users' => [
        [
            'name' => 'John',
            'profile' => ['age' => 30, 'city' => 'NYC']
        ],
        [
            'name' => 'Jane',
            'profile' => ['age' => 25, 'city' => 'LA']
        ]
    ]
];
```

### For Loops

Create numbered loops with `{% for %}...{% endfor %}`:

```php
<!-- Template file -->
<select name="year">
{% for year in 2020..2025 %}
    <option value="{year}">{year}</option>
{% endfor %}
</select>
```

## Advanced Features

### Template Caching

Enable caching for better performance:

```php
// Enable caching (default: enabled)
$this->template->enableCache(true);

// Clear cache manually
$this->template->clearCache();

// Force clear even in production
$this->template->clearCache(true);
```

Configure caching in `Config/Template.php`:

```php
class Template
{
    public bool $enableCache = true;           // Enable/disable caching
    public int $cacheLifetime = 3600;         // Cache lifetime in seconds
    public string $cacheDir = 'templates';     // Cache subdirectory
    public bool $autoClearInDevelopment = true; // Auto-clear in dev mode
}
```

### Error Handling

The template system includes built-in error handling:

```php
// This will render an error template and exit
$this->template->exception('Something went wrong');

// Custom error template
$this->template->exception('Database error', 'error/database');
```

### Security Features

The template system includes security measures:

- **Safe Variable Extraction**: Only extracts variables with safe names (alphanumeric + underscore)
- **Input Validation**: Validates template data types
- **Error Isolation**: Prevents template errors from crashing the application

## Configuration

### Template Configuration

Customize the template system in `Config/Template.php`:

```php
class Template
{
    // Caching settings
    public bool $enableCache = true;
    public int $cacheLifetime = 3600;
    public string $cacheDir = 'templates';
    public bool $autoClearInDevelopment = true;

    // Additional settings can be added here
}
```

### Views Directory Structure

Organize your templates in `App/Views/`:

```
App/Views/
├── layouts/
│   └── main.php          # Main layout template
├── partials/
│   └── header.php        # Reusable header partial
├── pages/
│   ├── home.php         # Home page template
│   └── about.php        # About page template
└── error/
    └── default.php       # Default error template
```

## Best Practices

### 1. Template Organization

- Keep templates focused on presentation only
- Use layouts for common HTML structure
- Create partials for reusable components
- Organize templates by feature/page

### 2. Performance Optimization

- Enable caching in production
- Use appropriate cache lifetimes
- Avoid complex logic in templates
- Minimize database calls in templates

### 3. Security Considerations

- Never trust user data in templates
- Validate all template variables
- Use the built-in security features
- Avoid executing user-controlled code

### 4. Maintainability

- Use consistent naming conventions
- Comment complex template logic
- Keep templates readable and well-structured
- Use meaningful variable names

## Integration with Controllers

### Basic Controller Integration

```php
class UserController extends Controller
{
    public function profile($userId)
    {
        $user = $this->model('User')->find($userId);
        $posts = $this->model('Post')->getByUserId($userId);

        return $this->render('user/profile', [
            'user' => $user,
            'posts' => $posts,
            'title' => 'User Profile'
        ]);
    }
}
```

### Layout Integration

```php
// Controller
$data = [
    'title' => 'My Page',
    'content' => $this->render('pages/content', $pageData, true)
];

return $this->render('layouts/main', $data);
```

```php
<!-- Layout template (layouts/main.php) -->
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    <header><!-- Header content --></header>
    <main>{content}</main>
    <footer><!-- Footer content --></footer>
</body>
</html>
```

## Troubleshooting

### Common Issues

1. **Template not found**: Check file path and extension (.php required)
2. **Variables not displaying**: Ensure variable names match exactly
3. **Syntax errors in templates**: Check template syntax carefully
4. **Caching issues**: Clear cache or disable temporarily for debugging

### Debug Mode

Enable debug mode to see template errors:

```php
// In development
$this->template->enableCache(false); // Disable caching for debugging
```

### Logging

The template system logs errors and warnings:

```php
// Errors are logged automatically
// Check your logs directory for template-related errors
```

## Examples

### Web-Accessible Examples

Visit the following URLs to see interactive template examples:

- **Examples Index**: `/examples` - Overview of all available examples
- **Basic Template**: `/examples/basic` - Simple variable replacement
- **Conditional Logic**: `/examples/conditional` - If/else statements
- **Foreach Loops**: `/examples/foreach` - Array iteration
- **Nested Data**: `/examples/nested` - Accessing nested properties
- **Blog Post**: `/examples/blog` - Complex multi-feature template
- **Dashboard**: `/examples/dashboard` - Advanced features showcase
- **Product Page**: `/examples/product` - E-commerce template example

### Code Examples

See `examples/template_examples.php` for comprehensive usage examples including:

- Basic template rendering
- Advanced conditional logic
- Complex nested loops
- Caching strategies
- Error handling
- Performance optimization

## Migration from Older Versions

If upgrading from an older version of Phuse:

1. **Template syntax** remains the same - no changes needed
2. **Caching** is now enabled by default - adjust `Config/Template.php` if needed
3. **Error handling** is more robust - existing error templates will continue to work
4. **Performance** improvements are automatic

The template system is designed to be backward compatible while providing enhanced features and better performance.
