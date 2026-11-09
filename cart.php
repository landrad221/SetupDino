<?php $pageTitle='Carrinho';require __DIR__.'/includes/header.php'; ?>
<section class="section"><div class="container"><h1>Seu carrinho</h1><p class="muted">Os preços e a disponibilidade serão confirmados ao finalizar o pedido.</p><div data-cart-items></div><h2>Estimativa do total: <span data-cart-total>R$ 0,00</span></h2><a class="btn btn-primary" data-checkout-link href="/checkout.php" hidden>Finalizar pedido</a> <a class="btn btn-outline" href="/products.php">Continuar comprando</a></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
