<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$docId = get_int('doc');
if (!$docId) {
    redirect_with('upload', 'error', 'Asiakirjaa ei löydy.');
}

$document = DocumentRepository::findById($docId);
if (!$document) {
    redirect_with('upload', 'error', 'Asiakirjaa ei löydy.');
}

if (!in_array($document['status'], ['draft', 'sent'], true)) {
    redirect_with('document', 'info', 'Allekirjoittajia ei voi enää muokata.', ['id' => $docId]);
}

$signers = SignerRepository::findByDocument($docId);

layout_start('Lisää allekirjoittajat');
?>

<div class="page-container narrow">
    <div class="panel" data-animate="fade-up">
        <div class="panel-header">
            <h1 class="panel-title">Allekirjoittajat</h1>
            <span class="panel-subtitle">Asiakirja: <strong><?= e($document['title']) ?></strong></span>
        </div>

        <!-- Progress steps -->
        <div class="steps">
            <div class="step done"><span class="step-num">1</span><span class="step-lbl">Lataus</span></div>
            <div class="step-connector"></div>
            <div class="step active"><span class="step-num">2</span><span class="step-lbl">Allekirjoittajat</span></div>
            <div class="step-connector"></div>
            <div class="step"><span class="step-num">3</span><span class="step-lbl">Lähetys</span></div>
        </div>

        <form id="signersForm" method="post" action="/actions/save_signers.php">
            <?= csrf_field() ?>
            <input type="hidden" name="document_id" value="<?= (int)$docId ?>">

            <div id="signersList">
                <?php foreach ($signers as $i => $signer): ?>
                <div class="signer-row" data-index="<?= $i ?>">
                    <div class="signer-num"><?= $i + 1 ?></div>
                    <div class="signer-fields">
                        <input type="text" name="signers[<?= $i ?>][name]" class="form-control"
                               placeholder="Nimi *" value="<?= e($signer['name']) ?>" required maxlength="150">
                        <input type="email" name="signers[<?= $i ?>][email]" class="form-control"
                               placeholder="Sähköposti *" value="<?= e($signer['email']) ?>" required maxlength="200">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-signer" title="Poista">✕</button>
                </div>
                <?php endforeach; ?>

                <?php if (empty($signers)): ?>
                <div class="signer-row" data-index="0">
                    <div class="signer-num">1</div>
                    <div class="signer-fields">
                        <input type="text" name="signers[0][name]" class="form-control"
                               placeholder="Nimi *" required maxlength="150">
                        <input type="email" name="signers[0][email]" class="form-control"
                               placeholder="Sähköposti *" required maxlength="200">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-signer" title="Poista">✕</button>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-group" style="margin-top:1rem">
                <button type="button" id="addSigner" class="btn btn-outline btn-sm">+ Lisää allekirjoittaja</button>
            </div>

            <div class="form-actions">
                <button type="submit" name="action" value="save_and_send" class="btn btn-primary btn-lg">
                    Tallenna ja lähetä →
                </button>
                <button type="submit" name="action" value="save_draft" class="btn btn-outline">
                    Tallenna luonnokseksi
                </button>
                <a href="<?= page_url('document', ['id' => $docId]) ?>" class="btn btn-ghost">Peruuta</a>
            </div>
        </form>
    </div>
</div>

<?php layout_end(); ?>
