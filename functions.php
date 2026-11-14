<?php
/* Provides shared input, validation, and session functions. */

declare(strict_types = 1);
function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function clean($value): string {
    if (!is_string($value)) {
        return '';
    }
    return trim(stripslashes($value));
}
function redirect(string $path): void {
    header('Location: ' . $path, true, 303);
    exit;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): void {
    echo '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf_token(), $_POST['csrf'])) {
        http_response_code(403);
        $title = 'Request expired';
        $active = '';
        require __DIR__ . '/header.inc';
        echo '<h1>Request expired</h1><p>Please reload the form and try again. No changes were made.</p>';
        require __DIR__ . '/footer.inc';
        exit;
    }
}
function require_manager(): void {
    if (empty($_SESSION['manager_id'])) redirect('login.php');
    if (time() - ($_SESSION['last_activity'] ?? 0) > 1800) {
        unset($_SESSION['manager_id'], $_SESSION['manager_name']);
        redirect('login.php?expired=1');
    }
    $_SESSION['last_activity'] = time();
}
function text_length(string $value): int {
    // Count UTF-8 characters without requiring mbstring.
    $count = preg_match_all('/./us', $value);
    if ($count === false) {
        return PHP_INT_MAX;
    }
    return $count;
}
function normalize_application_text(array $input): array {
    $data = [];
    $errors = [];
    $fields = ['job-reference','first-name','last-name','dob','gender','street','suburb','state','postcode','email','phone','other-skills'];
    foreach ($fields as $field) {
        $data[$field] = clean($input[$field] ?? '');
        if (isset($input[$field]) && !is_string($input[$field])) $errors[$field] = 'Enter a single text value.';
    }
    return [$data, $errors];
}
function validate_application_skills(array $input, array &$data, array &$errors): void {
    $allowed = ['Team communication','Problem solving','Version control'];
    $skills = $input['skills'] ?? [];
    $data['skills'] = [];
    if (!is_array($skills)) {
        $errors['skills'] = 'Select the three core skills.';
    } else {
        foreach ($skills as $skill) {
            if (!is_string($skill) || !in_array($skill, $allowed, true)) $errors['skills'] = 'Only the listed skills are accepted.';
            else $data['skills'][] = $skill;
        }
        $data['skills'] = array_values(array_unique($data['skills']));
    }
    if (count($data['skills']) !== 3) $errors['skills'] = 'Confirm all three core skills, as required by this form.';
}
function validate_application(array $input): array {
    [$data, $errors] = normalize_application_text($input);
    if (!preg_match('/\A[A-Za-z0-9]{5}\z/', $data['job-reference'])) $errors['job-reference'] = 'Use exactly five letters or digits.';
    foreach (['first-name','last-name'] as $field) {
        if (!preg_match('/\A[A-Za-z]{1,20}\z/', $data[$field])) $errors[$field] = 'Use 1–20 letters (A–Z), as required by the assignment.';
    }
    $date = false;
    if (preg_match('~\A\d{2}/\d{2}/\d{4}\z~', $data['dob'])) {
        $date = date_create_immutable_from_format('!d/m/Y', $data['dob']);
    }
    if (!preg_match('~\A\d{2}/\d{2}/\d{4}\z~', $data['dob']) || !$date || date_format($date, 'd/m/Y') !== $data['dob'] || (int)date_format($date, 'Y') < 1 || $date > date_create_immutable('today')) {
        $errors['dob'] = 'Enter a real date on or before today in dd/mm/yyyy format.';
    }
    if (!in_array($data['gender'], ['Female','Male','Non-binary','Prefer not to say'], true)) $errors['gender'] = 'Select a gender option.';
    foreach (['street','suburb'] as $field) {
        if ($data[$field] === '' || text_length($data[$field]) > 40) $errors[$field] = 'Enter 1–40 characters.';
    }
    if (!in_array($data['state'], ['VIC','NSW','QLD','NT','WA','SA','TAS','ACT'], true)) $errors['state'] = 'Select a listed Australian state or territory.';
    if (!preg_match('/\A[0-9]{4}\z/', $data['postcode'])) $errors['postcode'] = 'Enter exactly four digits.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 254) $errors['email'] = 'Enter a valid email address, no longer than 254 characters.';
    if (!preg_match('/\A[0-9]{8,12}\z/', $data['phone'])) $errors['phone'] = 'Enter 8–12 digits without spaces or symbols.';
    validate_application_skills($input, $data, $errors);
    if (text_length($data['other-skills']) > 5000) $errors['other-skills'] = 'Keep other skills within 5,000 characters.';
    return [$data, $errors];
}
function like_term(string $term): string {
    return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term) . '%';
}
