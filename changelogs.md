### v1.2.1 (2026-05-22)

#### Core/Template — Double-Brace Syntax Overhaul

**Breaking change**: Variable placeholders changed from single `{variable}` to double `{{variable}}`, matching the syntax used by **Twig** and **Laravel Blade**. Control-flow tags (`{% if %}`, `{% foreach %}`, `{% for %}`) are unchanged.

##### Why

Single curly braces conflicted with inline CSS (`.class { color: red; }`) and inline JavaScript (`var obj = { key: val };`), corrupting templates that contained styles or scripts. Double braces eliminate all conflicts — only `{{ }}` triggers parsing.

##### New variable syntax

- **`{{variable}}`** — scalar variable output (replaces `{variable}`)
- **`{{user.profile.age}}`** — dot-notation nested access (replaces `{user.profile.age}`)
- **`{{name|upper}}`** — filter (replaces `{name|upper}`)
- **`{{name|substr:0:1|upper}}`** — chained filters with params (unchanged behaviour, new delimiter)

##### New v1.2.1 syntax additions

- **`{!! variable !!}`** — Raw / unescaped HTML output (Laravel Blade parity). Use for trusted rich-text content only.
- **`{# comment #}`** — Template comments stripped entirely from output (Twig parity). Supports multi-line.
- **`@{{variable}}`** — Escaped output tag — renders as the literal text `{{variable}}` without substitution (Blade `@{{ }}` parity).

##### Parser pipeline improvements

- `parseComments()` — strips `{# … #}` blocks before any other processing
- `parseRawOutput()` — resolves `{!! var !!}` expressions
- `parseEscapedSyntax()` / `restoreEscapedSyntax()` — protects `@{{…}}` blocks so they survive the full pipeline
- `parseArray()` — block-style array loop updated to `{{var}}…{{/var}}` delimiter; removed the old `str_replace(['{','}'],'',…)` hack that could corrupt CSS/JS inside loop bodies
- `restoreHtmlBlocks()` — updated to resolve `{{key}}` (was `{key}`) in `<script>` blocks on restore
- `parseForeach()` / `processNestedForeach()` — loop variable replacements now use `{{loopVar}}` keys
- `parseFor()` — numeric loop replacement updated to `{{i}}`
- All filter and nested-property regexes updated to match `{{…}}` double-brace delimiters

##### Inline CSS & JavaScript safety (the core fix)

```html
<style>
  /* ✅ CSS rules with { } are 100% safe — never parsed */
  .btn { color: red; border-radius: 0.25rem; }
  .hero { background: {{bgColor}}; }   /* dynamic value still works */
</style>

<script>
  // ✅ JS objects and control flow are completely safe
  var cfg = { debug: false };
  if (cfg.debug) { console.log("ok"); }

  // ✅ Inject PHP values with {{variable}}
  var apiUrl = "{{apiUrl}}";
</script>
```

##### Migration from v1.2.0

| Old | New |
| --- | --- |
| `{variable}` | `{{variable}}` |
| `{user.name}` | `{{user.name}}` |
| `{name\|upper}` | `{{name\|upper}}` |
| `{items}…{/items}` | `{{items}}…{{/items}}` |
| `{% if %}` | unchanged |
| `{% foreach %}` | unchanged |

#### New example: Inline CSS & JS Safety

- Added `App/Views/examples/inline_assets.php` — live demonstration of single-brace CSS/JS safety, new syntax features, and dynamic value injection into style attributes and script blocks
- Accessible at `/examples/inline-assets`

#### Documentation

- `docs/template-system.md` fully rewritten for v1.2.1 syntax with Twig/Blade comparison table, migration guide, inline CSS/JS safety section, and troubleshooting

#### Tests

- `tests/Core/TemplateTest.php` updated — all template strings use `{{variable}}` syntax
- New test cases: inline CSS preservation, inline JS preservation, JS variable injection inside `<script>` tags, `{# comment #}` stripping, `{!! raw !!}` output, `@{{escaped}}` literal output, filter chaining, numeric for loop

---

### v1.2.0a (2026-05-18)

#### Core/Http/Request.php

