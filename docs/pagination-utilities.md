# Pagination Utilities

## Overview

The Pagination utilities provides a robust, accessible, and highly configurable solution for paginating data sets in web applications. It generates semantic HTML with proper ARIA attributes for screen reader support and follows modern web standards.

## Features

- **Accessible**: Full ARIA support with screen reader labels
- **Configurable**: Extensive configuration options for customization
- **Framework Integration**: Uses Core\Log for logging and follows framework patterns
- **URL Generation**: Automatic URL generation with query parameter handling
- **Responsive**: Mobile-friendly design with ellipsis for large page counts
- **Error Handling**: Comprehensive validation and error reporting
- **Fluent Interface**: Method chaining for easy configuration

## Installation

The Pagination utilities is included in the Phuse Framework and requires no additional installation.

## Basic Usage

```php
use Core\Utilities\Pagination\Pager;

// Create a basic pager
$pager = new Pager(150, 1); // 150 total items, page 1
echo $pager->render();
```

## Configuration

```php
use Core\Utilities\Pagination\PagerConfig;

// Create custom configuration
$config = new PagerConfig();
$config->defaultItemsPerPage = 25;
$config->containerClass = 'my-pagination';
$config->enableAccessibility = true;

// Create pager with configuration
$pager = new Pager(500, 1, $config);
```

## Fluent Interface

```php
use Core\Utilities\Pagination\Pager;

$pager = new Pager();
$result = $pager
    ->setTotalItems(1000)
    ->setItemsPerPage(50)
    ->setCurrentPage(3)
    ->setUrl('/products')
    ->setNumLinks(7);

echo $result->render();
```

## Configuration Options

### Basic Settings

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `defaultItemsPerPage` | int | 20 | Default number of items per page |
| `minItemsPerPage` | int | 1 | Minimum items per page |
| `maxItemsPerPage` | int | 1000 | Maximum items per page |
| `defaultNumLinks` | int | 5 | Default number of page links to show |

### Styling

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `containerClass` | string | 'pagination' | CSS class for the container |
| `itemClass` | string | 'page-item' | CSS class for page items |
| `linkClass` | string | 'page-link' | CSS class for page links |
| `activeClass` | string | 'active' | CSS class for active page |
| `disabledClass` | string | 'disabled' | CSS class for disabled items |

### Navigation Text

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `firstText` | string | '&laquo;' | Text for first page link |
| `lastText` | string | '&raquo;' | Text for last page link |
| `previousText` | string | '&lt;' | Text for previous page link |
| `nextText` | string | '&gt;' | Text for next page link |

### URL Generation

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `urlPattern` | string | '' | URL pattern with {page} placeholder |
| `pageParameter` | string | 'page' | Query parameter name for page number |

### Features

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `showFirstLast` | bool | true | Show first/last navigation links |
| `showPrevNext` | bool | true | Show previous/next navigation links |
| `showPageNumbers` | bool | true | Show numbered page links |
| `enableAccessibility` | bool | true | Enable ARIA labels and attributes |
| `enableLogging` | bool | true | Enable framework logging |

## Advanced Usage

### Custom Navigation Text

```php
$pager = new Pager(100, 1);
$pager
    ->setFirstText('First')
    ->setLastText('Last')
    ->setPreviousText('Previous')
    ->setNextText('Next');
```

### URL with Query Parameters

```php
// Preserves existing query parameters
$pager = new Pager(200, 1);
$pager->setUrl('/products?category=electronics&sort=price');
echo $pager->render(); // Generates: /products?category=electronics&sort=price&page=2
```

### Custom Page Parameter

```php
$config = new PagerConfig();
$config->pageParameter = 'p';

$pager = new Pager(150, 1, $config);
$pager->setUrl('/search');
echo $pager->render(); // Generates: /search?p=2
```

### URL Pattern

```php
$config = new PagerConfig();
$config->urlPattern = '/products/page/{page}';

$pager = new Pager(300, 1, $config);
echo $pager->render(); // Generates: /products/page/2
```

## Accessibility Features

The component includes comprehensive accessibility support:

- **ARIA Labels**: Descriptive labels for all navigation elements
- **ARIA Current**: Indicates the current page for screen readers
- **Semantic HTML**: Uses proper navigation and list elements
- **Keyboard Navigation**: All links are keyboard accessible

