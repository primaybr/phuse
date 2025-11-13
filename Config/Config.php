<?php

declare(strict_types=1);

namespace Config;

return [
    'env' => 'development',
    'https' => false, // only use it for development on local machine
    'site' => [
        'baseUrl' => 'vertex',
        'adminUrl' => 'admin',
        'assetsUrl' => 'assets',
        'title' => 'Vertex CMS',
        'imgUrl'  => 'vertex/',
        'metaTitle' => 'Vertex CMS - Your Solution for Advanced Projects.',
        'metaDescription' => 'Vertex CMS - Your Solution for Advanced Projects.',
        'metaKeywords' => 'vertex cms, vertex'
    ]
];