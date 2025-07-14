<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
  header('Location: ../website/login.php');
  exit;
}
?>
<?php include '../../website/includes/_barra_lateral.php'; ?>
<?php include '../../website/includes/header_backofice.php'; ?>


<main class="main-content">
  <section class="slider">
   
  </section>
</main>


