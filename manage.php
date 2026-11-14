<?php
/* Lists applications and handles manager actions. */

require __DIR__ . '/bootstrap.php';
require_manager();
function handle_management_action(mysqli $database_connection, string $action): array {
    if ($action === 'status') {
        return [update_application_status($database_connection), null];
    }
    if ($action !== 'preview-delete' && $action !== 'delete') {
        return ['Unknown management action.', null];
    }

    $reference = clean($_POST['delete_reference'] ?? '');
    if (!preg_match('/\A[A-Za-z0-9]{5}\z/', $reference)) {
        return ['Enter a five-character job reference for deletion.', null];
    }
    if ($action === 'preview-delete') {
        return preview_application_deletion($database_connection, $reference);
    }
    return [confirm_application_deletion($database_connection, $reference), null];
}

function update_application_status(mysqli $database_connection): string {
    $number = clean($_POST['eoi_number'] ?? '');
    $status = clean($_POST['status'] ?? '');
    if (!preg_match('/\A[1-9][0-9]{0,18}\z/', $number) || !in_array($status, ['New', 'Current', 'Final'], true)) {
        return 'Choose a valid EOI number and status.';
    }

    $lookup = mysqli_prepare($database_connection, 'SELECT EOInumber FROM eoi WHERE EOInumber=?');
    mysqli_stmt_execute($lookup, [$number]);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($lookup))) {
        return 'That application no longer exists.';
    }

    $update = mysqli_prepare($database_connection, 'UPDATE eoi SET status=? WHERE EOInumber=?');
    mysqli_stmt_execute($update, [$status, $number]);
    $_SESSION['notice'] = 'EOI ' . $number . ' status set to ' . $status . '.';
    redirect('manage.php');
}

function preview_application_deletion(mysqli $database_connection, string $reference): array {
    $count = mysqli_prepare($database_connection, 'SELECT COUNT(*) FROM eoi WHERE job_reference=?');
    mysqli_stmt_execute($count, [$reference]);
    $total = (int) first_statement_value($count);
    if ($total === 0) {
        return ['There are no applications for that reference.', null];
    }

    $_SESSION['delete_confirmation'] = [
        'reference' => $reference,
        'token' => bin2hex(random_bytes(32)),
        'expires' => time() + 300,
    ];
    return ['', [
        'reference' => $reference,
        'count' => $total,
        'token' => $_SESSION['delete_confirmation']['token'],
    ]];
}

function confirm_application_deletion(mysqli $database_connection, string $reference): string {
    $pending = $_SESSION['delete_confirmation'] ?? null;
    $token = $_POST['confirmation_token'] ?? null;
    if (!$pending || !is_string($token) || $pending['reference'] !== $reference
        || $pending['expires'] < time() || !hash_equals($pending['token'], $token)) {
        return 'Deletion confirmation expired. Preview the deletion again.';
    }

    $delete = mysqli_prepare($database_connection, 'DELETE FROM eoi WHERE job_reference=?');
    mysqli_stmt_execute($delete, [$reference]);
    unset($_SESSION['delete_confirmation']);
    $_SESSION['notice'] = 'Deleted ' . mysqli_stmt_affected_rows($delete) . ' application(s) for ' . $reference . '.';
    redirect('manage.php');
}


$title='HR dashboard';
$active='manage';
$database_connection = db();
ensure_eoi_table($database_connection);
$error='';
$confirmation = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $action = clean($_POST['action'] ?? '');
    [$error, $confirmation] = handle_management_action($database_connection, $action);
}
$sorts = ['EOInumber'=>'EOI number','job_reference'=>'Job reference','first_name'=>'First name','last_name'=>'Last name','status'=>'Status','created_at'=>'Submission time'];
$sort = clean($_GET['sort'] ?? 'EOInumber');
if (!isset($sorts[$sort])) $sort='EOInumber';
$direction = clean($_GET['direction'] ?? 'DESC');
if (!in_array($direction, ['ASC','DESC'], true)) $direction='DESC';
$filters = ['job_reference'=>clean($_GET['job_reference'] ?? ''),'first_name'=>clean($_GET['first_name'] ?? ''),'last_name'=>clean($_GET['last_name'] ?? '')];
$where = [];
$params = [];
foreach ($filters as $key => $value) {
    if ($value==='') continue;
    if (text_length($value)>100) { $error='Keep each filter within 100 characters.';
    $where[]='1=0'; continue; }
    if ($key==='job_reference') { $where[]='job_reference=?';
    $params[] = $value; }
    else { $where[] = $key . " LIKE ? ESCAPE '!'";
    $params[] = like_term($value); }
}
// Only allowlisted column names and directions are interpolated into SQL.
$sql = 'SELECT * FROM eoi';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY ' . $sort . ' ' . $direction . ', EOInumber DESC';
$query = mysqli_prepare($database_connection, $sql);
mysqli_stmt_execute($query, $params);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($query), MYSQLI_ASSOC);
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);
require __DIR__ . '/header.inc';
?>
<div class="manager-heading">
    <div>
        <h1>Expressions of interest</h1><p>Signed in as <?= e($_SESSION['manager_name']) ?></p>
    </div>
    <form action="logout.php" method="post">
        <?php csrf_field(); ?><button class="secondary" type="submit">Sign out</button>
    </form>
