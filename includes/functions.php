<?php
declare(strict_types=1);

/**
 * General-purpose helper functions.
 */

// ─── Logging ──────────────────────────────────────────────────────────────────

function log_message(string $level, string $message, array $context = []): void {
    static $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
    $threshold = $levels[LOG_LEVEL] ?? 0;
    if (($levels[$level] ?? 0) < $threshold) {
        return;
    }
    $ctx  = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $line = sprintf('[%s] [%s] %s%s', date('Y-m-d H:i:s'), strtoupper($level), $message, $ctx) . PHP_EOL;
    @file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

function log_debug(string $msg, array $ctx = []): void   { log_message('debug',   $msg, $ctx); }
function log_info(string $msg,  array $ctx = []): void   { log_message('info',    $msg, $ctx); }
function log_warn(string $msg,  array $ctx = []): void   { log_message('warning', $msg, $ctx); }
function log_error(string $msg, array $ctx = []): void   { log_message('error',   $msg, $ctx); }

// ─── Formatting ───────────────────────────────────────────────────────────────

function format_bytes(int $bytes): string {
    if ($bytes < 1024)        { return $bytes . ' B'; }
    if ($bytes < 1048576)     { return round($bytes / 1024, 1) . ' KB'; }
    if ($bytes < 1073741824)  { return round($bytes / 1048576, 1) . ' MB'; }
    return round($bytes / 1073741824, 1) . ' GB';
}

function format_datetime(string $datetime): string {
    try {
        $dt = new DateTimeImmutable($datetime);
        return $dt->format('d.m.Y H:i');
    } catch (Exception) {
        return $datetime;
    }
}

function format_date(string $datetime): string {
    try {
        $dt = new DateTimeImmutable($datetime);
        return $dt->format('d.m.Y');
    } catch (Exception) {
        return $datetime;
    }
}

function time_ago(string $datetime): string {
    try {
        $dt   = new DateTimeImmutable($datetime);
        $diff = (new DateTimeImmutable())->diff($dt);
        if ($diff->days === 0)  { return 'tänään'; }
        if ($diff->days === 1)  { return 'eilen'; }
        if ($diff->days < 7)   { return $diff->days . ' päivää sitten'; }
        if ($diff->days < 30)  { return (int)($diff->days / 7) . ' viikkoa sitten'; }
        if ($diff->days < 365) { return (int)($diff->days / 30) . ' kuukautta sitten'; }
        return (int)($diff->days / 365) . ' vuotta sitten';
    } catch (Exception) {
        return $datetime;
    }
}

// ─── Status labels (Finnish) ──────────────────────────────────────────────────

function status_label(string $status): string {
    return match ($status) {
        'draft'    => 'Luonnos',
        'sent'     => 'Lähetetty',
        'signed'   => 'Allekirjoitettu',
        'rejected' => 'Hylätty',
        'expired'  => 'Vanhentunut',
        default    => ucfirst($status),
    };
}

function status_class(string $status): string {
    return match ($status) {
        'draft'    => 'badge-draft',
        'sent'     => 'badge-sent',
        'signed'   => 'badge-signed',
        'rejected' => 'badge-rejected',
        'expired'  => 'badge-expired',
        default    => 'badge-default',
    };
}

function signer_status_label(string $status): string {
    return match ($status) {
        'pending'  => 'Odottaa',
        'viewed'   => 'Katsottu',
        'signed'   => 'Allekirjoitettu',
        'rejected' => 'Hylätty',
        default    => ucfirst($status),
    };
}

function signer_status_class(string $status): string {
    return match ($status) {
        'pending'  => 'badge-draft',
        'viewed'   => 'badge-sent',
        'signed'   => 'badge-signed',
        'rejected' => 'badge-rejected',
        default    => 'badge-default',
    };
}

// ─── URL helpers ─────────────────────────────────────────────────────────────

function page_url(string $page, array $params = []): string {
    $q = $params ? '&' . http_build_query($params) : '';
    return '/?page=' . urlencode($page) . $q;
}

function asset(string $path): string {
    return '/assets/' . ltrim($path, '/');
}

// ─── File helpers ─────────────────────────────────────────────────────────────

function safe_filename(string $original): string {
    $ext  = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    return sprintf('%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(4)), $ext);
}

function mime_icon(string $mime): string {
    return match (true) {
        str_contains($mime, 'pdf')  => '📄',
        str_contains($mime, 'image')=> '🖼️',
        default                      => '📎',
    };
}

// ─── Pagination helper ────────────────────────────────────────────────────────

function paginate(int $total, int $page, int $perPage = ITEMS_PER_PAGE): array {
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));
    $offset     = ($page - 1) * $perPage;
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
    ];
}
