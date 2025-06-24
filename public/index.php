<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
?>

<div class="container">
  <div class="main-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <h3>Categorias</h3>
      <ul>
        <li><a href="#" class="active">Todos os produtos <span>(25)</span></a></li>
        <li><a href="#">Acessórios <span>(8)</span></a></li>
        <li><a href="#">Decoração <span>(12)</span></a></li>
        <li><a href="#">Ferramentas <span>(6)</span></a></li>
        <li><a href="#">Materiais <span>(15)</span></a></li>
        <li><a href="#">Móveis <span>(9)</span></a></li>
        <li><a href="#">Projetos <span>(4)</span></a></li>
        <li><a href="#">Promoções <span>(7)</span></a></li>
        <li><a href="#">Serviços <span>(3)</span></a></li>
      </ul>
    </aside>

    <!-- Conteúdo principal -->
    <section class="product-area">
      <div class="top-bar">
        <input type="text" placeholder="Pesquisar...">
        <div class="top-actions">
          <select>
            <option>Destaques</option>
            <option>Mais baratos</option>
            <option>Mais caros</option>
          </select>
          <button class="filter-btn"><i class="fas fa-filter"></i> Filtros</button>
        </div>
      </div>

      <div class="products-grid">
        <?php
        $stmt = $conn->query("SELECT p.id, p.nome, p.preco, i.caminho_imagem FROM Produtos p LEFT JOIN Imagens_Produtos i ON p.id = i.produto_id AND i.ordem = 1");
        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="card">';
            echo '  <img src="/public/' . htmlspecialchars(strtolower($product['caminho_imagem'])) . '" alt="' . htmlspecialchars($product['nome']) . '">';
            echo '  <div class="card-body">';
            echo '    <h4>' . htmlspecialchars($product['nome']) . '</h4>';
            echo '    <div class="card-footer">';
            echo '      <span class="price">' . number_format($product['preco'], 2, ',', '.') . ' $</span>';
            echo '      <button class="add-btn">Adicionar</button>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
        ?>
      </div>
    </section>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
