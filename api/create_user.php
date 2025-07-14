<?php
// Habilitar exibição de erros (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de CORS (se necessário)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Coletar dados do POST
$perfil = trim($_POST['perfil'] ?? '');
$username = trim($_POST['username'] ?? '');
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['palavra_passe'] ?? '';

// Validações
$errors = [];

// Campos obrigatórios
if (empty($perfil)) $errors[] = 'Perfil é obrigatório';
if (empty($username)) $errors[] = 'Username é obrigatório';
if (empty($email)) $errors[] = 'Email é obrigatório';
if (empty($password)) $errors[] = 'Senha é obrigatória';

// Tamanhos máximos
if (strlen($perfil) > 20) $errors[] = 'Perfil não pode exceder 20 caracteres';
if (strlen($username) > 50) $errors[] = 'Username não pode exceder 50 caracteres';
if (strlen($email) > 50) $errors[] = 'Email não pode exceder 50 caracteres';

// Validação de formato
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Formato de email inválido';

// Segurança da senha
if (strlen($password) < 6) $errors[] = 'Senha deve ter pelo menos 6 caracteres';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('; ', $errors)]);
    exit;
}

try {
    // Verificar se usuário ou email já existem
    $stmt = $conn->prepare("SELECT id FROM utilizadores WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username ou email já estão em uso']);
        exit;
    }

    // Criar hash da senha
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Inserir novo usuário
    $stmt = $conn->prepare("INSERT INTO utilizadores 
                          (perfil, username, nome, email, palavra_passe) 
                          VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute([$perfil, $username, $nome, $email, $hash]);

    echo json_encode(['success' => true, 'message' => 'Registro realizado com sucesso!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no servidor:' . $e->getMessage()]);
}