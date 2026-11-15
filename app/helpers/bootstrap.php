<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/auth.php';

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
        app_log('ERROR', 'Database connection failed', ['error' => $e->getMessage()]);
        http_response_code(503);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Layanan Database Tidak Tersedia</title></head>';
        echo '<body style="font-family:system-ui,sans-serif;background:#EEF2FF;color:#1E1B4B;padding:2rem;">';
        echo '<div style="max-width:540px;margin:3rem auto;background:#fff;padding:2rem;border-radius:12px;border:1px solid #C7D2FE;">';
        echo '<h1 style="margin-top:0;font-size:1.25rem;">Koneksi Database Belum Siap</h1>';
        echo '<p>Pastikan service <strong>MySQL XAMPP</strong> aktif dan skrip setup database sudah dijalankan.</p>';
        echo '</div></body></html>';
        exit;
    }
}
