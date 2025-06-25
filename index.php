<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
?>

<div class="container">
  <div class="main-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <h3>Categorias</h3>
      <ul>
        <!-- categorias serão carregadas via JS -->
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
        <!-- produtos serão carregados via JS -->
      </div>
    </section>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
