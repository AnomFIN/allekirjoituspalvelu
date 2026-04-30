<?php declare(strict_types=1);
$currentPage = get_str('page') ?: 'dashboard';
$nav = [
    'dashboard' => ['label' => 'Etusivu',       'icon' => '🏠'],
    'upload'    => ['label' => 'Lähetä',         'icon' => '📤'],
    'documents' => ['label' => 'Asiakirjat',     'icon' => '📁'],
    'sent'      => ['label' => 'Lähetetyt',      'icon' => '✉️'],
];
?><!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?> — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✍️</text></svg>">
</head>
<body>

<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <span class="logo-icon">✍️</span>
            <span class="logo-text"><?= e(APP_NAME) ?></span>
        </div>
        <nav class="sidebar-nav">
            <ul>
<?php foreach ($nav as $page => $item): ?>
                <li>
                    <a href="<?= page_url($page) ?>" class="nav-link<?= ($currentPage === $page) ? ' active' : '' ?>">
                        <span class="nav-icon"><?= $item['icon'] ?></span>
                        <span class="nav-label"><?= e($item['label']) ?></span>
                    </a>
                </li>
<?php endforeach; ?>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <span class="version-tag">v<?= e(APP_VERSION) ?></span>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main content area -->
    <div class="main-wrapper">
        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" aria-label="Avaa valikko">☰</button>
            <div class="topbar-title"><?= e($title ?? APP_NAME) ?></div>
            <div class="topbar-actions">
                <a href="<?= page_url('upload') ?>" class="btn btn-primary btn-sm">+ Uusi asiakirja</a>
            </div>
        </header>

        <main class="page-content">
