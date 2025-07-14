<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID não especificado']);
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT id, perfil, username, nome, email FROM utilizadores WHERE id = ?");
    $stmt->execute([$id]);
    $utilizador = $stmt->fetch();

    if ($utilizador) {
        echo json_encode(['success' => true, 'data' => $utilizador]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Utilizador não encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar utilizador',
        'error' => $e->getMessage()
    ]);
}
?>