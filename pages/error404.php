<?php
declare(strict_types=1);
/** Generic 404 page */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
http_response_code(404);
layout_start('Sivua ei löydy');
?>
<div class="page-container narrow">
    <div class="empty-state" data-animate="fade-up">
        <div class="empty-icon">🔍</div>
        <h1>404 — Sivua ei löydy</h1>
        <p>Haluamasi sivu ei ole olemassa tai on siirretty.</p>
        <a href="<?= page_url('dashboard') ?>" class="btn btn-primary">← Takaisin etusivulle</a>
    </div>
</div>
<?php layout_end(); ?>
