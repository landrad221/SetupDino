<?php
declare(strict_types=1);
require_once __DIR__.'/includes/auth.php';requireLogin();require_once __DIR__.'/includes/functions.php';require_once __DIR__.'/config/database.php';require_once __DIR__.'/src/OrderService.php';
$error='';$fields=['recipient_name'=>120,'postal_code'=>12,'street'=>180,'number'=>20,'complement'=>100,'neighborhood'=>120,'city'=>120,'state'=>2];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrf($_POST['csrf']??null);$address=[];
 foreach($fields as $key=>$max){$address[$key]=trim((string)($_POST[$key]??''));if(strlen($address[$key])>$max || ($key!=='complement' && $address[$key]===''))$error='Complete all required delivery fields.';}
 if(!preg_match('/^[A-Za-z]{2}$/',$address['state']))$error='Enter a valid two-letter state code.';
 $raw=json_decode((string)($_POST['cart_json']??''),true);
 if(!is_array($raw))$error='Invalid cart. Refresh and try again.';
 if(!$error){try{$orderId=(new OrderService(getDatabaseConnection()))->create((int)$_SESSION['user_id'],$raw,$address);header('Location: /order-success.php?id='.$orderId.'&ordered=1');exit;}catch(InvalidArgumentException $e){$error=$e->getMessage();}catch(Throwable $e){error_log($e->getMessage());$error='Could not place order. Please try again.';}}
}
$pageTitle='Checkout';require __DIR__.'/includes/header.php';?>
<section class="section"><div class="container narrow"><h1>Checkout</h1><p class="muted">Demo checkout only. No real payment is collected. Your order will be saved as pending.</p><?php if($error):?><p class="form-error"><?=escape($error)?></p><?php endif;?>
<form class="stack-form" method="post" data-checkout-form><input type="hidden" name="csrf" value="<?=escape(csrfToken())?>"><input type="hidden" name="cart_json" value="">
<?php foreach($fields as $field=>$max):?><label><?=escape(ucwords(str_replace('_',' ',$field)))?><?= $field==='complement'?' (optional)':'' ?><input name="<?=escape($field)?>" maxlength="<?=$max?>" value="<?=escape($_POST[$field]??'')?>" <?= $field==='complement'?'':'required' ?>></label><?php endforeach; ?>
<button class="btn btn-primary" type="submit">Place demo order</button></form></div></section>
<?php require __DIR__.'/includes/footer.php';?>
