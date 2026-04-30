<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$token  = post_str('token', 200);
$reason = post_str('reason', 500);

if (!$token) {
    redirect_with('dashboard', 'error', 'Virheellinen pyyntö.');
}

$signerRow = SignerRepository::validateToken($token);
if (!$signerRow) {
    // Already used or expired — show generic rejection page
    header('Location: /?page=sign&token=' . urlencode($token));
    exit;
}

$docId    = (int)$signerRow['document_id'];
$signerId = (int)$signerRow['id'];

// Mark signer as rejected
SignerRepository::updateStatus($signerId, 'rejected');
SignerRepository::consumeToken($token);

ActivityRepository::log(
    $docId,
    'Hylätty',
    $signerRow['name'] . ' hylkäsi asiakirjan.' . ($reason ? ' Syy: ' . $reason : ''),
    $signerRow['email']
);

// Update document status
DocumentRepository::syncStatus($docId);
$document = DocumentRepository::findById($docId);
?><!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hylätty — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="sign-page">
<div class="sign-shell">
    <header class="sign-header">
        <span class="logo-icon">✍️</span>
        <span class="logo-text"><?= e(APP_NAME) ?></span>
    </header>
    <div class="sign-card">
        <div class="sign-icon">❌</div>
        <h1>Allekirjoituspyyntö hylätty</h1>
        <p>Hei <strong><?= e($signerRow['name']) ?></strong>, olet hylännyt allekirjoituspyynnön asiakirjalle
           <strong><?= e($document['title'] ?? '') ?></strong>.</p>
        <?php if ($reason): ?>
            <p><small>Syy: <?= e($reason) ?></small></p>
        <?php endif; ?>
        <p>Asiakirjan lähettäjä on saanut tiedon hylkäyksestä.</p>
    </div>
    <footer class="sign-footer"><?= e(APP_NAME) ?> &copy; <?= date('Y') ?></footer>
</div>
</body>
</html>
