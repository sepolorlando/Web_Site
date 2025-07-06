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
        <?php include '../../website/backofice/partial/encomendas_partial.php'; ?>
    </section>
</main>
<link rel="stylesheet" href="../../website/public/assets/css/encomendas.css">  
<script src="../../website/public/assets/js/encomendas.js" defer></script>
<?php include '../../website/includes/footer.php'; ?>