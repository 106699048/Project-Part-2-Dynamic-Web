<?php if (!function_exists('e')) { http_response_code(404); exit; } ?>
<nav aria-label="Main navigation">
    <ul>
        <?php foreach (['index'=>'Home','jobs'=>'Jobs','apply'=>'Apply','about'=>'About','manage'=>'HR portal'] as $page=>$label): ?>
        <li>
            <a href="<?= e($page) ?>.php"<?= ($active ?? '') === $page ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
