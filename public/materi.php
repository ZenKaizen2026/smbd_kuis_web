<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/services/CustomModuleService.php';

$user = require_auth();
$isAdmin = is_admin_user($user);
$pdo = app_pdo();
$catalog = require dirname(__DIR__) . '/app/data/modules_catalog.php';

$filterModul = isset($_GET['modul']) ? (int) $_GET['modul'] : null;
if ($filterModul !== null && ($filterModul < 1 || $filterModul > 5)) {
    $filterModul = null;
}

$error = null;
$old = ['modul_no' => $filterModul ?? 1, 'judul' => '', 'isi_materi' => '', 'contoh_sql' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isAdmin) {
        set_flash('error', 'Akses ditolak: hanya Admin (mahasiswa_demo) yang dapat menambah atau menghapus materi.');
        redirect_to('materi.php' . ($filterModul ? '?modul=' . $filterModul : ''));
    }

    if (!csrf_verify($_POST['_csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'create') {
            $old['modul_no'] = (int) ($_POST['modul_no'] ?? 1);
            $old['judul'] = (string) ($_POST['judul'] ?? '');
            $old['isi_materi'] = (string) ($_POST['isi_materi'] ?? '');
            $old['contoh_sql'] = (string) ($_POST['contoh_sql'] ?? '');

            $res = CustomModuleService::create(
                $pdo,
                $user['id'],
                $old['modul_no'],
                $old['judul'],
                $old['isi_materi'],
                $old['contoh_sql']
            );
            if ($res['ok']) {
                set_flash('success', 'Materi tambahan berhasil dipublikasikan ke sistem!');
                redirect_to('materi.php' . ($filterModul ? '?modul=' . $filterModul : ''));
            }
            $error = $res['error'];
        } elseif ($action === 'delete') {
            $noteId = (int) ($_POST['note_id'] ?? 0);
            if ($noteId > 0 && CustomModuleService::delete($pdo, $user['id'], $noteId)) {
                set_flash('success', 'Materi tambahan berhasil dihapus.');
            } else {
                set_flash('error', 'Materi tidak ditemukan atau tidak memiliki akses.');
            }
            redirect_to('materi.php' . ($filterModul ? '?modul=' . $filterModul : ''));
        }
    }
}

$notes = CustomModuleService::listAll($pdo, $filterModul);
$pageTitle = 'Materi Tambahan';
$activeNav = 'materi';
$useAppShell = true;

require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/layouts/navbar.php';
require dirname(__DIR__) . '/app/views/materi/index.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