- **`extractResponseCode()` Scope Fix**: `$http_response_header` is a PHP local variable set only in the scope where `fopen()` runs — it was never accessible inside `extractResponseCode()`, causing it to always return the fallback `200`. The headers array is now passed as a parameter from the calling scope. This was the root cause of all HTTP response code detection being broken (401 checks, token refresh triggering, CMS token expiry detection)
- **`refreshRequest()` — `json_decode` fix**: Session token (`sesstoken`) is stored as a JSON string; the method was accessing it as an object without decoding first, causing property lookups to always fail and the refresh to throw before even attempting
- **`refreshRequest()` — refresh body fix**: The refresh endpoint receives the full token JSON (matching `Token.php`), not just `{"refresh_token":"..."}` — only `access_token` presence is now required before attempting the call
- **`updateSessionWithNewToken()` — session storage fix**: New token is now stored as `json_encode()`d string; previously stored as a raw PHP object, which broke subsequent JSON decoding of the session token

#### Core/Http/URI.php

- **`redirect()` relative path support**: Method no longer rejects non-absolute URLs — relative paths are resolved to absolute URLs using the current scheme and host before validation, allowing controller redirects like `redirect('/admin/login')` to work correctly
- **`redirect()` loopback allowance**: Added explicit `$isLoopback` check so redirects to `127.*` / `::1` addresses are permitted (previously blocked by the private IP filter)

#### Core/Template/ParserTrait.php

- **`restoreHtmlBlocks()` script variable substitution**: Protected `<script>` blocks are now processed for template variable substitution on restore — variables like `{adminUrl}`, `{apiUrl}` inside `<script>` tags now resolve correctly instead of being left as literal placeholders

---

### v1.2.0 (2026-05-15)

#### Database Layer

- **Critical Query Parameter Fix**: Rewrote parameter binding in `BuildersTrait` to use unique placeholder names (`param_N`, `where_N`) — eliminates bind conflicts when the same column appears in multiple clauses or queries
- **`!=` Operator Support**: Added `!=` to the comparison operators list in query builders
- **PostgreSQL Driver Overhaul**: `PgSQL` now has its own `compile()` and `resetQuery()` implementations; `resetQuery()` also clears the binds array to prevent cross-query parameter leakage
- **Connection Tracking**: `Connection` now tracks bound parameters internally via `$boundParams`; `arrayBind()` correctly handles colon-prefixed parameter keys; `execute()` merges tracked and passed params
- **UUID Primary Key Support**: `save()` return type widened to `int|string|bool`; PostgreSQL `RETURNING` clause properly fetches string UUIDs without casting to int
- **Double-Execution Fix**: `Connection::single()` no longer re-executes a statement that already ran (fixes INSERT + RETURNING flow)
- **Build Safety**: `Model::build()` now ensures the `FROM` clause is always set before `compile()` is called
- **Bind Lifecycle Fix**: `save()` and `build()` capture binds before `compile()` resets them; `resetBoundParams()` is called after each query to prevent accumulation
- **`whereNull` / `whereNotNull` Fix**: Both now call `whereQuery()` instead of `where()` to avoid unintended parameter binding

#### ORM / Model

- **Audit Fields**: Added `created_by`, `updated_by`, `deleted_by` column support with configurable nullable column properties (`createdByColumn`, `updatedByColumn`, `deletedByColumn`)
- **`setCurrentUser()` / `getCurrentUser()`**: New methods to set the current user ID for automatic audit trail population on insert and update
- **`primaryKey` Visibility**: Changed from `public` to `protected` to prevent accidental external mutation
- **Nullable Timestamp Columns**: `createdAtColumn`, `updatedAtColumn`, `deletedAtColumn` are now nullable — set to `null` to disable individual timestamp fields
- **`where()` Smart Detection**: Operator/value swap now only triggers when the second argument is an actual SQL operator string, preventing false positives with UUID values
- **Debug Properties**: Added `lastDebugQuery` and `lastDebugBinds` public properties for error reporting without echoing to output
- **Detailed Error Logging**: PDOException code, message, file, and line are now logged on save/query failure; update errors include the SQL and bound params

#### Template System

- **Filter Chaining**: Filters can now be chained with `|` — e.g. `{name|substr:0:1|upper}`
- **Parameterized Filters**: Filters now accept colon-delimited parameters with quoted string support — e.g. `{date|date:'M d, Y'}`
- **New `substr` Filter**: `{variable|substr:start:length}` for substring extraction
- **New `date` Filter**: `{variable|date:'Y-m-d'}` supporting both Unix timestamps and date strings
- **Nested `{% if %}` Block Support**: Replaced regex-based if parsing with a proper nesting-aware parser (`parseNestedIfBlocks` / `findTopLevelIfBlocks`) — nested conditionals no longer break the outer block
- **`{% else %}` in Loop Conditionals**: `{% if %}...{% else %}...{% endif %}` blocks inside `{% foreach %}` loops now correctly render the else branch
- **Filter Order Fix**: Filters are now parsed after `{% foreach %}` processing using `$this->data`, ensuring loop variables are available to filters
- **Filters Inside Loops**: Filters are now also applied to content inside foreach loop iterations
- **Condition Filter Support**: Condition evaluation now resolves `variable|count` expressions and handles arrays (non-empty = `true`) and objects in conditions

