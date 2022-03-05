<?php

\Core\Api\ApiManager::register([
    'groups/:id' => [
        'api_service' => 'groups.api',
        'maps' => [
            'get' => 'get',
            'put' => 'put',
            'delete' => 'delete'
        ],
        'where' => ['id' => '\d+']
    ],
    'groups' => [
        'api_service' => 'groups.api',
        'maps' => [
            'get' => 'gets',
            'post' => 'post'
        ]
    ]
]);