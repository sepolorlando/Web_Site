<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
  header('Location: login.php');
  exit;
}
?>
<?php include '../includes/_barra_lateral.php'; ?>
<?php include '../includes/header_backofice.php'; ?>


<main class="main-content">
  <section class="slider">
   <?php include '../backofice/partial/produtos_partial.php'; ?>
  </section>
</main>

<!-- jQuery e DataTables JS -->



<script src="/public/assets/js/produtos.js"></script>
<?php include '../includes/footer.php'; ?>
