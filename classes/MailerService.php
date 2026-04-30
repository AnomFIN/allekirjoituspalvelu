<?php
declare(strict_types=1);

/**
 * MailerService — sends email notifications via PHP mail() with fallback logging.
 *
 * In development (APP_ENV=development), emails are logged to the log file instead
 * of being sent.  In production they are sent via mail().
 * For production deployments consider replacing with PHPMailer + SMTP.
 */
class MailerService
{
    /**
     * Send a signing-request email to a signer.
     */
    public static function sendSigningRequest(array $signer, array $document, string $signingUrl): bool
    {
        $subject = 'Pyyntö allekirjoittaa: ' . $document['title'];
        $body    = self::signingRequestBody($signer, $document, $signingUrl);
        return self::send($signer['email'], $subject, $body);
    }

    /**
     * Send a reminder email to a signer.
     */
    public static function sendReminder(array $signer, array $document, string $signingUrl): bool
    {
        $subject = 'Muistutus: Allekirjoita — ' . $document['title'];
        $body    = self::reminderBody($signer, $document, $signingUrl);
        return self::send($signer['email'], $subject, $body);
    }

    /**
     * Notify the document sender that all signers have signed.
     */
    public static function sendCompletionNotice(array $document): bool
    {
        if (empty($document['sender_email'])) {
            return false;
        }
        $subject = 'Asiakirja allekirjoitettu: ' . $document['title'];
        $body    = self::completionBody($document);
        return self::send($document['sender_email'], $subject, $body);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private static function send(string $to, string $subject, string $body): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            log_warn('MailerService: invalid recipient', ['to' => $to]);
            return false;
        }

        $from    = 'noreply@' . (parse_url(APP_URL, PHP_URL_HOST) ?? 'localhost');
        $headers = implode("\r\n", [
            'From: ' . APP_NAME . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . PHP_VERSION,
        ]);

        if (APP_ENV !== 'production') {
            log_info('📧 [DEV EMAIL] To: ' . $to, ['subject' => $subject]);
            log_debug('Email body', ['body' => $body]);
            return true;
        }

        $result = mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
        if (!$result) {
            log_error('MailerService: mail() returned false', ['to' => $to, 'subject' => $subject]);
        }
        return $result;
    }

    private static function signingRequestBody(array $signer, array $document, string $url): string
    {
        return self::htmlWrapper('Allekirjoituspyyntö', '
            <p>Hei ' . e($signer['name']) . ',</p>
            <p><strong>' . e($document['sender_name']) . '</strong> pyytää sinua allekirjoittamaan asiakirjan:
            <strong>' . e($document['title']) . '</strong>.</p>
            ' . (!empty($document['message_body']) ? '<p><em>' . nl2br(e($document['message_body'])) . '</em></p>' : '') . '
            <p><a href="' . e($url) . '" style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block">
                Allekirjoita asiakirja
            </a></p>
            <p style="color:#888;font-size:12px">Linkki vanhenee ' . TOKEN_EXPIRY_HOURS . ' tunnin kuluttua.</p>
        ');
    }

    private static function reminderBody(array $signer, array $document, string $url): string
    {
        return self::htmlWrapper('Muistutus allekirjoituksesta', '
            <p>Hei ' . e($signer['name']) . ',</p>
            <p>Muistutus: sinulla on allekirjoittamaton asiakirja:
            <strong>' . e($document['title']) . '</strong>.</p>
            <p><a href="' . e($url) . '" style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block">
                Allekirjoita nyt
            </a></p>
        ');
    }

    private static function completionBody(array $document): string
    {
        return self::htmlWrapper('Asiakirja allekirjoitettu', '
            <p>Hei,</p>
            <p>Asiakirja <strong>' . e($document['title']) . '</strong> on nyt allekirjoitettu kaikkien osapuolten toimesta.</p>
            <p><a href="' . e(APP_URL . '/?page=document&id=' . $document['id']) . '"
               style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block">
                Katso asiakirja
            </a></p>
        ');
    }

    private static function htmlWrapper(string $title, string $content): string
    {
        return '<!DOCTYPE html><html lang="fi"><head><meta charset="UTF-8">
            <title>' . e($title) . '</title></head>
            <body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333">
            <h2 style="color:#4f46e5">' . e(APP_NAME) . '</h2>
            ' . $content . '
            <hr style="border:none;border-top:1px solid #eee;margin-top:32px">
            <p style="color:#aaa;font-size:11px">Tämä on automaattinen viesti. Älä vastaa tähän.</p>
            </body></html>';
    }
}
