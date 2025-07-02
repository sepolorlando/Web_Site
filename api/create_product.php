<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Método não permitido']);
    exit;
}

try {
    // 1) coleta campos básicos
    $nome  = trim($_POST['nome'] ?? '');
    $qtde  = (int) ($_POST['quantidade'] ?? 0);
    $preco = number_format((float)($_POST['preco'] ?? 0), 2, '.', '');
    $pub   = isset($_POST['publicado']) ? 1 : 0;

    // 2) cria o produto
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

    // 3) garante que a pasta uploads exista
    $uploadDir = realpath(__DIR__ . 'public/uploads');
    if (!$uploadDir) {
        // cria com permissão 0755
        mkdir(__DIR__ . 'public/uploads', 0755, true);
        $uploadDir = realpath(__DIR__ . 'public//uploads');
    }

    // 4) Fprocessa cada imagem e insere em imagens_produtos
    if (
        isset($_FILES['imagens'])
        && is_array($_FILES['imagens']['tmp_name'])
    ){
        foreach ($_FILES['imagens']['tmp_name'] as $i => $tmpPath) {
            // certifique-se de que veio um upload válido
            if ($tmpPath && is_uploaded_file($tmpPath)) {
                // ext original
                $ext = strtolower(
                    pathinfo($_FILES['imagens']['name'][$i], PATHINFO_EXTENSION)
                );

                // nome único e seguro
                $basename = uniqid("prod_{$produtoId}_", true);
                $filename = "{$basename}_{$i}.{$ext}";

                // destino absoluto (ajustado ao seu DOCUMENT_ROOT)
                $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
                        . DIRECTORY_SEPARATOR . 'public/uploads';
                // cria se faltando
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;

                // move e, se OK, grava no banco
                if (move_uploaded_file($tmpPath, $target)) {
                    $caminho = 'uploads/' . $filename;
                    $sqlImg  = "
                        INSERT INTO imagens_produtos 
                            (produto_id, caminho_imagem, `ordem`)
                        VALUES 
                            (:pid, :caminho, :ordem)
                    ";
                    $stImg = $conn->prepare($sqlImg);
                    $stImg->execute([
                        ':pid'     => $produtoId,
                        ':caminho' => $caminho,
                        ':ordem'   => $i + 1,
                    ]);
                } else {
                    // log de erro em caso de falha no move
                    error_log("Falha ao gravar imagem #{$i} para o produto {$produtoId}");
                }
            }
        }
    }
    // // 5) processa categorias (form deve ter name="categorias[]" múltiplo)
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

    echo json_encode(['success'=>true,'id'=>$produtoId]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro create_product: ".$e->getMessage());
    echo json_encode(['success'=>false,'error'=>'Falha ao criar produto']);
}
