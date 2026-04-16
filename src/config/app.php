<?php

declare(strict_types=1);

/**
 * Application configuration.
 * Values are read from config.php
 */
$config = require BASE_PATH . '/../config.php';

return [
    'name'     => $config['app']['name'] ?? 'EMAG.PK',
    'env'      => $config['app']['env'] ?? 'production',
    'debug'    => $config['app']['debug'] ?? false,
    'url'      => rtrim($config['app']['url'] ?? 'http://localhost', '/'),
    'key'      => $config['app']['key'] ?? '',
    'timezone' => $config['app']['timezone'] ?? 'Asia/Karachi',
];
