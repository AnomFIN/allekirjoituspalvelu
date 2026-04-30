<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
verify_csrf();

$title       = post_str('title', 200);
$senderName  = post_str('sender_name', 150);
$senderEmail = post_str('sender_email', 200);
$messageBody = post_str('message_body', 2000);

// Validate text fields
$v = ValidationService::make()
    ->required('title',        $title,        'Asiakirjan nimi')
    ->maxLength('title',       $title, 200,   'Asiakirjan nimi')
    ->required('sender_name',  $senderName,   'Lähettäjän nimi')
    ->required('sender_email', $senderEmail,  'Lähettäjän sähköposti')
    ->email('sender_email',    $senderEmail,  'Lähettäjän sähköposti');

if ($v->hasErrors()) {
    // Preserve form data across redirect
    $_SESSION['form_data'] = compact('title', 'sender_name', 'sender_email', 'message_body');
    redirect_with('upload', 'error', $v->firstError());
}

// Handle file upload
if (empty($_FILES['document']['name'])) {
    $_SESSION['form_data'] = compact('title', 'sender_name', 'sender_email', 'message_body');
    redirect_with('upload', 'error', 'Tiedostoa ei valittu.');
}

try {
    $uploaded = UploadService::process($_FILES['document']);
} catch (RuntimeException $e) {
    $_SESSION['form_data'] = compact('title', 'sender_name', 'sender_email', 'message_body');
    redirect_with('upload', 'error', $e->getMessage());
}

// Persist to database
try {
    $docId = DocumentRepository::create([
        'uuid'              => generate_token(16),
        'title'             => $title,
        'original_filename' => $uploaded['original_filename'],
        'stored_filename'   => $uploaded['stored_filename'],
        'file_path'         => $uploaded['file_path'],
        'mime_type'         => $uploaded['mime_type'],
        'size_bytes'        => $uploaded['size_bytes'],
        'status'            => 'draft',
        'sender_name'       => $senderName,
        'sender_email'      => $senderEmail,
        'message_body'      => $messageBody,
    ]);
} catch (Throwable $e) {
    // Roll back the uploaded file
    UploadService::delete($uploaded['stored_filename']);
    log_error('upload_document: DB insert failed', ['error' => $e->getMessage()]);
    redirect_with('upload', 'error', 'Asiakirjan tallennus epäonnistui. Yritä uudelleen.');
}

ActivityRepository::log($docId, 'Luotu', 'Asiakirja ladattiin.', $senderEmail);
log_info('Document created', ['id' => $docId, 'title' => $title]);

redirect_with('add_signers', 'success', 'Asiakirja ladattu onnistuneesti. Lisää allekirjoittajat.', ['doc' => $docId]);
