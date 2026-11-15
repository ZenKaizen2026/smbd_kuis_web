<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/services/ExtraModuleService.php';

$user = require_admin();
$pdo = app_pdo();

$error = null;
$old = ['modul_code' => 'Modul 06', 'judul' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'upload') {
            $old['modul_code'] = (string) ($_POST['modul_code'] ?? '');
            $old['judul'] = (string) ($_POST['judul'] ?? '');
            $res = ExtraModuleService::processAndCreate(
                $pdo,
                $user['id'],
                $_FILES['modul_file'] ?? [],
                $old['modul_code'],
                $old['judul']
            );
            if ($res['ok']) {
                set_flash('success', 'Modul baru berhasil diproses dan ditambahkan ke Dashboard!');
                redirect_to('admin_modul.php');
            }
            $error = $res['error'];
        } elseif ($action === 'delete') {
            $modId = (int) ($_POST['module_id'] ?? 0);
            if ($modId > 0 && ExtraModuleService::delete($pdo, $modId)) {
                set_flash('success', 'Modul tambahan berhasil dihapus.');
            } else {
                set_flash('error', 'Modul tidak ditemukan.');
            }
            redirect_to('admin_modul.php');
        }
    }
}

$extraModules = ExtraModuleService::listAll($pdo);
$pageTitle = 'Tambah Modul (Admin)';
$activeNav = 'admin_modul';
$useAppShell = true;

require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/layouts/navbar.php';
require dirname(__DIR__) . '/app/views/admin/modul.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
