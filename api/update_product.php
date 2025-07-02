<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php'; // Assume que seu db.php está na pasta includes

// Função para enviar respostas de erro padronizadas
function sendErrorResponse($code, $error, $details = []) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $error,
        'details' => $details
    ]);
    exit;
}

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse(405, 'Método não permitido. Use POST.');
}

// Verifica se a conexão PDO está disponível
if (!isset($conn) || !($conn instanceof PDO)) {
    sendErrorResponse(500, 'Erro no servidor', ['details' => 'Conexão com o banco de dados não disponível']);
}

try {
    // Inicia transação
    $conn->beginTransaction();

    // Validação do ID do produto
    $productId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$productId || $productId <= 0) {
        sendErrorResponse(400, 'ID do produto inválido');
    }

    // 1. Atualiza os dados básicos do produto
    $stmt = $conn->prepare("
        UPDATE produtos 
        SET nome = :nome, 
            quantidade = :quantidade, 
            preco = :preco, 
            publicado = :publicado, 
            atualizado_em = NOW()
        WHERE id = :id
    ");

    $stmt->execute([
        ':nome' => $_POST['nome'],
        ':quantidade' => (int)$_POST['quantidade'],
        ':preco' => (float)str_replace(',', '.', $_POST['preco']),
        ':publicado' => isset($_POST['publicado']) ? 1 : 0,
        ':id' => $productId
    ]);

    // 2. Processa as categorias
    if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
        // Remove associações antigas
        $conn->prepare("DELETE FROM produto_categorias WHERE produto_id = :id")
             ->execute([':id' => $productId]);

        // Adiciona novas associações
        $stmtCats = $conn->prepare("
            INSERT INTO produto_categorias (produto_id, categoria_id)
            VALUES (:produto_id, :categoria_id)
        ");

        foreach ($_POST['categorias'] as $categoriaId) {
            $stmtCats->execute([
                ':produto_id' => $productId,
                ':categoria_id' => (int)$categoriaId
            ]);
        }
    }

    // 3. Processa novas imagens (se enviadas)
    if (!empty($_FILES['imagens']['name'][0])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/';
        
        foreach ($_FILES['imagens']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['imagens']['error'][$key] !== UPLOAD_ERR_OK) {
                continue; // Pula arquivos com erro
            }

            // Gera nome único para o arquivo
            $fileName = 'prod_' . $productId . '_' . uniqid() . '_' . $key . '.jpg';
            $filePath = $uploadDir . $fileName;
            $relativePath = '/public/uploads/' . $fileName;

            // Move o arquivo para o diretório de uploads
            if (move_uploaded_file($tmpName, $filePath)) {
                // Insere no banco de dados
                $conn->prepare("
                    INSERT INTO imagens_produtos (produto_id, caminho_imagem)
                    VALUES (:produto_id, :caminho)
                ")->execute([
                    ':produto_id' => $productId,
                    ':caminho' => $relativePath
                ]);
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
    sendErrorResponse(500, 'Erro no banco de dados', [
        'database_error' => $e->getMessage()
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erro geral ao atualizar produto: " . $e->getMessage());
    sendErrorResponse(500, 'Erro ao processar requisição', [
        'error' => $e->getMessage()
    ]);
}