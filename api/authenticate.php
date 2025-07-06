<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Verifica se os campos foram enviados
if (!isset($_POST['username'], $_POST['password'])) {
    exit('Por favor preencha o nome de utilizador e a palavra-passe.');
}

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare('SELECT * FROM utilizadores WHERE username = ?');
$stmt->execute([$username]);
$conta = $stmt->fetch();

if ($conta && password_verify($password, $conta['palavra_passe'])) {
    session_regenerate_id(true); // segurança
    $_SESSION['account_loggedin'] = true;
    $_SESSION['account_id'] = $conta['id'];
    $_SESSION['account_username'] = $conta['username'];
    $_SESSION['nome'] = $conta['nome'];
    $_SESSION['palavra_passe'] = $conta['palavra_passe'];
    $_SESSION['account_data'] = $conta;

    header('Location: ../../website/backofice/home.php');
    exit;
} else {
    echo '<script>alert("Credenciais inválidas.");window.location.href="../../website/backofice/login.php";</script>';
}
