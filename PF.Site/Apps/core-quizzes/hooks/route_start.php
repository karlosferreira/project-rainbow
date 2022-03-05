<?php

\Core\Api\ApiManager::register([
    'quiz/:id' => [
        'api_service' => 'quiz.api',
        'maps' => [
            'get' => 'get',
            'put' => 'put',
            'delete' => 'delete'
        ],
        'where' => ['id' => '\d+']
    ],
    'quiz' => [
        'api_service' => 'quiz.api',
        'maps' => [
            'get' => 'gets',
            'post' => 'post'
        ]
    ],
]);
