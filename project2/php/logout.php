<?php
require __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('login.php');
verify_csrf();
$_SESSION=[];
$p=session_get_cookie_params();
setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>'Lax']);
session_destroy();
redirect('login.php');
