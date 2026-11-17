<?php
declare(strict_types=1);

/**
 * Konfigurasi koneksi database native PHP tanpa dependensi eksternal.
 * Mendukung override opsional melalui file .env di root proyek.
 */
function db_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $env = [];
    $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
    if (is_file($envPath) && is_readable($envPath)) {
        $parsed = parse_ini_file($envPath, false, INI_SCANNER_RAW);
        if (is_array($parsed)) {
            $env = $parsed;
        }
    }

    $get = static function (string $key, string $default) use ($env): string {
        if (isset($env[$key]) && $env[$key] !== '') {
            return (string) $env[$key];
        }
        $val = getenv($key);
        return ($val !== false && $val !== '') ? (string) $val : $default;
    };

    $config = [
        'host'    => $get('DB_HOST', '127.0.0.1'),
        'port'    => $get('DB_PORT', '3306'),
        'user'    => $get('DB_USER', 'root'),
        'pass'    => $get('DB_PASS', ''),
        'charset' => 'utf8mb4',
        'ssl'        => strtolower($get('DB_SSL', 'false')) === 'true',
        'ssl_ca'     => $get('DB_SSL_CA', ''),
        'ssl_ca_pem' => $get('DB_SSL_CA_PEM', ''),
        'ssl_verify' => strtolower($get('DB_SSL_VERIFY', 'false')) === 'true',
        'dbs'     => [
            'app' => $get('DB_APP_NAME', 'db_smbd_kuis'),
            'lab' => $get('DB_LAB_NAME', 'db_penjualan_lab'),
        ],
    ];

    return $config;
}

/**
 * Membuat koneksi PDO untuk database 'app' (db_smbd_kuis) atau 'lab' (db_penjualan_lab).
 * Jika $dbKey === 'server', terhubung ke server MySQL tanpa memilih database.
 */
function get_db_connection(string $dbKey = 'app'): PDO
{
    $cfg = db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $cfg['charset']
    );

    if ($dbKey !== 'server') {
        if (!isset($cfg['dbs'][$dbKey])) {
            throw new InvalidArgumentException("Unknown database key: {$dbKey}");
        }
        $dsn .= ';dbname=' . $cfg['dbs'][$dbKey];
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (!empty($cfg['ssl'])) {
        $caPath = $cfg['ssl_ca'];
        if ($caPath === '' && $cfg['ssl_ca_pem'] !== '') {
            $tmpPem = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smbd_aiven_ca.pem';
            if (!is_file($tmpPem)) {
                @file_put_contents($tmpPem, str_replace('\n', "\n", $cfg['ssl_ca_pem']));
            }
            $caPath = $tmpPem;
        }
        if ($caPath !== '' && is_file($caPath) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
        }
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = !empty($cfg['ssl_verify']) && $caPath !== '';
        }
    }

    return new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
}
