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

// Verifica o método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendErrorResponse(405, 'Método não permitido', 'Use o método DELETE para esta requisição');
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

if ($id <= 0) {
    sendErrorResponse(400, 'ID inválido', 'O ID deve ser um número positivo', [
        'received_id' => $id
    ]);
}

try {
    // Verifica se o produto existe
    $stmt = $conn->prepare("SELECT id FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() === 0) {
        sendErrorResponse(404, 'Não encontrado', 'O produto especificado não existe');
    }

    // Inicia transação
    $conn->beginTransaction();

    // 1. Obtém caminhos das imagens para remover
    $stmt = $conn->prepare("SELECT caminho_imagem FROM imagens_produtos WHERE produto_id = :id");
    $stmt->execute([':id' => $id]);
    $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Remove registros relacionados
    $conn->prepare("DELETE FROM imagens_produtos WHERE produto_id = :id")->execute([':id' => $id]);
    $conn->prepare("DELETE FROM produto_categorias WHERE produto_id = :id")->execute([':id' => $id]);

    // 3. Remove o produto principal
    $conn->prepare("DELETE FROM produtos WHERE id = :id")->execute([':id' => $id]);

    // 4. Remove arquivos físicos (com segurança)
    $deletedFiles = 0;
    $basePath = realpath($_SERVER['DOCUMENT_ROOT']);
    
    foreach ($imagens as $imagem) {
        $fullPath = $basePath . str_replace('/', DIRECTORY_SEPARATOR, $imagem);
        
        if (file_exists($fullPath) && is_file($fullPath) && strpos($realpath = realpath($fullPath), $basePath) === 0) {
            if (@unlink($realpath)) {
                $deletedFiles++;
            }
        }
    }

    // Confirma a transação
    $conn->commit();

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Produto excluído com sucesso',
        'deleted_images' => $deletedFiles,
        'total_images' => count($imagens)
    ]);

} catch (PDOException $e) {
    // Rollback em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    sendErrorResponse(500, 'Erro no banco de dados', $e->getMessage(), [
        'error_code' => $e->getCode()
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    sendErrorResponse(500, 'Erro no servidor', $e->getMessage());
}