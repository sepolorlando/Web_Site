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

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse(405, 'method_not_allowed', 'Método não permitido. Use POST.');
}

// Verifica conexão com o banco
if (!isset($conn) || !($conn instanceof PDO)) {
    sendErrorResponse(500, 'database_error', 'Erro no servidor', ['details' => 'Conexão com o banco de dados não disponível']);
}

try {
    // Inicia transação
    $conn->beginTransaction();

    // Validação do ID do produto
    $productId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$productId || $productId <= 0) {
        sendErrorResponse(400, 'invalid_product_id', 'ID do produto inválido');
    }

    // 1. Atualiza os dados básicos do produto
    $stmt = $conn->prepare("
        UPDATE produtos 
        SET nome = :nome, 
            quantidade = :quantidade, 
            preco = :preco, 
            publicado = :publicado
        WHERE id = :id
    ");

    $preco = (float) str_replace(',', '.', $_POST['preco'] ?? '0');

    $stmt->execute([
        ':nome' => trim($_POST['nome'] ?? ''),
        ':quantidade' => (int) ($_POST['quantidade'] ?? 0),
        ':preco' => $preco,
        ':publicado' => isset($_POST['publicado']) ? 1 : 0,
        ':id' => $productId
    ]);

    // 2. Processa as categorias (formato esperado: categorias[] como array)
    if (isset($_POST['categoria'])) {
        // Remove associações antigas
        $conn->prepare("DELETE FROM produto_categorias WHERE produto_id = :id")
             ->execute([':id' => $productId]);

        // Adiciona nova associação (tratando categoria como valor único)
        $stmtCats = $conn->prepare("
            INSERT INTO produto_categorias (produto_id, categoria_id)
            VALUES (:produto_id, :categoria_id)
        ");

        $categoriaId = (int) $_POST['categoria'];
        if ($categoriaId > 0) {
            $stmtCats->execute([
                ':produto_id' => $productId,
                ':categoria_id' => $categoriaId
            ]);
        }
    }

    // 3. Processa novas imagens (se enviadas)
    if (!empty($_FILES['imagens']['name'][0])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/website/public/uploads/';
        
        // Garante que o diretório existe
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception("Falha ao criar diretório de uploads");
            }
        }

        foreach ($_FILES['imagens']['tmp_name'] as $i => $tmpPath) {
            if ($_FILES['imagens']['error'][$i] !== UPLOAD_ERR_OK || !is_uploaded_file($tmpPath)) {
                continue; // Pula arquivos com erro
            }

            // Valida o tipo de arquivo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];
            
            if (!in_array($mime, array_keys($allowedTypes))) {
                continue; // Pula arquivos não permitidos
            }
            
            $ext = $allowedTypes[$mime];
            $basename = uniqid("prod_{$productId}_", true);
            $filename = "{$basename}.{$ext}";
            $target = $uploadDir . $filename;

            if (move_uploaded_file($tmpPath, $target)) {
                // Caminho relativo consistente com o script de criação
                $caminho = '/uploads/' . $filename;
                
                $sqlImg = "INSERT INTO imagens_produtos 
                          (produto_id, caminho_imagem, ordem)
                          VALUES (:pid, :caminho, :ordem)";
                $stImg = $conn->prepare($sqlImg);
                
                // Obtém a próxima ordem disponível
                $nextOrder = $conn->query("
                    SELECT IFNULL(MAX(ordem), 0) + 1 
                    FROM imagens_produtos 
                    WHERE produto_id = $productId
                ")->fetchColumn();
                
                $stImg->execute([
                    ':pid' => $productId,
                    ':caminho' => $caminho,
                    ':ordem' => (int) $nextOrder
                ]);
            } else {
                error_log("Falha ao mover imagem: " . $_FILES['imagens']['name'][$i]);
                continue;
            }
        }
    }

    // Commit da transação
    $conn->commit();

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Produto atualizado com sucesso',
        'product_id' => $productId
    ]);

} catch (PDOException $e) {
    // Rollback em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erro ao atualizar produto: " . $e->getMessage());
    sendErrorResponse(500, 'database_error', 'Erro no banco de dados', [
        'database_error' => $e->getMessage()
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erro geral ao atualizar produto: " . $e->getMessage());
    sendErrorResponse(500, 'server_error', 'Erro ao processar requisição', [
        'error' => $e->getMessage()
    ]);
}