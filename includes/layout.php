<?php
declare(strict_types=1);
/** Layout helper — called from every page to wrap content. */

/**
 * Start buffering page output and save the page title.
 */
function layout_start(string $title): void {
    $GLOBALS['_page_title'] = $title;
    ob_start();
}

/**
 * Flush buffer, wrap in full HTML layout, print.
 */
function layout_end(): void {
    $content = ob_get_clean();
    $title   = $GLOBALS['_page_title'] ?? APP_NAME;
    $flash   = consume_flash();
    include __DIR__ . '/header.php';
    if ($flash) {
        echo '<div class="flash flash-' . e($flash['type']) . '" id="flashMessage">';
        echo '<span>' . e($flash['message']) . '</span>';
        echo '<button class="flash-close" onclick="this.parentElement.remove()">×</button>';
        echo '</div>';
    }
    echo $content;
    include __DIR__ . '/footer.php';
}
