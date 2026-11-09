<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

<<<<<<< HEAD
$db = getDatabaseConnection();
$counts = [
    'products' => (int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'sellers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn(),
    'customers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];
=======
$counts = getStorage()->getAdminDashboardCounts();
>>>>>>> master
$pageTitle = 'Administração';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="admin-layout">
            <?php $managementIsAdmin = true; require __DIR__ . '/../includes/management-sidebar.php'; ?>
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
<?php require __DIR__ . '/../includes/footer.php'; ?>
