<?php
declare(strict_types=1);

/**
 * Application Configuration
 * Allekirjoituspalvelu — e-signing service
 */

// ─── Environment ──────────────────────────────────────────────────────────────
define('APP_NAME',    'Allekirjoituspalvelu');
define('APP_VERSION', '2.0.0');
define('APP_ENV',     getenv('APP_ENV') ?: 'development');   // development | production
define('APP_DEBUG',   APP_ENV === 'development');
define('APP_URL',     getenv('APP_URL')  ?: 'http://127.0.0.1:8000');

// ─── Paths ────────────────────────────────────────────────────────────────────
define('BASE_PATH',   dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('CLASS_PATH',  BASE_PATH . '/classes');
define('PAGES_PATH',  BASE_PATH . '/pages');
define('ACTIONS_PATH',BASE_PATH . '/actions');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH',BASE_PATH . '/uploads');
define('LOGS_PATH',   BASE_PATH . '/logs');

// ─── Upload limits ────────────────────────────────────────────────────────────
define('MAX_UPLOAD_BYTES',    20 * 1024 * 1024);           // 20 MB
define('ALLOWED_MIME_TYPES',  ['application/pdf', 'image/png', 'image/jpeg']);
define('ALLOWED_EXTENSIONS',  ['pdf', 'png', 'jpg', 'jpeg']);

// ─── Security ─────────────────────────────────────────────────────────────────
define('SESSION_NAME',        'allekirjoitus_session');
define('CSRF_TOKEN_LENGTH',   32);
define('SIGN_TOKEN_LENGTH',   48);
define('TOKEN_EXPIRY_HOURS',  72);                          // signing link validity

// ─── Pagination ───────────────────────────────────────────────────────────────
define('ITEMS_PER_PAGE', 20);

// ─── Logging ──────────────────────────────────────────────────────────────────
define('LOG_FILE',  LOGS_PATH . '/app.log');
define('LOG_LEVEL', APP_DEBUG ? 'debug' : 'error');        // debug | info | warning | error
