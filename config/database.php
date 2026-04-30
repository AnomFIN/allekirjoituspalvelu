<?php
declare(strict_types=1);

/**
 * Database Configuration
 * Values can be overridden via environment variables.
 */

define('DB_HOST',    getenv('DB_HOST')    ?: '127.0.0.1');
define('DB_PORT',    (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME',    getenv('DB_NAME')    ?: 'allekirjoituspalvelu');
define('DB_USER',    getenv('DB_USER')    ?: 'app');
define('DB_PASS',    getenv('DB_PASS')    ?: 'app');
define('DB_CHARSET', 'utf8mb4');

define('DB_DSN', sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
));

define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 5,
]);
