<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/services/ExtraModuleService.php';

$user = require_auth();
$pdo = app_pdo();
$id = (int) ($_GET['id'] ?? 0);
$module = $id > 0 ? ExtraModuleService::findById($pdo, $id) : null;

if (!$module) {
    set_flash('error', 'Modul tambahan tidak ditemukan.');
    redirect_to('dashboard.php');
}

$pageTitle = $module['judul'];
$activeNav = 'dashboard';
$useAppShell = true;

require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/layouts/navbar.php';
require dirname(__DIR__) . '/app/views/modul/view.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
