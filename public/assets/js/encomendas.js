 document.addEventListener('DOMContentLoaded', () => {
    let currentPage = 1,
          perPage = 10,
          encomendasData = []; // guardamos aqui para usar nos detalhes
    const tableBody = document.getElementById('encomendas-body');
    const searchFilter = document.getElementById('filter-search');
    const btnApply = document.getElementById('btn-apply-filters');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const paginationInfo = document.getElementById('pagination-info');
const modal = document.getElementById('encomendaModal');
const closeModal = document.querySelector('.close-modal');

closeModal.addEventListener('click', () => {
  modal.style.display = 'none';
  document.body.style.overflow = 'auto';
});

window.addEventListener('click', (e) => {
  if (e.target === modal) {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  }
});
    function loadEncomendas() {
  tableBody.innerHTML = `
    <tr>
      <td colspan="6" class="loading-text">
        <i class="fas fa-spinner fa-spin"></i> Carregando encomendas...
      </td>
    </tr>`;
  const params = new URLSearchParams({
    page: currentPage,
    per_page: perPage,
    search: searchFilter.value.trim()
  });
  fetch(`/api/get_encomendas.php?${params}`)
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.error || 'Erro desconhecido');
      encomendasData = data.encomendas; // Armazena os dados recebidos
      renderEncomendas(data.encomendas);
      updatePagination({
        total: data.total,
        per_page: perPage,
        current_page: currentPage
      });
    })
    .catch(err => {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="loading-text" style="color:#e74c3c">
            <i class="fas fa-exclamation-circle"></i> ${err.message}
          </td>
        </tr>`;
      console.error(err);
    });
}

    function renderEncomendas(list) {
      if (!Array.isArray(list) || list.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="6" class="loading-text">
              Nenhuma encomenda encontrada
            </td>
          </tr>`;
        return;
      }
      tableBody.innerHTML = '';
      list.forEach(e => {
        const dataFmt = e.data_encomenda
          ? new Date(e.data_encomenda).toLocaleDateString('pt-PT')
          : '–';
        const totalItens = Array.isArray(e.itens)
          ? e.itens.reduce((sum, i) => sum + (i.quantidade||0), 0)
          : 0;
        const preco = parseFloat(e.preco_total) || 0;
        const precoFmt = preco.toFixed(2) + ' ECV';
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>#${e.id||'–'}</td>
          <td>
            <strong>${e.nome_cliente||'–'}</strong><br/>
            <small class="text-muted">${e.morada||'–'}</small>
          </td>
          <td>${dataFmt}</td>
          <td>${precoFmt}</td>
          <td>${totalItens} ${totalItens===1?'item':'itens'}</td>
          <td>
            <button class="btn btn-outline btn-sm btn-view" data-id="${e.id}">
              <i class="fas fa-eye"></i> Detalhes
            </button>
          </td>`;
        tableBody.appendChild(row);
      });
      document.querySelectorAll('.btn-view').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const id = btn.dataset.id;
        if (id) showEncomendaDetails(id);
      });
    });
    }

  function showEncomendaDetails(encomendaId) {
    const encomenda = encomendasData.find(e => e.id == encomendaId);
    if (!encomenda) return;
    
    // Preenche o modal com os dados
    document.getElementById('modalEncomendaId').textContent = encomenda.id;
    document.getElementById('modalClienteNome').textContent = encomenda.nome_cliente || 'N/A';
    document.getElementById('modalClienteMorada').textContent = encomenda.morada || 'N/A';
    
    const dataEncomenda = encomenda.data_encomenda 
      ? new Date(encomenda.data_encomenda).toLocaleString('pt-PT') 
      : 'N/A';
    document.getElementById('modalEncomendaData').textContent = dataEncomenda;
    
    const precoTotal = parseFloat(encomenda.preco_total) || 0;
    document.getElementById('modalEncomendaTotal').textContent = 
      precoTotal.toLocaleString('pt-PT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }) + ' ECV';
    
    // Preenche os itens
    const itemsContainer = document.getElementById('modalItensLista');
    itemsContainer.innerHTML = '';
    
    if (encomenda.itens && encomenda.itens.length > 0) {
      const itemsTable = document.createElement('table');
      itemsTable.className = 'items-table';
      itemsTable.innerHTML = `
        <thead>
          <tr>
            <th>Produto</th>
            <th>Preço</th>
            <th>Qtd</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          ${encomenda.itens.map(item => {
            const subtotal = (parseFloat(item.produto_preco) || 0) * (parseInt(item.quantidade) || 0);
            return `
              <tr>
                <td>${item.produto_nome || 'N/A'}</td>
                <td>${(parseFloat(item.produto_preco) || 0).toFixed(2)} ECV</td>
                <td>${item.quantidade || 0}</td>
                <td>${subtotal.toFixed(2)} ECV</td>
              </tr>
            `;
          }).join('')}
        </tbody>
      `;
      itemsContainer.appendChild(itemsTable);
    } else {
      itemsContainer.innerHTML = '<p>Nenhum item nesta encomenda</p>';
    }
    
    // Mostra o modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
    function updatePagination({ total, per_page, current_page }) {
      const from = total === 0 ? 0 : (current_page - 1) * per_page + 1;
      const to = Math.min(current_page * per_page, total);
      paginationInfo.textContent = `Mostrando ${from} a ${to} de ${total} encomendas`;
      btnPrev.disabled = current_page <= 1;
      btnNext.disabled = current_page >= Math.ceil(total / per_page);
    }

    btnApply.addEventListener('click', () => {
      currentPage = 1;
      loadEncomendas();
    });
    btnPrev.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        loadEncomendas();
      }
    });
    btnNext.addEventListener('click', () => {
      currentPage++;
      loadEncomendas();
    });

    loadEncomendas();
  });