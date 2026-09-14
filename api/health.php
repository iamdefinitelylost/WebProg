<?php

declare(strict_types=1);

ob_start();
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function health_response(array $data, int $status = 200): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($status);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

try {
    require_once __DIR__ . '/../db.php';

    $pdo->query('SELECT 1');

    health_response([
        'ok' => true,
        'php' => PHP_VERSION,
        'database' => 'connected',
    ]);
} catch (Throwable $e) {
    error_log('[F1 MERCH health.php] ' . $e->getMessage());

    health_response([
        'ok' => false,
        'php' => PHP_VERSION,
        'database' => 'unavailable',
        'error' => 'Database connection is unavailable.',
    ], 500);
}
