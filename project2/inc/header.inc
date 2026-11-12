<?php if (!function_exists('e')) { http_response_code(404); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Careers') ?> | Halcyon Health</title>
<link rel="icon" href="images/logo.png">
<link rel="stylesheet" href="styles/style.css">
<!-- Limited embedded CSS retained from Project 1. -->
<style>.page-kicker { letter-spacing: .14em; }</style>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header"><div class="container header-inner">
<a class="brand" href="index.php"><img src="images/logo.png" alt="" width="76" height="40"><span>Halcyon Health<small>Digital health, made human.</small></span></a>
<?php require __DIR__ . '/nav.inc'; ?>
</div></header>
<main id="main" class="container">
<p class="page-kicker" style="text-transform: uppercase;">People. Care. Technology.</p>
