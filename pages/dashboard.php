<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$stats    = DocumentRepository::getStats();
$recent   = ActivityRepository::getRecentActivity(8);
$drafts   = DocumentRepository::findDrafts();

layout_start('Etusivu');
?>

<div class="dashboard-grid">

    <!-- Stat cards -->
    <div class="stats-row">
        <div class="stat-card stat-total" data-animate="fade-up">
            <div class="stat-icon">📄</div>
            <div class="stat-body">
                <div class="stat-value"><?= (int)$stats['total'] ?></div>
                <div class="stat-label">Asiakirjat yhteensä</div>
            </div>
        </div>
        <div class="stat-card stat-sent" data-animate="fade-up" data-delay="60">
            <div class="stat-icon">✉️</div>
            <div class="stat-body">
                <div class="stat-value"><?= (int)$stats['sent'] ?></div>
                <div class="stat-label">Odottaa allekirjoitusta</div>
            </div>
        </div>
        <div class="stat-card stat-signed" data-animate="fade-up" data-delay="120">
            <div class="stat-icon">✅</div>
            <div class="stat-body">
                <div class="stat-value"><?= (int)$stats['signed'] ?></div>
                <div class="stat-label">Allekirjoitettu</div>
            </div>
        </div>
        <div class="stat-card stat-draft" data-animate="fade-up" data-delay="180">
            <div class="stat-icon">📝</div>
            <div class="stat-body">
                <div class="stat-value"><?= (int)$stats['drafts'] ?></div>
                <div class="stat-label">Luonnokset</div>
            </div>
        </div>
    </div>

    <div class="dashboard-columns">
        <!-- Drafts panel -->
        <div class="panel" data-animate="slide-in">
            <div class="panel-header">
                <h2 class="panel-title">Luonnokset</h2>
                <a href="<?= page_url('upload') ?>" class="btn btn-primary btn-sm">+ Uusi</a>
            </div>
            <?php if (empty($drafts)): ?>
                <div class="empty-state-small">
                    <p>Ei luonnoksia.<br><a href="<?= page_url('upload') ?>">Lataa ensimmäinen asiakirja →</a></p>
                </div>
            <?php else: ?>
                <ul class="doc-list">
                <?php foreach ($drafts as $doc): ?>
                    <li class="doc-list-item">
                        <a href="<?= page_url('document', ['id' => $doc['id']]) ?>" class="doc-link">
                            <span class="doc-icon"><?= mime_icon($doc['mime_type'] ?? '') ?></span>
                            <span class="doc-name"><?= e($doc['title']) ?></span>
                        </a>
                        <span class="<?= status_class($doc['status']) ?>"><?= status_label($doc['status']) ?></span>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Recent activity -->
        <div class="panel" data-animate="slide-in" data-delay="80">
            <div class="panel-header">
                <h2 class="panel-title">Viimeisimmät tapahtumat</h2>
            </div>
            <?php if (empty($recent)): ?>
                <div class="empty-state-small"><p>Ei tapahtumahistoriaa.</p></div>
            <?php else: ?>
                <ul class="activity-list">
                <?php foreach ($recent as $ev): ?>
                    <li class="activity-item">
                        <span class="activity-event"><?= e($ev['event']) ?></span>
                        <span class="activity-doc">
                            <a href="<?= page_url('document', ['id' => $ev['document_id']]) ?>">
                                <?= e($ev['document_title']) ?>
                            </a>
                        </span>
                        <span class="activity-time"><?= time_ago($ev['created_at']) ?></span>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php layout_end(); ?>
