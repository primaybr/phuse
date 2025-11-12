# Phuse ORM Examples Guide

This guide explains how to set up and run the ORM examples in the Phuse framework, including database setup and model configuration.

## Prerequisites

Before running the ORM examples, ensure you have:

1. **Database Setup**: A MySQL/PostgreSQL database configured in `Config/Database.php`
2. **Required Tables**: The following tables must exist in your database:

### Database Schema

```sql
-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    role_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    user_id INT NOT NULL,
    published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Comments table
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
```

## Database Configuration

Update your `Config/Database.php` file with your database credentials:

```php
<?php
return [
    'connections' => [
        'default' => [
            'driver' => 'mysql', // or 'pgsql'
            'host' => 'localhost',
            'port' => '3306', // or '5432' for PostgreSQL
            'database' => 'your_database_name',
            'username' => 'your_username',
            'password' => 'your_password',
            'charset' => 'utf8mb4',
            'prefix' => ''
        ]
    ]
];
```

## Running the Examples

1. **Navigate to the project root**:
   ```bash
   cd /path/to/your/phuse/project
   ```

2. **Run the ORM example**:
   ```bash
   php examples/orm_example.php
   ```

## Understanding the ORM Features

### Model Setup

Each model extends `Core\Model` and defines its table and properties:

```php
<?php
class User extends Core\Model
{
    // Define the database table
    public function __construct()
    {
        parent::__construct('users');
    }

    // Specify fillable attributes (mass assignable)
    protected array $fillable = ['name', 'email', 'password', 'active'];

    // Hide sensitive attributes from JSON output
    protected array $hidden = ['password'];

    // Cast attributes to specific types
    protected array $casts = [
        'active' => 'boolean',
        'created_at' => 'string',
        'updated_at' => 'string'
    ];

    // Enable soft deletes
    protected bool $softDeletes = true;

    // Define relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Accessor: Transform attribute when retrieving
    protected function getNameAttribute($value)
    {
        return ucwords($value);
    }

    // Mutator: Transform attribute when saving
    protected function setEmailAttribute($value)
    {
        return strtolower($value);
    }

    // Scope: Filter queries
    public function scopeActive()
    {
        return $this->where('active', 1);
    }
}
```

### Key ORM Features Demonstrated

#### 1. **Basic CRUD Operations**
```php
// Create
$userId = $user->save([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'hashed_password',
    'active' => 1
]);

// Read
$user = (new User())->where('id', $userId)->get(1);

// Update
$user->where('id', $userId)->update(['name' => 'Jane Doe']);

// Delete
$user->where('id', $userId)->delete();
```

#### 2. **Relationships**
```php
// One-to-Many: User has many posts
$userWithPosts = (new User())->with(['posts'])->where('id', $userId)->get(1);

// Many-to-One: Post belongs to user
$postWithUser = (new Post())->with(['user'])->where('id', $postId)->get(1);
```

#### 3. **Scopes**
```php
// Use custom scopes
$activeUsers = (new User())->scopeActive()->get();
$publishedPosts = (new Post())->scopePublished()->get();
```

#### 4. **Soft Deletes**
```php
// Soft delete
$post->where('id', $postId)->softDelete();

// Include soft deleted records
$allPosts = (new Post())->withTrashed()->get();

// Only soft deleted records
$trashedPosts = (new Post())->onlyTrashed()->get();

// Restore
$post->where('id', $postId)->restore();
```

#### 5. **Model Events**
```php
$user->registerEvent('saving', function($data, $model) {
    echo "About to save user: {$data['email']}";
});

$user->registerEvent('created', function($data, $model) {
    echo "User created successfully";
});
```

#### 6. **Attribute Casting & Transformation**
```php
// Automatic casting
$user = (new User())->where('id', 1)->get(1);
// $user['active'] is now boolean true instead of int 1

// Accessors transform data on retrieval
// $user['name'] becomes "John Doe" (capitalized)

// Mutators transform data on saving
// Email is automatically lowercased before saving
```

## Troubleshooting

### Common Issues

1. **"Database query execution failed"**
   - Check your database credentials in `Config/Database.php`
   - Ensure the required tables exist
   - Verify table column names match the model expectations

