<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/ProductRepository.php';
require_once __DIR__ . '/includes/functions.php';
$search = trim((string)($_GET['search'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
$sort = (string)($_GET['sort'] ?? 'newest');
$allowedSorts = ['newest', 'price_asc', 'price_desc', 'name_asc'];
if (!in_array($sort, $allowedSorts, true)) $sort = 'newest';
$search = mb_substr($search, 0, 100);
$category = substr($category, 0, 120);
try {
    $repo = new ProductRepository(getDatabaseConnection());
    $categories = $repo->getCategories();
    $products = $repo->search($search, $category, $sort);
} catch (Throwable $e) { error_log($e->getMessage()); renderErrorPage(); }
$pageTitle = 'Product Catalog';
require __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container"><span class="eyebrow">EXPLORE THE ARSENAL</span><h1 class="section-title">Product catalog</h1><p class="muted">Find the right gear for your setup.</p>
<form method="get" action="/products.php" class="filter-panel" role="search" data-filter-form>
<div class="field field-search"><label for="search">Search products</label><input id="search" name="search" type="search" maxlength="100" value="<?= escape($search) ?>" placeholder="Keyboard, headset, chair..."></div>
<div class="field"><label for="category">Category</label><select id="category" name="category"><option value="">All categories</option><?php foreach ($categories as $cat): ?><option value="<?= escape($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= escape($cat['name']) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="sort">Sort by</label><select id="sort" name="sort"><option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option><option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Lowest price</option><option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Highest price</option><option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name A–Z</option></select></div>
<div class="filter-actions"><button type="submit" class="btn btn-primary">Apply filters</button><a class="btn btn-outline" href="/products.php">Clear</a></div>
</form><p class="result-count" aria-live="polite"><?= count($products) ?> product(s) found</p>
<?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product) { require __DIR__ . '/includes/product-card.php'; } ?></div><?php else: ?><div class="empty-state"><h2>No products found</h2><p>Try another search or remove a filter.</p><a href="/products.php" class="btn btn-primary">See all products</a></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
