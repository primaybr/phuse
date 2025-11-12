# Text Utilities

Phuse provides a comprehensive set of text manipulation utilities located in the `Core/Utilities/Text/` namespace. These utilities offer secure, efficient, and feature-rich solutions for common text processing tasks including string manipulation, number formatting, HTML processing, CSS minification, and JavaScript minification.

## Overview

The Text Utilities system consists of five main classes:

- **`Str`** - Advanced string manipulation and generation
- **`Number`** - Number formatting and currency handling
- **`HTML`** - HTML minification and processing
- **`CSS`** - CSS minification and optimization
- **`JS`** - JavaScript minification and processing

All utilities are designed with security, performance, and modern PHP practices in mind.

## String Utilities (Str)

The `Str` class provides comprehensive string manipulation capabilities with a focus on security and performance.

### Basic String Operations

```php
use Core\Utilities\Text\Str;

// Truncate strings with custom suffix
$shortText = Str::cutString('This is a very long string', 20); // "This is a very ..."

// Convert case styles
$studly = Str::studly('hello_world_example'); // "HelloWorldExample"
$snake = Str::snake('HelloWorldExample'); // "hello_world_example"

// Generate secure random strings
$token = Str::randomString(32); // "aB3kL9mN2pQ8rS5tV7wX4yZ"
```

### Advanced String Processing

```php
// Time elapsed formatting
$elapsed = Str::timeElapsedString('2023-01-01 12:00:00'); // "2 years ago"

// Indonesian date formatting
$date = Str::convertTimeFormat('2023-12-25'); // "Senin, 25 Desember 2023"

// Base64 validation
$isValid = Str::isBase64('SGVsbG8gV29ybGQ='); // true

// Generate meta keywords from text
$keywords = Str::generateMetaKeywords('PHP is a popular programming language');
// "php, popular, programming, language"
```

### Enhanced UUID Generation

The `Str::generateUUID()` method provides multiple UUID versions with maximum uniqueness guarantees:

```php
// UUID v4 (Random) - Default, highest entropy
$uuid = Str::generateUUID(); // "550e8400-e29b-41d4-a716-446655440000"

// UUID v1 (Time-based) - Includes timestamp for sorting
$timeUUID = Str::generateUUID(1); // "92712ee2-bf5c-11f0-93a6-c929aa7b5359"

// UUID v3 (Name-based MD5) - Deterministic
$dnsNamespace = Str::getNamespaces()['dns'];
$nameUUID = Str::generateUUID(3, $dnsNamespace, 'example.com');
// Always returns: "9073926b-929f-31c2-abc9-fad77ae3e8eb"

// UUID v5 (Name-based SHA1) - Deterministic, preferred over v3
$nameUUID = Str::generateUUID(5, $dnsNamespace, 'example.com');
// Always returns: "cfbff0d1-9375-5685-968c-48ce8b15ae17"
```

#### UUID Features

- **Cryptographically Secure**: Uses `random_bytes()` with additional entropy mixing
- **Multiple Versions**: Support for v1, v3, v4, and v5 UUIDs
- **RFC 4122 Compliant**: Follows official UUID standards
- **Namespace Support**: Predefined namespaces for DNS, URL, OID, and X.500
- **Validation**: Built-in UUID format validation
- **Uniqueness Guarantee**: Enhanced entropy for practical uniqueness

#### Available Namespaces

```php
$namespaces = Str::getNamespaces();
echo $namespaces['dns'];  // "6ba7b810-9dad-11d1-80b4-00c04fd430c8"
echo $namespaces['url'];  // "6ba7b811-9dad-11d1-80b4-00c04fd430c8"
echo $namespaces['oid'];  // "6ba7b812-9dad-11d1-80b4-00c04fd430c8"
echo $namespaces['x500']; // "6ba7b814-9dad-11d1-80b4-00c04fd430c8"
```

#### UUID Validation

```php
$valid = Str::isValidUUID('550e8400-e29b-41d4-a716-446655440000'); // true
$invalid = Str::isValidUUID('not-a-uuid'); // false
```

### Pluralization

```php
// Basic pluralization rules
echo Str::plural('cat');     // "cats"
echo Str::plural('child');   // "children"
echo Str::plural('person');  // "people"
echo Str::plural('mouse');   // "mice"
echo Str::plural('focus');   // "foci" (Latin plural)
```

## Number Utilities (Number)

The `Number` class provides comprehensive number formatting and currency handling.

### Number Formatting

```php
use Core\Utilities\Text\Number;

// Short number formatting
echo Number::shortNumber(1500);    // "1K"
echo Number::shortNumber(2500000); // "2M"
echo Number::shortNumber(-5000);   // "-5K"

// Currency formatting
echo Number::formatCurrency(1234.56, '$');        // "$1.235"
echo Number::formatCurrency(1234.56, '€', 2, ',', '.'); // "€1,234.56"
```

### Phone Number Formatting

```php
// International phone number formatting with country codes
echo Number::formatPhoneNumber('08123456789', '', 'id');  // "628123456789"
echo Number::formatPhoneNumber('555-123-4567', '', 'us'); // "15551234567"
echo Number::formatPhoneNumber('+44 20 7123 4567', '', 'uk'); // "442071234567"
```

#### Supported Countries

