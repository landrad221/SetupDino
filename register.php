<?php
declare(strict_types=1);
require_once __DIR__.'/includes/auth.php'; require_once __DIR__.'/includes/functions.php'; require_once __DIR__.'/config/database.php';
if (isLoggedIn()) {header('Location: /');exit;}
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verifyCsrf($_POST['csrf']??null);
 $name=trim((string)($_POST['name']??'')); $email=trim((string)($_POST['email']??'')); $password=(string)($_POST['password']??'');
 if (mb_strlen($name)<2 || mb_strlen($name)>120 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || strlen($password)<8) $error='Informe um nome e e-mail válidos e uma senha com pelo menos 8 caracteres.';
<<<<<<< HEAD
 else {try {$stmt=getDatabaseConnection()->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,\'customer\')');$stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);session_regenerate_id(true);$_SESSION['user_id']=(int)getDatabaseConnection()->lastInsertId();$_SESSION['role']='customer';header('Location: /checkout.php');exit;} catch(PDOException $e) {error_log($e->getMessage());$error='Não foi possível concluir o cadastro. Este e-mail talvez já esteja registrado.';}}
=======
 else {try {$account=getStorage()->createCustomer($name,$email,password_hash($password,PASSWORD_DEFAULT));session_regenerate_id(true);$_SESSION['user_id']=$account['id'];$_SESSION['role']=$account['role'];header('Location: '.($account['role']==='admin'?'/admin/':'/checkout.php'));exit;} catch(PDOException $e) {error_log($e->getMessage());$error='Não foi possível concluir o cadastro. Este e-mail talvez já esteja registrado.';}}
>>>>>>> master
}
$pageTitle='Criar conta';require __DIR__.'/includes/header.php'; ?>
<section class="section"><div class="container narrow"><h1>Criar conta</h1><?php if($error):?><p class="form-error"><?=escape($error)?></p><?php endif;?><form method="post" class="stack-form"><input type="hidden" name="csrf" value="<?=escape(csrfToken())?>"><label>Nome<input name="name" required maxlength="120" value="<?=escape($_POST['name']??'')?>"></label><label>E-mail<input type="email" name="email" required maxlength="190" value="<?=escape($_POST['email']??'')?>"></label><label>Senha<input type="password" name="password" required minlength="8"></label><button class="btn btn-primary">Cadastrar</button></form></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
