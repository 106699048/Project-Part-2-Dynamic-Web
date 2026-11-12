<?php
require __DIR__ . '/bootstrap.php';
if (empty($_SESSION['receipt'])) redirect('apply.php');
$title='Application received'; $active='apply';
require __DIR__ . '/header.inc';
?>
<section class="inclusion"><h1>Application received.</h1><p>Your EOI number is <strong><?= e($_SESSION['receipt']['number']) ?></strong>.</p><p>Job reference: <?= e($_SESSION['receipt']['reference']) ?>. Your application has been recorded for review.</p><p>Please keep your EOI number for your records.</p><a class="button" href="jobs.php">Back to jobs</a></section>
<?php require __DIR__ . '/footer.inc'; ?>
