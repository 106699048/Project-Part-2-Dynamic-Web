<?php
require __DIR__ . '/bootstrap.php';
$title='About our group'; $active='about';
$members=db()->query('SELECT * FROM about ORDER BY id')->fetchAll();
require __DIR__ . '/header.inc';
?>
<h1>The people behind the project.</h1><p class="lead">A student team exploring how thoughtful technology can support better care.</p>
<p class="notice">Replace the bracketed team details, quotes and photo placeholder with your group’s real information before submission. Contributions below are loaded from the database.</p>
<section><h2>Our group</h2><ul><li>Group name: [Enter group name]<ul><li>Class day: [Enter day]</li><li>Class time: [Enter time]</li><li>Class / tutor: [Enter class and tutor]</li></ul></li></ul></section>
<section><h2>A team effort</h2><figure class="group-photo"><img src="images/group-photo-placeholder.svg" alt="Placeholder: replace with a real photograph of the whole project group" width="900" height="420"><figcaption>[Group name] — [member names from left to right]. Use one real group photo under 300 KB.</figcaption></figure></section>
<section><h2>Contributions and voices</h2><dl class="members">
<?php foreach ($members as $member): ?>
<dt><?= e($member['member_name']) ?> <span class="student-id"><?= e($member['student_id']) ?></span></dt><dd>
<p><strong>Project 1:</strong> <?= e($member['project1_contribution']) ?></p><p><strong>Project 2:</strong> <?= e($member['project2_contribution']) ?></p>
<blockquote><p lang="<?= e($member['quote_language']) ?>"><?= e($member['quote_original']) ?></p><p><strong>English translation:</strong> <?= e($member['quote_translation']) ?></p></blockquote><p><?= e($member['quote_attribution']) ?></p></dd>
<?php endforeach; ?></dl>
<?php if (!$members): ?><p>Group contributions have not yet been entered.</p><?php endif; ?></section>
<section><h2>Beyond the code</h2><div class="table-wrap"><table class="fun-facts"><caption>Meet our team: ambitions, snacks and hometowns</caption><thead><tr><th scope="col">Member</th><th scope="col">Dream job</th><th scope="col">Coding snack</th><th scope="col">Hometown</th></tr></thead><tbody>
<?php foreach ($members as $member): ?><tr><th scope="row"><?= e($member['member_name']) ?></th><td><?= e($member['dream_job']) ?></td><td><?= e($member['coding_snack']) ?></td><td><?= e($member['hometown']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<section id="project-links" class="notice"><h2>Project links to complete</h2><p>Replace the Jira placeholder in footer.inc with your actual project URL. Confirm the existing GitHub repository and demonstration email address. Update group records in the about table using your local database administration tool.</p></section>
<?php require __DIR__ . '/footer.inc'; ?>
