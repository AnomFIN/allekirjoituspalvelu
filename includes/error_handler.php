<?php
declare(strict_types=1);

/**
 * Error handler — catches fatal errors and exceptions uniformly.
 * Must be included FIRST before any other include.
 */

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    $message = sprintf('[%s] %s in %s on line %d', _error_level($errno), $errstr, $errfile, $errline);
    _write_error_log($message);
    if (APP_DEBUG) {
        echo '<pre style="color:red;background:#fff;padding:1rem;font-family:monospace">' . htmlspecialchars($message) . '</pre>';
    }
    return true;
});

set_exception_handler(function (Throwable $e): void {
    $message = sprintf(
        '[EXCEPTION] %s: %s in %s on line %d%sStack trace:%s%s',
        get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), PHP_EOL, PHP_EOL, $e->getTraceAsString()
    );
    _write_error_log($message);
    http_response_code(500);
    if (APP_DEBUG) {
        echo '<pre style="color:red;background:#fff;padding:1rem;font-family:monospace">' . htmlspecialchars($message) . '</pre>';
    } else {
        include BASE_PATH . '/pages/error500.php';
    }
    exit(1);
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $message = sprintf('[FATAL] %s in %s on line %d', $error['message'], $error['file'], $error['line']);
        _write_error_log($message);
    }
});

function _error_level(int $errno): string {
    return match ($errno) {
        E_ERROR, E_CORE_ERROR    => 'ERROR',
        E_WARNING, E_CORE_WARNING=> 'WARNING',
        E_NOTICE                 => 'NOTICE',
        E_DEPRECATED             => 'DEPRECATED',
        default                  => "UNKNOWN($errno)",
    };
}

function _write_error_log(string $message): void {
    if (!defined('LOG_FILE')) {
        return;
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}
