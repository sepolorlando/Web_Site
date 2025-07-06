<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;

if ($categoria_id > 0) {
    $sql = "
        SELECT DISTINCT p.id, p.nome, p.preco, p.quantidade, i.caminho_imagem
        FROM produtos p
        INNER JOIN produto_categorias pc ON p.id = pc.produto_id
        LEFT JOIN imagens_produtos i ON p.id = i.produto_id AND i.ordem = 1
        WHERE pc.categoria_id = :cat_id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cat_id', $categoria_id);
    $stmt->execute();
} else {
    $sql = "
        SELECT p.id, p.nome, p.preco, p.quantidade, i.caminho_imagem
        FROM produtos p
        LEFT JOIN imagens_produtos i ON p.id = i.produto_id AND i.ordem = 1
    ";
    $stmt = $conn->query($sql);
}

$produtos = [];
while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $produtos[] = [
        'id' => $product['id'],
        'nome' => $product['nome'],
        'preco' => number_format($product['preco'], 2, ',', '.'),
        'quantidade' => $product['quantidade'],
        'imagem' => '../website/public/' . strtolower($product['caminho_imagem']),
    ];
}

echo json_encode($produtos);
