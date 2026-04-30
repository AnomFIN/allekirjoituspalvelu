<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$page    = max(1, get_int('p') ?? 1);
$total   = DocumentRepository::countSent();
$paging  = paginate($total, $page);
$docs    = DocumentRepository::findSent($paging['per_page'], $paging['offset']);

layout_start('Lähetetyt');
?>

<div class="page-container">
    <div class="page-header" data-animate="fade-up">
        <h1 class="page-title">Lähetetyt asiakirjat</h1>
        <p class="page-subtitle">Kaikki allekirjoituspyynnöt, jotka olet lähettänyt.</p>
    </div>

    <?php if (empty($docs)): ?>
        <div class="empty-state" data-animate="fade-up">
            <div class="empty-icon">✉️</div>
            <h2>Ei lähetettyjä asiakirjoja</h2>
            <p>Lähetä ensimmäinen allekirjoituspyyntö aloittaaksesi.</p>
            <a href="<?= page_url('upload') ?>" class="btn btn-primary">+ Lataa asiakirja</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper" data-animate="fade-up">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Asiakirja</th>
                        <th>Tila</th>
                        <th>Allekirjoittajat</th>
                        <th>Lähetetty</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($docs as $doc):
                    $signers = SignerRepository::findByDocument($doc['id']);
                    $pending = array_filter($signers, fn($s) => $s['status'] === 'pending');
                ?>
                    <tr>
                        <td class="td-doc">
                            <span class="doc-icon"><?= mime_icon($doc['mime_type'] ?? '') ?></span>
                            <a href="<?= page_url('document', ['id' => $doc['id']]) ?>">
                                <?= e($doc['title']) ?>
                            </a>
                        </td>
                        <td><span class="badge <?= status_class($doc['status']) ?>"><?= status_label($doc['status']) ?></span></td>
                        <td class="td-signers">
                            <span class="signers-signed"><?= count($signers) - count($pending) ?>/<?= count($signers) ?></span>
                            allekirjoittanut
                        </td>
                        <td class="td-date"><?= format_datetime($doc['created_at']) ?></td>
                        <td class="td-actions">
                            <a href="<?= page_url('document', ['id' => $doc['id']]) ?>" class="btn btn-ghost btn-sm">Avaa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($paging['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($paging['has_prev']): ?>
                <a href="<?= page_url('sent', ['p' => $paging['current'] - 1]) ?>" class="btn btn-ghost btn-sm">← Edellinen</a>
            <?php endif; ?>
            <span class="page-info">Sivu <?= $paging['current'] ?> / <?= $paging['total_pages'] ?></span>
            <?php if ($paging['has_next']): ?>
                <a href="<?= page_url('sent', ['p' => $paging['current'] + 1]) ?>" class="btn btn-ghost btn-sm">Seuraava →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php layout_end(); ?>
