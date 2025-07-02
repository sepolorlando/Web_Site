<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
// Função para respostas de erro padronizadas

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

// Verifica se a conexão PDO ($conn) está disponível
if (!isset($conn) || !($conn instanceof PDO)) {
    sendErrorResponse(500, 'Erro no servidor', 'Falha na conexão com o banco de dados');
}

// Recebe e processa o input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse(400, 'JSON inválido', json_last_error_msg(), [
        'json_error' => json_last_error()
    ]);
}

// Validação do ID
$id = $input['id'] ?? null;

if ($id === null) {
    sendErrorResponse(400, 'Campo obrigatório', 'O campo ID é obrigatório');
}

if (!is_numeric($id)) {
    sendErrorResponse(400, 'ID inválido', 'O ID deve ser um valor numérico', [
        'received_id' => $id,
        'received_type' => gettype($id)
    ]);
}

$id = (int)$id;


try {
    // 1. Busca dados básicos do produto
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        sendErrorResponse(404, 'Produto não encontrado');
    }

    // 2. Busca imagens do produto
    $stmt = $conn->prepare("SELECT id, caminho_imagem FROM imagens_produtos WHERE produto_id = :id");
    $stmt->execute([':id' => $id]);
    $produto['imagens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Busca categorias do produto
    $stmt = $conn->prepare("SELECT categoria_id FROM produto_categorias WHERE produto_id = :id");
    $stmt->execute([':id' => $id]);
    $produto['categorias'] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // 4. Formata os dados de preço
    $produto['preco'] = (float)$produto['preco'];
    $produto['quantidade'] = (int)$produto['quantidade'];
    $produto['publicado'] = (bool)$produto['publicado'];

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'data' => $produto
    ]);

} catch (PDOException $e) {
    error_log("Erro no get_product: " . $e->getMessage());
    sendErrorResponse(500, $e);

} catch (Exception $e) {
    error_log("Erro geral no get_product: " . $e->getMessage());
    sendErrorResponse(500, 'Erro ao processar requisição');
}