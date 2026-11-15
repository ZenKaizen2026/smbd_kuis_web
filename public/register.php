<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';

require_guest();

$error = null;
$old = ['username' => '', 'nama_lengkap' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman.';
    } else {
        $old['username'] = (string) ($_POST['username'] ?? '');
        $old['nama_lengkap'] = (string) ($_POST['nama_lengkap'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if ($password !== $confirm) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $pdo = app_pdo();
            $result = register_user($pdo, $old['username'], $old['nama_lengkap'], $password);
            if ($result['ok']) {
                set_flash('success', 'Registrasi berhasil! Silakan login dengan akun barumu.');
                redirect_to('login.php');
            }
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Daftar Mahasiswa';
require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/auth/register.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
