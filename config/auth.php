<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'hash' => True,
        ],
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'hash' => True,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\User::class
        ]
    ]
];