# Template Caching in Phuse

Phuse includes a powerful template caching system to improve the performance of your application by reducing the overhead of template compilation on each request.

## How It Works

1. **First Request**: When a template is rendered for the first time, it's compiled and the result is stored in the cache directory.
2. **Subsequent Requests**: The cached version is used instead of recompiling the template, as long as the template file hasn't been modified.

## Configuration

Template caching can be configured in `Config/Template.php`:

```php
class Template
{
    // Enable or disable template caching globally
    public bool $enableCache = true;
    
    // Cache lifetime in seconds (default: 1 hour)
    public int $cacheLifetime = 3600;
    
    // Cache directory (relative to the main cache directory)
    public string $cacheDir = 'templates';
    
    // Automatically clear cache in development mode
    public bool $autoClearInDevelopment = true;
}
```

## Usage

### Enabling/Disabling Caching

```php
// In your controller
$this->template->enableCache(true);  // Enable caching (default)
$this->template->enableCache(false); // Disable caching
```

### Clearing the Cache

```php
// Clear the entire template cache
$success = $this->template->clearCache();

// Force clear even if auto-clear is disabled
$success = $this->template->clearCache(true);
```

## Best Practices

1. **Development vs Production**:
   - In development, enable `autoClearInDevelopment` to ensure you always see your changes.
   - In production, set a reasonable `cacheLifetime` based on how often your templates change.

2. **Cache Invalidation**:
   - The cache is automatically invalidated when the template file is modified.
   - You can manually clear the cache using the `clearCache()` method.

3. **Performance Tuning**:
   - For high-traffic sites, consider using a faster cache backend (e.g., Redis or Memcached) by extending the `TemplateCache` class.
   - Adjust the `cacheLifetime` based on your template update frequency.

## Advanced: Custom Cache Implementation

To use a different caching mechanism, create a new class that implements the same interface as `TemplateCache` and update the `Parser` class to use your implementation.
