<?php
require __DIR__ . '/bootstrap.php';
// A direct URL visit cannot create a record.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('apply.php');
verify_csrf();
$key = $_POST['submission_key'] ?? null;
if (!is_string($key) || !isset($_SESSION['submission_key']) || !hash_equals($_SESSION['submission_key'], $key)) redirect('apply.php');
[$data, $errors] = validate_application($_POST);
$pdo = db();
if (!isset($errors['job-reference'])) {
    $find = $pdo->prepare('SELECT reference FROM jobs WHERE reference = ?');
    $find->execute([$data['job-reference']]);
    $reference = $find->fetchColumn();
    if ($reference === false) $errors['job-reference'] = 'Choose an available job reference.';
    else $data['job-reference'] = $reference;
}
if ($errors) {
    $_SESSION['application_old'] = $data;
    $_SESSION['application_errors'] = $errors;
    redirect('apply.php');
}
ensure_eoi_table($pdo);
// UNIQUE submission_key also prevents repeat inserts if a request is retried.
$existing = $pdo->prepare('SELECT EOInumber FROM eoi WHERE submission_key = ?');
$existing->execute([$key]);
$number = $existing->fetchColumn();
if ($number === false) {
    $insert = $pdo->prepare('INSERT INTO eoi (job_reference,first_name,last_name,date_of_birth,gender,street_address,suburb,state,postcode,email,phone,skills,other_skills,submission_key) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $date = DateTimeImmutable::createFromFormat('!d/m/Y',$data['dob']);
    $insert->execute([
        $data['job-reference'],$data['first-name'],$data['last-name'],$date->format('Y-m-d'),$data['gender'],
        $data['street'],$data['suburb'],$data['state'],$data['postcode'],$data['email'],$data['phone'],
        implode(', ', $data['skills']),$data['other-skills'],$key,
    ]);
    $number = $pdo->lastInsertId();
}
$_SESSION['receipt'] = ['number'=>(string)$number,'reference'=>$data['job-reference']];
unset($_SESSION['submission_key'],$_SESSION['application_old'],$_SESSION['application_errors']);
redirect('confirmation.php');
