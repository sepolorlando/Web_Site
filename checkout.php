<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="checkout-container">
    <h2>Finalizar Encomenda</h2>

    <form id="checkout-form" method="POST" action="/api/finalizar_encomenda.php">
        <div class="form-group"><label for="nome">Nome completo:</label><input type="text" name="nome" id="nome" required></div>

        <div class="form-group half"><label for="data_nascimento">Data de nascimento:</label><input type="date" name="data_nascimento" id="data_nascimento" required></div>

        <div class="form-group half"><label for="idade">Idade:</label><input type="number" name="idade" id="idade" readonly></div>

        <div class="form-group"><label for="morada">Morada:</label><input type="text" name="morada" id="morada" required></div>
          <input type="hidden" name="carrinho" id="carrinhoField" value="">

         <p><strong>Total:</strong> <strong class="total">0,00 $</strong></p>
        <button type="submit">Concluir encomenda</button>
        <div id="mensagem-sucesso" style="display:none; color: green; margin-top: 15px;"></div>
    </form>
</div>
<div id="emptyCartPopup" class="popup-overlay" style="display: none;">
  <div class="popup-container">
    <div class="popup-content">
      <h3>Carrinho Vazio</h3>
      <p>Seu carrinho está vazio. Adicione produtos antes de finalizar sua compra.</p>
      <div class="popup-buttons">
        <a href="index.php" class="btn btn-continue">Continuar comprando</a>
        <button class="btn btn-close-popup">Fechar</button>
      </div>
    </div>
  </div>
</div>
   <?php require_once __DIR__ . '/includes/footer.php'; ?>