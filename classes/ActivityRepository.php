<?php
declare(strict_types=1);

/**
 * ActivityRepository — log and query document_events.
 */
class ActivityRepository
{
    public static function log(int $documentId, string $event, string $description = '', string $actorEmail = ''): void
    {
        Database::query(
            'INSERT INTO document_events (document_id, event, description, actor_email, created_at)
             VALUES (?, ?, ?, ?, NOW())',
            [$documentId, $event, $description, $actorEmail]
        );
    }

    public static function findByDocument(int $documentId, int $limit = 50): array
    {
        return Database::fetchAll(
            'SELECT * FROM document_events WHERE document_id = ? ORDER BY created_at DESC LIMIT ?',
            [$documentId, $limit]
        );
    }

    public static function getRecentActivity(int $limit = 10): array
    {
        return Database::fetchAll(
            'SELECT de.*, d.title AS document_title
             FROM document_events de
             JOIN documents d ON d.id = de.document_id
             ORDER BY de.created_at DESC
             LIMIT ?',
            [$limit]
        );
    }
}
