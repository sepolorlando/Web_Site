<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

try {
    // Parâmetros da requisição
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : null;
    $toDate = isset($_GET['to_date']) ? $_GET['to_date'] : null;
    
    // Cálculo do offset
    $offset = ($page - 1) * $perPage;
    
    // Query base
    $sql = "
        SELECT
            e.id,
            e.nome_cliente,
            e.morada,
            e.preco_total,
            e.data_encomenda,
            COUNT(ep.id) AS total_itens
        FROM encomendas e
        LEFT JOIN encomenda_produtos ep ON ep.encomenda_id = e.id
    ";
    
    // Filtros
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "e.nome_cliente LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($fromDate)) {
        $where[] = "e.data_encomenda >= :from_date";
        $params[':from_date'] = "$fromDate 00:00:00";
    }
    
    if (!empty($toDate)) {
        $where[] = "e.data_encomenda <= :to_date";
        $params[':to_date'] = "$toDate 23:59:59";
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    // Agrupamento e ordenação
    $sql .= " GROUP BY e.id ORDER BY e.data_encomenda DESC";
    
    // Query para contar o total
    $countSql = "SELECT COUNT(*) as total FROM ($sql) AS total_query";
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Query para os dados paginados
    $sql .= " LIMIT :offset, :per_page";
    $params[':offset'] = $offset;
    $params[':per_page'] = $perPage;
    
    $stmt = $conn->prepare($sql);
    
    // Bind dos parâmetros
    foreach ($params as $key => &$val) {
        if ($key === ':offset' || $key === ':per_page') {
            $stmt->bindParam($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $val);
        }
    }
    
    $stmt->execute();
    $encomendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar itens para cada encomenda
    foreach ($encomendas as &$encomenda) {
        $stmt = $conn->prepare("
            SELECT 
                ep.id AS item_id,
                ep.produto_id,
                p.nome AS produto_nome,
                p.preco AS produto_preco,
                ep.quantidade
            FROM encomenda_produtos ep
            JOIN produtos p ON p.id = ep.produto_id
            WHERE ep.encomenda_id = :encomenda_id
        ");
        $stmt->execute([':encomenda_id' => $encomenda['id']]);
        $encomenda['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'total' => (int)$total,
        'encomendas' => $encomendas
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro get_encomendas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Falha ao buscar encomendas'
    ]);
}