<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/ProductRepository.php';
require_once __DIR__ . '/includes/functions.php';
try {
    $repo = new ProductRepository(getDatabaseConnection());
    $products = $repo->getFeaturedProducts();
    $categories = $repo->getCategories();
} catch (Throwable $e) { error_log($e->getMessage()); renderErrorPage(); }
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero"><div class="container hero-content"><span class="eyebrow">NEXT-GEN GEAR // GAMERSELL</span><h1>LEVEL UP <span class="glow">YOUR SETUP.</span></h1><p>Upgrade your battlestation with gaming accessories, peripherals and chairs built for your next victory.</p><a href="/products.php" class="btn btn-primary">Explore products →</a></div></section>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">DISCOVER</span><h2 class="section-title">Shop by category</h2></div></div><div class="category-grid"><?php foreach ($categories as $category): ?><a class="category-tile" href="/products.php?category=<?= urlencode($category['slug']) ?>"><span>⌁</span><?= escape($category['name']) ?> <span aria-hidden="true">↗</span></a><?php endforeach; ?></div></div></section>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">HANDPICKED</span><h2 class="section-title">Featured products</h2></div><a href="/products.php" class="text-link">View all →</a></div><div class="product-grid"><?php foreach ($products as $product) { require __DIR__ . '/includes/product-card.php'; } ?></div><?php if (!$products): ?><p class="empty-state">No featured products yet.</p><?php endif; ?></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
