<?php
/* Checks the application validation rules. */

// Run with: php tests/validation.php (no database needed).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../functions.php';
date_default_timezone_set('Australia/Melbourne');
$valid = ['job-reference'=>'FE001','first-name'=>'Ada','last-name'=>'Lovelace','dob'=>'10/12/1990','gender'=>'Female','street'=>'1 Example Street','suburb'=>'Melbourne','state'=>'VIC','postcode'=>'0123','email'=>'ada@example.com','phone'=>'0412345678','skills'=>['Team communication','Problem solving','Version control'],'other-skills'=>''];
$passed=0;
function check(bool $condition, string $message): void { global $passed; if (!$condition) { fwrite(STDERR,'FAIL: '.$message.PHP_EOL); exit(1); } $passed++; }
[$data,$errors]=validate_application($valid); check(!$errors,'valid application');
$cases=[
 ['job-reference','AB!23'],['job-reference','123456'],['first-name','Ada1'],['first-name',str_repeat('a',21)],
 ['last-name',''],['dob','31/02/2000'],['dob','29/02/2001'],['dob','01/01/2999'],['dob','2000-01-01'],['dob',"01\0/01/2000"],
 ['gender','Invalid'],['street',str_repeat('a',41)],['suburb',''],['state','ZZ'],['postcode','123'],['postcode','12345'],
 ['email','invalid'],['phone','1234567'],['phone','1234567890123'],['phone','+6012345678'],['skills',[]],['skills','Team communication'],
 ['skills',['Team communication','Problem solving','Version control','Injected']],['first-name',['array']],['other-skills',str_repeat('x',5001)],
];
foreach ($cases as [$field,$value]) { $input=$valid; $input[$field]=$value; [, $errors]=validate_application($input); check(isset($errors[$field]),'reject '.$field.' invalid case'); }
$input=$valid; $input['dob']='29/02/2000'; [, $errors]=validate_application($input); check(!$errors,'valid leap day');
$input=$valid; $input['street']=str_repeat('é',40); [, $errors]=validate_application($input); check(!$errors,'40 Unicode address characters');
$input=$valid; $input['first-name']=' Ada '; [$data,$errors]=validate_application($input); check($data['first-name']==='Ada' && !$errors,'trim whitespace');
check(e('<script>"&')==='&lt;script&gt;&quot;&amp;','HTML escaping');
check(clean(" A\\B ")==='AB','trim and strip slashes');
check(like_term('a_%!')==='%a!_!%!!%','escape LIKE metacharacters');
echo "PASS: $passed validation assertions\n";