#### Utilities

- **`Str` Formatting Methods**: Added seven new static methods to `Core\Utilities\Text\Str`:
  - `formatBytes(int $bytes, int $precision)` — human-readable file sizes (B/KB/MB/GB/TB)
  - `formatNumber($number, int $decimals)` — thousands-separated number formatting
  - `formatCurrency($amount, string $currency, int $decimals)` — currency with symbol
  - `formatPercentage($value, int $decimals)` — percentage formatting
  - `formatDatetime($datetime, string $format)` — flexible datetime formatting
  - `slug(string $text)` — URL-safe slug generation
  - `formatPhone(string $phone, string $format)` — configurable phone number formatting

#### Core / Config

- **Lazy URI Loading**: `Config` no longer instantiates `URI` in the constructor; it is created on demand via `getURI()` — prevents HTTP context errors during CLI or early bootstrap
- **Config Validation**: Added check that the config file returns an array; throws a clear exception if not
- **PHP Compatibility**: Removed `readonly` from `Config` properties for PHP 8.2/8.3 cross-version compatibility

#### Session

- **Idempotent Initialization**: `Session` constructor and `ensureSessionStarted()` now check `session_status()` before calling `session_start()` — eliminates "session already active" warnings in environments that start sessions early
- **Fallback Save Path**: When the configured session save path is missing or not writable, the session falls back to a writable subdirectory under `sys_get_temp_dir()`

#### Cache

- **Named Cache Directories**: `FileCache` now accepts a `cacheType` constructor argument passed from `CacheManager`, so each named cache (`query`, `templates`, etc.) writes to its own subdirectory
- **`CacheConfig` Key Fix**: Corrected subdirectory key `'template'` → `'templates'` to match the rest of the framework
- **`FileCache::clear()` Fix**: Fixed inverted deletion condition and switched to `DIRECTORY_SEPARATOR` for cross-platform path correctness; deleted file count is now accurate

#### HTTP

- **`Input::post()` Array Fix**: When a POST value is itself an array, `post()` now calls `sanitizeArray()` instead of `sanitize()`, preventing a type error on multi-value fields (e.g. checkboxes)

---

### v1.1.6 (2026-03-11)
- **Cross-Platform Routing Compatibility**: Enhanced routing system to support both domain-based and subdirectory-based access patterns
  - **Dynamic URL Generation**: Automatic detection of access type (domain vs localhost/subdirectory)
  - **Windows/Linux Compatibility**: Proper handling of directory separators across different operating systems
  - **Asset URL Generation**: Correct asset path generation for both access methods
  - **Template Variable Consistency**: Unified baseUrl and assetsUrl handling across controllers
  - **Router Pattern Matching**: Fixed regex patterns to work with both access types
  - **URL Separator Constants**: Added URL_SEPARATOR constant for consistent URL handling
  - **Development Environment**: Seamless switching between local development and production deployments
  - **Backward Compatibility**: All existing localhost/subdirectory installations continue to work
  - **Performance**: Optimized URL generation with minimal overhead
  - **Documentation**: Updated routing documentation with cross-platform examples

### v1.1.5 (2025-11-19)
- **Core Http URI**: Updated core URI handling for local development
- **Core Parser**: Enhanced parsing tag for `<script>` elements
- **Theme Variable Integration**: Integrated all Bootstrap 5.3.8 CSS variables with Phuse-specific `--ps-` prefix
  - **Automatic Theme Switching**: Support for `data-theme="dark"` and `datas-theme="light"` attributes
  - **Dark Theme variables**: Complete dark theme variable set with optimized colors
  - **Light Theme Variables**: Complete light theme variable set with optimized colors
  - **Semantic Color System**: Full semantic color palette (primary, secondary, success, info, warning, danger)
  - **Component Compatibility**: All existing Phuse components now support Bootstrap theme switching
  - **Performance Optimized**: CSS variables for efficient runtime theme customization
  - **Documentation Updated**: Complete CSS framework documentation with theme system integration examples
- **Theme Switching Documentation**: Comprehensive documentation for Bootstrap theme system integration
  - **Theme Switching Examples**: HTML and JavaScript examples for dynamic theme switching

