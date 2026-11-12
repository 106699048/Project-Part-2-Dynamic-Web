<?php
require __DIR__ . '/bootstrap.php';
$title='HR sign in'; $active='manage'; $error=''; $username='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $username=clean($_POST['username'] ?? '');
    $password=is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $pdo=db();
    $pdo->beginTransaction();
    $lookup=$pdo->prepare('SELECT * FROM users WHERE username = ? FOR UPDATE');
    $lookup->execute([substr($username,0,50)]);
    $account=$lookup->fetch();
    $locked=$account && $account['locked_until'] && strtotime($account['locked_until'].' UTC') > time();
    // Fixed dummy bcrypt hash makes missing-user paths perform a password check too.
    $hash=$account ? $account['password_hash'] : '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    $valid=strlen($password)<=4096 && password_verify($password,$hash);
    if ($account && !$locked && $valid && strlen($username)<=50) {
        $reset=$pdo->prepare('UPDATE users SET failed_attempts=0,locked_until=NULL WHERE id=?'); $reset->execute([$account['id']]);
        $pdo->commit();
        session_regenerate_id(true);
        $_SESSION['csrf']=bin2hex(random_bytes(32));
        $_SESSION['manager_id']=$account['id']; $_SESSION['manager_name']=$account['username']; $_SESSION['last_activity']=time();
        redirect('manage.php');
    }
    if ($account && !$locked) {
        $attempts=($account['locked_until'] ? 0 : (int)$account['failed_attempts'])+1;
        $until=$attempts>=5 ? gmdate('Y-m-d H:i:s',time()+300) : null;
        $fail=$pdo->prepare('UPDATE users SET failed_attempts=?,locked_until=? WHERE id=?'); $fail->execute([$attempts,$until,$account['id']]);
    }
    $pdo->commit();
    $error='Sign-in failed. Check your credentials, or wait five minutes if repeated attempts have locked the account.';
}
require __DIR__ . '/header.inc';
?>
<h1>HR sign in</h1><p class="lead">Review expressions of interest securely.</p>
<?php if (isset($_GET['expired'])): ?><p class="notice">Your session expired after 30 minutes of inactivity. Please sign in again.</p><?php endif; ?>
<?php if ($error): ?><p class="error-summary" role="alert"><?= e($error) ?></p><?php endif; ?>
<form action="login.php" method="post" class="application-form narrow" novalidate><?php csrf_field(); ?>
<div class="field"><label for="username">Username</label><input id="username" name="username" autocomplete="username" value="<?= e($username) ?>"></div>
<div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password"></div>
<button type="submit">Sign in</button></form>
<?php require __DIR__ . '/footer.inc'; ?>
