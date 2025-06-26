document.addEventListener('DOMContentLoaded', () => {
  renderizarCarrinho();

  function renderizarCarrinho() {
    const lista = document.querySelector('.cart-list');
    const subtotalEl = document.querySelector('.subtotal');
    const totalEl = document.querySelector('.total');
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || {};

    if (!Object.keys(carrinho).length) {
      lista.innerHTML = '<p>Carrinho vazio.</p>';
      subtotalEl.textContent = '0,00 $';
      totalEl.textContent = '0,00 $';
      return;
    }

    fetch('/api/get_products.php')
      .then(res => res.json())
      .then(produtos => {
        lista.innerHTML = '';
        let subtotal = 0;

        Object.keys(carrinho).forEach(id => {
          const produto = produtos.find(p => p.id == id);
          const quantidade = carrinho[id];
          const preco = parseFloat(produto.preco.replace('.', '').replace(',', '.'));
          const totalProduto = preco * quantidade;
          subtotal += totalProduto;

          const li = document.createElement('li');
          li.classList.add('cart-item');
          li.innerHTML = `
            <img src="${produto.imagem}" alt="${produto.nome}">
            <div class="info">
              <strong>${produto.nome}</strong>
              <p>${produto.descricao ?? ''}</p>
              <a href="#" class="remover" data-id="${produto.id}">Remover</a>
            </div>
            <div class="quantidade">
              <button class="menos" data-id="${produto.id}">-</button>
              <span>${quantidade}</span>
              <button class="mais" data-id="${produto.id}">+</button>
            </div>
            <div class="preco">${totalProduto.toFixed(2).replace('.', ',')} $</div>
          `;
          lista.appendChild(li);
        });

        subtotalEl.textContent = subtotal.toFixed(2).replace('.', ',') + ' $';
        totalEl.textContent = subtotalEl.textContent;

        ativarBotoes();
      });
  }

  function ativarBotoes() {
    document.querySelectorAll('.mais').forEach(btn => {
      btn.addEventListener('click', () => atualizarQtd(btn.dataset.id, 1));
    });

    document.querySelectorAll('.menos').forEach(btn => {
      btn.addEventListener('click', () => atualizarQtd(btn.dataset.id, -1));
    });

    document.querySelectorAll('.remover').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        atualizarQtd(link.dataset.id, 0);
      });
    });
  }

  function atualizarQtd(id, delta) {
    let carrinho = JSON.parse(localStorage.getItem('carrinho')) || {};
    if (delta === 0) {
      delete carrinho[id];
    } else {
      carrinho[id] = (carrinho[id] || 0) + delta;
      if (carrinho[id] <= 0) delete carrinho[id];
    }
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    renderizarCarrinho();
  }
});
