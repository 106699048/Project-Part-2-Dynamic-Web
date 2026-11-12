<?php
require __DIR__ . '/bootstrap.php';
$title='Home'; $active='index';
$jobs=db()->query('SELECT reference,title,employment_type,location FROM jobs ORDER BY reference')->fetchAll();
require __DIR__ . '/header.inc';
?>
<section class="hero"><div><p class="eyebrow">Careers at Halcyon Health</p><h1>Care shouldn’t<br>wait on hold.</h1>
<p class="lead">Digital health, made human.</p><p>We are a healthcare technology company building accessible patient platforms, appointment systems and clear health information websites. Help us make everyday care easier to access.</p>
<a class="button" href="jobs.php">Explore our roles <span aria-hidden="true">→</span></a></div>
<figure class="hero-art"><img src="images/care-platform.svg" alt="Illustration of a patient portal showing appointments, care messages and health information" width="600" height="450"><figcaption>Thoughtful technology. More time for care.</figcaption></figure></section>
<section class="search-panel"><h2>Find your next role</h2><form action="search.php" method="get" role="search"><label for="search">Search job titles or skills</label><div class="search-row"><input type="search" id="search" name="q" placeholder="Try frontend, Ruby or SQL" ><button type="submit">Search jobs</button></div></form></section>
<section><h2>What we build</h2><div class="cards"><article><span class="number">01</span><h3>Patient platforms</h3><p>Simple tools to manage appointments and communicate with care teams.</p></article><article><span class="number">02</span><h3>Clinic systems</h3><p>Reliable booking workflows that help staff spend more time with patients.</p></article><article><span class="number">03</span><h3>Health information</h3><p>Accessible websites that make complicated information easier to understand.</p></article></div></section>
<section><h2>Find your place in Digital Services</h2><div class="table-wrap"><table><caption>Current opportunities</caption><thead><tr><th scope="col">Team</th><th scope="col">Position / reference</th><th scope="col">Work arrangement</th></tr></thead><tbody>
<?php foreach ($jobs as $i=>$job): ?><tr>
<?php if ($i===0): ?><th scope="rowgroup" rowspan="<?= count($jobs) ?>">Digital Services</th><?php endif; ?>
<td><a href="jobs.php#<?= e($job['reference']) ?>"><?= e($job['title']) ?> · <?= e($job['reference']) ?></a></td><td><?= e($job['employment_type']) ?> · <?= e($job['location']) ?></td></tr><?php endforeach; ?>
<?php if (!$jobs): ?><tr><td colspan="3">No current opportunities.</td></tr><?php endif; ?>
</tbody><tfoot><tr><td colspan="3">Our roles support accessible, reliable patient services.</td></tr></tfoot></table></div></section>
<section class="inclusion"><h2>Care includes everyone</h2><p>We encourage applications from Aboriginal and Torres Strait Islander peoples. Their perspectives and experiences matter in designing digital services that respect the communities who use them.</p><p>Our recruitment approach offers accessible interview formats and space for applicants to discuss cultural commitments. This statement describes the inclusive employment approach proposed for our fictional company.</p></section>
<?php require __DIR__ . '/footer.inc'; ?>
