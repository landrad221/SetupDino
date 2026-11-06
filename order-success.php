<?php
declare(strict_types=1);
require_once __DIR__.'/includes/auth.php';requireLogin();require_once __DIR__.'/config/database.php';require_once __DIR__.'/includes/functions.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);if(!$id){http_response_code(404);exit('Order not found');}
$stmt=getDatabaseConnection()->prepare('SELECT id,total,status FROM orders WHERE id=? AND user_id=?');$stmt->execute([$id,$_SESSION['user_id']]);$order=$stmt->fetch();if(!$order){http_response_code(404);exit('Order not found');}
$pageTitle='Order received';require __DIR__.'/includes/header.php';?>
<section class="section"><div class="container narrow"><h1>Order #<?= (int)$order['id'] ?> received!</h1><p>Demo order saved successfully. Status: <?=escape($order['status'])?>.</p><p>Total: <strong><?=formatPrice($order['total'])?></strong></p><p class="muted">No payment was processed.</p><a class="btn btn-primary" href="/products.php">Keep shopping</a></div></section>
<?php require __DIR__.'/includes/footer.php';?>