</div>
<?php if ($notice): ?><p class="inclusion" role="status"><?= e($notice) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error-summary" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if ($confirmation): ?>
<section class="error-summary">
    <h2>Confirm permanent deletion</h2><p>There are currently <strong><?= $confirmation['count'] ?></strong> applications for <strong><?= e($confirmation['reference']) ?></strong>. Confirming deletes all applications for this reference, including any added before confirmation. This cannot be undone.</p>
    <form action="manage.php" method="post">
        <?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="delete_reference" value="<?= e($confirmation['reference']) ?>"><input type="hidden" name="confirmation_token" value="<?= e($confirmation['token']) ?>"><button class="danger" type="submit">Confirm deletion</button> <a href="manage.php">Cancel</a>
    </form>
</section>
<?php endif; ?>
<form action="manage.php" method="get" class="search-panel" novalidate>
    <h2>Find applications</h2>
    <div class="form-grid">
        <?php foreach (['job_reference'=>'Job reference (exact)','first_name'=>'First name (contains)','last_name'=>'Last name (contains)'] as $key=>$label): ?>
        <div class="field">
            <label for="filter-<?= e($key) ?>"><?= e($label) ?></label><input id="filter-<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($filters[$key]) ?>">
        </div>
        <?php endforeach; ?>
        <div class="field">
            <label for="sort">Sort by</label><select id="sort" name="sort"><?php foreach ($sorts as $key=>$label): ?><option value="<?= e($key) ?>"<?php if ($sort===$key) { echo ' selected'; } ?>><?= e($label) ?></option><?php endforeach; ?></select>
        </div>
        <div class="field">
            <label for="direction">Order</label><select id="direction" name="direction"><option value="ASC"<?php if ($direction==='ASC') { echo ' selected'; } ?>>Ascending</option><option value="DESC"<?php if ($direction==='DESC') { echo ' selected'; } ?>>Descending</option></select>
        </div>
    </div>
    <p>Supplied filters are combined. Leave all filters blank to list every EOI.</p><button type="submit">Apply filters</button> <a href="manage.php">List all EOIs</a>
</form>
<h2><?= count($rows) ?> application(s)</h2>
<?php if (!$rows): ?><p>No applications match these filters.</p><?php else: ?>
<div class="table-wrap">
    <table>
        <caption>Application results — expand a record to see all submitted details</caption>
        <thead>
            <tr>
                <th scope="col">
                    EOI / job
                </th>
                <th scope="col">
                    Applicant
                </th>
                <th scope="col">
                    Details
                </th>
                <th scope="col">
                    Status
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <th scope="row">
                    #<?= e($row['EOInumber']) ?><br><?= e($row['job_reference']) ?>
                </th>
                <td>
                    <?= e($row['first_name'] . ' ' . $row['last_name']) ?><br><?= e($row['email']) ?>
                </td>
                <td>
                    <details>
                        <summary>View application #<?= e($row['EOInumber']) ?></summary>
                        <dl class="application-details">
                            <?php foreach (['date_of_birth'=>'Date of birth','gender'=>'Gender','street_address'=>'Street address','suburb'=>'Suburb / Town','state'=>'State','postcode'=>'Postcode','phone'=>'Phone','skills'=>'Skills','other_skills'=>'Other skills','created_at'=>'Submitted (database time)'] as $key=>$label): ?>
                            <dt>
                                <?= e($label) ?>
                            </dt>
                            <dd>
                                <?php if ($key === 'date_of_birth'): ?><?= e(date_format(date_create_immutable_from_format('Y-m-d', $row[$key]), 'd/m/Y')) ?><?php else: ?><?= nl2br(e($row[$key])) ?><?php endif; ?>
                            </dd>
                            <?php endforeach; ?>
                        </dl>
                    </details>
                </td>
                <td>
                    <form action="manage.php" method="post" novalidate>
                        <?php csrf_field(); ?><input type="hidden" name="action" value="status"><input type="hidden" name="eoi_number" value="<?= e($row['EOInumber']) ?>"><label for="status-<?= e($row['EOInumber']) ?>">Status for #<?= e($row['EOInumber']) ?></label><select id="status-<?= e($row['EOInumber']) ?>" name="status"><?php foreach (['New','Current','Final'] as $status): ?><option value="<?= e($status) ?>"<?php if ($status === $row['status']) { echo ' selected'; } ?>><?= e($status) ?></option><?php endforeach; ?></select><button type="submit">Update status</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<section class="delete-panel">
    <h2>Delete applications by reference</h2><p>This affects every application for the selected reference. You will confirm on a separate screen before anything is deleted.</p>
    <form action="manage.php" method="post" novalidate>
        <?php csrf_field(); ?><input type="hidden" name="action" value="preview-delete"><label for="delete-reference">Job reference</label>
        <div class="search-row">
            <input id="delete-reference" name="delete_reference"><button class="danger" type="submit">Review deletion</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/footer.inc'; ?>
