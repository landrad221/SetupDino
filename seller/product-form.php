<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireSeller();
$productFormAdmin = false;
$productFormBase = '/seller';
require __DIR__ . '/../includes/product-form-page.php';
