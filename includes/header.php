<?php
require_once __DIR__.'/functions.php';
$pageTitle=$pageTitle ?? 'GamerSell';
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= escape($pageTitle) ?> | GamerSell</title><link rel="stylesheet" href="<?= escape(asset('css/main.css')) ?>"></head><body>
<header class="site-header"><nav class="navbar container" aria-label="Main navigation"><a href="/" class="logo">GAMER<span>SELL</span></a><div class="nav-links"><a href="/">Home</a><a href="/products.php">Products</a><a href="/cart.php">Cart</a><a href="/login.php">Login</a></div></nav></header><main>
