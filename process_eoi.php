<?php
/* Validates and records a submitted application. */

require __DIR__ . '/bootstrap.php';
function check_application_reference(mysqli $database_connection, array &$data, array &$errors): void {
    if (isset($errors['job-reference'])) {
        return;
    }
    $find = mysqli_prepare($database_connection, 'SELECT reference FROM jobs WHERE reference = ?');
    mysqli_stmt_execute($find, [$data['job-reference']]);
    $reference = first_statement_value($find);
    if ($reference === false) $errors['job-reference'] = 'Choose an available job reference.';
    else $data['job-reference'] = $reference;
}
function record_application(mysqli $database_connection, array $data, string $key) {
    $existing = mysqli_prepare($database_connection, 'SELECT EOInumber FROM eoi WHERE submission_key = ?');
    mysqli_stmt_execute($existing, [$key]);
    $number = first_statement_value($existing);
    if ($number === false) {
        $insert = mysqli_prepare($database_connection, 'INSERT INTO eoi (job_reference,first_name,last_name,date_of_birth,gender,street_address,suburb,state,postcode,email,phone,skills,other_skills,submission_key) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $date = date_create_immutable_from_format('!d/m/Y', $data['dob']);
        mysqli_stmt_execute($insert, [
            $data['job-reference'], $data['first-name'], $data['last-name'], date_format($date, 'Y-m-d'), $data['gender'],
            $data['street'], $data['suburb'], $data['state'], $data['postcode'], $data['email'], $data['phone'],
            implode(', ', $data['skills']), $data['other-skills'], $key,
        ]);
        $number = mysqli_insert_id($database_connection);
    }
    return $number;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('apply.php');
verify_csrf();
$key = $_POST['submission_key'] ?? null;
if (!is_string($key) || !isset($_SESSION['submission_key']) || !hash_equals($_SESSION['submission_key'], $key)) redirect('apply.php');
[$data, $errors] = validate_application($_POST);
$database_connection = db();
check_application_reference($database_connection, $data, $errors);
if ($errors) {
    $_SESSION['application_old'] = $data;
    $_SESSION['application_errors'] = $errors;
    redirect('apply.php');
}
ensure_eoi_table($database_connection);
// UNIQUE submission_key also prevents repeat inserts if a request is retried.
$number = record_application($database_connection, $data, $key);
$_SESSION['receipt'] = ['number'=>(string)$number,'reference'=>$data['job-reference']];
unset($_SESSION['submission_key'], $_SESSION['application_old'], $_SESSION['application_errors']);
redirect('confirmation.php');

