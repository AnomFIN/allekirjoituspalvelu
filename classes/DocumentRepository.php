<?php
declare(strict_types=1);

/**
 * DocumentRepository — all CRUD operations for the documents table.
 */
class DocumentRepository
{
    // ─── Read ─────────────────────────────────────────────────────────────────

    public static function findById(int $id): ?array
    {
        return Database::fetchOne('SELECT * FROM documents WHERE id = ?', [$id]);
    }

    public static function findByUuid(string $uuid): ?array
    {
        return Database::fetchOne('SELECT * FROM documents WHERE uuid = ?', [$uuid]);
    }

    public static function findAll(int $limit = ITEMS_PER_PAGE, int $offset = 0): array
    {
        return Database::fetchAll(
            'SELECT * FROM documents ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
    }

    public static function countAll(): int
    {
        return (int)Database::fetchScalar('SELECT COUNT(*) FROM documents');
    }

    public static function findSent(int $limit = ITEMS_PER_PAGE, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT * FROM documents WHERE status IN ('sent','signed','rejected')
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public static function countSent(): int
    {
        return (int)Database::fetchScalar(
            "SELECT COUNT(*) FROM documents WHERE status IN ('sent','signed','rejected')"
        );
    }

    public static function findDrafts(): array
    {
        return Database::fetchAll(
            "SELECT * FROM documents WHERE status = 'draft' ORDER BY created_at DESC"
        );
    }

    /** Fetch stats for dashboard. */
    public static function getStats(): array
    {
        $row = Database::fetchOne(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'draft')    AS drafts,
                SUM(status = 'sent')     AS sent,
                SUM(status = 'signed')   AS signed,
                SUM(status = 'rejected') AS rejected
             FROM documents"
        );
        return $row ?? ['total' => 0, 'drafts' => 0, 'sent' => 0, 'signed' => 0, 'rejected' => 0];
    }

    // ─── Write ────────────────────────────────────────────────────────────────

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO documents
                (uuid, title, original_filename, stored_filename, file_path, mime_type,
                 size_bytes, status, sender_name, sender_email, message_body, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['uuid'],
                $data['title'],
                $data['original_filename'],
                $data['stored_filename'],
                $data['file_path'],
                $data['mime_type'],
                $data['size_bytes'],
                $data['status'] ?? 'draft',
                $data['sender_name']  ?? '',
                $data['sender_email'] ?? '',
                $data['message_body'] ?? '',
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::query(
            'UPDATE documents SET status = ?, updated_at = NOW() WHERE id = ?',
            [$status, $id]
        );
    }

    public static function update(int $id, array $data): void
    {
        $sets   = [];
        $values = [];
        foreach ($data as $col => $val) {
            $sets[]   = "`$col` = ?";
            $values[] = $val;
        }
        $values[] = $id;
        Database::query(
            'UPDATE documents SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE id = ?',
            $values
        );
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM documents WHERE id = ?', [$id]);
    }

    /** Check if all signers have signed — update document status accordingly. */
    public static function syncStatus(int $documentId): void
    {
        $signers = Database::fetchAll(
            'SELECT status FROM signers WHERE document_id = ?',
            [$documentId]
        );
        if (empty($signers)) {
            return;
        }
        $allSigned   = !array_filter($signers, fn($s) => $s['status'] !== 'signed');
        $anyRejected = (bool)array_filter($signers, fn($s) => $s['status'] === 'rejected');

        if ($anyRejected) {
            self::updateStatus($documentId, 'rejected');
        } elseif ($allSigned) {
            self::updateStatus($documentId, 'signed');
        }
    }
}
