<?php
declare(strict_types=1);

/**
 * SignerRepository — CRUD for signers and signing_tokens tables.
 */
class SignerRepository
{
    // ─── Read ─────────────────────────────────────────────────────────────────

    public static function findByDocument(int $documentId): array
    {
        return Database::fetchAll(
            'SELECT * FROM signers WHERE document_id = ? ORDER BY sort_order, id',
            [$documentId]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::fetchOne('SELECT * FROM signers WHERE id = ?', [$id]);
    }

    public static function findByToken(string $token): ?array
    {
        return Database::fetchOne(
            'SELECT s.*, st.token, st.expires_at, st.used_at
             FROM signers s
             JOIN signing_tokens st ON st.signer_id = s.id
             WHERE st.token = ? AND st.used_at IS NULL',
            [$token]
        );
    }

    public static function countPending(int $documentId): int
    {
        return (int)Database::fetchScalar(
            "SELECT COUNT(*) FROM signers WHERE document_id = ? AND status = 'pending'",
            [$documentId]
        );
    }

    // ─── Write ────────────────────────────────────────────────────────────────

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO signers (document_id, name, email, sort_order, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $data['document_id'],
                $data['name'],
                $data['email'],
                $data['sort_order'] ?? 0,
                'pending',
            ]
        );
        return (int)Database::lastInsertId();
    }

    /** Replace all signers for a document. */
    public static function replaceAll(int $documentId, array $signers): void
    {
        Database::beginTransaction();
        try {
            Database::query('DELETE FROM signers WHERE document_id = ?', [$documentId]);
            foreach ($signers as $i => $signer) {
                self::create([
                    'document_id' => $documentId,
                    'name'        => $signer['name'],
                    'email'       => $signer['email'],
                    'sort_order'  => $i,
                ]);
            }
            Database::commit();
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function updateStatus(int $id, string $status, ?string $signedAt = null): void
    {
        if ($signedAt !== null) {
            Database::query(
                'UPDATE signers SET status = ?, signed_at = ?, updated_at = NOW() WHERE id = ?',
                [$status, $signedAt, $id]
            );
        } else {
            Database::query(
                'UPDATE signers SET status = ?, updated_at = NOW() WHERE id = ?',
                [$status, $id]
            );
        }
    }

    // ─── Signing tokens ───────────────────────────────────────────────────────

    /** Create a new signing token, invalidating any previous ones. */
    public static function createToken(int $signerId): string
    {
        // Invalidate previous tokens for this signer
        Database::query(
            'UPDATE signing_tokens SET used_at = NOW() WHERE signer_id = ? AND used_at IS NULL',
            [$signerId]
        );

        $token     = generate_token(SIGN_TOKEN_LENGTH);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . TOKEN_EXPIRY_HOURS . ' hours'));

        Database::query(
            'INSERT INTO signing_tokens (signer_id, token, expires_at, created_at)
             VALUES (?, ?, ?, NOW())',
            [$signerId, $token, $expiresAt]
        );
        return $token;
    }

    /** Validate a token: return signer row or null (invalid/expired/used). */
    public static function validateToken(string $token): ?array
    {
        $row = Database::fetchOne(
            'SELECT s.*, st.id AS token_id, st.token, st.expires_at, st.used_at
             FROM signing_tokens st
             JOIN signers s ON s.id = st.signer_id
             WHERE st.token = ?',
            [$token]
        );
        if (!$row) {
            return null;
        }
        if ($row['used_at'] !== null) {
            return null; // already used
        }
        if (new DateTimeImmutable($row['expires_at']) < new DateTimeImmutable()) {
            return null; // expired
        }
        return $row;
    }

    /** Mark a token as used. */
    public static function consumeToken(string $token): void
    {
        Database::query(
            'UPDATE signing_tokens SET used_at = NOW() WHERE token = ?',
            [$token]
        );
    }
}
