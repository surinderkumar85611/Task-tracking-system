<?php

use App\Models\User;
use App\Models\SuperAdmin;

return [



    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],


    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'super_admin' => [
            'driver' => 'session',
            'provider' => 'super_admins',
        ],
    ],


    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        'super_admins' => [
            'driver' => 'eloquent',
            'model' => SuperAdmin::class,
        ],
    ],



    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 10,
            'throttle' => 60,
        ],

        
    ],

    

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];