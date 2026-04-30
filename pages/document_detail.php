<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$docId = get_int('id');
if (!$docId) {
    redirect_with('documents', 'error', 'Asiakirja-ID puuttuu.');
}

$document = DocumentRepository::findById($docId);
if (!$document) {
    redirect_with('documents', 'error', 'Asiakirjaa ei löydy.');
}

$signers  = SignerRepository::findByDocument($docId);
$events   = ActivityRepository::findByDocument($docId, 30);
$canSend  = ($document['status'] === 'draft' && !empty($signers));
$canEdit  = in_array($document['status'], ['draft'], true);

layout_start($document['title']);
?>

<div class="page-container">

    <!-- Document header -->
    <div class="doc-detail-header" data-animate="fade-up">
        <div class="doc-detail-title-row">
            <span class="doc-detail-icon"><?= mime_icon($document['mime_type'] ?? '') ?></span>
            <div>
                <h1 class="doc-detail-title"><?= e($document['title']) ?></h1>
                <p class="doc-detail-meta">
                    <?= e($document['original_filename'] ?? '') ?>
                    <?php if (!empty($document['size_bytes'])): ?>
                        · <?= format_bytes((int)$document['size_bytes']) ?>
                    <?php endif; ?>
                    · <?= format_datetime($document['created_at']) ?>
                </p>
            </div>
        </div>
        <div class="doc-detail-status">
            <span class="badge <?= status_class($document['status']) ?> badge-lg">
                <?= status_label($document['status']) ?>
            </span>
        </div>
    </div>

    <div class="doc-detail-grid">

        <!-- Signers panel -->
        <div class="panel" data-animate="fade-up">
            <div class="panel-header">
                <h2 class="panel-title">Allekirjoittajat</h2>
                <?php if ($canEdit): ?>
                    <a href="<?= page_url('add_signers', ['doc' => $docId]) ?>" class="btn btn-outline btn-sm">Muokkaa</a>
                <?php endif; ?>
            </div>
            <?php if (empty($signers)): ?>
                <div class="empty-state-small">
                    <p>Ei allekirjoittajia.
                    <?php if ($canEdit): ?>
                        <a href="<?= page_url('add_signers', ['doc' => $docId]) ?>">Lisää allekirjoittajat →</a>
                    <?php endif; ?></p>
                </div>
            <?php else: ?>
                <ul class="signer-status-list">
                <?php foreach ($signers as $signer): ?>
                    <li class="signer-status-item">
                        <div class="signer-avatar"><?= strtoupper(mb_substr($signer['name'], 0, 1)) ?></div>
                        <div class="signer-info">
                            <strong><?= e($signer['name']) ?></strong>
                            <small><?= e($signer['email']) ?></small>
                        </div>
                        <div class="signer-action">
                            <span class="badge <?= signer_status_class($signer['status']) ?>">
                                <?= signer_status_label($signer['status']) ?>
                            </span>
                            <?php if ($signer['status'] === 'pending' && $document['status'] === 'sent'): ?>
                                <form method="post" action="/actions/remind_signer.php" class="inline-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="signer_id" value="<?= (int)$signer['id'] ?>">
                                    <input type="hidden" name="document_id" value="<?= (int)$docId ?>">
                                    <button type="submit" class="btn btn-ghost btn-xs">🔔 Muistutus</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Document info + actions -->
        <div class="side-panels">
            <div class="panel" data-animate="fade-up" data-delay="60">
                <div class="panel-header"><h2 class="panel-title">Tiedot</h2></div>
                <dl class="info-list">
                    <dt>Lähettäjä</dt>
                    <dd><?= e($document['sender_name'] ?? '–') ?></dd>
                    <dt>Sähköposti</dt>
                    <dd><?= e($document['sender_email'] ?? '–') ?></dd>
                    <?php if (!empty($document['message_body'])): ?>
                    <dt>Viesti</dt>
                    <dd><?= nl2br(e($document['message_body'])) ?></dd>
                    <?php endif; ?>
                    <dt>Luotu</dt>
                    <dd><?= format_datetime($document['created_at']) ?></dd>
                    <dt>Päivitetty</dt>
                    <dd><?= format_datetime($document['updated_at'] ?? $document['created_at']) ?></dd>
                </dl>
            </div>

            <!-- Actions -->
            <div class="panel" data-animate="fade-up" data-delay="120">
                <div class="panel-header"><h2 class="panel-title">Toiminnot</h2></div>
                <div class="action-buttons">
                    <?php if ($canSend): ?>
                        <form method="post" action="/actions/send_request.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="document_id" value="<?= (int)$docId ?>">
                            <button type="submit" class="btn btn-primary btn-full">✉️ Lähetä allekirjoituspyynnöt</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($document['status'] === 'draft' && empty($signers)): ?>
                        <a href="<?= page_url('add_signers', ['doc' => $docId]) ?>" class="btn btn-outline btn-full">
                            + Lisää allekirjoittajat
                        </a>
                    <?php endif; ?>
                    <a href="/uploads/<?= e(basename($document['stored_filename'] ?? '')) ?>"
                       class="btn btn-ghost btn-full" target="_blank">
                        📥 Lataa tiedosto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity log -->
    <?php if (!empty($events)): ?>
    <div class="panel" data-animate="fade-up">
        <div class="panel-header"><h2 class="panel-title">Tapahtumahistoria</h2></div>
        <ul class="timeline">
        <?php foreach ($events as $ev): ?>
            <li class="timeline-item">
                <span class="timeline-dot"></span>
                <div class="timeline-content">
                    <strong><?= e($ev['event']) ?></strong>
                    <?php if (!empty($ev['description'])): ?>
                        <p><?= e($ev['description']) ?></p>
                    <?php endif; ?>
                    <small><?= format_datetime($ev['created_at']) ?>
                        <?php if (!empty($ev['actor_email'])): ?>
                            · <?= e($ev['actor_email']) ?>
                        <?php endif; ?>
                    </small>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

</div>

<?php layout_end(); ?>
