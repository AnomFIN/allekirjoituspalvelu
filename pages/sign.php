<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$token = get_str('token', 100);

// Validate the token
$signerRow = null;
$document  = null;
$error     = null;

if (!$token) {
    $error = 'Allekirjoituslinkki puuttuu tai on virheellinen.';
} else {
    $signerRow = SignerRepository::validateToken($token);
    if (!$signerRow) {
        $error = 'Allekirjoituslinkki on vanhentunut tai jo käytetty.';
    } else {
        $document = DocumentRepository::findById((int)$signerRow['document_id']);
        if (!$document) {
            $error = 'Asiakirjaa ei löydy.';
        }
    }
}

// Mark signer as "viewed" if they haven't signed yet
if (!$error && $signerRow && $signerRow['status'] === 'pending') {
    SignerRepository::updateStatus((int)$signerRow['id'], 'viewed');
    ActivityRepository::log(
        (int)$signerRow['document_id'],
        'Katsottu',
        $signerRow['name'] . ' avasi asiakirjan.',
        $signerRow['email']
    );
}

// Render page without the normal admin sidebar layout
?><!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allekirjoita — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="sign-page">

<div class="sign-shell">
    <header class="sign-header">
        <span class="logo-icon">✍️</span>
        <span class="logo-text"><?= e(APP_NAME) ?></span>
    </header>

    <?php if ($error): ?>
        <div class="sign-card">
            <div class="sign-error">
                <div class="sign-icon">⚠️</div>
                <h1>Virhe</h1>
                <p><?= e($error) ?></p>
            </div>
        </div>
    <?php elseif ($signerRow['status'] === 'signed'): ?>
        <div class="sign-card">
            <div class="sign-success">
                <div class="sign-icon">✅</div>
                <h1>Olet jo allekirjoittanut</h1>
                <p>Olet jo allekirjoittanut tämän asiakirjan. Kiitos!</p>
            </div>
        </div>
    <?php elseif ($signerRow['status'] === 'rejected'): ?>
        <div class="sign-card">
            <div class="sign-error">
                <div class="sign-icon">❌</div>
                <h1>Olet hylännyt tämän asiakirjan</h1>
                <p>Olet aiemmin hylännyt tämän allekirjoituspyynnön.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="sign-card">
            <h1 class="sign-title">Allekirjoita asiakirja</h1>
            <p class="sign-greeting">Hei <strong><?= e($signerRow['name']) ?></strong>,</p>
            <p class="sign-doc-name">
                <?= e($document['sender_name'] ?? 'Lähettäjä') ?> pyytää sinua allekirjoittamaan:
                <strong><?= e($document['title']) ?></strong>
            </p>
            <?php if (!empty($document['message_body'])): ?>
                <div class="sign-message">
                    <?= nl2br(e($document['message_body'])) ?>
                </div>
            <?php endif; ?>

            <div class="sign-document-preview">
                <a href="/uploads/<?= e(basename($document['stored_filename'] ?? '')) ?>"
                   target="_blank" class="sign-preview-link">
                    <span><?= mime_icon($document['mime_type'] ?? '') ?></span>
                    <?= e($document['original_filename'] ?? $document['title']) ?>
                    <small>→ Avaa tiedosto</small>
                </a>
            </div>

            <div class="sign-actions">
                <form method="post" action="/actions/sign_document.php" id="signForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <div class="form-group">
                        <label class="form-label" for="full_name">Vahvista nimesi <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                               placeholder="Kirjoita koko nimesi vahvistukseksi"
                               value="<?= e($signerRow['name']) ?>"
                               maxlength="150" required>
                    </div>

                    <div class="sign-consent">
                        <label class="checkbox-label">
                            <input type="checkbox" name="consent" value="1" required>
                            Olen lukenut asiakirjan ja hyväksyn sen allekirjoittamisen.
                        </label>
                    </div>

                    <div class="sign-btn-row">
                        <button type="submit" name="action" value="sign" class="btn btn-primary btn-lg">
                            ✍️ Allekirjoita
                        </button>
                        <button type="button" class="btn btn-danger btn-outline" id="rejectBtn">
                            ✗ Hylkää
                        </button>
                    </div>
                </form>

                <!-- Reject confirmation -->
                <div id="rejectConfirm" class="reject-confirm hidden">
                    <p><strong>Haluatko varmasti hylätä tämän allekirjoituspyynnön?</strong></p>
                    <form method="post" action="/actions/reject_document.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <div class="form-group">
                            <textarea name="reason" class="form-control" rows="2"
                                      placeholder="Syy hylkäykselle (valinnainen)" maxlength="500"></textarea>
                        </div>
                        <div class="sign-btn-row">
                            <button type="submit" class="btn btn-danger">Hylkää allekirjoitus</button>
                            <button type="button" class="btn btn-ghost" id="cancelReject">Peruuta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer class="sign-footer">
        <?= e(APP_NAME) ?> &copy; <?= date('Y') ?>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
<script>
$(function() {
    $('#rejectBtn').on('click', function() {
        $('#signForm').addClass('hidden');
        $('#rejectConfirm').removeClass('hidden').hide().fadeIn(200);
    });
    $('#cancelReject').on('click', function() {
        $('#rejectConfirm').addClass('hidden');
        $('#signForm').removeClass('hidden');
    });
    $('#signForm').on('submit', function() {
        $(this).find('button[type=submit]').prop('disabled', true).text('Allekirjoitetaan…');
    });
});
</script>
</body>
</html>
