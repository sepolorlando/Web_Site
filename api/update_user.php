<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';

// Desativar exibição de erros HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Função para enviar resposta padronizada
function sendResponse($success, $message, $code = 200) {
    http_response_code($code);
    exit(json_encode([
        'sucesso' => $success,
        'mensagem' => $message
    ]));
}

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Método não permitido', 405);
    }

    // Ler input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(false, 'JSON inválido', 400);
    }

    // Validação dos campos
    $requiredFields = ['id', 'perfil', 'username', 'nome', 'email'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            sendResponse(false, "O campo $field é obrigatório", 400);
        }
    }

    // Validar email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Email inválido', 400);
    }

    // Verificar se utilizador existe
    $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE id = ?");
    $stmt->execute([$input['id']]);
    
    if ($stmt->rowCount() === 0) {
        sendResponse(false, 'Utilizador não encontrado', 404);
    }

    // Verificar conflitos de username/email
    $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$input['username'], $input['email'], $input['id']]);
    
    if ($stmt->rowCount() > 0) {
        sendResponse(false, 'Username ou email já estão em uso', 409);
    }

    // Preparar dados para atualização
    $updateData = [
        'perfil' => $input['perfil'],
        'username' => $input['username'],
        'nome' => $input['nome'],
        'email' => $input['email'],
        'id' => $input['id']
    ];

    // Atualizar password se fornecida
    if (!empty($input['palavra_passe'])) {
        if (strlen($input['palavra_passe']) < 6) {
            sendResponse(false, 'A password deve ter pelo menos 6 caracteres', 400);
        }
        $updateData['palavra_passe'] = password_hash($input['palavra_passe'], PASSWORD_DEFAULT);
    }

    // Construir query dinamicamente
    $setClause = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updateData)));
    $query = "UPDATE utilizadores SET $setClause WHERE id = :id";

    $stmt = $pdo->prepare($query);
    $stmt->execute($updateData);

    sendResponse(true, 'Utilizador atualizado com sucesso');

} catch (PDOException $e) {
    sendResponse(false, 'Erro na base de dados: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendResponse(false, 'Erro: ' . $e->getMessage(), 500);
}