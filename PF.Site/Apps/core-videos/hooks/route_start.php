<?php

\Core\Api\ApiManager::register([
    'video/:id' => [
        'api_service' => 'v.api',
        'maps' => [
            'get' => 'get',
            'put' => 'put',
            'delete' => 'delete'
        ],
        'where' => ['id' => '\d+']
    ],
    'video' => [
        'api_service' => 'v.api',
        'maps' => [
            'get' => 'gets',
            'post' => 'post'
        ]
    ],
    'video/file' => [
        'api_service' => 'v.api',
        'maps' => [
            'post' => 'upload'
        ]
    ]
]);
