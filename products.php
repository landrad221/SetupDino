<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';require_once __DIR__.'/src/ProductRepository.php';require_once __DIR__.'/includes/functions.php';
$category=trim((string)($_GET['category'] ?? ''));
try {$repo=new ProductRepository(getDatabaseConnection());$products=$repo->getAll($category);$categories=$repo->getCategories();}
catch(Throwable $e) {error_log($e->getMessage());renderErrorPage();}
$pageTitle='Products';require __DIR__.'/includes/header.php';
?><section class="section"><div class="container"><h1 class="section-title">Product Catalog</h1><form method="get" class="filter-bar"><label for="category">Category</label><select name="category" id="category"><option value="">All categories</option><?php foreach($categories as $cat): ?><option value="<?= escape($cat['slug']) ?>" <?= $category===$cat['slug']?'selected':'' ?>><?= escape($cat['name']) ?></option><?php endforeach; ?></select><button class="btn btn-primary">Filter</button></form><div class="product-grid"><?php foreach($products as $product): ?><article class="product-card"><div class="product-art">🎮</div><h3><?= escape($product['name']) ?></h3><p><?= escape($product['category_name']) ?></p><p class="product-price"><?= formatPrice($product['price']) ?></p><a class="btn btn-primary" href="/product.php?id=<?= (int)$product['id'] ?>">View Product</a></article><?php endforeach; ?></div><?php if (!$products): ?><p>No products found.</p><?php endif; ?></div></section>
<?php require __DIR__.'/includes/footer.php';
