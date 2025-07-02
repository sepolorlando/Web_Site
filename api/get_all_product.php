<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

try {
    // Monta um JSON de todas as imagens e retorna também quantidade e publicado
    $sql = "
      SELECT
        p.id,
        p.nome,
        p.quantidade,
        p.preco   AS preco_unitario,
        p.publicado,
        CONCAT(
          '[',
          GROUP_CONCAT(
            JSON_QUOTE(ip.caminho_imagem)
            ORDER BY ip.ordem
            SEPARATOR ','
          ),
          ']'
        ) AS imagens
      FROM produtos p
      LEFT JOIN imagens_produtos ip
        ON ip.produto_id = p.id
      GROUP BY p.id
      ORDER BY p.id DESC
    ";
    $stmt     = $conn->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decodifica JSON de imagens e define thumb
    foreach ($produtos as &$p) {
        $p['imagens'] = json_decode($p['imagens'], true) ?: [];
        $p['thumb']   = $p['imagens'][0] ?? null;
    }
    unset($p);

    echo json_encode([
      'success' => true,
      'data'    => $produtos
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'error'   => 'Falha ao buscar produtos'
    ]);
}
