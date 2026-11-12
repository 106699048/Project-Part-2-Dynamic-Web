<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';
date_default_timezone_set('Australia/Melbourne');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');
set_exception_handler(function (Throwable $error): void {
    error_log((string)$error);
    http_response_code(500);
    $title = 'Service unavailable'; $active = '';
    require __DIR__ . '/header.inc';
    echo '<h1>Service temporarily unavailable</h1><p>Please try again later. For a new local installation, follow the database setup steps in README.md.</p>';
    require __DIR__ . '/footer.inc';
});
