<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Verifica se os campos foram enviados
if (!isset($_POST['username'], $_POST['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code' => 400,
        'message' => 'Por favor preencha o nome de utilizador e a palavra-passe'
    ]);
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

try {
    $stmt = $conn->prepare('SELECT * FROM utilizadores WHERE username = ?');
    $stmt->execute([$username]);
    $conta = $stmt->fetch();

    // Verificação se usuário existe
    if (!$conta) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'code' => 0,
            'message' => 'Utilizador não encontrado'
        ]);
        exit;
    }

    // Verificação da senha
    if (password_verify($password, $conta['palavra_passe'])) {
        session_regenerate_id(true);
        $_SESSION['account_loggedin'] = true;
        $_SESSION['account_id'] = $conta['id'];
        $_SESSION['account_username'] = $conta['username'];
        $_SESSION['nome'] = $conta['nome'];
        $_SESSION['account_data'] = $conta;
        $_SESSION['perfil'] = $conta['perfil'];  // Corrigido: usando $conta em vez de $user

        echo json_encode([
            'success' => true,
            'code' => 1,
            'message' => 'Login bem-sucedido',
            'redirect' => '../../website/backofice/home.php'  // Caminho corrigido
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'code' => 2,
            'message' => 'Credenciais inválidas'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'code' => 500,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}