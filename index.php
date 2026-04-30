<?php
declare(strict_types=1);

/**
 * Front controller / router
 * All URLs are routed through this file via .htaccess or PHP built-in server.
 */
require_once __DIR__ . '/includes/bootstrap.php';

// ─── Block installer if locked ────────────────────────────────────────────────
if (file_exists(__DIR__ . '/installer.lock') && basename($_SERVER['PHP_SELF'] ?? '') === 'installer.php') {
    http_response_code(403);
    die('Installer is locked.');
}

// ─── Route page requests ──────────────────────────────────────────────────────
$page = get_str('page') ?: 'dashboard';

$routes = [
    'dashboard'   => 'dashboard.php',
    'upload'      => 'upload.php',
    'add_signers' => 'add_signers.php',
    'sent'        => 'sent.php',
    'documents'   => 'documents.php',
    'document'    => 'document_detail.php',
    'sign'        => 'sign.php',
];

if (!array_key_exists($page, $routes)) {
    include PAGES_PATH . '/error404.php';
    exit;
}

$pageFile = PAGES_PATH . '/' . $routes[$page];
if (!file_exists($pageFile)) {
    include PAGES_PATH . '/error404.php';
    exit;
}

include $pageFile;
