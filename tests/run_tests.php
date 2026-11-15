<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/csrf.php';
require_once dirname(__DIR__) . '/app/helpers/auth.php';
require_once dirname(__DIR__) . '/app/services/SnapshotResetter.php';
require_once dirname(__DIR__) . '/app/services/ProgressService.php';
require_once dirname(__DIR__) . '/app/services/CustomModuleService.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$passed = 0;
$failed = 0;

function test_case(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        echo "[PASS] {$name}\n";
        $passed++;
    } catch (Throwable $e) {
        echo "[FAIL] {$name}: {$e->getMessage()}\n";
        $failed++;
    }
}

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

test_case('PDO connection to db_smbd_kuis & db_penjualan_lab', static function (): void {
    $app = get_db_connection('app');
    $lab = get_db_connection('lab');
    assert_true((int) $app->query('SELECT 1')->fetchColumn() === 1, 'db_smbd_kuis ping failed');
    assert_true((int) $lab->query('SELECT 1')->fetchColumn() === 1, 'db_penjualan_lab ping failed');
});

test_case('Seeder account mahasiswa_demo exists and password verifies', static function (): void {
    $app = get_db_connection('app');
    $stmt = $app->prepare('SELECT id, password_hash FROM app_users WHERE nim_or_username = :u LIMIT 1');
    $stmt->execute([':u' => 'mahasiswa_demo']);
    $row = $stmt->fetch();
    assert_true(is_array($row), 'Seeder user mahasiswa_demo not found');
    assert_true(password_verify('demo1234', $row['password_hash']), 'Seeder password verification failed');
});

test_case('CSRF token generation & verification', static function (): void {
    $_SESSION = [];
    $tok = csrf_token();
    assert_true(strlen($tok) === 64, 'CSRF token must be 64 hex chars');
    assert_true(csrf_verify($tok), 'Valid CSRF token rejected');
    assert_true(!csrf_verify('invalid_token'), 'Invalid CSRF token accepted');
    assert_true(!csrf_verify(null), 'Null CSRF token accepted');
});

test_case('Register, duplicate check, login success & failure (isolated cleanup)', static function (): void {
    $app = get_db_connection('app');
    $testUser = 'test_mhs_' . bin2hex(random_bytes(4));

    try {
        $reg = register_user($app, $testUser, 'Tester Unit', 'secret123');
        assert_true($reg['ok'] === true, 'Register failed: ' . ($reg['error'] ?? ''));

        $dup = register_user($app, $testUser, 'Tester Unit', 'secret123');
        assert_true($dup['ok'] === false, 'Duplicate username must be rejected');

        $badLogin = attempt_login($app, $testUser, 'wrongpass');
        assert_true($badLogin['ok'] === false, 'Wrong password must fail');

        $_SESSION = [];
        $goodLogin = attempt_login($app, $testUser, 'secret123');
        assert_true($goodLogin['ok'] === true, 'Valid login failed');
        assert_true(($_SESSION['username'] ?? '') === $testUser, 'Session username mismatch');
    } finally {
        $del = $app->prepare('DELETE FROM app_users WHERE nim_or_username = :u');
        $del->execute([':u' => $testUser]);
    }
});

test_case('SnapshotResetter preserves exact seed stock and clean LogKasir', static function (): void {
    SnapshotResetter::resetLabDatabase();
    $lab = get_db_connection('lab');
    $stokP001 = (int) $lab->query("SELECT fcCekStok('P001')")->fetchColumn();
    $logCount = (int) $lab->query('SELECT COUNT(*) FROM logkasir')->fetchColumn();
    assert_true($stokP001 === 97, "Expected P001 stok=97 after reset, got {$stokP001}");
    assert_true($logCount === 0, "Expected logkasir count=0 after reset, got {$logCount}");
});

test_case('ProgressService & CustomModuleService CRUD + IDOR check', static function (): void {
    $app = get_db_connection('app');
    $stmt = $app->prepare('SELECT id FROM app_users WHERE nim_or_username = :u LIMIT 1');
    $stmt->execute([':u' => 'mahasiswa_demo']);
    $demoId = (int) $stmt->fetchColumn();
    assert_true($demoId > 0, 'Demo user ID not found');

    $summary = ProgressService::getDashboardSummary($app, $demoId);
    assert_true(count($summary['modules']) === 5, 'Dashboard must return 5 modules');

    $created = CustomModuleService::create($app, $demoId, 3, 'Catatan Alias', 't1/n adalah alias tabel', 'SELECT n.Nonota FROM Nota n;');
    assert_true($created['ok'] === true, 'CustomModule create failed');
    $noteId = (int) $created['id'];

    try {
        assert_true(is_admin_user(['nim_or_username' => 'mahasiswa_demo']) === true, 'mahasiswa_demo must be admin');
        assert_true(is_admin_user(['nim_or_username' => 'mhs_biasa']) === false, 'Regular student must not be admin');

        $list = CustomModuleService::listAll($app, 3);
        assert_true(count($list) >= 1, 'Expected at least 1 custom note in listAll for Modul 3');

        // IDOR check: user ID lain (999999) tidak boleh bisa menghapus catatan user ini
        $idorDelete = CustomModuleService::delete($app, 999999, $noteId);
        assert_true($idorDelete === false, 'IDOR: foreign user must not delete note');

        $ownerDelete = CustomModuleService::delete($app, $demoId, $noteId);
        assert_true($ownerDelete === true, 'Owner delete failed');
    } finally {
        CustomModuleService::delete($app, $demoId, $noteId);
    }
});

echo "\nSummary: {$passed} passed, {$failed} failed.\n";
exit($failed > 0 ? 1 : 0);
