<?php
declare(strict_types=1);
// CLI-only provisioning prevents strangers creating/resetting marker accounts.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/seed.php';
require __DIR__ . '/settings.php';
try {
    if (!preg_match('/\A[A-Za-z0-9_]+\z/', $sql_db)) throw new RuntimeException('Use letters, digits and underscores for the database name.');
    $server = db(false);
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . $sql_db . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo = db();
    $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
    ensure_eoi_table($pdo);
    $pdo->beginTransaction();
    seed_database($pdo);
    $pdo->commit();
    echo "Setup complete. Existing data preserved.\nLocal marker login: admin / admin (unless previously changed).\n";
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Setup failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
