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
        <div class="page-title">
            <div class="wrap">
                <p>Bem-vindo,
                    <strong>
                        <?= htmlspecialchars($_SESSION['nome']) ?>
                    </strong>!
                </p>
            </div>
        </div>

        <div class="block">

            <div class="profile-detail">
                <strong>Nome completo</strong>
                <?= htmlspecialchars($_SESSION['nome']) ?>
            </div>

            <div class="profile-detail">
                <strong>Username</strong>
                <?= htmlspecialchars($_SESSION['account_username']) ?>
            </div>
        </div>
    </section>
</main>


<script src="/public/assets/js/produtos.js"></script>
<?php include '../includes/footer.php'; ?>