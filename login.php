<?php
/* Signs managers in and tracks failed attempts. */

require __DIR__ . '/bootstrap.php';
function start_manager_session(array $account): void {
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $_SESSION['manager_id'] = $account['id'];
    $_SESSION['manager_name'] = $account['username'];
    $_SESSION['last_activity'] = time();
    redirect('manage.php');
}
$title='HR sign in';
$active='manage';
$error='';
$username='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $username = clean($_POST['username'] ?? '');
    $password = '';
    if (is_string($_POST['password'] ?? null)) {
        $password = $_POST['password'];
    }
    $database_connection = db();
    mysqli_begin_transaction($database_connection);
    $lookup = mysqli_prepare($database_connection, 'SELECT * FROM users WHERE username = ? FOR UPDATE');
    mysqli_stmt_execute($lookup, [substr($username, 0, 50)]);
    $account = mysqli_fetch_assoc(mysqli_stmt_get_result($lookup));
    $locked = $account && $account['locked_until'] && strtotime($account['locked_until'].' UTC') > time();
    // Fixed dummy bcrypt hash makes missing-user paths perform a password check too.
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    if ($account) {
        $hash = $account['password_hash'];
    }
    $valid = strlen($password)<=4096 && password_verify($password, $hash);
    if ($account && !$locked && $valid && strlen($username)<=50) {
        $reset = mysqli_prepare($database_connection, 'UPDATE users SET failed_attempts=0,locked_until=NULL WHERE id=?');
        mysqli_stmt_execute($reset, [$account['id']]);
        mysqli_commit($database_connection);
        start_manager_session($account);
    }
    if ($account && !$locked) {
        $attempts=1;
        if (!$account['locked_until']) {
            $attempts += (int) $account['failed_attempts'];
        }
        $until = null;
        if ($attempts >= 5) {
            $until = gmdate('Y-m-d H:i:s', time() + 300);
        }
        $fail = mysqli_prepare($database_connection, 'UPDATE users SET failed_attempts=?,locked_until=? WHERE id=?');
        mysqli_stmt_execute($fail, [$attempts, $until, $account['id']]);
    }
    mysqli_commit($database_connection);
    $error='Sign-in failed. Check your credentials, or wait five minutes if repeated attempts have locked the account.';
}
require __DIR__ . '/header.inc';
?>
<h1>HR sign in</h1><p class="lead">Review expressions of interest securely.</p>
<?php if (isset($_GET['expired'])): ?><p class="notice">Your session expired after 30 minutes of inactivity. Please sign in again.</p><?php endif; ?>
<?php if ($error): ?><p class="error-summary" role="alert"><?= e($error) ?></p><?php endif; ?>
<form action="login.php" method="post" class="application-form narrow" novalidate>
    <?php csrf_field(); ?>
    <div class="field">
        <label for="username">Username</label><input id="username" name="username" autocomplete="username" value="<?= e($username) ?>">
    </div>
    <div class="field">
        <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password">
    </div>
    <button type="submit">Sign in</button>
</form>
<?php require __DIR__ . '/footer.inc'; ?>
