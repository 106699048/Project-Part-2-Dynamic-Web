<?php
/* Displays the team profiles and contributions. */

require __DIR__ . '/bootstrap.php';
$title = 'About our group';
$active = 'about';
$members = mysqli_fetch_all(mysqli_query(db(), 'SELECT * FROM about ORDER BY id'), MYSQLI_ASSOC);
require __DIR__ . '/header.inc';
?>
<h1>The people behind the project.</h1>
<p class="lead">A student team exploring how thoughtful technology can support better care.</p>
<section>
    <h2>A team effort</h2>
    <figure class="group-photo">
        <img src="images/group-photo.png" alt="The three Project2 teammates standing together in a classroom" width="1280" height="960" style="max-width: 100%; height: auto; aspect-ratio: auto;">
        <figcaption>Group 1 — Htoo Nay Lin, Yuri Chingabo de Almeida Mabutana, and Cheong Kar Keat.</figcaption>
    </figure>
</section>
<section>
    <h2>Contributions and voices</h2>
    <dl class="members">
        <?php foreach ($members as $member): ?>
        <dt>
            <?= e($member['member_name']) ?>
            <span class="student-id"><?= e($member['student_id']) ?></span>
        </dt>
        <dd>
            <p><strong>Project 1:</strong> <?= e($member['project1_contribution']) ?></p>
            <p><strong>Project 2:</strong> <?= e($member['project2_contribution']) ?></p>
            <blockquote>
            <p lang="<?= e($member['quote_language']) ?>"><?= e($member['quote_original']) ?></p>
            </blockquote>
        </dd>
        <?php endforeach; ?>
    </dl>
    <?php if (!$members): ?>
    <p>Group contributions have not yet been entered.</p>
    <?php endif; ?>
</section>
<section>
    <h2>Beyond the code</h2>
    <div class="table-wrap">
        <table class="fun-facts">
            <caption>Meet our team: ambitions, snacks and hometowns</caption>
            <thead>
                <tr>
                    <th scope="col">
                        Member
                    </th>
                    <th scope="col">
                        Dream job
                    </th>
                    <th scope="col">
                        Coding snack
                    </th>
                    <th scope="col">
                        Hometown
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                <tr>
                    <th scope="row">
                        <?= e($member['member_name']) ?>
                    </th>
                    <td>
                        <?= e($member['dream_job']) ?>
                    </td>
                    <td>
                        <?= e($member['coding_snack']) ?>
                    </td>
                    <td>
                        <?= e($member['hometown']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<section id="project-links">
    <h2>Tools and technologies</h2>
    <p>We used PHP for server-side validation and database access, HTML and CSS for page structure and styling, and MySQL in XAMPP to store applications, jobs, team profiles, and user accounts. We wrote the code in Visual Studio Code and tested it in a browser.</p>
</section>
<?php require __DIR__ . '/footer.inc'; ?>
