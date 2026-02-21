<?php

return [
    // Route configuration
    'route_prefix' => '',
    'middleware' => ['web'],

    // Asset publish path (used by views)
    'asset_path' => 'vendor/role-permission',

    // Default constants used in views/controllers/helpers
    'constants' => [
        'SCRIPT_VERSION' => env('ROLE_PERMISSION_SCRIPT_VERSION', (string) time()),
        'UN_AUTHORIZE_MSG' => 'Un authorize Access',
        'ERROR_MESSAGE' => 'An unexpected error occurred. Please try again later',
        'IS_SHOW_ALERT' => true,
        'IS_TOAST_ALERT' => false,
        'SHOW_HAMBURG' => true,
        'ACTIVE' => 'Active',
        'INACTIVE' => 'In-Active',
        'UI_DATE_FORMAT' => 'D, d M Y',
        'CACHE_KEY_CLEAR_TIME' => 2000,
        'ERROR_CODE_ARRAY' => [403, 404, 422, 500],
        'SUPER_ADMIN_ROLE_ID' => 1,
        'ADMIN_ROLE_ID' => 2,
        'POS_ROLE_ID' => 3,
        'CUSTOMER_ROLE_ID' => 4,
        'DIGITAL_PARTNER_ROLE_ID' => 5,
    ],
];
