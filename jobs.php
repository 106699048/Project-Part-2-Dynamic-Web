<?php
/* Lists jobs and searches their descriptions. */

require __DIR__ . '/bootstrap.php';
$title = 'Open positions';
$active = 'jobs';
$q = clean($_GET['q'] ?? '');
if (text_length($q) > 100) { $q = '';
$search_error = 'Search using no more than 100 characters.'; }
$database_connection = db();
if ($q !== '') {
    $statement = mysqli_prepare($database_connection, "SELECT * FROM jobs WHERE CONCAT_WS(' ',reference,title,description,salary,reporting_line,location,employment_type,responsibilities,essential_requirements,preferable_requirements) LIKE ? ESCAPE '!' ORDER BY reference");
    mysqli_stmt_execute($statement, [like_term($q)]);
} else {
    $statement = mysqli_prepare($database_connection, 'SELECT * FROM jobs ORDER BY reference');
    mysqli_stmt_execute($statement);
}
$jobs = mysqli_fetch_all(mysqli_stmt_get_result($statement), MYSQLI_ASSOC);
require __DIR__ . '/header.inc';
?>
<h1>Build a better care experience.</h1><p class="lead">Find a role that makes healthcare easier to access.</p>
<form action="search.php" method="get" class="search-panel" role="search" novalidate>
    <label for="q">Search job titles, references or skills</label>
    <div class="search-row">
        <input type="search" id="q" name="q" value="<?= e($q) ?>"><button type="submit">Search jobs</button>
    </div>
</form>
<?php if (isset($search_error)): ?><p class="notice"><?= e($search_error) ?></p><?php endif; ?>
<p><?= count($jobs) ?> role(s)<?php if ($q !== ''): ?> matching “<?= e($q) ?>”<?php else: ?> available<?php endif; ?>. <a href="jobs.php">Show all roles</a></p>
<div class="jobs-layout">
    <aside class="jobs-aside">
        <h2>Working with us</h2><p>Collaborate with designers, clinicians and engineers on useful digital services.</p><h3>Our hiring process</h3>
        <ol>
            <li>
                Application review
            </li>
            <li>
                Practical skills discussion
            </li>
            <li>
                Team interview
            </li>
        </ol>
        <p>We welcome Aboriginal and Torres Strait Islander applicants and can discuss accessible interview formats and cultural commitments.</p>
    </aside>
    <div class="job-list">
        <?php foreach ($jobs as $job): ?>
        <article class="job-card" id="<?= e($job['reference']) ?>">
            <p class="eyebrow">Reference <?= e($job['reference']) ?></p><h2><?= e($job['title']) ?></h2><p><?= e($job['description']) ?></p>
            <dl class="job-meta">
                <?php foreach (['salary'=>'Salary','reporting_line'=>'Reports to','location'=>'Location','employment_type'=>'Employment'] as $key=>$label): ?>
                <dt>
                    <?= e($label) ?>
                </dt>
                <dd>
                    <?= e($job[$key]) ?>
                </dd>
                <?php endforeach; ?>
            </dl>
            <?php foreach (['responsibilities'=>'Key responsibilities','essential_requirements'=>'Essential requirements','preferable_requirements'=>'Preferable requirements'] as $key=>$heading): ?>
            <section>
                <h3><?= e($heading) ?></h3>
                <?php $tag = 'ul'; if ($key === 'responsibilities') { $tag = 'ol'; } echo '<' . $tag . '>'; ?>
                <?php foreach (explode("\n", $job[$key]) as $item): if (trim($item) === '') continue; ?>
                <li>
                    <?= e($item) ?>
                </li>
                <?php endforeach; ?>
                <?php echo '</' . $tag . '>'; ?>
            </section>
            <?php endforeach; ?>
            <a class="button" href="apply.php?ref=<?= e(rawurlencode($job['reference'])) ?>">Apply for <?= e($job['reference']) ?></a>
        </article>
        <?php endforeach; ?>
        <?php if (!$jobs): ?><p class="notice">No roles match your search. Try another title or skill.</p><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/footer.inc'; ?>
