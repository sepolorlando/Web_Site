<?php
require_once __DIR__ . '../../website/includes/header_backofice.php';
?>
<div class="content">
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
</div>
</body>

</html>