- **id** (Indonesia): `+62`
- **us** (United States): `+1`
- **uk** (United Kingdom): `+44`
- **au** (Australia): `+61`
- **ca** (Canada): `+1`
- **de** (Germany): `+49`
- **fr** (France): `+33`
- **jp** (Japan): `+81`
- **kr** (South Korea): `+82`
- **sg** (Singapore): `+65`

## HTML Utilities (HTML)

The `HTML` class provides secure HTML minification and processing with XSS protection.

### HTML Minification

```php
use Core\Utilities\Text\HTML;

$html = new HTML();

// Basic minification
$content = '<div class="container">  <p>Hello   World</p>  </div>';
$minified = $html->minify($content);
// Result: '<div class="container"><p>Hello World</p></div>'

// Minification with CSS and JS processing
$fullContent = '
<!DOCTYPE html>
<html>
<head>
    <style>
        .header { margin: 0px; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">Welcome</div>
    <script>
        console.log("Hello World");
    </script>
</body>
</html>';

$optimized = $html->minify($fullContent, true, true);
```

### Features

- **Whitespace Removal**: Eliminates unnecessary spaces and line breaks
- **Attribute Optimization**: Cleans up HTML attributes
- **Comment Removal**: Removes HTML comments (preserves IE conditionals)
- **CSS Processing**: Minifies inline styles
- **JavaScript Processing**: Minifies inline scripts
- **Security**: Prevents XSS through proper escaping

## CSS Utilities (CSS)

The `CSS` class provides comprehensive CSS minification and optimization.

### CSS Minification

```php
use Core\Utilities\Text\CSS;

$css = new CSS(['/path/to/styles.css', '/path/to/more-styles.css']);

// Minify multiple CSS files
$minifiedCSS = $css->minify();

// Minify single CSS string
$minifier = new CSS();
$minified = $minifier->minifyCSS('
    .header {
        margin: 0px;
        padding: 10px 20px;
        background-color: #ffffff;
    }
');
// Result: '.header{margin:0;padding:10px 20px;background-color:#fff}'
```

### Features

- **Comment Removal**: Strips CSS comments
- **Whitespace Optimization**: Removes unnecessary spaces
- **Value Optimization**: Converts `0px` to `0`, etc.
- **Color Minification**: Shortens hex colors (`#ffffff` → `#fff`)
- **Selector Optimization**: Removes empty rules
- **File Validation**: Checks CSS file accessibility

## JavaScript Utilities (JS)

The `JS` class provides JavaScript minification with safety and performance in mind.

### JavaScript Minification

```php
use Core\Utilities\Text\JS;

// Static method for simple minification
$minified = JS::minify('console.log("Hello World"); alert("Test");');
// Result: 'console.log("Hello World");alert("Test");'

// Advanced minification with options
$minified = JS::minify($jsCode, [
    'preserve_comments' => false,
    'compress_whitespace' => true
]);
```

### Features

- **Comment Removal**: Strips JavaScript comments
- **Whitespace Compression**: Reduces unnecessary spaces
- **Safe Processing**: Handles strings and regexes correctly
- **Performance Optimized**: Efficient parsing and minification

## Security Considerations

All Text Utilities are designed with security as a primary concern:

- **XSS Protection**: HTML utilities automatically escape dangerous content
- **Input Validation**: All methods validate input parameters
- **Secure Random Generation**: UUID and random string generation use cryptographically secure methods
- **Safe File Handling**: File operations include proper validation and error handling

## Performance Optimizations

- **Efficient Algorithms**: Optimized for speed and memory usage
- **Lazy Loading**: Resources loaded only when needed
- **Caching Support**: Compatible with framework's caching system
- **Stream Processing**: Large files processed in chunks

## Error Handling

All utilities provide comprehensive error handling:

```php
try {
    $uuid = Str::generateUUID(1);
} catch (\InvalidArgumentException $e) {
    // Handle invalid version
    echo "Error: " . $e->getMessage();
} catch (\RuntimeException $e) {
    // Handle random generation failure
    echo "Error: " . $e->getMessage();
}
```

## Integration with Framework

The Text Utilities integrate seamlessly with other Phuse components:

```php
// In a Controller
class ExampleController extends Controller
{
    public function index()
    {
        // Use string utilities
        $token = $this->str->randomString(32);

        // Use number formatting
        $price = $this->textNumber->formatCurrency(99.99, '$');

        // Generate UUID for database record
        $uuid = Str::generateUUID();

        $this->render('example', [
            'token' => $token,
            'price' => $price,
            'uuid' => $uuid
        ]);
    }
}
```

## Best Practices

1. **Use Appropriate UUID Versions**:
   - v4 for general unique identifiers
   - v1 for time-sortable IDs
   - v3/v5 for deterministic, name-based identifiers

2. **Validate Input**: Always validate input before processing

3. **Handle Errors**: Implement proper error handling for production code

4. **Cache Results**: Consider caching expensive operations when appropriate

5. **Security First**: Utilize built-in security features for XSS protection

## Migration from Core\Text

If upgrading from the old `Core\Text` namespace:

```php
// Old (deprecated)
use Core\Text\Str;
use Core\Text\Number;

// New (recommended)
use Core\Utilities\Text\Str;
use Core\Utilities\Text\Number;
```

The new utilities provide enhanced features, better security, and improved performance while maintaining backward compatibility where possible.
