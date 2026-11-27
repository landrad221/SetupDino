<?php
declare(strict_types=1);
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit('Page not found.');
}

require_once dirname(__DIR__, 2) . '/includes/auth.php';
requireSeller();
$productFormAdmin = false;
$productFormBase = '/seller';
require dirname(__DIR__, 2) . '/includes/product-form-page.php';
