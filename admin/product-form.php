<?php declare(strict_types=1);

// Entrada do administrador para o formulário compartilhado de produto.
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();
$productFormAdmin = true;
$productFormBase = "/admin";
require __DIR__ . "/../includes/product-form-page.php";
