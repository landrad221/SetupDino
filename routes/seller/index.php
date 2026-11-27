<?php
declare(strict_types=1);
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit('Page not found.');
}

require_once dirname(__DIR__, 2) . '/includes/auth.php';
requireSeller();
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$db = getDatabaseConnection();
$count = $db->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ?');
$count->execute([(int)$_SESSION['user_id']]);
$productCount = (int)$count->fetchColumn();
$stock = $db->prepare('SELECT COALESCE(SUM(stock), 0) FROM products WHERE seller_id = ?');
$stock->execute([(int)$_SESSION['user_id']]);
$stockCount = (int)$stock->fetchColumn();
$pageTitle = 'Painel do vendedor';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="admin-layout">
            <?php $managementIsAdmin = false; require dirname(__DIR__, 2) . '/includes/management-sidebar.php'; ?>
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
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
