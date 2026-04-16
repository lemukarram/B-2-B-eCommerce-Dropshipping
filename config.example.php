<?php

/**
 * EMAG.PK Configuration Template
 * 
 * Copy this file to 'config.php' and update with your server-specific settings.
 */

return [
    'app' => [
        'name'     => 'EMAG.PK',
        'env'      => 'production', // 'development' or 'production'
        'debug'    => false,        // Set to true to see detailed error stack traces
        'url'      => 'https://yourdomain.com',
        'key'      => 'change_me_to_a_32_char_random_string',
        'timezone' => 'Asia/Karachi',
    ],
    'database' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'your_db_name',
        'user'     => 'your_db_user',
        'password' => 'your_db_password',
    ]
];
