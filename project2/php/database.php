<?php
declare(strict_types=1);
// All connections obtain their credentials from the one settings file.
function db(bool $with_database = true): PDO {
    require __DIR__ . '/settings.php';
    $dsn = 'mysql:host=' . $host . ';charset=utf8mb4';
    if ($with_database) $dsn .= ';dbname=' . $sql_db;
    return new PDO($dsn, $user, $pwd, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
function ensure_eoi_table(PDO $pdo): void {
    // Also called by process_eoi.php: creates the table if it is absent.
    $pdo->exec("CREATE TABLE IF NOT EXISTS eoi (
        EOInumber BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        job_reference CHAR(5) NOT NULL,
        first_name VARCHAR(20) NOT NULL,
        last_name VARCHAR(20) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender VARCHAR(20) NOT NULL,
        street_address VARCHAR(40) NOT NULL,
        suburb VARCHAR(40) NOT NULL,
        state VARCHAR(3) NOT NULL,
        postcode CHAR(4) NOT NULL,
        email VARCHAR(254) NOT NULL,
        phone VARCHAR(12) NOT NULL,
        skills TEXT NOT NULL,
        other_skills TEXT NOT NULL,
        status ENUM('New','Current','Final') NOT NULL DEFAULT 'New',
        submission_key CHAR(64) NOT NULL UNIQUE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_eoi_reference (job_reference),
        INDEX idx_eoi_name (last_name, first_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
