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
<<<<<<< HEAD
    $repo = new ProductRepository(getDatabaseConnection());
=======
    $repo = new ProductRepository(getStorage());
>>>>>>> master
    $categories = $repo->getCategories();
    $products = $repo->search($search, $category, $sort);
} catch (Throwable $e) { error_log($e->getMessage()); renderErrorPage(); }
$pageTitle = 'Produtos';
require __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container"><span class="eyebrow">Seu setup merece esse upgrade</span><h1 class="section-title">Produtos</h1><p class="muted">Encontre os acessórios, periféricos e móveis ideais para o seu setup.</p>
<form method="get" action="/products.php" class="filter-panel" role="search" data-filter-form>
<div class="field field-search"><label for="search">Buscar produtos</label><input id="search" name="search" type="search" maxlength="100" value="<?= escape($search) ?>" placeholder="Teclado, headset, cadeira..."></div>
<div class="field"><label for="category">Categoria</label><select id="category" name="category"><option value="">Todas as categorias</option><?php foreach ($categories as $cat): ?><option value="<?= escape($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= escape($cat['name']) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="sort">Ordenar por</label><select id="sort" name="sort"><option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mais recentes</option><option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Menor preço</option><option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Maior preço</option><option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nome A–Z</option></select></div>
<div class="filter-actions"><button type="submit" class="btn btn-primary">Filtrar</button><a class="btn btn-outline" href="/products.php">Limpar</a></div>
</form><p class="result-count" aria-live="polite"><?= count($products) ?> produto(s) encontrado(s)</p>
<?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product) { require __DIR__ . '/includes/product-card.php'; } ?></div><?php else: ?><div class="empty-state"><h2>Nenhum produto encontrado</h2><p>Tente outra busca ou remova algum filtro.</p><a href="/products.php" class="btn btn-primary">Ver todos os produtos</a></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
