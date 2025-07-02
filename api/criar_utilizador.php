<?php
require_once __DIR__ . '/../includes/db.php'; // Garante que $conn será incluído

// Dados do novo utilizador
$username = 'admin';
$nome = 'orlando';
$password = '1234';

// Gera hash da senha
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Prepara e executa inserção
    $stmt = $conn->prepare("INSERT INTO utilizadores (username, nome, palavra_passe) VALUES (?, ?, ?)");
    $stmt->execute([$username, $nome, $hash]);

    echo "✅ Utilizador criado com sucesso!";
} catch (PDOException $e) {
    echo "❌ Erro ao criar utilizador: " . $e->getMessage();
}
