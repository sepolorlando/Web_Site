<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mercearia Online</title>
  <link rel="stylesheet" href="../website/public/assets/css/style.css">
  <link rel="stylesheet" href="../website/public/assets/css/header.css">
  <link rel="stylesheet" href="../website/public/assets/css/carrinho.css">
   <link rel="stylesheet" href="../website/public/assets/css/footer.css">
  <link rel="stylesheet" href="../website/public/assets/css/checkout.css">
  <link rel="stylesheet" href="../website/public/assets/css/not-found.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


</head>

<body>


  <header class="top-header">
    <!-- Linha 1: logo, busca central, icons -->
    <div class="header-row-1">
      <div class="header-left">
        <div class="logo">
          <img src="../website/public/uploads/logo.png" alt="Logo da loja">
        </div>
      </div>
      <div class="header-center">
        <div class="header-search">
          <input type="text" placeholder="O que estás à procura?">
          <button><i class="fas fa-search"></i></button>
        </div>
      </div>
      <div class="header-right">
        <a href="../website/backofice/login.php" class="contact-btn"></i> Olá! Iniciar Sessão</a>
        <div href="website/carrinho.php" class="header-icons">
          <div class="cart-icon" id="abrir-carrinho">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count">0</span>
          </div>
        </div>
      </div>
    </div>
    <!-- Linha 2: menu centralizado -->
    <div class="header-row-2">
      <nav class="main-nav">
        <ul>
          <li><a href="../website/" class="active">Home</a></li>
          <li><a href="#">Promoções</a></li>
          <li><a href="#">Blog</a></li>
          <li><a href="../website/404.php">Sobre nós</a></li>
          <li><a href="../website/contact.php">Contato</a></li>
        </ul>
      </nav>
    </div>
  </header>


  <script src="../website/public/assets/js/main.js" defer></script>

  <script src="../website/public/assets/js/alert.js" defer></script>