<div class="encomendas-container">
  <div class="encomendas-header">
    <h2><i class="fas fa-clipboard-list"></i> Gestão de Encomendas</h2>
    <div class="encomendas-filters">
      <input
        type="text"
        id="filter-search"
        class="form-input filter-search"
        placeholder="Cliente ou ID..."
      />
      <button id="btn-apply-filters" class="btn btn-primary">
        <i class="fas fa-filter"></i> Filtrar
      </button>
    </div>
  </div>

  <div class="encomendas-table-container">
    <table class="encomendas-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Data</th>
          <th>Total</th>
          <th>Itens</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody id="encomendas-body">
        <tr>
          <td colspan="6" class="loading-text">
            <i class="fas fa-spinner fa-spin"></i> Carregando encomendas...
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="encomendas-pagination">
    <div class="pagination-info" id="pagination-info">
      Mostrando 0 de 0 encomendas
    </div>
    <div class="pagination-controls">
      <button id="btn-prev" class="btn btn-outline" disabled>
        <i class="fas fa-chevron-left"></i> Anterior
      </button>
      <button id="btn-next" class="btn btn-outline" disabled>
        Próxima <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</div>
<div id="encomendaModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <h3>Detalhes da Encomenda #<span id="modalEncomendaId"></span></h3>
    
    <div class="modal-section">
      <h4>Informações do Cliente</h4>
      <p><strong>Nome:</strong> <span id="modalClienteNome"></span></p>
      <p><strong>Morada:</strong> <span id="modalClienteMorada"></span></p>
    </div>
    
    <div class="modal-section">
      <h4>Informações da Encomenda</h4>
      <p><strong>Data:</strong> <span id="modalEncomendaData"></span></p>
      <p><strong>Total:</strong> <span id="modalEncomendaTotal"></span></p>
    </div>
    
    <div class="modal-section">
      <h4>Itens da Encomenda</h4>
      <div id="modalItensLista"></div>
    </div>
  </div>
</div>
<style>
 
</style>

<script>
 
</script>
