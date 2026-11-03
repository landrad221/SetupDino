<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';require_once __DIR__.'/src/ProductRepository.php';require_once __DIR__.'/includes/functions.php';
try {$products=(new ProductRepository(getDatabaseConnection()))->getFeaturedProducts();}
catch(Throwable $e) {error_log($e->getMessage());renderErrorPage();}
$pageTitle='Home';require __DIR__.'/includes/header.php';
?><section class="hero"><div class="container"><div class="eyebrow">NEXT-GEN GEAR</div><h1>LEVEL UP <span class="glow">YOUR SETUP.</span></h1><p>Discover gaming peripherals, accessories and equipment designed for your next victory.</p><a class="btn btn-primary" href="/products.php">Explore Products →</a></div></section>
<section class="section"><div class="container"><h2 class="section-title">Featured Products</h2><div class="product-grid"><?php foreach($products as $product): ?><article class="product-card"><div class="product-art">🎮</div><h3><?= escape($product['name']) ?></h3><p><?= escape($product['description']) ?></p><p class="product-price"><?= formatPrice($product['price']) ?></p><a class="btn btn-primary" href="/product.php?id=<?= (int)$product['id'] ?>">View Product</a></article><?php endforeach; ?></div><?php if (!$products): ?><p>No featured products available.</p><?php endif; ?></div></section>
<?php require __DIR__.'/includes/footer.php';
