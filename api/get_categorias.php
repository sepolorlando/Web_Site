<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$stmt = $conn->query("
    SELECT c.id, c.nome, COUNT(pc.produto_id) as total
    FROM categorias c
    LEFT JOIN produto_categorias pc ON c.id = pc.categoria_id
    GROUP BY c.id
");

echo json_encode($stmt->fetchAll());
