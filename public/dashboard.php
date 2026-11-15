<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/services/ProgressService.php';

$user = require_auth();
$pdo = app_pdo();
$summary = ProgressService::getDashboardSummary($pdo, $user['id']);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$useAppShell = true;
require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/layouts/navbar.php';
require dirname(__DIR__) . '/app/views/dashboard/index.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
