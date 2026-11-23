<?php
// Cabeçalho compartilhado: documento, marca, menu hambúrguer e links conforme perfil.
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/auth.php";
$pageTitle = $pageTitle ?? "SetupDino";
?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= escape($pageTitle) ?> | SetupDino</title>
<link rel="stylesheet" href="<?= escape(asset("css/main.css")) ?>">
<script src="<?= escape(asset("js/catalog.js")) ?>" defer></script>
<script src="<?= escape(asset("js/cart.js")) ?>" defer></script>
</head><body>
<header class="site-header"><nav class="navbar container" aria-label="Navegação principal">
<div class="header-brand">
<a href="/" class="header-logo" aria-label="SetupDino — página inicial"><img src="<?= escape(asset("images/logo.png")) ?>" alt=""></a>
<a href="/" class="logo" aria-label="SetupDino — página inicial">Setup<span>Dino</span></a>
</div>
<button class="menu-toggle" type="button" aria-label="Abrir ou fechar navegação" aria-expanded="false" aria-controls="main-nav" data-menu-toggle><span></span><span></span><span></span></button>
<div class="nav-links" id="main-nav" data-menu><a href="/">Início</a><a href="/products.php">Produtos</a><a href="/promotions.php">Promoções</a><a href="/cart.php">Carrinho (<span data-cart-count>0</span>)</a>
<?php if (
    isLoggedIn()
): ?><a href="/logout.php">Sair</a><?php else: ?><a href="/login.php">Entrar</a><a href="/register.php">Criar conta</a><?php endif; ?>
<?php if (isAdmin()): ?><a href="/admin/">Administração</a><?php elseif (
    isSeller()
): ?><a href="/seller/">Painel do vendedor</a><?php endif; ?></div>
</nav></header><main>
