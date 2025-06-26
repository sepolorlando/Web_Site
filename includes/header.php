<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mercearia Online</title>
  <link rel="stylesheet" href="/public/assets/css/style.css">
  <link rel="stylesheet" href="/public/assets/css/header.css">
  <link rel="stylesheet" href="/public/assets/css/carrinho.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<header class="top-header">
  <div class="container header-inner">
    <div class="logo">
      <img src="/public/uploads/logo.jpg" alt="Logo da loja">
    </div>

    <nav class="main-nav">
      <ul>
        <li><a href="#">Início</a></li>
        <li><a href="#">Loja</a></li>
        <li><a href="#">Sobre nós</a></li>
        <li><a href="#">Contato</a></li>
      </ul>
    </nav>

    <div href="/carrinho.php" class="header-icons">
     <div class="cart-icon" id="abrir-carrinho">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">0</span>
      </div>
      <a href="#" class="contact-btn"><i class="fas fa-user"></i> Contato</a>
    </div>
  </div>
</header>

<script src="/public/assets/js/main.js" defer></script>