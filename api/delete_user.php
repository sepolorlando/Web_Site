<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID não especificado']);
    exit;
}

$id = $_GET['id'];

try {
    // Verificar se é o último admin
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM utilizadores WHERE perfil = 'admin'");
    $stmt->execute();
    $total_admins = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT perfil FROM utilizadores WHERE id = ?");
    $stmt->execute([$id]);
    $utilizador = $stmt->fetch();
    
    if ($utilizador['perfil'] == 'admin' && $total_admins <= 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Não pode eliminar o último administrador']);
        exit;
    }
    
    // Eliminar utilizador
    $stmt = $conn->prepare("DELETE FROM utilizadores WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Utilizador eliminado com sucesso']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Utilizador não encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao eliminar utilizador',
        'error' => $e->getMessage()
    ]);
}
?>