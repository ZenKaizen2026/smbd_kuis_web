<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/code_ui.php';

start_secure_session();

/**
 * Mengambil koneksi DB aplikasi dengan penanganan error aman agar tidak membocorkan kredensial.
 */
function app_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $pdo = get_db_connection('app');
        return $pdo;
    } catch (Throwable $e) {
        if (str_contains($e->getMessage(), 'Unknown database') || (string) $e->getCode() === '1049') {
            try {
                $serverPdo = get_db_connection('server');
                $cfg = db_config();
                $appDb = $cfg['dbs']['app'];
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$appDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                $serverPdo->exec("USE `{$appDb}`");

                $appSql = file_get_contents(dirname(__DIR__, 2) . '/sql/db_smbd_kuis.sql');
                $statements = array_filter(
                    array_map('trim', explode(";\n", str_replace("\r\n", "\n", (string) $appSql))),
                    static fn (string $s): bool => $s !== '' && !preg_match('/^(CREATE DATABASE|USE)\b/i', $s)
                );
                foreach ($statements as $stmt) {
                    $serverPdo->exec(rtrim($stmt, ';'));
                }

                $pdo = get_db_connection('app');
                $check = $pdo->prepare('SELECT id FROM app_users WHERE nim_or_username = :u LIMIT 1');
                $check->execute([':u' => 'mahasiswa_demo']);
                if (!$check->fetch()) {
                    register_user($pdo, 'mahasiswa_demo', 'Mahasiswa Demo SI167', 'demo1234');
                }

                require_once dirname(__DIR__) . '/services/SnapshotResetter.php';
                SnapshotResetter::resetLabDatabase($serverPdo);
                return $pdo;
            } catch (Throwable $initErr) {
                app_log('ERROR', 'Auto-bootstrap database failed', ['error' => $initErr->getMessage()]);
            }
        }

        app_log('ERROR', 'Database connection failed', ['error' => $e->getMessage()]);
        http_response_code(503);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Layanan Database Tidak Tersedia</title></head>';
        echo '<body style="font-family:system-ui,sans-serif;background:#EEF2FF;color:#1E1B4B;padding:2rem;">';
        echo '<div style="max-width:540px;margin:3rem auto;background:#fff;padding:2rem;border-radius:12px;border:1px solid #C7D2FE;">';
        echo '<h1 style="margin-top:0;font-size:1.25rem;">Koneksi Database Belum Siap</h1>';
        echo '<p>Pastikan layanan database aktif dan konfigurasi Environment Variables (DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_SSL) sudah benar.</p>';
        echo '</div></body></html>';
        exit;
    }
}
