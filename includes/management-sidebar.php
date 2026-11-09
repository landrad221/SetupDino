<link rel="stylesheet" href="/assets/css/admin.css">
<aside class="admin-sidebar">
    <h2><?= $managementIsAdmin ? 'Administração' : 'Painel do vendedor' ?></h2>
    <?php if ($managementIsAdmin): ?>
        <a href="/admin/">Visão geral</a>
        <a href="/admin/products.php">Produtos</a>
        <a href="/admin/categories.php">Categorias</a>
        <a href="/admin/orders.php">Pedidos</a>
        <a href="/admin/customers.php">Clientes e vendedores</a>
    <?php else: ?>
        <a href="/seller/">Visão geral</a>
        <a href="/seller/products.php">Meus produtos</a>
        <a href="/seller/product-form.php">Adicionar produto</a>
    <?php endif; ?>
</aside>
