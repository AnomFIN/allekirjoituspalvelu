#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * check_project.php — QA / Project health checker
 *
 * Verifies that all required files exist, PHP syntax is valid, DB tables
 * and necessary columns exist, and environment is correctly configured.
 *
 * Usage:  /usr/bin/php scripts/check_project.php
 *         /usr/bin/php scripts/check_project.php --json
 */

$outputJson = in_array('--json', $argv ?? [], true);

// ─── Bootstrap (no session) ────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

// ─── Check registry ────────────────────────────────────────────────────────────

$checks  = [];
$passed  = 0;
$failed  = 0;
$warned  = 0;

function chk(string $category, string $label, bool $ok, string $detail = '', bool $warn = false): void
{
    global $checks, $passed, $failed, $warned;
    $status = $ok ? 'pass' : ($warn ? 'warn' : 'fail');
    $checks[] = compact('category', 'label', 'status', 'detail');
    if ($ok)         $passed++;
    elseif ($warn)   $warned++;
    else             $failed++;
}

// ─── 1. Required files ────────────────────────────────────────────────────────

$requiredFiles = [
    'index.php',
    'installer.php',
    'config/config.php',
    'config/database.php',
    'includes/bootstrap.php',
    'includes/error_handler.php',
    'includes/functions.php',
    'includes/security.php',
    'includes/layout.php',
    'includes/header.php',
    'includes/footer.php',
    'classes/Database.php',
    'classes/DocumentRepository.php',
    'classes/SignerRepository.php',
    'classes/ActivityRepository.php',
    'classes/UploadService.php',
    'classes/MailerService.php',
    'classes/ValidationService.php',
    'pages/dashboard.php',
    'pages/upload.php',
    'pages/add_signers.php',
    'pages/sent.php',
    'pages/documents.php',
    'pages/document_detail.php',
    'pages/sign.php',
    'pages/error404.php',
    'pages/error500.php',
    'actions/upload_document.php',
    'actions/save_signers.php',
    'actions/send_request.php',
    'actions/sign_document.php',
    'actions/reject_document.php',
    'actions/remind_signer.php',
    'assets/css/style.css',
    'assets/js/app.js',
];

foreach ($requiredFiles as $rel) {
    $path = BASE_PATH . '/' . $rel;
    chk('Tiedostot', $rel, file_exists($path), file_exists($path) ? '' : 'PUUTTUU');
}

// ─── 2. PHP syntax check ──────────────────────────────────────────────────────

$phpFiles = array_filter($requiredFiles, fn($f) => str_ends_with($f, '.php'));
foreach ($phpFiles as $rel) {
    $path = BASE_PATH . '/' . $rel;
    if (!file_exists($path)) continue;
    $out  = shell_exec('/usr/bin/php -l ' . escapeshellarg($path) . ' 2>&1');
    $ok   = str_contains($out ?? '', 'No syntax errors');
    chk('PHP Syntax', $rel, $ok, $ok ? '' : trim($out ?? 'lint failed'));
}

// ─── 3. PHP extensions ───────────────────────────────────────────────────────

$requiredExts = ['pdo', 'pdo_mysql', 'mbstring', 'fileinfo', 'json', 'session'];
foreach ($requiredExts as $ext) {
    chk('PHP Extensions', $ext, extension_loaded($ext));
}

// ─── 4. Writable directories ─────────────────────────────────────────────────

foreach ([UPLOADS_PATH, LOGS_PATH] as $dir) {
    $rel = str_replace(BASE_PATH . '/', '', $dir);
    $exists   = is_dir($dir);
    $writable = $exists && is_writable($dir);
    chk('Hakemistot', $rel . ' (exists)',    $exists,   $exists ? '' : 'hakemisto puuttuu');
    chk('Hakemistot', $rel . ' (writable)',  $writable, $writable ? '' : 'ei kirjoitusoikeutta', !$exists);
}

// ─── 5. Database connectivity & schema ───────────────────────────────────────

$dbOk = false;
try {
    $pdo  = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
    $dbOk = true;
    chk('Tietokanta', 'Yhteys ' . DB_HOST . ':' . DB_PORT . '/' . DB_NAME, true);
} catch (PDOException $e) {
    chk('Tietokanta', 'Yhteys', false, $e->getMessage());
}

