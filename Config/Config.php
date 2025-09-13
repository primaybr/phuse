<?php

declare(strict_types=1);

namespace Config;

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
    ]
];