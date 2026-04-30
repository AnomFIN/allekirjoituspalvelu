<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$signerId   = post_int('signer_id');
$documentId = post_int('document_id');

if (!$signerId || !$documentId) {
    redirect_with('documents', 'error', 'Virheellinen pyyntö.');
}

$signer = SignerRepository::findById($signerId);
if (!$signer || (int)$signer['document_id'] !== $documentId) {
    redirect_with('document', 'error', 'Allekirjoittajaa ei löydy.', ['id' => $documentId]);
}

$document = DocumentRepository::findById($documentId);
if (!$document) {
    redirect_with('documents', 'error', 'Asiakirjaa ei löydy.');
}

if ($signer['status'] !== 'pending' && $signer['status'] !== 'viewed') {
    redirect_with('document', 'info', 'Muistutusta ei voida lähettää — allekirjoittaja on jo toiminut.', ['id' => $documentId]);
}

// Create a fresh token (invalidates old one) and send reminder
$token      = SignerRepository::createToken($signerId);
$signingUrl = APP_URL . '/?page=sign&token=' . urlencode($token);

MailerService::sendReminder($signer, $document, $signingUrl);

ActivityRepository::log(
    $documentId,
    'Muistutus lähetetty',
    'Muistutus lähetetty: ' . $signer['name'],
    $signer['email']
);

redirect_with('document', 'success', 'Muistutus lähetetty osoitteeseen ' . $signer['email'] . '.', ['id' => $documentId]);
