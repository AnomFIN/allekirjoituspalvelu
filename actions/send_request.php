<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$docId = post_int('document_id');
if (!$docId) {
    redirect_with('documents', 'error', 'Asiakirja-ID puuttuu.');
}

$document = DocumentRepository::findById($docId);
if (!$document) {
    redirect_with('documents', 'error', 'Asiakirjaa ei löydy.');
}
if ($document['status'] !== 'draft') {
    redirect_with('document', 'error', 'Vain luonnoksia voidaan lähettää.', ['id' => $docId]);
}

$signers = SignerRepository::findByDocument($docId);
if (empty($signers)) {
    redirect_with('document', 'error', 'Lisää ensin allekirjoittajat.', ['id' => $docId]);
}

// Generate tokens and send emails
$sent = 0;
foreach ($signers as $signer) {
    try {
        $token      = SignerRepository::createToken((int)$signer['id']);
        $signingUrl = APP_URL . '/?page=sign&token=' . urlencode($token);
        MailerService::sendSigningRequest($signer, $document, $signingUrl);
        $sent++;
        ActivityRepository::log(
            $docId,
            'Lähetetty',
            'Allekirjoituspyyntö lähetetty: ' . $signer['name'],
            $signer['email']
        );
    } catch (Throwable $e) {
        log_error('send_request: token/email error', [
            'signer' => $signer['email'],
            'error'  => $e->getMessage(),
        ]);
    }
}

// Update document status to 'sent'
DocumentRepository::updateStatus($docId, 'sent');
ActivityRepository::log($docId, 'Tila muutettu', 'Asiakirja merkitty lähetetyksi.', '');

redirect_with('document', 'success',
    "Allekirjoituspyynnöt lähetetty $sent allekirjoittajalle.",
    ['id' => $docId]
);
