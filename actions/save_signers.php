<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$docId  = post_int('document_id');
$action = post_str('action', 20); // 'save_draft' | 'save_and_send'

if (!$docId) {
    redirect_with('documents', 'error', 'Asiakirja-ID puuttuu.');
}

$document = DocumentRepository::findById($docId);
if (!$document) {
    redirect_with('documents', 'error', 'Asiakirjaa ei löydy.');
}
if (!in_array($document['status'], ['draft', 'sent'], true)) {
    redirect_with('document', 'error', 'Allekirjoittajia ei voi enää muokata.', ['id' => $docId]);
}

// Parse signers from POST
$rawSigners = $_POST['signers'] ?? [];
if (!is_array($rawSigners)) {
    redirect_with('add_signers', 'error', 'Virheellinen syöte.', ['doc' => $docId]);
}

$signers = [];
foreach ($rawSigners as $row) {
    $name  = mb_substr(trim(strip_tags((string)($row['name'] ?? ''))),  0, 150);
    $email = mb_substr(trim(strip_tags((string)($row['email'] ?? ''))), 0, 200);
    if ($name === '' || $email === '') {
        continue;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with('add_signers', 'error', "Sähköpostiosoite ei ole kelvollinen: " . htmlspecialchars($email), ['doc' => $docId]);
    }
    $signers[] = ['name' => $name, 'email' => $email];
}

if (empty($signers)) {
    redirect_with('add_signers', 'error', 'Lisää vähintään yksi allekirjoittaja.', ['doc' => $docId]);
}
if (count($signers) > 20) {
    redirect_with('add_signers', 'error', 'Enintään 20 allekirjoittajaa per asiakirja.', ['doc' => $docId]);
}

// Save signers (replace any existing)
try {
    SignerRepository::replaceAll($docId, $signers);
} catch (Throwable $e) {
    log_error('save_signers: failed', ['error' => $e->getMessage(), 'doc' => $docId]);
    redirect_with('add_signers', 'error', 'Allekirjoittajien tallennus epäonnistui.', ['doc' => $docId]);
}

ActivityRepository::log($docId, 'Allekirjoittajat päivitetty', count($signers) . ' allekirjoittajaa.', '');

if ($action === 'save_and_send') {
    // Forward to send action
    $_POST['document_id'] = $docId;
    redirect('send_request_redirect', ['doc' => $docId, '_send' => '1']);
} else {
    redirect_with('document', 'success', 'Allekirjoittajat tallennettu.', ['id' => $docId]);
}
