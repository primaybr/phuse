<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use Core\Controller as Controller;

class Welcome extends Controller
{
    public function __construct()
    {
        parent::__construct();
		
    }

    public function index(): void
    {
        // Define some sample data
		$data = [
			'date' => date('Y')
		];
				
        $this->render('default/welcome', $data);
    }
}
