<?php

/**
 * ORM Features Example
 *
 * This example demonstrates the modern ORM features added to the Phuse Model class.
 * It shows how to use relationships, scopes, soft deletes, attribute casting,
 * accessors/mutators, and model events.
 *
 * Note: This example shows the ORM structure and features. To run it with a real database,
 * ensure you have the proper database configured in Config/Database.php and the required
 * tables created.
 */

require_once __DIR__ . '/../Core/Boot.php';


// Example User Model
class User extends Core\Model
{
    protected array $fillable = ['name', 'email', 'password', 'active'];
    protected array $hidden = ['password'];
    protected array $casts = [
        'active' => 'boolean',
        'created_at' => 'string',
        'updated_at' => 'string'
    ];
    protected bool $softDeletes = false;

    public function __construct()
    {
        parent::__construct('users');
    }

    // Relationship: User has many posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Relationship: User belongs to a role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Accessor for name (capitalize first letter)
    protected function getNameAttribute($value)
    {
        return ucwords($value);
    }

    // Mutator for email (convert to lowercase)
    protected function setEmailAttribute($value)
    {
        return strtolower($value);
    }

    // Scope for active users
    public function scopeActive(): self
    {
        return $this->where('active', 1);
    }

    // Scope for users by role
    public function scopeByRole($roleId): self
    {
        return $this->where('role_id', $roleId);
    }
}

// Example Post Model
class Post extends Core\Model
{
    protected array $fillable = ['title', 'content', 'user_id', 'published'];
    protected array $casts = [
        'published' => 'boolean',
        'created_at' => 'string',
        'updated_at' => 'string'
    ];
    protected bool $softDeletes = false;

    public function __construct()
    {
        parent::__construct('posts');
    }

    // Relationship: Post belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Post has many comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Accessor for title
    protected function getTitleAttribute($value)
    {
        return htmlspecialchars($value);
    }

    // Scope for published posts
    public function scopePublished(): self
    {
        return $this->where('published', 1);
    }
}

// Example Comment Model
class Comment extends Core\Model
{
    protected array $fillable = ['content', 'user_id', 'post_id'];
    protected bool $softDeletes = true;

    public function __construct()
    {
        parent::__construct('comments');
    }

    // Relationship: Comment belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Comment belongs to a post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

// Example Role Model
class Role extends Core\Model
{
    protected array $fillable = ['name', 'permissions'];

    public function __construct()
    {
        parent::__construct('roles');
    }

    // Relationship: Role has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Cast permissions as array
    protected array $casts = [
        'permissions' => 'array'
    ];
}

try {
    echo "=== Phuse ORM Features Demo ===\n\n";

    // 1. Create a role
    echo "1. Creating a role...\n";
    $role = new Role();
    $roleId = $role->save([
        'name' => 'Administrator',
        'permissions' => json_encode(['create', 'read', 'update', 'delete']) // JSON encode for database storage
    ]);
    echo "Role created with ID: $roleId\n\n";

    // 2. Create a user with relationships
    echo "2. Creating a user...\n";
    $user = new User();
    $userId = $user->save([
        'name' => 'john doe ' . time(),
        'email' => 'JOHN' . time() . '@EXAMPLE.COM', // Will be converted to lowercase by mutator
        'password' => 'hashed_password',
        'active' => 1,
        'role_id' => $roleId
    ]);
    echo "User created with ID: $userId\n";

    // Get the user data to show accessor in action
    $userData = (new User())->where('id', $userId)->get(1);
    echo "User name (accessor applied): " . ($userData['name'] ?? 'N/A') . "\n\n";

    // 3. Create posts for the user
    echo "3. Creating posts...\n";
    $post = new Post();
    $postId1 = $post->save([
        'title' => '<b>Hello World</b>', // Will be escaped by accessor
        'content' => 'This is my first post!',
        'user_id' => $userId,
        'published' => 1
    ]);

    $postId2 = $post->save([
        'title' => 'Second Post',
        'content' => 'This is my second post!',
        'user_id' => $userId,
        'published' => 0
    ]);
    echo "Posts created with IDs: $postId1, $postId2\n\n";

    // 4. Demonstrate relationships
    echo "4. Loading relationships...\n";

    // Get user with posts (eager loading)
    $userWithPosts = new User();
    $userData = $userWithPosts->with(['posts'])->where('id', $userId)->get(1);
    echo "User with posts loaded: " . json_encode($userData, JSON_PRETTY_PRINT) . "\n\n";

    // Get post with user relationship
    $postWithUser = new Post();
    $postData = $postWithUser->with(['user'])->where('id', $postId1)->get(1);
    echo "Post with user loaded: " . json_encode($postData, JSON_PRETTY_PRINT) . "\n\n";

    // 5. Demonstrate scopes
    echo "5. Using scopes...\n";

    // Get only active users
    $activeUsers = new User();
    $activeUserCount = $activeUsers->scopeActive()->totalRows();
    echo "Active users count: $activeUserCount\n";

    // Get only published posts
    $publishedPosts = new Post();
    $publishedPostCount = $publishedPosts->scopePublished()->totalRows();
    echo "Published posts count: $publishedPostCount\n\n";

    // 6. Demonstrate soft deletes
    echo "6. Soft delete demonstration...\n";

    // Soft delete a post
    $postToDelete = new Post();
    $postToDelete->where('id', $postId2)->softDelete();
    echo "Post soft deleted\n";

    // Get all posts (should exclude soft deleted)
    $allPosts = new Post();
    $postCount = $allPosts->totalRows();
    echo "Total posts (excluding soft deleted): $postCount\n";

    // Get only trashed posts
    $trashedPosts = new Post();
    $trashedCount = $trashedPosts->onlyTrashed()->totalRows();
    echo "Trashed posts count: $trashedCount\n";

    // Restore the soft deleted post
    $restorePost = new Post();
    $restorePost->where('id', $postId2)->restore();
    echo "Post restored\n\n";

    // 7. Demonstrate model events
    echo "7. Model events demonstration...\n";

    // Register event listeners
    $user->registerEvent('saving', function($data, $model) {
        echo "Event: Saving user with email: {$data['email']}\n";
    });

    $user->registerEvent('created', function($data, $model) {
        echo "Event: User created successfully\n";
    });

    // Create another user to trigger events
    $newUser = new User();
    $newUserId = $newUser->save([
        'name' => 'jane smith',
        'email' => 'JANE' . (time() + 1) . '@EXAMPLE.COM',
        'password' => 'hashed_password',
        'active' => 1,
        'role_id' => $roleId
    ]);
    echo "New user created with ID: $newUserId\n\n";

    // 8. Demonstrate attribute casting and hiding
    echo "8. Attribute casting and hiding...\n";

    $userForCast = new User();
    $userCastData = $userForCast->where('id', $userId)->get(1);
    echo "User data (password hidden, active cast to boolean): " . json_encode($userCastData) . "\n\n";

    echo "=== ORM Demo Complete ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
