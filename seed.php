<?php
/* Adds starter jobs, profiles, and a manager account. */

// Seed data only; no destructive resets. Lists are stored one item per line.
function seed_database(mysqli $database_connection): void {
    $jobs = [
        ['FE001','Frontend Engineer','Build accessible patient-facing interfaces for appointments and care information.','$85,000–$110,000 AUD per year, plus superannuation','Frontend Engineering Lead','Melbourne, Australia · Hybrid','Full-time',
        "Implement responsive, accessible interfaces.\nIntegrate APIs and handle loading and error states.\nReview code and test across browsers.",
        "Strong HTML, CSS and JavaScript skills.\nExperience with version control and responsive layouts.\nAbility to communicate clearly and test your work.",
        "Experience with React and automated UI tests.\nFamiliarity with accessibility testing."],
        ['BE001','Backend Engineer','Develop reliable APIs and data services that support patient platforms.','$95,000–$125,000 AUD per year, plus superannuation','Backend Engineering Lead','Melbourne, Australia · Hybrid','Full-time',
        "Build and maintain secure application APIs.\nDesign SQL data models and improve query performance.\nWrite tests, review code and investigate service failures.",
        "Practical experience with Ruby, Python or a similar server language.\nKnowledge of SQL, HTTP and API design.\nUnderstanding of authentication, testing and version control.",
        "Experience with cloud deployment and monitoring.\nExperience handling sensitive data responsibly."],
    ];
    $find = mysqli_prepare($database_connection, 'SELECT reference FROM jobs WHERE reference = ?');
    $add = mysqli_prepare($database_connection, 'INSERT INTO jobs (reference,title,description,salary,reporting_line,location,employment_type,responsibilities,essential_requirements,preferable_requirements) VALUES (?,?,?,?,?,?,?,?,?,?)');
    foreach ($jobs as $job) {
        mysqli_stmt_execute($find, [$job[0]]);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($find))) {
            mysqli_stmt_execute($add, $job);
        }
    }
    $findUser = mysqli_prepare($database_connection, 'SELECT id FROM users WHERE username = ?');
    mysqli_stmt_execute($findUser, ['admin']);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($findUser))) {
        $addUser = mysqli_prepare($database_connection, 'INSERT INTO users (username,password_hash) VALUES (?,?)');
        mysqli_stmt_execute($addUser, ['admin', password_hash('admin', PASSWORD_DEFAULT)]);
    }
    if ((int)mysqli_fetch_row(mysqli_query($database_connection, 'SELECT COUNT(*) FROM about'))[0] === 0) {
        $member = mysqli_prepare($database_connection, 'INSERT INTO about (member_name,student_id,project1_contribution,project2_contribution,quote_original,quote_language,quote_translation,quote_attribution,dream_job,coding_snack,hometown) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $profiles = [
            [
                'Htoo Nay Lin',
                '106687179',
                'Built the front-end structure using semantic HTML across four core pages (index.html, jobs.html, apply.html, and about.html), incorporating data tables and interactive form fields.',
                'Led database and form development. Created the database tables, validated and saved application data with PHP and prepared statements, and implemented job search. The main challenge was validating every field without JavaScript. His standout feature was the application handler, which creates the EOI table when needed and generates a unique EOI number.',
                'Work hard, play hard.',
                'en',
                '',
                '',
                'Software Engineer',
                'Pizza',
                'Yangon',
            ],
            [
                'Yuri Chingabo de Almeida Mabutana',
                '106699048',
                'Jira board, task tracking, GitHub repository, branch management, merging, integration testing, HTML validation, accessibility checks, final submission',
                'Led the admin portal and security work. Built the login page and HR dashboard, and organized the final group demo. He used PHP sessions, password hashing, and dynamic SQL queries to secure access and support multiple filters. His standout feature was the dashboard, where managers can filter applications by name or job reference and update their status.',
                'The mind is everything. What you think you become.',
                'en',
                '',
                '',
                'Tech Executive',
                'Ramen',
                'Maputo',
            ],
            [
                'Cheong Kar Keat',
                '106681058',
                'Develop styles.css, responsive layout, navigation styling, form Grid/Flexbox, floated aside, background graphic, hover effects, accessibility styling',
                'Led front-end and content integration. Converted HTML pages to PHP, built shared headers and footers, and displayed jobs and team profiles from MySQL. He tested query loops and corrected relative paths to keep the layout intact. His standout feature was the dynamic job board, which loads job details from the database.',
                'It is never too late to be what you might have been.',
                'en',
                '',
                '',
                'Game Developer',
                'Nasi Lemak',
                'Petaling Jaya',
            ],
        ];
        foreach ($profiles as $profile) {
            mysqli_stmt_execute($member, $profile);
        }
    }
}
