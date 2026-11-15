<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['_csrf_token'] ?? null)) {
    redirect_to('dashboard.php');
}

logout_user();
start_secure_session();
set_flash('success', 'Kamu telah keluar dari sesi.');
redirect_to('login.php');
