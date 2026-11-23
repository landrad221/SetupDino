<?php declare(strict_types=1);

// Página pública com produtos que têm preço promocional ativo.
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/src/ProductRepository.php";
require_once __DIR__ . "/includes/functions.php";
try {
    $products = array_values(
        array_filter(
            (new ProductRepository(getStorage()))->search(),
            static fn(array $product): bool => !empty(
                $product["promotion_price"]
            ),
        ),
    );
} catch (Throwable $e) {
    error_log($e->getMessage());
    renderErrorPage();
}
$pageTitle = "Promoções";
require __DIR__ . "/includes/header.php";
?>
<section class="section"><div class="container"><span class="eyebrow">Preços especiais</span><h1 class="section-title">Promoções</h1><p class="muted">Confira os produtos com preço promocional ativo.</p>
<?php if ($products): ?><div class="product-grid"><?php foreach (
    $products
    as $product
) {
    require __DIR__ . "/includes/product-card.php";
} ?></div><?php else: ?><div class="empty-state"><h2>Nenhuma promoção ativa</h2><p>Volte em breve para conferir novas ofertas.</p><a href="/products.php" class="btn btn-primary">Ver todos os produtos</a></div><?php endif; ?>
</div></section>
<?php require __DIR__ . "/includes/footer.php"; ?>
