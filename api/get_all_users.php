<?php
// Inclui o arquivo de conexão que você já tem
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Usa a conexão $conn que já está definida no db.php
    $stmt = $conn->query("SELECT id, perfil, username, nome, email FROM utilizadores ORDER BY nome");
    $utilizadores = $stmt->fetchAll();

    // Formata a resposta
    echo json_encode([
        'success' => true,
        'data' => $utilizadores,
        'count' => count($utilizadores)
    ]);

} catch (PDOException $e) {
    // Registra o erro e retorna mensagem amigável
    error_log("Erro em get_all_users.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar utilizadores',
        'error' => $e->getMessage() // Apenas para desenvolvimento
    ]);
}
?>