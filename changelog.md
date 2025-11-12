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
