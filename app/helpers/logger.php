<?php
declare(strict_types=1);

/**
 * Mencatat log aplikasi ke storage/logs/app.log tanpa membocorkan detail ke UI.
 */
function app_log(string $level, string $message, array $context = []): void
{
    $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $logFile = $logDir . DIRECTORY_SEPARATOR . 'app.log';
    $timestamp = date('Y-m-d H:i:s');
    $ctxStr = $context !== [] ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $line = "[{$timestamp}] [{$level}] {$message}{$ctxStr}" . PHP_EOL;

    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    if (in_array(strtoupper($level), ['ERROR', 'WARNING'], true)) {
        @error_log(trim($line));
    }
}
