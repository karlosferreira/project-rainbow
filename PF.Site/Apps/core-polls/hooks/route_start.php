<?php

\Core\Api\ApiManager::register([
    'poll/:id' => [
        'api_service' => 'poll.api',
        'maps' => [
            'get' => 'get',
            'put' => 'put',
            'delete' => 'delete'
        ],
        'where' => ['id' => '\d+']
    ],
    'poll' => [
        'api_service' => 'poll.api',
        'maps' => [
            'get' => 'gets',
            'post' => 'post'
        ]
    ],
]);
