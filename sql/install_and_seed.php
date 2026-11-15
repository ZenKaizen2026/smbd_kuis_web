<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/auth.php';
require_once dirname(__DIR__) . '/app/services/SnapshotResetter.php';

echo "=== Setup Database SMBDL Quiz & Lab ===\n";

$serverPdo = get_db_connection('server');
$appSql = file_get_contents(__DIR__ . '/db_smbd_kuis.sql');
$statements = array_filter(
    array_map('trim', explode(";\n", str_replace("\r\n", "\n", (string) $appSql))),
    static fn(string $s): bool => $s !== ''
);
foreach ($statements as $stmt) {
    $serverPdo->exec(rtrim($stmt, ';'));
}
echo "[OK] Database db_smbd_kuis & tabel aplikasi siap.\n";

$appPdo = get_db_connection('app');
$check = $appPdo->prepare('SELECT id FROM app_users WHERE nim_or_username = :u LIMIT 1');
$check->execute([':u' => 'mahasiswa_demo']);
if (!$check->fetch()) {
    $res = register_user($appPdo, 'mahasiswa_demo', 'Mahasiswa Demo SI167', 'demo1234');
    if ($res['ok']) {
        echo "[OK] Akun demo seeder dibuat: mahasiswa_demo / demo1234\n";
    }
} else {
    echo "[SKIP] Akun demo mahasiswa_demo sudah ada.\n";
}

SnapshotResetter::resetLabDatabase($serverPdo);
echo "[OK] Database sandbox db_penjualan_lab siap (tabel + seed + views + UDF + SP + triggers).\n";
