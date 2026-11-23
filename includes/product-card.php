<?php
// Partial reutilizável do cartão de produto; espera a variável $product fornecida pela página.
$image = (string) ($product["image"] ?? "");
$imagePath = __DIR__ . "/../assets/images/products/" . basename($image);
$hasImage = $image !== "" && is_file($imagePath);
$regularPriceCents = (int) round((float) $product["price"] * 100);
$currentPrice = $product["promotion_price"] ?? $product["price"];
?>
<article class="product-card">
    <a class="product-visual" href="/product.php?id=<?= (int) $product[
        "id"
    ] ?>" aria-label="Ver <?= escape($product["name"]) ?>">
        <?php if ($hasImage): ?>
            <img src="<?= escape(
                asset("images/products/" . basename($image)),
            ) ?>" alt="<?= escape($product["name"]) ?>" loading="lazy">
        <?php else: ?><span class="product-placeholder" aria-hidden="true">🎮</span><?php endif; ?>
    </a>
    <div class="product-card-body">
        <span class="eyebrow"><?= escape(
            $product["category_name"] ?? "Acessórios gamer",
        ) ?></span>
        <?php if (!empty($product["promotion_price"])): ?>
            <span class="promotion-badge">Oferta</span>
        <?php endif; ?>
        <h3><a href="/product.php?id=<?= (int) $product["id"] ?>"><?= escape(
    $product["name"],
) ?></a></h3>
        <p class="product-description"><?= escape(
            $product["description"] ?? "",
        ) ?></p>
        <div class="product-meta"><div class="product-prices">
            <?php if (!empty($product["promotion_price"])): ?>
                <del class="regular-price"><?= formatPrice(
                    $product["price"],
                ) ?></del>
            <?php endif; ?>
            <strong class="product-price"><?= formatPrice(
                $currentPrice,
            ) ?></strong>
        </div><span class="stock <?= (int) $product["stock"] > 0
    ? "available"
    : "unavailable" ?>"><?= (int) $product["stock"] > 0
    ? "Em estoque"
    : "Fora de estoque" ?></span></div>
        <div class="product-actions"><a class="btn btn-outline" href="/product.php?id=<?= (int) $product[
            "id"
        ] ?>">Ver detalhes</a>
<button class="btn btn-primary" type="button" data-add-cart data-id="<?= (int) $product[
    "id"
] ?>" data-name="<?= escape(
    $product["name"],
) ?>" data-price-cents="<?= (int) round(
    (float) $currentPrice * 100,
) ?>" data-regular-price-cents="<?= $regularPriceCents ?>" data-stock="<?= (int) $product[
    "stock"
] ?>" <?= (int) $product["stock"] === 0
    ? "disabled"
    : "" ?>>Adicionar ao carrinho</button></div>
    </div>
</article>
