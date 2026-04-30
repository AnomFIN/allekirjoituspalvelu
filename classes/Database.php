<?php
declare(strict_types=1);

/**
 * Database — PDO singleton wrapper.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
            } catch (PDOException $e) {
                log_error('Database connection failed', ['error' => $e->getMessage()]);
                throw new RuntimeException('Tietokantayhteyttä ei voitu muodostaa.');
            }
        }
        return self::$instance;
    }

    /** Execute a query and return the statement. */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $pdo  = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch all rows. */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Fetch a single row or null. */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    /** Fetch a single scalar value or null. */
    public static function fetchScalar(string $sql, array $params = []): mixed
    {
        $row = self::query($sql, $params)->fetch(PDO::FETCH_NUM);
        return $row !== false ? $row[0] : null;
    }

    /** Return the last insert ID. */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /** Begin a transaction. */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /** Commit the current transaction. */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /** Roll back the current transaction. */
    public static function rollback(): void
    {
        if (self::getInstance()->inTransaction()) {
            self::getInstance()->rollBack();
        }
    }

    /** Check whether a table column exists. */
    public static function columnExists(string $table, string $column): bool
    {
        $sql = 'SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?';
        return (int)self::fetchScalar($sql, [$table, $column]) > 0;
    }

    /** Check whether a table exists. */
    public static function tableExists(string $table): bool
    {
        $sql = 'SELECT COUNT(*) FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?';
        return (int)self::fetchScalar($sql, [$table]) > 0;
    }
}
