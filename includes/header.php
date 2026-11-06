<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'GamerSell';
?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= escape($pageTitle) ?> | GamerSell</title>
<link rel="stylesheet" href="<?= escape(asset('css/main.css')) ?>">
<script src="<?= escape(asset('js/catalog.js')) ?>" defer></script>
<script src="<?= escape(asset('js/cart.js')) ?>" defer></script>
</head><body>
<header class="site-header"><nav class="navbar container" aria-label="Main navigation">
<a href="/" class="logo">GAMER<span>SELL</span></a>
<button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="main-nav" data-menu-toggle>☰</button>
<div class="nav-links" id="main-nav" data-menu><a href="/">Home</a><a href="/products.php">Products</a><a href="/cart.php">Cart (<span data-cart-count>0</span>)</a>
<?php if (isLoggedIn()): ?><a href="/logout.php">Logout</a><?php else: ?><a href="/login.php">Login</a><a href="/register.php">Register</a><?php endif; ?>
<?php if (isAdmin()): ?><a href="/admin/">Admin</a><?php endif; ?></div>
</nav></header><main>
