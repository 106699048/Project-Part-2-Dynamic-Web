<?php
/* Creates and seeds the local project database. */

declare(strict_types = 1);
// CLI-only provisioning prevents strangers creating/resetting marker accounts.
if (PHP_SAPI !== 'cli') { http_response_code(404);
exit; }
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/seed.php';
require __DIR__ . '/settings.php';
try {
    if (!preg_match('/\A[A-Za-z0-9_]+\z/', $sql_db)) throw new RuntimeException('Use letters, digits and underscores for the database name.');
    $server = db(false);
    mysqli_query($server, 'CREATE DATABASE IF NOT EXISTS `' . $sql_db . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $database_connection = db();
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    foreach (explode(';', $schema) as $statement) {
        if (trim($statement) !== '') {
            mysqli_query($database_connection, $statement);
        }
    }
    ensure_eoi_table($database_connection);
    mysqli_begin_transaction($database_connection);
    seed_database($database_connection);
    mysqli_commit($database_connection);
    echo "Setup complete. Existing data preserved.\nLocal marker login: admin / admin (unless previously changed).\n";
} catch (Throwable $error) {
    if (isset($database_connection)) mysqli_rollback($database_connection);
    fwrite(STDERR, 'Setup failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
