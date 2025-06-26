<?php
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
  <h2>Resumo do pedido</h2>
  <div class="cart-layout">
    <div class="cart-products">
      <ul class="cart-list">
        <!-- Produtos serão inseridos via JS -->
      </ul>
    </div>

    <div class="cart-summary">
      <h3>Total</h3>
      <p>Entrega: <span>0,00 $</span></p>
      <p>Subtotal: <span class="subtotal">0,00 $</span></p>
      <p>Impostos: <span>0,00 $</span></p>
      <hr>
      <p><strong>Total:</strong> <strong class="total">0,00 $</strong></p>

      <button class="checkout-btn">Checkout</button>
      <a href="index.php" class="continue-link">← Continuar comprando</a>
    </div>
  </div>
</div>

<script src="/public/assets/js/carrinho.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
