<?php $pageTitle='Shopping Cart';require __DIR__.'/includes/header.php'; ?>
<section class="section"><div class="container"><h1>Your Cart</h1><p class="muted">Prices and stock will be checked again at checkout.</p><div data-cart-items></div><h2>Total estimate: <span data-cart-total>R$ 0,00</span></h2><a class="btn btn-primary" data-checkout-link href="/checkout.php" hidden>Proceed to checkout</a> <a class="btn btn-outline" href="/products.php">Continue shopping</a></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
