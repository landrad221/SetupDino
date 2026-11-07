<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/ProductRepository.php';
require_once __DIR__ . '/includes/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Produto não encontrado.'); }
try { $product = (new ProductRepository(getDatabaseConnection()))->getById($id); }
catch (Throwable $e) { error_log($e->getMessage()); renderErrorPage(); }
if (!$product) { http_response_code(404); exit('Produto não encontrado.'); }
$image = (string)($product['image'] ?? '');
$hasImage = $image !== '' && is_file(__DIR__ . '/assets/images/products/' . basename($image));
$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container"><a class="text-link" href="/products.php">← Voltar aos produtos</a><div class="detail">
<div class="detail-visual"><?php if ($hasImage): ?><img src="<?= escape(asset('images/products/' . basename($image))) ?>" alt="<?= escape($product['name']) ?>"><?php else: ?><span class="product-placeholder" aria-hidden="true">🎮</span><?php endif; ?></div>
<div class="detail-info"><a class="eyebrow" href="/products.php?category=<?= urlencode($product['category_slug']) ?>"><?= escape($product['category_name']) ?></a><h1><?= escape($product['name']) ?></h1><p class="muted"><?= nl2br(escape($product['description'] ?? '')) ?></p><p class="detail-price"><?= formatPrice($product['price']) ?></p><p class="stock <?= (int)$product['stock'] > 0 ? 'available' : 'unavailable' ?>"><?= (int)$product['stock'] > 0 ? (int)$product['stock'] . ' em estoque' : 'Fora de estoque' ?></p><button class="btn btn-primary" type="button" data-add-cart data-id="<?= (int)$product['id'] ?>" data-name="<?= escape($product['name']) ?>" data-price-cents="<?= (int)round((float)$product['price']*100) ?>" data-stock="<?= (int)$product['stock'] ?>" <?= (int)$product['stock'] === 0 ? 'disabled' : '' ?>>Adicionar ao carrinho</button><a class="btn btn-outline" href="/products.php">Continuar comprando</a></div>
</div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
