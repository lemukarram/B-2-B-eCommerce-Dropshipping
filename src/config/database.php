<?php

declare(strict_types=1);

/**
 * Database connection configuration.
 * All values sourced from config.php
 */
$config = require BASE_PATH . '/../config.php';

return [
    'host'     => $config['database']['host'] ?? 'localhost',
    'port'     => (int)($config['database']['port'] ?? 3306),
    'name'     => $config['database']['name'] ?? 'emag_pk',
    'user'     => $config['database']['user'] ?? 'root',
    'password' => $config['database']['password'] ?? '',
];
