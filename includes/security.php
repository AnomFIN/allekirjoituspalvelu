<?php
declare(strict_types=1);

/**
 * Security helpers — CSRF, input sanitization, redirect guards.
 */

// ─── CSRF ─────────────────────────────────────────────────────────────────────

/**
 * Return (or create) the current CSRF token stored in the session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/**
 * Validate the CSRF token from POST data; die with 403 on failure.
 */
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

// ─── Input helpers ────────────────────────────────────────────────────────────

/**
 * Return a sanitized string from $_POST or '' if missing.
 */
function post_str(string $key, int $maxLen = 1000): string {
    $val = $_POST[$key] ?? '';
    return mb_substr(trim(strip_tags((string)$val)), 0, $maxLen);
}

/**
 * Return a sanitized integer from $_POST or null if missing/invalid.
 */
function post_int(string $key): ?int {
    $val = $_POST[$key] ?? null;
    return is_numeric($val) ? (int)$val : null;
}

/**
 * Return a sanitized string from $_GET or '' if missing.
 */
function get_str(string $key, int $maxLen = 200): string {
    $val = $_GET[$key] ?? '';
    return mb_substr(trim(strip_tags((string)$val)), 0, $maxLen);
}

/**
 * Return a sanitized integer from $_GET or null if missing/invalid.
 */
function get_int(string $key): ?int {
    $val = $_GET[$key] ?? null;
    return is_numeric($val) ? (int)$val : null;
}

// ─── Redirect ─────────────────────────────────────────────────────────────────

/**
 * Redirect to an internal page (safe — no open redirect).
 */
function redirect(string $page, array $params = []): never {
    $query = $params ? '&' . http_build_query($params) : '';
    $url   = '/?page=' . urlencode($page) . $query;
    header('Location: ' . $url);
    exit;
}

/**
 * Redirect back with a flash message stored in the session.
 */
function redirect_with(string $page, string $type, string $message, array $extra = []): never {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    redirect($page, $extra);
}

// ─── Flash messages ───────────────────────────────────────────────────────────

/**
 * Consume and return the current flash message, or null.
 * @return array{type:string, message:string}|null
 */
function consume_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ─── Misc ─────────────────────────────────────────────────────────────────────

/**
 * Escape a value for safe HTML output.
 */
function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Send a JSON response and exit.
 */
function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Return only the allowed keys from an array.
 */
function only(array $data, array $keys): array {
    return array_intersect_key($data, array_flip($keys));
}

/**
 * Check if the current request is POST.
 */
function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Enforce POST only — redirect away if not POST.
 */
function require_post(): void {
    if (!is_post()) {
        http_response_code(405);
        die('Method not allowed.');
    }
}

/**
 * Generate a cryptographically secure random token.
 */
function generate_token(int $bytes = SIGN_TOKEN_LENGTH): string {
    return bin2hex(random_bytes($bytes));
}
