<?php
/* Displays the job application form. */

require __DIR__ . '/bootstrap.php';
$title = 'Job application';
$active = 'apply';
$old = $_SESSION['application_old'] ?? [];
$errors = $_SESSION['application_errors'] ?? [];
unset($_SESSION['application_old'], $_SESSION['application_errors']);
if (!$old) $old['job-reference'] = clean($_GET['ref'] ?? '');
if (empty($_SESSION['submission_key'])) $_SESSION['submission_key'] = bin2hex(random_bytes(32));
$jobs = mysqli_fetch_all(mysqli_query(db(), 'SELECT reference,title FROM jobs ORDER BY reference'), MYSQLI_ASSOC);
function field_error(string $id, array $errors): void {
    if (isset($errors[$id])) echo '<p class="field-error" id="error-' . e($id) . '">' . e($errors[$id]) . '</p>';
}
function input_field(string $id, string $label, array $old, array $errors, string $type='text', string $autocomplete='off'): void {
    echo '<div class="field"><label for="' . e($id) . '">' . e($label) . ' *</label>';
    echo '<input type="' . e($type) . '" id="' . e($id) . '" name="' . e($id) . '" value="' . e($old[$id] ?? '') . '" autocomplete="' . e($autocomplete) . '" aria-required="true"';
    if (isset($errors[$id])) echo ' aria-invalid="true" aria-describedby="error-' . e($id) . '"';
    echo '>'; field_error($id, $errors);
    echo '</div>';
}
require __DIR__ . '/header.inc';
?>
<h1>Your next chapter starts here.</h1><p class="lead">Apply to join our digital services team.</p>
<?php if ($errors): ?>
<div class="error-summary" role="alert">
    <h2>Please correct your application</h2>
    <ul>
        <?php foreach ($errors as $key=>$message): ?>
        <li>
            <a href="#<?= e($key) ?>"><?= e($message) ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<!-- novalidate and absence of required/pattern/min/max attributes disable browser validation. -->
<form class="application-form" action="process_eoi.php" method="post" novalidate>
    <?php csrf_field(); ?><input type="hidden" name="submission_key" value="<?= e($_SESSION['submission_key']) ?>">
    <fieldset>
        <legend>01 / Position</legend>
        <?php input_field('job-reference','Job reference number',$old,$errors); ?>
        <p>Enter exactly five alphanumeric characters. Available roles:</p>
        <ul>
            <?php foreach ($jobs as $job): ?>
            <li>
                <?= e($job['reference']) ?> — <?= e($job['title']) ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </fieldset>
    <fieldset>
        <legend>02 / Personal details</legend>
        <div class="form-grid">
            <?php input_field('first-name','First name (up to 20 letters)',$old,$errors,'text','given-name'); ?>
            <?php input_field('last-name','Last name (up to 20 letters)',$old,$errors,'text','family-name'); ?>
            <?php input_field('dob','Date of birth (dd/mm/yyyy)',$old,$errors,'text','bday'); ?>
        </div>
        <fieldset class="choice-group" id="gender">
            <legend>Gender *</legend>
            <?php foreach (['Female','Male','Non-binary','Prefer not to say'] as $gender): ?>
            <label><input type="radio" name="gender" value="<?= e($gender) ?>"<?php if (($old['gender'] ?? '')===$gender) { echo ' checked'; } ?>> <?= e($gender) ?></label>
            <?php endforeach; field_error('gender',$errors); ?>
        </fieldset>
    </fieldset>
    <fieldset>
        <legend>03 / Address and contact</legend>
        <div class="form-grid">
            <?php input_field('street','Street address (up to 40 characters)',$old,$errors,'text','street-address'); ?>
            <?php input_field('suburb','Suburb / Town (up to 40 characters)',$old,$errors,'text','address-level2'); ?>
            <div class="field">
                <label for="state">State *</label><select id="state" name="state" autocomplete="address-level1" aria-required="true"<?php if (isset($errors['state'])) { echo ' aria-invalid="true" aria-describedby="error-state"'; } ?>><option value="">Select a state</option>
                <?php foreach (['VIC','NSW','QLD','NT','WA','SA','TAS','ACT'] as $state): ?><option value="<?= e($state) ?>"<?php if (($old['state'] ?? '')===$state) { echo ' selected'; } ?>><?= e($state) ?></option><?php endforeach; ?>
                </select><?php field_error('state',$errors); ?>
            </div>
            <?php input_field('postcode','Postcode (four digits)',$old,$errors,'text','postal-code'); ?>
            <?php input_field('email','Email address',$old,$errors,'email','email'); ?>
            <?php input_field('phone','Phone number (8–12 digits)',$old,$errors,'tel','tel'); ?>
        </div>
    </fieldset>
    <fieldset id="skills">
        <legend>04 / Skills</legend><p>Please confirm all three core skills to complete your application.</p>
        <div class="choice-group">
            <?php foreach (['Team communication','Problem solving','Version control'] as $skill): ?><label><input type="checkbox" name="skills[]" value="<?= e($skill) ?>"<?php if (in_array($skill,$old['skills'] ?? [],true)) { echo ' checked'; } ?>> <?= e($skill) ?> *</label><?php endforeach; ?>
        </div>
        <?php field_error('skills',$errors); ?>
        <div class="field">
            <label for="other-skills">Other skills (optional, up to 5,000 characters)</label><textarea id="other-skills" name="other-skills" rows="5"><?= e($old['other-skills'] ?? '') ?></textarea><?php field_error('other-skills',$errors); ?>
        </div>
    </fieldset>
    <button type="submit">Submit application</button>
</form>
<?php require __DIR__ . '/footer.inc'; ?>
