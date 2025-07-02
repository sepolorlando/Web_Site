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
        <?php include '../backofice/partial/encomendas_partial.php'; ?>
    </section>
</main>
<link rel="stylesheet" href="/public/assets/css/encomendas.css">  
<script src="/public/assets/js/encomendas.js" defer></script>
<?php include '../includes/footer.php'; ?>