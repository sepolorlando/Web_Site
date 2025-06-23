<!-- Arquivo: adicionar.php -->
<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

$productId = (int)$_POST['product_id'];

// Verificar se o produto existe
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$stmt->execute([$productId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar carrinho se não existir
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Adicionar produto ao carrinho
if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId]++;
} else {
    $_SESSION['cart'][$productId] = 1;
}

// Contar itens no carrinho
$cartCount = array_sum($_SESSION['cart']);

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $cartCount
]);