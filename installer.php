<?php
declare(strict_types=1);

/**
 * installer.php — Idempotent database installer for Allekirjoituspalvelu
 *
 * - Creates all 6 required tables if not present
 * - Adds missing columns to existing tables (safe migration)
 * - Creates indexes if not present
 * - Creates installer.lock on success to block re-running
 * - Shows a visual pass/fail report
 *
 * Usage: Visit /installer.php in a browser (no installer.lock must exist).
 */

define('INSTALLER_VERSION', '2.0.0');

// ─── Lock guard ───────────────────────────────────────────────────────────────
$lockFile = __DIR__ . '/installer.lock';
if (file_exists($lockFile)) {
    http_response_code(403);
    die(renderPage('Installer gesperrt', '<div style="text-align:center;padding:3rem">
        <p style="font-size:2rem">🔒</p>
        <h2>Asennus on jo suoritettu</h2>
        <p>Poista <code>installer.lock</code> jos haluat ajaa asennuksen uudelleen.</p>
        <a href="/" style="color:#4f46e5">← Takaisin sovellukseen</a>
    </div>'));
}

// ─── Bootstrap (minimal — DB not yet verified) ────────────────────────────────
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

define('STEP_OK',   '✅');
define('STEP_FAIL', '❌');
define('STEP_SKIP', '⏭️');
define('STEP_WARN', '⚠️');

$results = [];
$hasError = false;

// ─── Run installer on POST ────────────────────────────────────────────────────
$doInstall = ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['run'] ?? '') === '1');

if ($doInstall) {
    $results = runInstaller();
    $hasError = !empty(array_filter($results, fn($r) => $r['status'] === 'fail'));

    if (!$hasError) {
        // Create lock file
        file_put_contents($lockFile, json_encode([
            'installed_at' => date('c'),
            'version'      => INSTALLER_VERSION,
            'php'          => PHP_VERSION,
        ]));
    }
}

echo renderPage('Installer', buildHtml($doInstall, $results, $hasError));
exit;

// ─── Installer logic ──────────────────────────────────────────────────────────

function runInstaller(): array
{
    $results = [];

    // 1. Test DSN
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        $results[] = result('ok',   'Tietokantayhteys',    DB_HOST . ':' . DB_PORT . ' / ' . DB_NAME);
    } catch (PDOException $e) {
        $results[] = result('fail', 'Tietokantayhteys',    $e->getMessage());
        return $results;
    }

    // 2. Create / alter all tables
    foreach (getSchema() as $tableDef) {
        $results = array_merge($results, applyTable($pdo, $tableDef));
    }

    // 3. Uploads / logs directories writable
    foreach ([UPLOADS_PATH, LOGS_PATH] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (is_writable($dir)) {
            $results[] = result('ok',   'Hakemisto kirjoitettavissa', $dir);
        } else {
            $results[] = result('warn', 'Hakemisto EI kirjoitettavissa', $dir . ' — chmod 0775');
        }
    }

    // 4. PHP extensions
    foreach (['pdo_mysql', 'mbstring', 'fileinfo'] as $ext) {
        if (extension_loaded($ext)) {
            $results[] = result('ok',   "PHP-laajennus: $ext", 'ladattu');
        } else {
            $results[] = result('fail', "PHP-laajennus: $ext", 'PUUTTUU');
        }
    }

    return $results;
}

/**
 * Apply a table definition: create if missing, rename legacy columns, ALTER to add missing columns/indexes.
 */
