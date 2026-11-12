-- setup.php creates the database named in settings.php before running this file.
-- The eoi table is created by ensure_eoi_table() in database.php.
CREATE TABLE IF NOT EXISTS jobs (
    reference CHAR(5) PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    salary VARCHAR(160) NOT NULL,
    reporting_line VARCHAR(120) NOT NULL,
    location VARCHAR(120) NOT NULL,
    employment_type VARCHAR(80) NOT NULL,
    responsibilities TEXT NOT NULL,
    essential_requirements TEXT NOT NULL,
    preferable_requirements TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS about (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(40) NOT NULL,
    project1_contribution TEXT NOT NULL,
    project2_contribution TEXT NOT NULL,
    quote_original TEXT NOT NULL,
    quote_language VARCHAR(35) NOT NULL,
    quote_translation TEXT NOT NULL,
    quote_attribution VARCHAR(200) NOT NULL,
    dream_job VARCHAR(100) NOT NULL,
    coding_snack VARCHAR(100) NOT NULL,
    hometown VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