2. **"Table doesn't exist" errors**
   - Run the SQL schema provided above to create the required tables

3. **"Column not found" errors**
   - Ensure your table schema matches the examples
   - Check for missing columns like `created_at`, `updated_at`, `deleted_at`

4. **Soft delete issues**
   - Make sure tables have a `deleted_at` TIMESTAMP column
   - Set `protected bool $softDeletes = true;` in your model

### Debug Mode

Enable debug output by modifying the example:

```php
// Add this to see SQL queries
$user->logQuery();

// Or check the generated query
$query = $user->builder()->select('*')->compile();
echo "Generated query: $query\n";
```

## Performance Optimizations

### Connection Pooling

The ORM now uses connection pooling to improve performance by reusing database connections:

```php
<?php
// Connection pooling is automatically handled by the Model class
// Multiple model instances share the same connection pool
$user1 = new User('users');
$user2 = new User('users'); // Reuses connection from pool

// Get connection pool statistics
$stats = Core\Model::getConnectionPoolStats();
echo "Total connections: " . $stats['total_connections'] . "\n";
echo "Available connections: " . $stats['available_connections'] . "\n";
echo "Busy connections: " . $stats['busy_connections'] . "\n";
```

### Batch Operations

For better performance with large datasets, use batch operations:

#### Batch Insert
```php
<?php
$userModel = new User('users');

$users = [
    ['name' => 'John Doe', 'email' => 'john@example.com', 'active' => 1],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'active' => 1],
    ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'active' => 0],
    // ... more records
];

// Insert in chunks of 1000 records each
$result = $userModel->insertBatch($users, 1000);
echo "Successfully inserted: " . $result['success'] . " records\n";
if (!empty($result['errors'])) {
    echo "Errors: " . implode(', ', $result['errors']) . "\n";
}
```

#### Batch Update
```php
<?php
$userModel = new User('users');

// Update multiple records with different conditions
$updates = [
    [
        'data' => ['active' => 0, 'updated_at' => date('Y-m-d H:i:s')],
        'where' => ['id' => 1]
    ],
    [
        'data' => ['name' => 'Updated Name', 'updated_at' => date('Y-m-d H:i:s')],
        'where' => ['email' => 'old@example.com']
    ],
    // ... more updates
];

$result = $userModel->updateBatch($updates, 500);
echo "Successfully updated: " . $result['success'] . " records\n";
```

#### Batch Delete
```php
<?php
$userModel = new User('users');

// Delete multiple records by different conditions
$conditions = [
    ['id' => 1],
    ['email' => 'inactive@example.com'],
    ['active' => 0, 'created_at' => '< ' . date('Y-m-d', strtotime('-1 year'))],
    // ... more conditions
];

$result = $userModel->deleteBatch($conditions, 500);
echo "Successfully deleted: " . $result['success'] . " records\n";
```

### Query Caching

The ORM includes intelligent query caching:

```php
<?php
$userModel = new User('users');

// Enable caching for this model instance
$userModel->enableCache(true);

// First query - will execute and cache result
$activeUsers = $userModel->where('active', 1)->get();

// Second identical query - will use cached result
$activeUsersCached = $userModel->where('active', 1)->get();

// Clear cache for this table
$userModel->clearTableCache();

// Clear all cached queries
$userModel->clearAllCache();
```

## Database-Specific Behaviors

### MySQL Features
- Full-text search with `fullTextSearch()` method
- JSON operations with `jsonExtract()`, `jsonContains()`
- String aggregation with `groupConcat()`
- Soundex phonetic matching

### PostgreSQL Features
- JSON and JSONB operations
- Array operations
- Full-text search with tsvector
- Advanced indexing capabilities

## Next Steps

After understanding the basic ORM examples, you can:

1. **Create your own models** by extending `Core\Model`
2. **Define custom relationships** between your models
3. **Add validation rules** (see validation documentation)
4. **Implement custom accessors/mutators** for data transformation
5. **Use scopes** for complex query filtering
6. **Leverage batch operations** for bulk data processing
7. **Utilize connection pooling** for high-performance applications

## Related Documentation

- [Database System](database-system.md)
- [Template System](template-system.md)
- [Validation Utilities](validator-utilities.md)
- [Testing](testing.md)
- [Cache System](cache-system.md)
