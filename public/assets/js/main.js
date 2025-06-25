document.addEventListener('DOMContentLoaded', function () {
  carregarCategorias();
  carregarProdutos(); // todos

  function carregarCategorias() {
    fetch('/api/get_categorias.php')
      .then(res => res.json())
      .then(categorias => {
        const lista = document.querySelector('.sidebar ul');
        lista.innerHTML = `<li><a href="#" data-id="0" class="active">Todos os produtos</a></li>`;

        categorias.forEach(cat => {
          const li = document.createElement('li');
          li.innerHTML = `<a href="#" data-id="${cat.id}">${cat.nome} <span>(${cat.total})</span></a>`;
          lista.appendChild(li);
        });

        document.querySelectorAll('.sidebar a').forEach(link => {
          link.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');
            const categoriaId = this.dataset.id;
            carregarProdutos(categoriaId);
          });
        });
      });
  }

  function carregarProdutos(categoriaId = 0) {
    fetch(`/api/get_products.php?categoria_id=${categoriaId}`)
      .then(res => res.json())
      .then(data => {
        const grid = document.querySelector('.products-grid');
        grid.innerHTML = '';

        data.forEach(produto => {
          const card = document.createElement('div');
          card.className = 'card';
          card.innerHTML = `
            <img src="${produto.imagem}" alt="${produto.nome}">
            <div class="card-body">
              <h4>${produto.nome}</h4>
              <div class="card-footer">
                <span class="price">${produto.preco} $</span>
                <button class="add-btn">Adicionar</button>
              </div>
            </div>
          `;
          grid.appendChild(card);
        });
      });
  }
});
