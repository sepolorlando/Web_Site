<!-- Arquivo: index.php -->
<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
?>

<header class="inwood-header">
    <div class="container">
        <h1>INWOOD</h1>
        <nav>
            <ul>
                <li><a href="#">Products</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="search-section">
    <div class="container">
        <h2>Search</h2>
        <div class="categories">
            <a href="#">Bedroom</a>
            <a href="#">Dining Room</a>
            <a href="#">Meeting Room</a>
            <a href="#">Workspace</a>
            <a href="#">Living Room</a>
            <a href="#">Kitchen</a>
            <a href="#">Living Space</a>
        </div>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php
            // Busca produtos no banco de dados
            $stmt = $pdo->query("SELECT * FROM products LIMIT 4");
            while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="product-card">';
                echo '<img src="/assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                echo '<p class="price">' . number_format($product['price'], 2, ',', '.') . ' $</p>';
                echo '<button class="add-to-cart" data-id="' . $product['id'] . '">Add to Cart</button>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<section class="all-categories">
    <div class="container">
        <a href="#" class="btn">All Categories</a>
    </div>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>