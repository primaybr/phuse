<?php

return [
    'env' => 'development',
	'https' => false, // only use it for development on local machine
    'site' => [
        'baseUrl' => 'http://localhost/phuse/',
        'adminUrl' => 'admin',
        'assetsUrl' => 'assets',
		'title' => 'Phuse',
		'imgUrl'  => 'http://localhost/phuse/',
		'metaTitle' => 'Phuse - PHP Framework',
		'metaDescription' => 'Phuse - PHP Framework',
		'metaKeywords' => 'phuse'
    ],
    'database' => ['default' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'dbname' => '',
        'prefix' => '',
    ],
    ],
];
