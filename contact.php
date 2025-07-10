<?php
// Processamento do formulário
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação e processamento dos dados
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    if ($name && $email && $message) {
        // Aqui você pode adicionar o código para enviar o e-mail
        // ou salvar no banco de dados
        $success = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="contact-main">
  <section class="contact-section">
    <h1>Fale connosco</h1>
    
    <?php if ($success): ?>
      <div class="contact-success">
        <p>Mensagem enviada com sucesso! Entraremos em contato em breve.</p>
      </div>
    <?php else: ?>
      <form class="contact-form" method="POST">
        <div class="form-group">
          <label for="name"><strong>Nome:</strong></label>
          <input type="text" id="name" name="name" placeholder="Digite seu nome" required>
        </div>
        
        <div class="form-group">
          <label for="email"><strong>E-mail:</strong></label>
          <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
        </div>
        
        <div class="form-group">
          <label for="message"><strong>Mensagem:</strong></label>
          <textarea id="message" name="message" placeholder="Digite sua mensagem" required></textarea>
        </div>
        
        <button type="submit" class="contact-submit">Enviar</button>
      </form>
    <?php endif; ?>
    
    <div class="social-promo">
      <p>Siga-nos nas redes sociais</p>
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>