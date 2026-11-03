<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';require_once __DIR__.'/src/ProductRepository.php';require_once __DIR__.'/includes/functions.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$id || $id<1) {http_response_code(404);exit('Product not found.');}
try {$product=(new ProductRepository(getDatabaseConnection()))->getById($id);} catch(Throwable $e) {error_log($e->getMessage());renderErrorPage();}
if (!$product) {http_response_code(404);exit('Product not found.');}
$pageTitle=$product['name'];require __DIR__.'/includes/header.php';
?><section class="section"><div class="container"><a href="/products.php">← Back to catalog</a><div class="detail"><div class="product-art large">🎮</div><div><h1><?= escape($product['name']) ?></h1><p><?= escape($product['description']) ?></p><p class="product-price"><?= formatPrice($product['price']) ?></p><p>In stock: <?= (int)$product['stock'] ?></p><p class="muted">Cart functionality will be implemented in the next step.</p></div></div></div></section><?php require __DIR__.'/includes/footer.php';