```php
$config = new PagerConfig();
$config->enableAccessibility = true;

echo $pager->render();
// Generates: <nav aria-label="Pagination Navigation">...</nav>
```

## Error Handling

The component includes comprehensive validation and error handling:

```php
try {
    $pager = new Pager();
    $pager->setTotalItems(-1); // Throws InvalidArgumentException
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Configuration Validation

```php
$config = new PagerConfig();
$errors = $config->validate();

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "Configuration error: " . $error . "\n";
    }
}
```

## Logging Integration

The component integrates with the framework's logging system:

```php
$config = new PagerConfig();
$config->enableLogging = true;
$config->logFileName = 'pagination_component';

$pager = new Pager(100, 1, $config);
// All operations are logged to Logs/pagination_component.log
```

## Performance Considerations

- **Efficient Rendering**: Only generates HTML when pagination is needed
- **Memory Efficient**: Minimal memory footprint
- **Fast Validation**: Quick input validation with descriptive errors
- **Optimized URLs**: Clean URL generation without redundant parameters

## CSS Framework Integration

The component is designed to work with popular CSS frameworks:

### Bootstrap 4/5

```css
.pagination {
    /* Bootstrap pagination styles */
}

.page-item {
    /* Bootstrap page item styles */
}

.page-link {
    /* Bootstrap page link styles */
}
```

### Custom Styling

```css
.my-pagination {
    display: flex;
    list-style: none;
    padding: 0;
}

.my-pagination .page-item-custom {
    margin: 0 2px;
}

.my-pagination .page-link-custom {
    padding: 8px 12px;
    text-decoration: none;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.my-pagination .current-page {
    background-color: #007bff;
    color: white;
}
```

## JavaScript Integration

For enhanced user experience, you can add JavaScript:

```javascript
// Handle pagination clicks via AJAX
document.querySelectorAll('.pagination .page-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.getAttribute('href');

        // Load page content via AJAX
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Update page content
                document.getElementById('content').innerHTML = html;
            });
    });
});
```

## Testing

The component includes comprehensive tests covering:

- Basic functionality and rendering
- Configuration validation
- URL generation
- Accessibility features
- Error handling
- Edge cases and boundary conditions

Run tests with:

```bash
phpunit tests/Core/Utilities/Pagination/
```

## Examples

See the comprehensive examples in `examples/pagination_examples.php` for detailed usage patterns and advanced features.

## Migration from Previous Versions

If upgrading from a previous version:

1. **Configuration**: The configuration format has changed. Update your configuration to use the new `PagerConfig` class.

2. **Method Signatures**: All setter methods now return `self` for fluent interface support.

3. **Logging**: The component now uses `Core\Log` instead of custom logging.

4. **HTML Output**: The HTML structure has been improved with better accessibility and semantic markup.

## API Reference

### Pager Class

#### Constructor

```php
new Pager(int $totalItems = 0, int $currentPage = 1, PagerConfig $config = null)
```

#### Methods

- `render(): string` - Generate pagination HTML
- `setTotalItems(int $totalItems): self` - Set total number of items
- `setItemsPerPage(int $itemsPerPage): self` - Set items per page
- `setCurrentPage(int $currentPage): self` - Set current page
- `setUrl(string $url): self` - Set base URL
- `setNumLinks(int $numLinks): self` - Set number of page links to show
- `setFirstText(string $text): self` - Set first page link text
- `setLastText(string $text): self` - Set last page link text
- `setPreviousText(string $text): self` - Set previous page link text
- `setNextText(string $text): self` - Set next page link text
- `setActiveClass(string $class): self` - Set CSS class for active items
- `setConfig(PagerConfig $config): self` - Set configuration
- `getInfo(): array` - Get pagination information
- `getStartItem(): int` - Get starting item number
- `getEndItem(): int` - Get ending item number

### PagerConfig Class

#### Static Methods

- `fromArray(array $config): self` - Create configuration from array
- `validate(): array` - Validate configuration

#### Properties

All configuration options listed in the Configuration Options section above.

## Contributing

When contributing to the Pagination utilities:

1. Follow the existing code style and patterns
2. Add tests for new features
3. Update documentation
4. Ensure accessibility compliance
5. Test with various configurations

## License

This component is part of the Phuse Framework and follows the same license terms.
