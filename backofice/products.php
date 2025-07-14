<?php
session_start();
if (!isset($_SESSION['account_loggedin'])) {  
    header('Location: login.php');  
    exit;
}  

// Correct way to get the profile from your session structure
$perfil = $_SESSION['account_data']['perfil'] ?? '';
$isClient = (strtolower($perfil) == 'cliente');  
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="../../website/public/assets/css/backoffice.css">
</head>
<body>
    <?php include '../../website/includes/_barra_lateral.php'; ?>
    <?php include '../../website/includes/header_backofice.php'; ?>

    <main class="main-content">
        <section class="slider">
            <?php if (!$isClient): ?>
                <?php include '../../website/backofice/partial/products_partial.php'; ?>
            <?php else: ?>
                <div class="access-denied">
                    <h2>Acesso Restrito</h2>
                    <p>Você não tem permissão para acessar esta seção.</p>
                    <p>Seu perfil: <?= htmlspecialchars($perfil) ?></p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="../../website/public/assets/js/products.js"></script>
</body>
</html>