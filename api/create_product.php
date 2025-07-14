<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Método não permitido']);
    exit;
}

try {
    // 1) Coleta campos básicos
    $nome  = trim($_POST['nome'] ?? '');
    $qtde  = (int) ($_POST['quantidade'] ?? 0);
    $preco = number_format((float)($_POST['preco'] ?? 0), 2, '.', '');
    $pub   = isset($_POST['publicado']) ? 1 : 0;

    // 2) Cria o produto
    $sql = "INSERT INTO produtos (nome, quantidade, preco, publicado)
            VALUES (:nome, :qtde, :preco, :pub)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome'   => $nome,
        ':qtde'   => $qtde,
        ':preco'  => $preco,
        ':pub'    => $pub
    ]);
    $produtoId = $conn->lastInsertId();

    // 3) Configurações do upload - CAMINHO CORRIGIDO
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/website/public/uploads/';
    
    // Garante que a pasta existe e tem permissões
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Falha ao criar diretório de uploads");
        }
    }

    // 4) Processa cada imagem
    if (isset($_FILES['imagens']) && is_array($_FILES['imagens']['tmp_name'])) {
        foreach ($_FILES['imagens']['tmp_name'] as $i => $tmpPath) {
            if ($tmpPath && is_uploaded_file($tmpPath)) {
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
                $basename = uniqid("prod_{$produtoId}_", true);
                $filename = "{$basename}.{$ext}";
                $target = $uploadDir . $filename;

                if (move_uploaded_file($tmpPath, $target)) {
                    // CAMINHO RELATIVO CORRIGIDO (sem /website)
                    $caminho = '/uploads/' . $filename;
                    
                    $sqlImg = "INSERT INTO imagens_produtos 
                              (produto_id, caminho_imagem, `ordem`)
                              VALUES (:pid, :caminho, :ordem)";
                    $stImg = $conn->prepare($sqlImg);
                    $stImg->execute([
                        ':pid'     => $produtoId,
                        ':caminho' => $caminho,
                        ':ordem'   => $i + 1,
                    ]);
                } else {
                    error_log("Falha ao mover imagem: " . $_FILES['imagens']['name'][$i]);
                    throw new Exception("Falha ao mover arquivo enviado");
                }
            }
        }
    }

    // 5) Processa categorias
    if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
        $sqlCat = "INSERT INTO produto_categorias (produto_id,categoria_id)
                   VALUES (:pid, :cid)";
        $stCat  = $conn->prepare($sqlCat);
        foreach ($_POST['categorias'] as $catId) {
            $stCat->execute([
                ':pid' => $produtoId,
                ':cid' => (int)$catId
            ]);
        }
    }

    echo json_encode(['success'=>true, 'id'=>$produtoId]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro create_product: ".$e->getMessage());
    echo json_encode(['success'=>false, 'message'=>'Falha ao criar produto: ' . $e->getMessage()]);
}