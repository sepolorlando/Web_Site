<?php
http_response_code(404);
require_once __DIR__ . '/includes/header.php';
?>
<main class="main-content">
  <section class="slider">
    <div class="not-found">
      <h1>404 — Página não encontrada</h1>
      <p>Desculpe, mas a página que você pediu não existe.</p>
      <a href="../website/" class="btn btn-primary">Voltar ao Início</a>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
