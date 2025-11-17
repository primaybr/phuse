<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use Core\Controller;

/**
 * Examples Controller
 *
 * This controller handles rendering of template examples for demonstration purposes.
 * It provides access to various template examples showcasing different features
 * of the Phuse template system.
 */
class Examples extends Controller
{
    /**
     * Constructor for the Examples controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display the examples index page
     *
     * @return void
     */
    public function index(): void
    {
        $data = [
            'title' => 'Template System Examples',
            'description' => 'Explore the various features of the Phuse template system through interactive examples.',
            'examples' => [
                [
                    'name' => 'CSS Framework',
                    'description' => 'Modern CSS framework with Bootstrap 5+ compatibility, dark theme optimization, and grid system',
                    'url' => $this->baseUrl.'examples/css-framework',
                    'template' => 'css_framework'
                ],
                [
                    'name' => 'Basic Template',
                    'description' => 'Simple variable replacement and basic template rendering',
                    'url' => $this->baseUrl.'examples/basic',
                    'template' => 'welcome'
                ],
                [
                    'name' => 'Conditional Logic',
                    'description' => 'If/else statements and conditional rendering',
                    'url' => $this->baseUrl.'examples/conditional',
                    'template' => 'user_dashboard'
                ],
                [
                    'name' => 'Foreach Loops',
                    'description' => 'Iterating over arrays and collections',
                    'url' => $this->baseUrl.'examples/foreach',
                    'template' => 'product_list'
                ],
                [
                    'name' => 'Nested Data',
                    'description' => 'Accessing nested object properties and complex data structures',
                    'url' => $this->baseUrl.'examples/nested',
                    'template' => 'user_profile'
                ],
                [
                    'name' => 'Blog Post',
                    'description' => 'Complex template with multiple features and real-world usage',
                    'url' => $this->baseUrl.'examples/blog',
                    'template' => 'blog_post'
                ],
                [
                    'name' => 'Dashboard',
                    'description' => 'Advanced template features with statistics and notifications',
                    'url' => $this->baseUrl.'examples/dashboard',
                    'template' => 'dashboard'
                ],
                [
                    'name' => 'Product Page',
                    'description' => 'E-commerce product page with conditional pricing and related products',
                    'url' => $this->baseUrl.'examples/product',
                    'template' => 'product_page'
                ],
                [
                    'name' => 'Bootstrap Components',
                    'description' => 'Complete Bootstrap 5.3.8 compatible components: Alert, Carousel, Offcanvas, Popover, ScrollSpy, Tooltip',
                    'url' => $this->baseUrl.'examples/components',
                    'template' => 'components'
                ]
            ],
            'baseUrl' => $this->baseUrl,
            'year' => date('Y'),
        ];

        $this->render('examples/index', $data);
    }

    /**
     * Display basic template example
     *
     * @return void
     */
    public function basic(): void
    {
        $data = [
            'title' => 'Basic Template Example',
            'name' => 'John Doe',
            'company' => 'Tech Corp',
            'description' => 'This example demonstrates basic variable replacement in templates.',
            'year' => date('Y'),
        ];

        $this->render('examples/welcome', $data);
    }

    /**
     * Display conditional logic example
     *
     * @return void
     */
    public function conditional(): void
    {
        $data = [
            'title' => 'Conditional Logic Example',
            'logged_in' => true,
            'username' => 'johndoe',
            'role' => 'admin',
            'notifications' => 5,
            'description' => 'This example demonstrates conditional statements in templates.',
            'year' => date('Y'),
        ];

        $this->render('examples/user_dashboard', $data);
    }

    /**
     * Display foreach loop example
     *
     * @return void
     */
    public function foreach(): void
    {
        $products = [
            ['name' => 'Laptop', 'price' => 999.99, 'category' => 'Electronics'],
            ['name' => 'Mouse', 'price' => 25.50, 'category' => 'Electronics'],
            ['name' => 'Keyboard', 'price' => 75.00, 'category' => 'Electronics'],
            ['name' => 'Monitor', 'price' => 299.99, 'category' => 'Electronics']
        ];

        // Calculate average price
        $total = array_sum(array_column($products, 'price'));
        $average_price = $total / count($products);
        $average_price_rounded = round($average_price, 2);

        $data = [
            'title' => 'Foreach Loop Example',
            'products' => $products,
            'category_filter' => 'Electronics',
            'average_price' => $average_price,
            'average_price_rounded' => $average_price_rounded,
            'products_count' => count($products),
            'description' => 'This example demonstrates foreach loops for iterating over arrays.',
            'year' => date('Y'),
        ];

        $this->render('examples/product_list', $data);
    }

