<?php

return [
    'env' => 'development',
	'https' => false, // only use it for development on local machine
    'site' => [
        'baseUrl' => 'phuse',
        'adminUrl' => 'admin',
        'assetsUrl' => 'assets',
		'title' => 'Phuse',
		'imgUrl'  => 'phuse/',
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
