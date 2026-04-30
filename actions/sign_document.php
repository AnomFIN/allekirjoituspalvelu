<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$token    = post_str('token', 200);
$fullName = post_str('full_name', 150);
$action   = post_str('action', 20);

if (!$token || $action !== 'sign') {
    redirect_with('dashboard', 'error', 'Virheellinen pyyntö.');
}

// Validate token
$signerRow = SignerRepository::validateToken($token);
if (!$signerRow) {
    // Show error inline on sign page
    header('Location: /?page=sign&token=' . urlencode($token));
    exit;
}

$docId    = (int)$signerRow['document_id'];
$signerId = (int)$signerRow['id'];

// Validate consent (checkbox must be checked — enforced client-side but verify server-side)
if (empty($_POST['consent'])) {
    redirect('sign', ['token' => $token]);
}

// Validate name confirmation
$v = ValidationService::make()
    ->required('full_name', $fullName, 'Nimi')
    ->maxLength('full_name', $fullName, 150, 'Nimi');
if ($v->hasErrors()) {
    redirect('sign', ['token' => $token]);
}

// Mark signer as signed
SignerRepository::updateStatus($signerId, 'signed', date('Y-m-d H:i:s'));
SignerRepository::consumeToken($token);

ActivityRepository::log(
    $docId,
    'Allekirjoitettu',
    $signerRow['name'] . ' allekirjoitti asiakirjan (nimi: ' . $fullName . ').',
    $signerRow['email']
);

// Check if all signers have signed and update doc status
DocumentRepository::syncStatus($docId);

// If fully signed, notify sender
$document = DocumentRepository::findById($docId);
if ($document && $document['status'] === 'signed') {
    MailerService::sendCompletionNotice($document);
    ActivityRepository::log($docId, 'Valmis', 'Kaikki osapuolet ovat allekirjoittaneet.', '');
}

// Show success page
?><!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Allekirjoitettu — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="sign-page">
<div class="sign-shell">
    <header class="sign-header">
        <span class="logo-icon">✍️</span>
        <span class="logo-text"><?= e(APP_NAME) ?></span>
    </header>
    <div class="sign-card sign-success-card" data-animate="fade-up">
        <div class="sign-icon success-bounce">✅</div>
        <h1>Allekirjoitettu!</h1>
        <p>Kiitos, <strong><?= e($signerRow['name']) ?></strong>. Asiakirja
           <strong><?= e($document['title'] ?? '') ?></strong> on nyt allekirjoitettu.</p>
        <p class="sign-timestamp">Allekirjoitettu <?= format_datetime(date('Y-m-d H:i:s')) ?></p>
    </div>
    <footer class="sign-footer"><?= e(APP_NAME) ?> &copy; <?= date('Y') ?></footer>
</div>
</body>
</html>
