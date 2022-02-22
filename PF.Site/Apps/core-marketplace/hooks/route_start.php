<?php

\Core\Api\ApiManager::register([
    'marketplace/:id' => [
        'api_service' => 'marketplace.api',
        'maps' => [
            'get' => 'get',
            'put' => 'put',
            'delete' => 'delete'
        ],
        'where' => ['id' => '\d+']
    ],
    'marketplace' => [
        'api_service' => 'marketplace.api',
        'maps' => [
            'get' => 'gets',
            'post' => 'post'
        ]
    ],
]);
