<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$page   = max(1, get_int('p') ?? 1);
$search = get_str('q', 100);
$total  = DocumentRepository::countAll();
$paging = paginate($total, $page);
$docs   = DocumentRepository::findAll($paging['per_page'], $paging['offset']);

layout_start('Kaikki asiakirjat');
?>

<div class="page-container">
    <div class="page-header" data-animate="fade-up">
        <h1 class="page-title">Kaikki asiakirjat</h1>
        <a href="<?= page_url('upload') ?>" class="btn btn-primary">+ Uusi asiakirja</a>
    </div>

    <div class="filters-bar" data-animate="fade-up">
        <form method="get" action="/" class="search-form">
            <input type="hidden" name="page" value="documents">
            <input type="search" name="q" value="<?= e($search) ?>" class="form-control search-input"
                   placeholder="Hae asiakirjoista...">
            <button type="submit" class="btn btn-outline btn-sm">Hae</button>
        </form>
    </div>

    <?php if (empty($docs)): ?>
        <div class="empty-state" data-animate="fade-up">
            <div class="empty-icon">📁</div>
            <h2>Ei asiakirjoja</h2>
            <p>Lataa ensimmäinen asiakirja aloittaaksesi.</p>
            <a href="<?= page_url('upload') ?>" class="btn btn-primary">+ Lataa asiakirja</a>
        </div>
    <?php else: ?>
        <div class="doc-grid" data-animate="fade-up">
            <?php foreach ($docs as $doc): ?>
            <div class="doc-card">
                <div class="doc-card-header">
                    <span class="doc-card-icon"><?= mime_icon($doc['mime_type'] ?? '') ?></span>
                    <span class="badge <?= status_class($doc['status']) ?>"><?= status_label($doc['status']) ?></span>
                </div>
                <div class="doc-card-body">
                    <h3 class="doc-card-title">
                        <a href="<?= page_url('document', ['id' => $doc['id']]) ?>"><?= e($doc['title']) ?></a>
                    </h3>
                    <p class="doc-card-meta">
                        <?= e($doc['original_filename'] ?? '') ?>
                        <?php if (!empty($doc['size_bytes'])): ?>
                            · <?= format_bytes((int)$doc['size_bytes']) ?>
                        <?php endif; ?>
                    </p>
                    <p class="doc-card-date"><?= format_datetime($doc['created_at']) ?></p>
                </div>
                <div class="doc-card-actions">
                    <a href="<?= page_url('document', ['id' => $doc['id']]) ?>" class="btn btn-outline btn-sm">Avaa</a>
                    <?php if ($doc['status'] === 'draft'): ?>
                        <a href="<?= page_url('add_signers', ['doc' => $doc['id']]) ?>" class="btn btn-primary btn-sm">
                            + Allekirjoittajat
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($paging['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($paging['has_prev']): ?>
                <a href="<?= page_url('documents', ['p' => $paging['current'] - 1, 'q' => $search]) ?>" class="btn btn-ghost btn-sm">← Edellinen</a>
            <?php endif; ?>
            <span class="page-info">Sivu <?= $paging['current'] ?> / <?= $paging['total_pages'] ?> (<?= $total ?> asiakirjaa)</span>
            <?php if ($paging['has_next']): ?>
                <a href="<?= page_url('documents', ['p' => $paging['current'] + 1, 'q' => $search]) ?>" class="btn btn-ghost btn-sm">Seuraava →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php layout_end(); ?>
