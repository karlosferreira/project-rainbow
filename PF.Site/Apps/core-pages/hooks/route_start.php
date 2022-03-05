<?php

\Core\Api\ApiManager::register([
    'pages/:id' => [
        'api_service' => 'pages.api',
        'maps' => [
            'get' => 'get',
            'put' => 'put',
            'delete' => 'delete'
        ],
        'where' => ['id' => '\d+']
    ],
    'pages' => [
        'api_service' => 'pages.api',
        'maps' => [
            'get' => 'gets',
            'post' => 'post'
        ]
    ]
]);
