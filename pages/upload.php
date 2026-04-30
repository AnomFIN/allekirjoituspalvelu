<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

layout_start('Lataa asiakirja');
?>

<div class="page-container narrow">
    <div class="panel upload-panel" data-animate="fade-up">
        <div class="panel-header">
            <h1 class="panel-title">Lataa allekirjoitettava asiakirja</h1>
        </div>

        <form id="uploadForm" method="post" action="/actions/upload_document.php" enctype="multipart/form-data"
              data-max-bytes="<?= MAX_UPLOAD_BYTES ?>"
              data-allowed-ext="<?= e(implode(',', ALLOWED_EXTENSIONS)) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title" class="form-label">Asiakirjan nimi <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control"
                       placeholder="esim. Sopimus, Tarjous, Allekirjoituspyyntö"
                       maxlength="200" required
                       value="<?= e($_SESSION['form_data']['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="sender_name" class="form-label">Lähettäjän nimi <span class="required">*</span></label>
                <input type="text" id="sender_name" name="sender_name" class="form-control"
                       placeholder="Etunimi Sukunimi" maxlength="150" required
                       value="<?= e($_SESSION['form_data']['sender_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="sender_email" class="form-label">Lähettäjän sähköposti <span class="required">*</span></label>
                <input type="email" id="sender_email" name="sender_email" class="form-control"
                       placeholder="nimi@yritys.fi" maxlength="200" required
                       value="<?= e($_SESSION['form_data']['sender_email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="message_body" class="form-label">Viesti allekirjoittajille</label>
                <textarea id="message_body" name="message_body" class="form-control" rows="3"
                          placeholder="Vapaaehtoinen viesti, joka lähetetään allekirjoittajille."
                          maxlength="2000"><?= e($_SESSION['form_data']['message_body'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Tiedosto <span class="required">*</span></label>
                <div class="dropzone" id="dropzone">
                    <div class="dropzone-inner">
                        <div class="dropzone-icon">📂</div>
                        <p class="dropzone-hint">Vedä tiedosto tähän tai <strong>valitse tiedostosta</strong></p>
                        <p class="dropzone-meta">PDF, PNG tai JPG — max <?= format_bytes(MAX_UPLOAD_BYTES) ?></p>
                    </div>
                    <input type="file" id="fileInput" name="document" class="dropzone-input"
                           accept=".pdf,.png,.jpg,.jpeg" required>
                </div>
                <div id="filePreview" class="file-preview hidden"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg" id="uploadSubmit">
                    <span class="btn-text">Jatka → Lisää allekirjoittajat</span>
                    <span class="btn-spinner hidden">⏳ Ladataan…</span>
                </button>
                <a href="<?= page_url('dashboard') ?>" class="btn btn-ghost">Peruuta</a>
            </div>
        </form>
    </div>
</div>

<?php
unset($_SESSION['form_data']);
layout_end();
?>
