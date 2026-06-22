<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use Core\Controller as Controller;

/**
 * Welcome Controller
 *
 * This controller handles the welcome page and data posting functionality
 */
class Welcome extends Controller
{
    /**
     * Constructor for the Welcome controller
     */
    public function __construct()
    {
        parent::__construct();
		
    }

    /**
     * Display the welcome page
     *
     * @return void
     */
    public function index(): void
    {
        $data = [
            'date'    => date('Y'),
            'version' => '1.2.4',

            // Core feature cards shown on the welcome page
            'features' => [
                [
                    'icon'        => 'pi-layers',
                    'title'       => 'MVC Architecture',
                    'description' => 'Clean separation of Models, Views, and Controllers keeps code organised and testable.',
                ],
                [
                    'icon'        => 'pi-database',
                    'title'       => 'Active Record ORM',
                    'description' => 'Relationships, eager loading, soft deletes, scopes, and automatic validation out of the box.',
                ],
                [
                    'icon'        => 'pi-arrow-right',
                    'title'       => 'Smart Routing',
                    'description' => 'Clean URL routing with automatic detection for domain and subdirectory deployments.',
                ],
                [
                    'icon'        => 'pi-code',
                    'title'       => 'Template Engine',
                    'description' => 'Twig/Blade-inspired <code>{{variable}}</code> syntax. Inline CSS and JS are always safe.',
                ],
                [
                    'icon'        => 'pi-zap',
                    'title'       => 'Query Caching',
                    'description' => 'Intelligent query result caching with automatic invalidation on data changes.',
                ],
                [
                    'icon'        => 'pi-shield',
                    'title'       => 'Security Built-in',
                    'description' => 'CSRF protection, XSS-safe HTML generation, input sanitisation, and secure sessions.',
                ],
            ],

            // Quick-links shown in the "Try it out" section
            'examples' => [
                ['label' => 'Template Examples',  'url' => '/examples',              'badge' => 'Index'],
                ['label' => 'Inline CSS/JS Safety','url' => '/examples/inline-assets','badge' => 'New'],
                ['label' => 'Dashboard Demo',      'url' => '/examples/dashboard',    'badge' => 'UI'],
                ['label' => 'E-commerce Page',     'url' => '/examples/product',      'badge' => 'UI'],
                ['label' => 'CSS Framework',       'url' => '/examples/css-framework','badge' => 'Styles'],
                ['label' => 'JS Components',       'url' => '/examples/components',   'badge' => 'JS'],
            ],
        ];

        $this->render('default/welcome', $data);
    }

    /**
     * Handle POST request data
     *
     * @return void
     */
    public function postData(): void
    {
        // Handle POST request data
        $postData = $_POST;
        // Process the data as needed
        // ...
        // Return a response
        $response = [
            'success' => true,
            'message' => 'Data received successfully',
            'data' => $postData,
        ];

        echo json_encode($response);
    }
}
