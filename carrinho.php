<?php
require_once __DIR__ . '../includes/header.php';
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
      <p>Entrega: <span>0,00 €</span></p>
      <p>Subtotal: <span class="subtotal">0,00 €</span></p>
      <p>Impostos: <span>0,00 €</span></p>
      <hr>
      <p><strong>Total:</strong> <strong class="total">0,00 €</strong></p>

      <button id="btnOpenCheckout" class="btn btn-primary"> Checkout</button>
      <a href="index.php" class="continue-link">← Continuar comprando</a>
    </div>
  </div>
</div>


<div id="checkoutModal" class="modal-backdrop" style="display:none;">
  <div class="modal-window">
    <button class="modal-close">&times;</button>
    <h3>Finalizar Encomenda</h3>
    <form id="checkoutModalForm">
      <div class="form-group">
        <label>Nome completo</label>
        <input type="text" name="nome_cliente" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Data de nascimento</label>
          <input type="date" name="data_nascimento" required>
        </div>
        <div class="form-group">
          <label>Idade</label>
          <input type="number" name="idade" min="0" required>
        </div>
      </div>
      <div class="form-group">
        <label>Morada</label>
        <input type="text" name="morada" required>
      </div>
      <!-- campo oculto para o JSON do carrinho -->
      <input type="hidden" name="carrinho" id="modalCarrinhoField">
      <button type="submit" class="btn btn-success btn-block">
        Concluir encomenda
      </button>
    </form>
  </div>
</div>
<script src="../website/public/assets/js/carrinho.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
