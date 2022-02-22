<?php

\Core\Api\ApiManager::register([
    'forum/thread/:id' => [
        'api_service' => 'forum.api',
        'maps' => [
            'get' => 'getThread',
            'delete' => 'deleteThread',
            'put' => 'putThread'
        ],
        'where' => ['id' => '\d+']
    ],
    'forum/post/:id' => [
        'api_service' => 'forum.api',
        'maps' => [
            'get' => 'getPost',
            'delete' => 'deletePost',
            'put' => 'putPost'
        ],
        'where' => ['id' => '\d+']
    ],
    'forum/:id' => [
        'api_service' => 'forum.api',
        'maps' => [
            'get' => 'get',
        ],
        'where' => ['id' => '\d+']
    ],
    'forum/thread' => [
        'api_service' => 'forum.api',
        'maps' => [
            'get' => 'getThreads',
            'post' => 'postThread'
        ],
    ],
    'forum/post' => [
        'api_service' => 'forum.api',
        'maps' => [
            'get' => 'getPosts',
            'post' => 'postPost'
        ],
    ],
    'forum' => [
        'api_service' => 'forum.api',
        'maps' => [
            'get' => 'gets',
        ]
    ],
]);
