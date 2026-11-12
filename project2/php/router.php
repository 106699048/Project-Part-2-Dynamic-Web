<?php
// Optional safe router for the built-in PHP development server.
// php -S localhost:8000 router.php
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$target = realpath(__DIR__ . $path);
$base = basename($path);
$public = ['index.php','jobs.php','search.php','apply.php','process_eoi.php','confirmation.php','about.php','login.php','logout.php','manage.php'];
if ($path === '/') { require __DIR__ . '/index.php'; return true; }
if ($target === false || strpos($target, __DIR__ . DIRECTORY_SEPARATOR) !== 0 || !is_file($target)) { http_response_code(404); echo 'Not found'; return true; }
if (dirname($path)==='/' && in_array($base,$public,true)) { require $target; return true; }
if (preg_match('~^/(images|styles)/[A-Za-z0-9_./-]+\.(png|jpg|jpeg|svg|gif|webp|css)$~i',$path)) return false;
http_response_code(404); echo 'Not found'; return true;
