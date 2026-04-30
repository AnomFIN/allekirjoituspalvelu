<?php
declare(strict_types=1);

/**
 * Bootstrap — loads config, error handler, functions, starts session.
 * Every PHP entry point (index.php, installer.php, action handlers) includes this.
 */

// Resolve base path relative to this file's location.
define('BASE_PATH_DEFINED', true);

// Load configuration first (defines constants used everywhere).
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Error handler needs APP_DEBUG and LOG_FILE.
require_once __DIR__ . '/error_handler.php';

// General helpers.
require_once __DIR__ . '/functions.php';

// Security helpers.
require_once __DIR__ . '/security.php';

// ─── PHP runtime settings ─────────────────────────────────────────────────────
error_reporting(APP_DEBUG ? E_ALL : E_ERROR | E_WARNING);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', LOG_FILE);
ini_set('date.timezone', 'Europe/Helsinki');

// ─── Session ─────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (APP_ENV === 'production'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ─── Autoload classes ─────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ─── Security headers ─────────────────────────────────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

log_debug('Bootstrap complete', ['env' => APP_ENV]);
