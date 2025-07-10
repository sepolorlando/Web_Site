<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados do payload JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validação básica dos dados
if (empty($data['customer']) || empty($data['products'])) {
    echo json_encode(['success' => false, 'message' => 'Dados do pedido inválidos']);
    exit;
}

// Validar idade mínima
if ($data['customer']['idade'] < 18) {
    echo json_encode(['success' => false, 'message' => 'É necessário ter pelo menos 18 anos para finalizar a compra']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // 1. Calcular o preço total
    $precoTotal = 0;
    $produtosInfo = [];
    
    foreach ($data['products'] as $produtoId => $quantidade) {
        // Obter informações do produto
        $stmt = $conn->prepare("SELECT preco FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produto) {
            throw new Exception("Produto ID: $produtoId não encontrado");
        }
        
        $precoTotal += $produto['preco'] * $quantidade;
        $produtosInfo[$produtoId] = $produto;
    }
    
    // 2. Criar a encomenda
    $stmt = $conn->prepare("
        INSERT INTO encomendas 
        (nome_cliente, data_nascimento, morada, data_encomenda, preco_total) 
        VALUES 
        (:nome, :data_nascimento, :morada, NOW(), :preco_total)
    ");
    
    $stmt->execute([
        ':nome' => $data['customer']['nome_cliente'],
        ':data_nascimento' => $data['customer']['data_nascimento'],
        ':morada' => $data['customer']['morada'],
        ':preco_total' => $precoTotal
    ]);
    
    $encomendaId = $conn->lastInsertId();
    
    // 3. Processar cada produto do carrinho
    foreach ($data['products'] as $produtoId => $quantidade) {
        // Verificar estoque disponível
        $stmt = $conn->prepare("SELECT quantidade FROM produtos WHERE id = :id FOR UPDATE");
        $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
        $stmt->execute();
        $estoqueAtual = $stmt->fetchColumn();
        
        if ($estoqueAtual < $quantidade) {
            throw new Exception("Estoque insuficiente para o produto ID: $produtoId");
        }
        
        // Adicionar encomenda_produtos
        $stmt = $conn->prepare("
            INSERT INTO `encomenda_produtos`
            (encomenda_id, produto_id, quantidade)  
            VALUES 
            (:encomenda_id, :produto_id, :quantidade)
        ");
        
        $stmt->execute([
            ':encomenda_id' => $encomendaId,
            ':produto_id' => $produtoId,
            ':quantidade' => $quantidade
        ]);
        
        // Atualizar estoque
        $stmt = $conn->prepare("
            UPDATE produtos 
            SET quantidade = quantidade - :quantidade 
            WHERE id = :produto_id
        ");
        
        $stmt->execute([
            ':quantidade' => $quantidade,
            ':produto_id' => $produtoId
        ]);
    }
    
    // 4. Confirmar a transação
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'encomenda_id' => $encomendaId,
        'preco_total' => $precoTotal,
        'message' => 'Encomenda registrada com sucesso'
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar encomenda: ' . $e->getMessage()
    ]);
}