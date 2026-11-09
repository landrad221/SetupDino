<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$productFormAdmin = true;
$productFormBase = '/admin';
require __DIR__ . '/../includes/product-form-page.php';