function applyTable(PDO $pdo, array $def): array
{
    $results = [];
    $table   = $def['table'];

    // Create table if not exists
    try {
        $pdo->exec($def['create']);
        $exists = tableExists($pdo, $table);
        $results[] = result('ok', "Taulu `$table`", $exists ? 'jo olemassa' : 'luotu');
    } catch (PDOException $e) {
        $results[] = result('fail', "Taulu `$table`", $e->getMessage());
        return $results;
    }

    // Rename legacy columns: ['old_col', 'new_col', 'full_ddl']
    foreach ($def['renames'] ?? [] as $rename) {
        [$oldCol, $newCol, $newDdl] = $rename;
        if (columnExists($pdo, $table, $oldCol) && !columnExists($pdo, $table, $newCol)) {
            try {
                $pdo->exec("ALTER TABLE `$table` CHANGE COLUMN `$oldCol` $newDdl");
                $results[] = result('ok', "Sarake `$table`.`$oldCol` → `$newCol`", 'nimetty uudelleen');
            } catch (PDOException $e) {
                $results[] = result('fail', "Sarake `$table`.`$oldCol` → `$newCol`", $e->getMessage());
            }
        } elseif (columnExists($pdo, $table, $newCol)) {
            $results[] = result('skip', "Sarake `$table`.`$newCol`", 'jo olemassa');
        }
    }

    // Add any missing columns
    foreach ($def['columns'] ?? [] as $col => $ddl) {
        if (!columnExists($pdo, $table, $col)) {
            try {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN $ddl");
                $results[] = result('ok',   "Sarake `$table`.`$col`", 'lisätty');
            } catch (PDOException $e) {
                $results[] = result('fail', "Sarake `$table`.`$col`", $e->getMessage());
            }
        } else {
            $results[] = result('skip', "Sarake `$table`.`$col`", 'jo olemassa');
        }
    }

    // Add any missing indexes
    foreach ($def['indexes'] ?? [] as $idxName => $idxDdl) {
        if (!indexExists($pdo, $table, $idxName)) {
            try {
                $pdo->exec("ALTER TABLE `$table` ADD $idxDdl");
                $results[] = result('ok',   "Indeksi `$table`.`$idxName`", 'lisätty');
            } catch (PDOException $e) {
                $results[] = result('fail', "Indeksi `$table`.`$idxName`", $e->getMessage());
            }
        } else {
            $results[] = result('skip', "Indeksi `$table`.`$idxName`", 'jo olemassa');
        }
    }

    return $results;
}

// ─── Schema definition ─────────────────────────────────────────────────────────

