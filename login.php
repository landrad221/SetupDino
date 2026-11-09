<?php
declare(strict_types=1);
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';require_once __DIR__.'/config/database.php';
if(isLoggedIn()){header('Location: /');exit;}
$error='';$next=(($_GET['next']??$_POST['next']??'')==='checkout')?'/checkout.php':'/';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrf($_POST['csrf']??null);$email=trim((string)($_POST['email']??''));$password=(string)($_POST['password']??'');
<<<<<<< HEAD
 try{$stmt=getDatabaseConnection()->prepare('SELECT id,password_hash,role FROM users WHERE email=? LIMIT 1');$stmt->execute([$email]);$user=$stmt->fetch();if($user&&password_verify($password,$user['password_hash'])){session_regenerate_id(true);$_SESSION['user_id']=(int)$user['id'];$_SESSION['role']=$user['role'];$destination=$next;if($next==='/'&&$user['role']==='admin')$destination='/admin/';elseif($next==='/'&&$user['role']==='seller')$destination='/seller/';header('Location: '.$destination);exit;}}catch(Throwable $e){error_log($e->getMessage());}
=======
 try{$user=getStorage()->findUserByEmail($email);if($user&&password_verify($password,$user['password_hash'])){session_regenerate_id(true);$_SESSION['user_id']=(int)$user['id'];$_SESSION['role']=$user['role'];$destination=$next;if($next==='/'&&$user['role']==='admin')$destination='/admin/';elseif($next==='/'&&$user['role']==='seller')$destination='/seller/';header('Location: '.$destination);exit;}}catch(Throwable $e){error_log($e->getMessage());}
>>>>>>> master
 $error='E-mail ou senha inválidos.';
}
$pageTitle='Entrar';require __DIR__.'/includes/header.php';?>
<section class="section"><div class="container narrow"><h1>Entrar</h1><?php if($error):?><p class="form-error"><?=escape($error)?></p><?php endif;?><form class="stack-form" method="post"><input type="hidden" name="csrf" value="<?=escape(csrfToken())?>"><input type="hidden" name="next" value="<?= $next==='/checkout.php'?'checkout':'' ?>"><label>E-mail<input type="email" name="email" required></label><label>Senha<input type="password" name="password" required></label><button class="btn btn-primary">Entrar</button></form><p>Ainda não tem conta? <a class="text-link" href="/register.php">Cadastre-se</a></p></div></section>
<?php require __DIR__.'/includes/footer.php';?>