    /**
     * Display nested data example
     *
     * @return void
     */
    public function nested(): void
    {
        $data = [
            'title' => 'Nested Data Example',
            'users' => [
                [
                    'name' => 'Alice Johnson',
                    'profile' => [
                        'age' => 28,
                        'city' => 'San Francisco',
                        'occupation' => 'Designer'
                    ],
                    'skills' => ['Photoshop', 'Illustrator', 'Figma']
                ],
                [
                    'name' => 'Bob Smith',
                    'profile' => [
                        'age' => 32,
                        'city' => 'New York',
                        'occupation' => 'Developer'
                    ],
                    'skills' => ['PHP', 'JavaScript', 'React']
                ]
            ],
            'description' => 'This example demonstrates accessing nested data structures.'
        ];

        $this->render('examples/user_profile', $data);
    }

    /**
     * Display blog post example
     *
     * @return void
     */
    public function blog(): void
    {
        $data = [
            'title' => 'Blog Post Example',
            'author' => 'Jane Doe',
            'date' => '2023-12-01',
            'content' => 'This is the main content of the blog post with multiple features demonstrated.',
            'tags' => ['php', 'web-development', 'templates'],
            'comments' => [
                ['author' => 'User1', 'text' => 'Great post!'],
                ['author' => 'User2', 'text' => 'Very helpful, thanks!']
            ],
            'description' => 'This example demonstrates a complex template with multiple features.',
            'year' => date('Y'),
        ];

        $this->render('examples/blog_post', $data);
    }

    /**
     * Display dashboard example
     *
     * @return void
     */
    public function dashboard(): void
    {
        $data = [
            'title' => 'Dashboard Example',
            'user' => [
                'name' => 'Admin User',
                'role' => 'administrator',
                'last_login' => '2023-12-01 10:30:00'
            ],
            'stats' => [
                'total_users' => 1250,
                'active_sessions' => 89,
                'pending_orders' => 12
            ],
            'recent_activity' => [
                ['action' => 'User registered', 'time' => '2 minutes ago'],
                ['action' => 'Order placed', 'time' => '5 minutes ago'],
                ['action' => 'Payment processed', 'time' => '8 minutes ago']
            ],
            'notifications' => [
                ['type' => 'warning', 'message' => 'Server load is high'],
                ['type' => 'info', 'message' => 'New version available'],
                ['type' => 'error', 'message' => 'Database connection failed']
            ],
            'description' => 'This example demonstrates an advanced dashboard template.',
            'year' => date('Y'),
        ];

        $this->render('examples/dashboard', $data);
    }

    /**
     * Display CSS framework example
     *
     * @return void
     */
    public function cssFramework(): void
    {
        $data = [
            'title' => 'CSS Framework Examples',
            'description' => 'Explore the modern CSS framework features including grid system, components, and utilities optimized for dark themes.',
            'year' => date('Y'),
            'assetsUrl' => $this->baseUrl.'assets/',
        ];

        $this->render('examples/css_framework', $data);
    }

    /**
     * Display product page example
     *
     * @return void
     */
    public function product(): void
    {
        $data = [
            'title' => 'Product Page Example',
            'product' => [
                'id' => 12345,
                'name' => 'Wireless Bluetooth Headphones',
                'price' => 89.99,
                'description' => 'High-quality wireless headphones with noise cancellation and 30-hour battery life.',
                'image' => '/images/products/headphones.jpg',
                'in_stock' => true,
                'stock_quantity' => 25,
                'category' => 'Electronics',
                'brand' => 'AudioTech',
                'rating' => 4.5,
                'reviews' => 128
            ],
            'related_products' => [
                ['name' => 'USB-C Cable', 'price' => 12.99],
                ['name' => 'Phone Case', 'price' => 19.99],
                ['name' => 'Screen Protector', 'price' => 9.99]
            ],
            'user_preferences' => [
                'currency' => 'USD',
                'show_prices' => true,
                'compact_view' => false
            ],
            'description' => 'This example demonstrates an e-commerce product page template.',
            'year' => date('Y'),
        ];

        $this->render('examples/product_page', $data);
    }

    /**
     * Display Bootstrap-compatible components example
     *
     * @return void
     */
    public function components(): void
    {
        $data = [
            'title' => 'Bootstrap Components Examples',
            'description' => 'Interactive demonstrations of all Bootstrap 5.3.8 compatible components integrated with Phuse framework.',
            'year' => date('Y'),
            'assetsUrl' => $this->baseUrl.'assets/',
        ];

        $this->render('examples/components', $data);
    }
}
