<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/services/ExtraModuleService.php';

require_auth();
$pdo = app_pdo();
$id = (int) ($_GET['id'] ?? 0);
$module = $id > 0 ? ExtraModuleService::findById($pdo, $id) : null;

if (!$module || $module['source_type'] !== 'pdf') {
    http_response_code(404);
    exit('File PDF tidak ditemukan.');
}

$path = ExtraModuleService::uploadDir() . DIRECTORY_SEPARATOR . basename($module['stored_filename']);
if (!is_file($path)) {
    http_response_code(404);
    exit('Berkas tidak ditemukan di storage.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $module['original_filename']) . '"');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . (string) filesize($path));
readfile($path);
exit;
