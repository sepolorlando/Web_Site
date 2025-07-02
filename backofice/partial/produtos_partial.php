<div class="products-container">
    <!-- Barra de filtro e botão Novo -->
    <div class="filter-bar mb-3 d-flex flex-wrap gap-2 align-items-center">
        <input type="text" id="filterName" class="form-control flex-grow-1" placeholder="Filtrar por Nome">
        <select id="filterStatus" class="form-select w-auto">
            <option value="">Todos</option>
            <option value="1">Publicado</option>
            <option value="0">Não publicado</option>
        </select>
        <button id="btnFilter" class="btn btn-primary">Pesquisar</button>
        <button id="btnNovo" class="btn btn-success">
            <i class="fas fa-plus"></i> Novo
        </button>
    </div>

    <!-- Tabela de produtos -->
    <div class="table-responsive">
        <table id="productTable" class="table">
            <thead>
                <tr>
                    <th width="60px"></th>
                    <th>Produto</th>
                    <th width="100px" class="text-center">Qtd</th>
                    <th width="120px" class="text-center">Publicado</th>
                    <th width="120px" class="text-end">Preço</th>
                    <th width="150px" class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody id="productBody">
                <!-- preenchido dinamicamente por produtos.js -->
            </tbody>
        </table>
    </div>

    <!-- Modal de Novo Produto -->
    <div id="modalAdd" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-add" method="post" action="/api/create_product.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome:</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Imagens:</label>
                            <input type="file" name="imagens[]" id="img-input-modal" class="form-control" accept="image/*" multiple>
                            <div id="img-preview-modal" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoria:</label>
                            <select name="categoria" id="selectCategoria" class="form-select" required>
                                <option value="">Carregando categorias...</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantidade:</label>
                                <input type="number" name="quantidade" class="form-control" min="0" value="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preço:</label>
                                <div class="input-group">
                                    <input type="number" name="preco" class="form-control" step="0.01" min="0" value="0.00" required>
                                    <span class="input-group-text">CVE</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="publicado" class="form-check-input" id="chkPublicado">
                            <label for="chkPublicado" class="form-check-label">Publicar?</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal de Edição -->
    <div id="modalEdit" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-edit" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome:</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Imagens Atuais:</label>
                            <div id="img-preview-edit" class="d-flex flex-wrap gap-2 mb-3"></div>
                            <label class="form-label">Adicionar Novas Imagens:</label>
                            <input type="file" name="imagens[]" class="form-control" accept="image/*" multiple>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>