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
        // Define some sample data
		$data = [
			'date' => date('Y')
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
