<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache expiration time in minutes
    |
    */
    'cache' => [
        'ttl' => env('PERMISSION_CACHE_TTL', 24 * 60), // 1 day
    ],

    /*
    |--------------------------------------------------------------------------
    | Role & Permission Settings
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'super_admin_id' => env('SUPER_ADMIN_ROLE_ID', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_permissions' => 'role_permissions',
        'master_menu' => 'master_menu',
    ],
];
