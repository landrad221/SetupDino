<?php
declare(strict_types=1);

$executedScript = realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
if ($executedScript === __FILE__) {
    http_response_code(404);
    exit('Page not found.');
}

final class RouteController
{
    private const ROUTES = [
        '/index' => 'index.php',
        '/cart' => 'cart.php',
        '/checkout' => 'checkout.php',
        '/login' => 'login.php',
        '/logout' => 'logout.php',
        '/order-success' => 'order-success.php',
        '/product' => 'product.php',
        '/products' => 'products.php',
        '/promotions' => 'promotions.php',
        '/register' => 'register.php',
        '/admin' => 'admin/index.php',
        '/admin/index' => 'admin/index.php',
        '/admin/categories' => 'admin/categories.php',
        '/admin/customers' => 'admin/customers.php',
        '/admin/orders' => 'admin/orders.php',
        '/admin/product-form' => 'admin/product-form.php',
        '/admin/products' => 'admin/products.php',
        '/seller' => 'seller/index.php',
        '/seller/index' => 'seller/index.php',
        '/seller/product-form' => 'seller/product-form.php',
        '/seller/products' => 'seller/products.php',
        '/' => 'index.php',
        '/index.php' => 'index.php',
        '/cart.php' => 'cart.php',
        '/checkout.php' => 'checkout.php',
        '/login.php' => 'login.php',
        '/logout.php' => 'logout.php',
        '/order-success.php' => 'order-success.php',
        '/product.php' => 'product.php',
        '/products.php' => 'products.php',
        '/promotions.php' => 'promotions.php',
        '/register.php' => 'register.php',
        '/admin' => 'admin/index.php',
        '/admin/index.php' => 'admin/index.php',
        '/admin/categories.php' => 'admin/categories.php',
        '/admin/customers.php' => 'admin/customers.php',
        '/admin/orders.php' => 'admin/orders.php',
        '/admin/product-form.php' => 'admin/product-form.php',
        '/admin/products.php' => 'admin/products.php',
        '/seller' => 'seller/index.php',
        '/seller/index.php' => 'seller/index.php',
        '/seller/product-form.php' => 'seller/product-form.php',
        '/seller/products.php' => 'seller/products.php',
    ];

    public static function resolve(string $scriptName): string
    {
        $path = parse_url($scriptName, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            http_response_code(404);
            exit('Page not found.');
        }

        $path = '/' . trim($path, '/');

        $matchedRoute = null;
        $matchedLength = 0;
        foreach (self::ROUTES as $uri => $page) {
            if (($path === $uri || ($uri !== '/' && str_ends_with($path, $uri))) && strlen($uri) > $matchedLength) {
                $matchedRoute = $page;
                $matchedLength = strlen($uri);
            }
        }

        if ($matchedRoute === null) {
            http_response_code(404);
            exit('Page not found.');
        }

        return __DIR__ . '/' . $matchedRoute;
    }
}