if ($dbOk) {
    $requiredTables = ['documents', 'signers', 'signing_tokens', 'document_events', 'users', 'settings'];
    foreach ($requiredTables as $t) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?"
        );
        $stmt->execute([$t]);
        $exists = (int)$stmt->fetchColumn() > 0;
        chk('Tietokanta Taulut', "`$t`", $exists, $exists ? '' : 'taulu puuttuu — aja installer.php');
    }

    // Check critical columns
    $criticalCols = [
        'documents'     => ['id','uuid','title','original_filename','stored_filename','file_path','mime_type','size_bytes','status','sender_name','sender_email','created_at'],
        'signers'       => ['id','document_id','name','email','status','signed_at','created_at'],
        'signing_tokens'=> ['id','signer_id','token','expires_at','used_at'],
        'document_events'=> ['id','document_id','event','description','actor_email','created_at'],
    ];
    foreach ($criticalCols as $table => $cols) {
        foreach ($cols as $col) {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?"
            );
            $stmt->execute([$table, $col]);
            $exists = (int)$stmt->fetchColumn() > 0;
            chk('DB Sarakkeet', "`$table`.`$col`", $exists, $exists ? '' : 'puuttuu — aja installer.php');
        }
    }
}

// ─── 6. installer.lock ────────────────────────────────────────────────────────

$lockExists = file_exists(BASE_PATH . '/installer.lock');
chk('Turvallisuus', 'installer.lock (asennus lukittu)', $lockExists,
    $lockExists ? '' : 'Aja installer.php ja lukitse', !$lockExists
);

// ─── 7. Security checks ───────────────────────────────────────────────────────

chk('Turvallisuus', 'uploads/.htaccess',
    file_exists(BASE_PATH . '/uploads/.htaccess'), '', false
);
chk('Turvallisuus', 'APP_ENV != production (devcont.)',
    APP_ENV !== 'production', APP_ENV, true
);

// ─── Output ───────────────────────────────────────────────────────────────────

$total = $passed + $failed + $warned;

if ($outputJson) {
    echo json_encode([
        'summary' => compact('total', 'passed', 'failed', 'warned'),
        'checks'  => $checks,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo PHP_EOL;
    exit($failed > 0 ? 1 : 0);
}

// ── Terminal output ────────────────────────────────────────────────────────────

$width   = 78;
$green   = "\033[32m";
$red     = "\033[31m";
$yellow  = "\033[33m";
$reset   = "\033[0m";
$bold    = "\033[1m";

echo PHP_EOL;
echo str_repeat('─', $width) . PHP_EOL;
echo "$bold   Allekirjoituspalvelu — check_project.php v2.0$reset" . PHP_EOL;
echo str_repeat('─', $width) . PHP_EOL . PHP_EOL;

$currentCat = '';
foreach ($checks as $c) {
    if ($c['category'] !== $currentCat) {
        $currentCat = $c['category'];
        echo PHP_EOL . "$bold  ▸ $currentCat$reset" . PHP_EOL;
    }
    $icon = match($c['status']) {
        'pass'  => $green  . '  ✓' . $reset,
        'fail'  => $red    . '  ✗' . $reset,
        'warn'  => $yellow . '  ⚠' . $reset,
        default => '  ?',
    };
    $label  = str_pad(mb_substr($c['label'], 0, 55), 56);
    $detail = $c['detail'] ? ' ' . mb_substr($c['detail'], 0, 25) : '';
    echo "$icon  $label$detail" . PHP_EOL;
}

echo PHP_EOL . str_repeat('─', $width) . PHP_EOL;
$summaryColor = $failed > 0 ? $red : ($warned > 0 ? $yellow : $green);
echo "  $bold{$summaryColor}Yhteensä: $total   ✓ $passed   ✗ $failed   ⚠ $warned$reset" . PHP_EOL;
echo str_repeat('─', $width) . PHP_EOL . PHP_EOL;

exit($failed > 0 ? 1 : 0);
