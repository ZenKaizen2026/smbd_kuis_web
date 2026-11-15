<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';

require_guest();

$error = null;
$oldUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $oldUsername = (string) ($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $pdo = app_pdo();
        $result = attempt_login($pdo, $oldUsername, $password);
        if ($result['ok']) {
            set_flash('success', 'Selamat datang kembali, ' . $_SESSION['nama_lengkap'] . '!');
            redirect_to('dashboard.php');
        }
        $error = $result['error'];
    }
}

$pageTitle = 'Login';
require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/auth/login.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
