<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireSeller();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$summary = getStorage()->getSellerSummary((int)$_SESSION['user_id']);
$productCount = $summary['product_count'];
$stockCount = $summary['stock_count'];
$pageTitle = 'Painel do vendedor';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="admin-layout">
            <?php $managementIsAdmin = false; require __DIR__ . '/../includes/management-sidebar.php'; ?>
            <section class="admin-content">
                <span class="eyebrow">Área do vendedor</span>
                <h1>Seus produtos</h1>
                <p class="muted">Cadastre e atualize seus produtos. As alterações ficam visíveis na loja imediatamente.</p>
                <div class="metric-grid">
                    <article class="metric-card"><span>Produtos cadastrados</span><strong><?= $productCount ?></strong></article>
                    <article class="metric-card"><span>Unidades em estoque</span><strong><?= $stockCount ?></strong></article>
                </div>
                <a class="btn btn-primary" href="/seller/product-form.php">Adicionar produto</a>
            </section>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
