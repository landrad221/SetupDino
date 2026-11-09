<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/ProductRepository.php';
require_once __DIR__ . '/includes/functions.php';
try {
    $repo = new ProductRepository(getStorage());
    $products = $repo->getFeaturedProducts();
    $categories = $repo->getCategories();
} catch (Throwable $e) { error_log($e->getMessage()); renderErrorPage(); }
$pageTitle = 'Acessórios e móveis gamer';
require __DIR__ . '/includes/header.php';
?>
<section class="hero"><div class="container hero-content"><span class="eyebrow">Seu próximo upgrade começa aqui</span><h1>Você está pedindo um <span class="glow">SetupDino.</span></h1><p>Periféricos, cadeiras e móveis gamer para transformar seu espaço no setup que você sempre quis.</p><a href="/products.php" class="btn btn-primary">Encontrar meu upgrade →</a></div></section>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">Encontre o que combina com você</span><h2 class="section-title">Explore por categoria</h2></div></div><div class="category-grid"><?php foreach ($categories as $category): ?><a class="category-tile" href="/products.php?category=<?= urlencode($category['slug']) ?>"><span>⌁</span><?= escape($category['name']) ?> <span aria-hidden="true">↗</span></a><?php endforeach; ?></div></div></section>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">Escolhas para elevar seu setup</span><h2 class="section-title">Produtos em destaque</h2></div><a href="/products.php" class="text-link">Ver todos →</a></div><div class="product-grid"><?php foreach ($products as $product) { require __DIR__ . '/includes/product-card.php'; } ?></div><?php if (!$products): ?><p class="empty-state">Ainda não há produtos em destaque.</p><?php endif; ?></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
