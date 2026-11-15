<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';

if (current_user() !== null) {
    redirect_to('dashboard.php');
}
redirect_to('login.php');
