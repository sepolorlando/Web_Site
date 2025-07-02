
<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

try {
   // Busca todas as categorias ativas
    $stmt = $conn->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'    => true,
        'categories' => $categories,
        'count'      => count($categories)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Falha ao buscar categorias',
        'details' => $e->getMessage()
    ]);
}