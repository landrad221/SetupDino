<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';requireAdmin();
$pageTitle='Customers';require __DIR__.'/../includes/header.php';
?><section class="section"><div class="container"><h1>Customers — Admin</h1><p class="muted">Protected administration page. Functionality is planned for a later milestone.</p><a href="/admin/">Back to dashboard</a></div></section><?php require __DIR__.'/../includes/footer.php';
