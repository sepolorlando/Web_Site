<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

// Função para respostas de erro padronizadas
function sendErrorResponse($code, $error, $message = '', $details = []) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $error,
        'message' => $message,
        'details' => $details
    ]);
    exit;
}

// Verifica conexão com o banco
if (!isset($conn) || !($conn instanceof PDO)) {
    sendErrorResponse(500, 'database_error', 'Falha na conexão com o banco de dados');
}

// Suporta tanto GET quanto POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse(400, 'invalid_json', 'JSON inválido', [
            'json_error' => json_last_error_msg()
        ]);
    }
    $id = $input['id'] ?? null;
}

// Validação robusta do ID
if ($id === null || $id === '') {
    sendErrorResponse(400, 'missing_id', 'O campo ID é obrigatório');
}

if (!ctype_digit((string)$id)) {
    sendErrorResponse(400, 'invalid_id', 'O ID deve ser um número inteiro positivo', [
        'received_id' => $id,
        'received_type' => gettype($id)
    ]);
}

$id = (int)$id;
if ($id <= 0) {
    sendErrorResponse(400, 'invalid_id_range', 'O ID deve ser maior que zero');
}

try {
    // Consulta separada para melhor compatibilidade
    
    // 1. Busca dados básicos do produto
    $sqlProduto = "SELECT * FROM produtos WHERE id = :id";
    $stmt = $conn->prepare($sqlProduto);
    $stmt->execute([':id' => $id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        sendErrorResponse(404, 'product_not_found', 'Produto não encontrado');
    }

    // 2. Busca imagens do produto
    $sqlImagens = "SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = :id";
    $stmt = $conn->prepare($sqlImagens);
    $stmt->execute([':id' => $id]);
    $caminho_imagens = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // 3. Busca categorias do produto
    $sqlCategorias = "SELECT categoria_id FROM produto_categorias WHERE produto_id = :id";
    $stmt = $conn->prepare($sqlCategorias);
    $stmt->execute([':id' => $id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Processa os dados
    $produto['caminho_imagens'] =  $caminho_imagens ?: [];
    $produto['categorias'] = $categorias ?: [];
    $produto['preco'] = (float)$produto['preco'];
    $produto['quantidade'] = (int)$produto['quantidade'];
    $produto['publicado'] = (bool)$produto['publicado'];

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'data' => $produto
    ]);

} catch (PDOException $e) {
    error_log("PDOException in get_details_product: " . $e->getMessage());
    sendErrorResponse(500, 'database_error', 'Erro ao acessar o banco de dados', [
        'pdo_error' => $e->getMessage()
    ]);

} catch (Exception $e) {
    error_log("Exception in get_details_product: " . $e->getMessage());
    sendErrorResponse(500, 'server_error', 'Erro interno no servidor');
}