function getSchema(): array
{
    return [
        // ── documents ────────────────────────────────────────────────────────
        [
            'table'  => 'documents',
            'create' => "CREATE TABLE IF NOT EXISTS `documents` (
                `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                `uuid`              VARCHAR(64)     NOT NULL DEFAULT '',
                `title`             VARCHAR(200)    NOT NULL DEFAULT '',
                `original_filename` VARCHAR(400)    NOT NULL DEFAULT '',
                `stored_filename`   VARCHAR(400)    NOT NULL DEFAULT '',
                `file_path`         VARCHAR(800)    NOT NULL DEFAULT '',
                `mime_type`         VARCHAR(100)    NOT NULL DEFAULT '',
                `size_bytes`        BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `status`            ENUM('draft','sent','signed','rejected','expired')
                                    NOT NULL DEFAULT 'draft',
                `sender_name`       VARCHAR(150)    NOT NULL DEFAULT '',
                `sender_email`      VARCHAR(200)    NOT NULL DEFAULT '',
                `message_body`      TEXT            NULL,
                `created_by`        INT UNSIGNED    NULL,
                `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            'columns' => [
                'uuid'        => "`uuid`        VARCHAR(64)     NOT NULL DEFAULT '' AFTER `id`",
                'file_path'   => "`file_path`   VARCHAR(800)    NOT NULL DEFAULT '' AFTER `stored_filename`",
                'created_by'  => "`created_by`  INT UNSIGNED    NULL AFTER `message_body`",
                'created_at'  => "`created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_by`",
                'updated_at'  => "`updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
            ],
            'indexes' => [
                'idx_documents_status'     => 'INDEX `idx_documents_status` (`status`)',
                'idx_documents_uuid'       => 'UNIQUE INDEX `idx_documents_uuid` (`uuid`)',
                'idx_documents_created_at' => 'INDEX `idx_documents_created_at` (`created_at`)',
            ],
        ],

        // ── signers ──────────────────────────────────────────────────────────
        [
            'table'  => 'signers',
            'create' => "CREATE TABLE IF NOT EXISTS `signers` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `document_id` INT UNSIGNED NOT NULL,
                `name`        VARCHAR(150) NOT NULL DEFAULT '',
                `email`       VARCHAR(200) NOT NULL DEFAULT '',
                `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `status`      ENUM('pending','viewed','signed','rejected')
                              NOT NULL DEFAULT 'pending',
                `signed_at`   DATETIME NULL,
                `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            'renames' => [
                // Old first-migration columns → new names
                ['signer_name',  'name',  '`name`  VARCHAR(150) NOT NULL DEFAULT \'\''],
                ['signer_email', 'email', '`email` VARCHAR(200) NOT NULL DEFAULT \'\''],
            ],
            'columns' => [
                'signed_at'  => '`signed_at`  DATETIME NULL AFTER `status`',
                'updated_at' => '`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`',
            ],
            'indexes' => [
                'idx_signers_document_id' => 'INDEX `idx_signers_document_id` (`document_id`)',
                'idx_signers_status'      => 'INDEX `idx_signers_status` (`status`)',
                'idx_signers_email'       => 'INDEX `idx_signers_email` (`email`)',
            ],
        ],

        // ── signing_tokens ────────────────────────────────────────────────────
        [
            'table'  => 'signing_tokens',
            'create' => "CREATE TABLE IF NOT EXISTS `signing_tokens` (
                `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `signer_id`  INT UNSIGNED  NOT NULL,
                `token`      VARCHAR(200)  NOT NULL,
                `expires_at` DATETIME      NOT NULL,
                `used_at`    DATETIME      NULL,
                `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            'columns' => [],
            'indexes' => [
                'idx_signing_tokens_token'     => 'UNIQUE INDEX `idx_signing_tokens_token` (`token`)',
                'idx_signing_tokens_signer_id' => 'INDEX `idx_signing_tokens_signer_id` (`signer_id`)',
                'idx_signing_tokens_expires_at'=> 'INDEX `idx_signing_tokens_expires_at` (`expires_at`)',
            ],
        ],

        // ── document_events ───────────────────────────────────────────────────
        [
            'table'  => 'document_events',
            'create' => "CREATE TABLE IF NOT EXISTS `document_events` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `document_id` INT UNSIGNED NOT NULL,
                `event`       VARCHAR(100) NOT NULL DEFAULT '',
                `description` TEXT         NULL,
                `actor_email` VARCHAR(200) NOT NULL DEFAULT '',
                `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            'renames' => [
                // Old first-migration columns → new names
                ['event_type', 'event',       '`event`       VARCHAR(100) NOT NULL DEFAULT \'\''],
                ['event_text', 'description', '`description` TEXT NULL'],
            ],
            'columns' => [
                'actor_email' => "`actor_email` VARCHAR(200) NOT NULL DEFAULT ''",
            ],
            'indexes' => [
                'idx_doc_events_document_id' => 'INDEX `idx_doc_events_document_id` (`document_id`)',
                'idx_doc_events_created_at'  => 'INDEX `idx_doc_events_created_at` (`created_at`)',
            ],
        ],

        // ── users ─────────────────────────────────────────────────────────────
        [
            'table'  => 'users',
            'create' => "CREATE TABLE IF NOT EXISTS `users` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name`         VARCHAR(150) NOT NULL DEFAULT '',
                `email`        VARCHAR(200) NOT NULL,
                `password_hash`VARCHAR(255) NOT NULL DEFAULT '',
                `role`         ENUM('admin','user') NOT NULL DEFAULT 'user',
                `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
                `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            'columns' => [],
            'indexes' => [
                'idx_users_email' => 'UNIQUE INDEX `idx_users_email` (`email`)',
            ],
        ],

        // ── settings ──────────────────────────────────────────────────────────
        [
            'table'  => 'settings',
            'create' => "CREATE TABLE IF NOT EXISTS `settings` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `key_name`   VARCHAR(100) NOT NULL,
                `value`      TEXT         NOT NULL,
                `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            'columns' => [],
            'indexes' => [
                'idx_settings_key_name' => 'UNIQUE INDEX `idx_settings_key_name` (`key_name`)',
            ],
        ],
    ];
}

// ─── DB helpers ────────────────────────────────────────────────────────────────

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?"
    );
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?"
    );
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function indexExists(PDO $pdo, string $table, string $indexName): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=?"
    );
    $stmt->execute([$table, $indexName]);
    return (int)$stmt->fetchColumn() > 0;
}

// ─── Result helpers ────────────────────────────────────────────────────────────

function result(string $status, string $label, string $detail = ''): array
{
    return ['status' => $status, 'label' => $label, 'detail' => $detail];
}

// ─── HTML rendering ────────────────────────────────────────────────────────────

function buildHtml(bool $ran, array $results, bool $hasError): string
{
    $icon = ['ok' => STEP_OK, 'fail' => STEP_FAIL, 'skip' => STEP_SKIP, 'warn' => STEP_WARN];

    $body = '<div class="installer-card">';
    $body .= '<div class="installer-title">✍️ ' . APP_NAME . ' — Asennus</div>';
    $body .= '<div class="installer-subtitle">Versio ' . INSTALLER_VERSION . ' · PHP ' . PHP_VERSION . ' · DB: ' . DB_HOST . '/' . DB_NAME . '</div>';

    if (!$ran) {
        $body .= '
        <p>Tämä skripti luo tai päivittää tietokantarakenteet. Toimenpide on idempotentti
           (turvallinen ajaa uudelleen).</p>
        <p style="margin-top:.75rem; color:#64748b; font-size:.875rem">
            Suorituksen jälkeen luodaan <code>installer.lock</code>, joka estää asennuksen
            uudelleenajamisen.
        </p>
        <form method="post" action="/installer.php" style="margin-top:2rem">
            <input type="hidden" name="run" value="1">
            <button type="submit" class="btn btn-primary btn-lg">Käynnistä asennus</button>
            <a href="/" class="btn btn-ghost" style="margin-left:.75rem">Peruuta</a>
        </form>';
    } else {
        $total    = count($results);
        $okCount  = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
        $failCount= count(array_filter($results, fn($r) => $r['status'] === 'fail'));

        if ($hasError) {
            $body .= '<div class="flash flash-error" style="margin:0 0 1.5rem">
                ❌ Asennus epäonnistui — korjaa virheet ja yritä uudelleen.
            </div>';
        } else {
            $body .= '<div class="flash flash-success" style="margin:0 0 1.5rem">
                ✅ Asennus valmis! ' . $okCount . '/' . $total . ' toimenpidettä onnistui.
                Installer on nyt lukittu (<code>installer.lock</code>).
            </div>';
        }

        $body .= '<div style="margin-bottom:1.5rem">';
        foreach ($results as $r) {
            $ic    = $icon[$r['status']] ?? '•';
            $cls   = match($r['status']) {
                'ok'   => 'check-ok',
                'fail' => 'check-fail',
                'warn' => 'check-warn',
                default=> 'check-warn',
            };
            $body .= '<div class="check-row">'
                .'<span class="' . $cls . '">' . $ic . '</span>'
                .'<span class="check-label">' . htmlspecialchars($r['label']) . '</span>'
                .'<span class="check-detail">' . htmlspecialchars($r['detail']) . '</span>'
                .'</div>';
        }
        $body .= '</div>';

        if (!$hasError) {
            $body .= '<a href="/" class="btn btn-primary">← Avaa sovellus</a>';
        } else {
            $body .= '<form method="post" action="/installer.php">
                <input type="hidden" name="run" value="1">
                <button type="submit" class="btn btn-primary">Yritä uudelleen</button>
            </form>';
        }
    }

    $body .= '</div>'; // .installer-card
    return '<div class="installer-shell">' . $body . '</div>';
}

function renderPage(string $title, string $content): string
{
    $cssPath = __DIR__ . '/assets/css/style.css';
    $inlineCss = file_exists($cssPath) ? '' :
        '<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9}</style>';
    $cssTag = file_exists($cssPath)
        ? '<link rel="stylesheet" href="/assets/css/style.css">'
        : $inlineCss;
    return '<!DOCTYPE html><html lang="fi"><head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>' . htmlspecialchars($title) . ' — ' . APP_NAME . '</title>' . $cssTag . '
        </head><body>' . $content . '</body></html>';
}
