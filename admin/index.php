<?php
declare(strict_types=1);require_once __DIR__.'/../includes/auth.php';requireAdmin();
$pageTitle='Admin Dashboard';require __DIR__.'/../includes/header.php';
?><link rel="stylesheet" href="/assets/css/admin.css"><div class="admin-layout"><aside class="admin-sidebar"><h2>Admin Panel</h2><a href="/admin/">Dashboard</a><a href="/admin/products.php">Products</a><a href="/admin/categories.php">Categories</a><a href="/admin/orders.php">Orders</a><a href="/admin/customers.php">Customers</a></aside><section class="admin-content"><h1>SetupDino Dashboard</h1><p>Welcome to the administration panel. Metrics and CRUD will be implemented later.</p></section></div><?php require __DIR__.'/../includes/footer.php';
