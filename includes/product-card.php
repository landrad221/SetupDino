<?php
// Expects $product from the calling template.
$image = (string) ($product['image'] ?? '');
$imagePath = __DIR__ . '/../assets/images/products/' . basename($image);
$hasImage = $image !== '' && is_file($imagePath);
?>
<article class="product-card">
    <a class="product-visual" href="/product.php?id=<?= (int)$product['id'] ?>" aria-label="View <?= escape($product['name']) ?>">
        <?php if ($hasImage): ?>
            <img src="<?= escape(asset('images/products/' . basename($image))) ?>" alt="<?= escape($product['name']) ?>" loading="lazy">
        <?php else: ?><span class="product-placeholder" aria-hidden="true">🎮</span><?php endif; ?>
    </a>
    <div class="product-card-body">
        <span class="eyebrow"><?= escape($product['category_name'] ?? 'Gaming gear') ?></span>
        <h3><a href="/product.php?id=<?= (int)$product['id'] ?>"><?= escape($product['name']) ?></a></h3>
        <p class="product-description"><?= escape($product['description'] ?? '') ?></p>
        <div class="product-meta"><strong class="product-price"><?= formatPrice($product['price']) ?></strong><span class="stock <?= (int)$product['stock'] > 0 ? 'available' : 'unavailable' ?>"><?= (int)$product['stock'] > 0 ? 'In stock' : 'Out of stock' ?></span></div>
        <div class="product-actions"><a class="btn btn-outline" href="/product.php?id=<?= (int)$product['id'] ?>">View details</a>
<button class="btn btn-primary" type="button" data-add-cart data-id="<?= (int)$product['id'] ?>" data-name="<?= escape($product['name']) ?>" data-price-cents="<?= (int)round((float)$product['price']*100) ?>" data-stock="<?= (int)$product['stock'] ?>" <?= (int)$product['stock'] === 0 ? 'disabled' : '' ?>>Add to cart</button></div>
    </div>
</article>
