<?php
declare(strict_types=1);

/**
 * Mengatur ulang database sandbox db_penjualan_lab secara deterministik:
 * 1. Create database jika belum ada
 * 2. Drop & Create tabel (sql/db_penjualan_lab_tables.sql)
 * 3. Insert seed data awal (sql/db_penjualan_lab_seed.sql)
 * 4. Create Views, Functions, Procedures, & Triggers (sql/db_penjualan_lab_routines.php)
 * Urutan ini mencegah trigger memotong stok 2x atau mengotori LogKasir saat seeding.
 */
final class SnapshotResetter
{
    public static function resetLabDatabase(?PDO $serverPdo = null): void
    {
        $cfg = db_config();
        $labDb = $cfg['dbs']['lab'];
        $pdo = $serverPdo ?? get_db_connection('server');

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$labDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo->exec("USE `{$labDb}`");

        $sqlDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'sql';
        self::executeSqlStatements($pdo, $sqlDir . DIRECTORY_SEPARATOR . 'db_penjualan_lab_tables.sql');
        self::executeSqlStatements($pdo, $sqlDir . DIRECTORY_SEPARATOR . 'db_penjualan_lab_seed.sql');

        $routines = require $sqlDir . DIRECTORY_SEPARATOR . 'db_penjualan_lab_routines.php';
        foreach ($routines as $stmt) {
            $trimmed = trim($stmt);
            if ($trimmed !== '') {
                $pdo->exec($trimmed);
            }
        }
    }

    private static function executeSqlStatements(PDO $pdo, string $filePath): void
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("SQL file not found: {$filePath}");
        }
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new RuntimeException("Failed reading SQL file: {$filePath}");
        }

        // Aman untuk file tables & seed karena tidak mengandung stored routine/trigger
        $statements = array_filter(
            array_map('trim', explode(";\n", str_replace("\r\n", "\n", $sql))),
            static fn(string $s): bool => $s !== ''
        );

        foreach ($statements as $stmt) {
            $pdo->exec(rtrim($stmt, ';'));
        }
    }
}
