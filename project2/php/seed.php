<?php
// Seed data only; no destructive resets. Lists are stored one item per line.
function seed_database(PDO $pdo): void {
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
    $find = $pdo->prepare('SELECT reference FROM jobs WHERE reference = ?');
    $add = $pdo->prepare('INSERT INTO jobs (reference,title,description,salary,reporting_line,location,employment_type,responsibilities,essential_requirements,preferable_requirements) VALUES (?,?,?,?,?,?,?,?,?,?)');
    foreach ($jobs as $job) { $find->execute([$job[0]]); if (!$find->fetch()) $add->execute($job); }
    $findUser = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $findUser->execute(['admin']);
    if (!$findUser->fetch()) {
        $addUser = $pdo->prepare('INSERT INTO users (username,password_hash) VALUES (?,?)');
        $addUser->execute(['admin', password_hash('admin', PASSWORD_DEFAULT)]);
    }
    if ((int)$pdo->query('SELECT COUNT(*) FROM about')->fetchColumn() === 0) {
        $member = $pdo->prepare('INSERT INTO about (member_name,student_id,project1_contribution,project2_contribution,quote_original,quote_language,quote_translation,quote_attribution,dream_job,coding_snack,hometown) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        for ($i=1; $i<=3; $i++) $member->execute([
            '[Member ' . $i . ' name]','[Student ID]','[Actual Project 1 contribution]','[Actual Project 2 contribution]',
            '[Quote in the member’s first language]','und','[English translation]','[Language and attribution]',
            '[Dream job]','[Coding snack]','[Hometown]',
        ]);
    }
}
