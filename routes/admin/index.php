<?php
declare(strict_types=1);
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit('Page not found.');
}

require_once dirname(__DIR__, 2) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

$db = getDatabaseConnection();
$counts = [
    'products' => (int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'sellers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn(),
    'customers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];
$pageTitle = 'Administração';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="admin-layout">
            <?php $managementIsAdmin = true; require dirname(__DIR__, 2) . '/includes/management-sidebar.php'; ?>
            <section class="admin-content">
                <span class="eyebrow">Painel administrativo</span>
                <h1>Visão geral</h1>
                <p class="muted">Gerencie o catálogo, vendedores, clientes e pedidos da SetupDino.</p>
                <div class="metric-grid">
                    <?php foreach (['products' => 'Produtos', 'orders' => 'Pedidos', 'sellers' => 'Vendedores', 'customers' => 'Clientes'] as $key => $label): ?>
                        <article class="metric-card"><span><?= escape($label) ?></span><strong><?= $counts[$key] ?></strong></article>
                    <?php endforeach; ?>
                </div>
                <div class="form-actions">
                    <a class="btn btn-primary" href="/admin/product-form.php">Adicionar produto</a>
                    <a class="btn btn-outline" href="/admin/customers.php">Gerenciar vendedores</a>
                </div>
            </section>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
