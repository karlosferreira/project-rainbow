<?php

\Core\Api\ApiManager::register([
    'music/song/:id' => [
        'api_service' => 'music.api',
        'maps' => [
            'get' => 'get',
            'put' => 'put',
            'delete' => 'delete'
        ],
        'where' => ['id'=>'\d+']
    ],
    'music/song' => [
        'api_service' => 'music.api',
        'maps' => [
            'get' => 'gets',
            'post' => 'post'
        ]
    ],
    'music/album/:id' => [
        'api_service' => 'music.api',
        'maps' => [
            'get' => 'getAlbum',
            'put' => 'putAlbum',
            'delete' => 'deleteAlbum'
        ],
        'where' => ['id'=>'\d+']
    ],
    'music/album' => [
        'api_service' => 'music.api',
        'maps' => [
            'get' => 'getAlbums',
            'post' => 'postAlbum'
        ]
    ],
    'music/playlist/:id' => [
        'api_service' => 'music.api',
        'maps' => [
            'get' => 'getPlaylist',
            'put' => 'putPlaylist',
            'delete' => 'deletePlaylist'
        ],
        'where' => ['id'=>'\d+']
    ],
    'music/playlist' => [
        'api_service' => 'music.api',
        'maps' => [
            'get' => 'getPlaylists',
            'post' => 'postPlaylist'
        ]
    ],
]);
