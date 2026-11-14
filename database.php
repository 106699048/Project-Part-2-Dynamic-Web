<?php
/* Connects to MySQL and prepares database tables. */

declare(strict_types = 1);
// All connections obtain their credentials from the one settings file.
function db(bool $with_database = true): mysqli {
    require __DIR__ . '/settings.php';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $database_name = null;
    if ($with_database) {
        $database_name = $sql_db;
    }
    $database_connection = mysqli_connect($host, $user, $pwd, $database_name);
    mysqli_set_charset($database_connection, 'utf8mb4');
    return $database_connection;
}

function first_statement_value(mysqli_stmt $statement) {
    $row = mysqli_fetch_row(mysqli_stmt_get_result($statement));
    if ($row === null) {
        return false;
    }
    return $row[0];
}

function ensure_eoi_table(mysqli $database_connection): void {
    // Also called by process_eoi.php: creates the table if it is absent.
    mysqli_query($database_connection, "CREATE TABLE IF NOT EXISTS eoi (
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
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci");
}
