<?php declare(strict_types=1);

// Entrada do vendedor para o formulário compartilhado de produto.
require_once __DIR__ . "/../includes/auth.php";
requireSeller();
$productFormAdmin = false;
$productFormBase = "/seller";
require __DIR__ . "/../includes/product-form-page.php";