### v1.1.4 (2025-11-17)
- **Complete Bootstrap JavaScript Components Integration**: Full Bootstrap 5.3.8 JavaScript compatibility with Phuse-specific implementations
  - **Alert Component**: Dismissible alert notifications with fade animations and auto-cleanup
    - Supports all Bootstrap alert types (primary, success, warning, danger, info)
    - Click-to-dismiss functionality with smooth animations
    - Automatic alert element removal for memory efficiency
  - **Button Toggle Component**: Interactive button states with checkbox/radio synchronization
    - Active/inactive state toggling via data attributes
    - Form input synchronization for proper form handling
    - Bootstrap-compatible button groups and toolbars
  - **Carousel Component**: Image/media slider with full Bootstrap 5 features
    - Slide navigation with next/previous controls
    - Indicator dots for direct slide access
    - Automatic slide transitions and smooth animations
    - Keyboard navigation and touch support foundation
  - **Offcanvas Component**: Sliding sidebar panels with multiple positioning options
    - Flexible positioning (top, bottom, left, right)
    - Backdrop overlay with click-outside-to-close
    - Smooth slide animations and mobile-optimized sizing
    - Accessibility features with proper ARIA attributes
  - **Popover Component**: Rich content overlays triggered by clicks
    - Customizable title and content via data attributes
    - Smart positioning with viewport awareness
    - Click-outside-to-hide functionality
    - Fade animations with proper cleanup
  - **ScrollSpy Component**: Navigation highlighting based on scroll position
    - Automatic active link updates during scrolling
    - Configurable scroll offset for precision targeting
    - Smooth navigation integration with hash links
    - Performance-optimized scroll event handling
  - **Tooltip Component**: Hover-activated information displays
    - Multiple placement options (top, bottom, left, right)
    - Automatic positioning with collision detection
    - Lightweight implementation without external dependencies
    - Accessible tooltip experience with proper ARIA labels
- **Enhanced Modal System**: Improved modal functionality with better UX
  - Enhanced focus management and accessibility
  - Better backdrop handling and body scroll prevention
  - Improved modal stacking and z-index management
  - Cross-browser compatibility improvements
- **Complete Event Delegation System**: All components use Phuse's optimized event delegation
  - Efficient event binding with automatic cleanup
  - Dynamic component initialization
  - Memory leak prevention through proper event management
  - Performance-optimized selector matching
- **CSS Framework Enhancements**: Supporting styles for new JavaScript components
  - Complete carousel styles with controls and indicators
  - Offcanvas positioning with backdrop support
  - Tooltip and popover positioning with arrows
  - Enhanced alert animations and button states
  - ScrollSpy active state management
  - Dark theme optimizations for all new components
- **Interactive Components Demo**: Complete example page showcasing all features
  - Live component demonstrations at `/examples/components`
  - Code examples with syntax highlighting
  - Real-world usage patterns and best practices
  - Responsive design with mobile optimization
  - Performance monitoring and loading states
- **Framework Integration**: Zero-configuration component initialization
  - Auto-initialization on DOM ready
  - Data attribute-driven configuration
  - No external JavaScript dependencies beyond Phuse core
  - Backward compatibility with existing projects
  - Production-ready component implementations
- **Bootstrap 5.3.8 Full Compatibility**: Complete JavaScript feature parity
  - All Bootstrap 5.3.8 interactive components supported
  - Consistent API with Bootstrap data attributes
  - Enhanced accessibility features
  - Modern JavaScript patterns without jQuery dependency
  - Lightweight and performant implementations
- **Developer Documentation**: Comprehensive component usage guides
  - HTML markup examples for each component
  - JavaScript API documentation
  - Customization and configuration options
  - Accessibility guidelines and best practices
  - Migration guides for existing applications

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

### v1.0.2a (2025-10-21)
- Removed Versioning information on code files
- Fixed session issue on local machine

### v1.0.2 (2025-09-13)
- Added Database Query Caching system
- Added Template Caching for improved performance
- Enhanced cache configuration options
- Improved documentation for caching features
- Optimized template rendering performance
- Core/Router - Added handle for empty url
- Core/Template/ParserTrait : Update the parseForEach method to not replace string that is outside the brackets scope
- Core/Router : Handling local machine routing

### v1.0.1 (2025-2-28)

- Added support for multiple HTTP methods in Router class
- Added Route Caching in Router class
- Added DocBlock to Core files
- Fixed namespace resolution issues in Router class
- Improved session system for better security and performance
- Improved Router class for better performance and flexibility
- Improved Base class for better performance and flexibility

### v1.0.0 (2023-11-21)

- Initial release of Phuse 1, based on collective and collaborative framework named 'Orceztra'(discontinued personal project).
