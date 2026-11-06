<?php
declare(strict_types=1);
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';require_once __DIR__.'/config/database.php';
if(isLoggedIn()){header('Location: /');exit;}
$error='';$next=(($_GET['next']??$_POST['next']??'')==='checkout')?'/checkout.php':'/';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrf($_POST['csrf']??null);$email=trim((string)($_POST['email']??''));$password=(string)($_POST['password']??'');
 try{$stmt=getDatabaseConnection()->prepare('SELECT id,password_hash,role FROM users WHERE email=? LIMIT 1');$stmt->execute([$email]);$user=$stmt->fetch();if($user&&password_verify($password,$user['password_hash'])){session_regenerate_id(true);$_SESSION['user_id']=(int)$user['id'];$_SESSION['role']=$user['role'];header('Location: '.$next);exit;}}catch(Throwable $e){error_log($e->getMessage());}
 $error='Invalid email or password.';
}
$pageTitle='Login';require __DIR__.'/includes/header.php';?>
<section class="section"><div class="container narrow"><h1>Sign in</h1><?php if($error):?><p class="form-error"><?=escape($error)?></p><?php endif;?><form class="stack-form" method="post"><input type="hidden" name="csrf" value="<?=escape(csrfToken())?>"><input type="hidden" name="next" value="<?= $next==='/checkout.php'?'checkout':'' ?>"><label>Email<input type="email" name="email" required></label><label>Password<input type="password" name="password" required></label><button class="btn btn-primary">Sign in</button></form><p>New customer? <a class="text-link" href="/register.php">Create account</a></p></div></section>
<?php require __DIR__.'/includes/footer.php';?>
