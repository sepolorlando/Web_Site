<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {
    header('Location: login.php');
    exit;
}
?>
<?php include '../../website/includes/_barra_lateral.php'; ?>
<?php include '../../website/includes/header_backofice.php'; ?>

<main class="main-content">
    <section class="slider">
         <?php include '../../website/backofice/partial/produtos_partial.php'; ?>
    </section>
</main>


<script src="../../website/public/assets/js/produtos.js"></script>
<?php include '../../website/includes/footer.php'; ?>
