<?php
declare(strict_types=1);

/**
 * Memulai PHP session dengan konfigurasi cookie yang aman.
 */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'              => (int) $_SESSION['user_id'],
        'nim_or_username' => (string) ($_SESSION['username'] ?? ''),
        'nama_lengkap'    => (string) ($_SESSION['nama_lengkap'] ?? ''),
    ];
}

function require_auth(): array
{
    $user = current_user();
    if ($user === null) {
        set_flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        redirect_to('login.php');
    }
    return $user;
}

function is_admin(?array $user): bool
{
    return $user !== null && ($user['nim_or_username'] ?? '') === 'mahasiswa_demo';
}

function require_admin(): array
{
    $user = require_auth();
    if (!is_admin($user)) {
        set_flash('error', 'Akses ditolak: fitur Tambah Modul hanya untuk Admin.');
        redirect_to('dashboard.php');
    }
    return $user;
}

function require_guest(): void
{
    if (current_user() !== null) {
        redirect_to('dashboard.php');
    }
}

/**
 * Memproses login mahasiswa dengan prepared statement dan password_verify.
 */
function attempt_login(PDO $pdo, string $username, string $password): array
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return ['ok' => false, 'error' => 'Username/NIM dan password wajib diisi.'];
    }

    $stmt = $pdo->prepare(
        'SELECT id, nim_or_username, nama_lengkap, password_hash FROM app_users WHERE nim_or_username = :u LIMIT 1'
    );
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'error' => 'Username/NIM atau password tidak valid.'];
    }

    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        session_regenerate_id(true);
    }

    $_SESSION['user_id']      = (int) $user['id'];
    $_SESSION['username']     = $user['nim_or_username'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

    return ['ok' => true, 'error' => null];
}

/**
 * Mendaftarkan akun mahasiswa baru dan menginisialisasi progres Modul 1-5.
 */
function register_user(PDO $pdo, string $username, string $nama, string $password): array
{
    $username = trim($username);
    $nama = trim($nama);

    if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
        return ['ok' => false, 'error' => 'Username/NIM harus 3-50 karakter (huruf, angka, titik, garis bawah, atau strip).'];
    }
    if (mb_strlen($nama) < 2 || mb_strlen($nama) > 100) {
        return ['ok' => false, 'error' => 'Nama lengkap harus 2-100 karakter.'];
    }
    if (strlen($password) < 6) {
        return ['ok' => false, 'error' => 'Password minimal 6 karakter.'];
    }

    $check = $pdo->prepare('SELECT id FROM app_users WHERE nim_or_username = :u LIMIT 1');
    $check->execute([':u' => $username]);
    if ($check->fetch()) {
        return ['ok' => false, 'error' => 'Username/NIM sudah terdaftar.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare(
            'INSERT INTO app_users (nim_or_username, nama_lengkap, password_hash) VALUES (:u, :n, :p)'
        );
        $ins->execute([':u' => $username, ':n' => $nama, ':p' => $hash]);
        $userId = (int) $pdo->lastInsertId();

        $prog = $pdo->prepare(
            "INSERT INTO app_learning_progress (user_id, modul_no, status) VALUES (:uid, :m, 'belum')"
        );
        for ($m = 1; $m <= 5; $m++) {
            $prog->execute([':uid' => $userId, ':m' => $m]);
        }

        $pdo->commit();
        return ['ok' => true, 'user_id' => $userId, 'error' => null];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
