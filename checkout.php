<?php
declare(strict_types=1);
require_once __DIR__.'/includes/auth.php';requireLogin();require_once __DIR__.'/includes/functions.php';require_once __DIR__.'/config/database.php';require_once __DIR__.'/src/OrderService.php';
$error='';$fields=['recipient_name'=>120,'postal_code'=>12,'street'=>180,'number'=>20,'complement'=>100,'neighborhood'=>120,'city'=>120,'state'=>2];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrf($_POST['csrf']??null);$address=[];
 foreach($fields as $key=>$max){$address[$key]=trim((string)($_POST[$key]??''));if(strlen($address[$key])>$max || ($key!=='complement' && $address[$key]===''))$error='Preencha todos os campos obrigatórios para entrega.';}
 if(!preg_match('/^[A-Za-z]{2}$/',$address['state']))$error='Informe a sigla válida do estado com duas letras.';
 $raw=json_decode((string)($_POST['cart_json']??''),true);
 if(!is_array($raw))$error='Carrinho inválido. Atualize a página e tente novamente.';
 if(!$error){try{$orderId=(new OrderService(getDatabaseConnection()))->create((int)$_SESSION['user_id'],$raw,$address);header('Location: /order-success.php?id='.$orderId.'&ordered=1');exit;}catch(InvalidArgumentException $e){$error=$e->getMessage();}catch(Throwable $e){error_log($e->getMessage());$error='Não foi possível concluir o pedido. Tente novamente.';}}
}
$pageTitle='Finalizar pedido';require __DIR__.'/includes/header.php';?>
<section class="section"><div class="container narrow"><h1>Finalizar pedido</h1><p class="muted">Esta é uma finalização demonstrativa: nenhum pagamento real será cobrado e o pedido será salvo como pendente.</p><?php if($error):?><p class="form-error"><?=escape($error)?></p><?php endif;?>
<form class="stack-form" method="post" data-checkout-form><input type="hidden" name="csrf" value="<?=escape(csrfToken())?>"><input type="hidden" name="cart_json" value="">
<?php $fieldLabels=['recipient_name'=>'Nome de quem recebe','postal_code'=>'CEP','street'=>'Rua','number'=>'Número','complement'=>'Complemento','neighborhood'=>'Bairro','city'=>'Cidade','state'=>'Estado (UF)']; foreach($fields as $field=>$max):?><label><?=escape($fieldLabels[$field])?><?= $field==='complement'?' (opcional)':'' ?><input name="<?=escape($field)?>" maxlength="<?=$max?>" value="<?=escape($_POST[$field]??'')?>" <?= $field==='complement'?'':'required' ?>></label><?php endforeach; ?>
<button class="btn btn-primary" type="submit">Confirmar pedido demonstrativo</button></form></div></section>
<?php require __DIR__.'/includes/footer.php';?>
