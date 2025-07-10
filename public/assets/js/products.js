// Gerenciador central de categorias
const categoryManager = {
    categories: [],
    loaded: false,

    async load() {
        try {
            const response = await fetch('/website/api/get_categorias2.php');
            const data = await response.json();
            
            if (data.success) {
                this.categories = data.categories || data.data; // Compatível com diferentes formatos de resposta
                this.loaded = true;
                console.log('Categorias carregadas:', this.categories);
            } else {
                console.error('Erro ao carregar categorias:', data.message || data.error);
                throw new Error(data.message || 'Erro ao carregar categorias');
            }
            return this.categories;
        } catch (error) {
            console.error('Erro ao carregar categorias:', error);
            throw error;
        }
    },

    async ensureLoaded() {
        if (!this.loaded) {
            await this.load();
        }
        return this.categories;
    },

    fillSelect(selectElement, selectedId = null) {
        if (!selectElement) return;
        
        selectElement.innerHTML = '<option value="">Selecione uma categoria</option>';
        
        if (this.categories.length === 0) {
            selectElement.innerHTML = '<option value="">Nenhuma categoria disponível</option>';
            return;
        }

        this.categories.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id;
            option.textContent = categoria.nome;
            if (selectedId && categoria.id == selectedId) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
    }
};

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    // Carrega categorias e produtos ao iniciar
    loadCategoriesAndProducts();

    // Configura o filtro
    document.getElementById('btnFilter').addEventListener('click', loadProducts);

    // Configura o botão Novo
    const btnNovo = document.getElementById('btnNovo');
    const modalAdd = new bootstrap.Modal(document.getElementById('modalAdd'));

    btnNovo.addEventListener('click', function () {
        prepareModal(modalAdd);
    });

    // Preview de imagens antes do upload (modal add)
    document.getElementById('img-input-modal').addEventListener('change', function (e) {
        const preview = document.getElementById('img-preview-modal');
        preview.innerHTML = '';

        if (this.files) {
            Array.from(this.files).forEach(file => {
                if (!file.type.match('image.*')) return;

                const reader = new FileReader();

                reader.onload = function (event) {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'position-relative d-inline-block';
                    imgContainer.innerHTML = `
                        <img src="${event.target.result}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-new-img position-absolute top-0 end-0 m-1">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.appendChild(imgContainer);

                    // Adiciona evento para remover imagem do preview
                    imgContainer.querySelector('.btn-remove-new-img').addEventListener('click', function() {
                        imgContainer.remove();
                    });
                }

                reader.readAsDataURL(file);
            });
        }
    });

    // Submit do formulário de adição
    document.getElementById('form-add').addEventListener('submit', async function (e) {
        e.preventDefault();

        try {
            const formData = new FormData(this);
            
            // Validação básica
            if (!formData.get('nome') || !formData.get('categoria')) {
                throw new Error('Preencha todos os campos obrigatórios');
            }

            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                modalAdd.hide();
                loadProducts();
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Produto adicionado com sucesso',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Ocorreu um erro ao adicionar o produto');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: error.message || 'Ocorreu um erro ao processar a requisição'
            });
        }
    });
});

// Carrega categorias e produtos
async function loadCategoriesAndProducts() {
    try {
        await categoryManager.load();
        loadProducts();
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
        loadProducts(); // Tenta carregar produtos mesmo com erro nas categorias
    }
}

// Prepara o modal de adição
async function prepareModal(modalInstance) {
    const form = document.getElementById('form-add');
    form.reset();
    document.getElementById('img-preview-modal').innerHTML = '';

    const selectCategoria = document.getElementById('selectCategoria');
    try {
        await categoryManager.ensureLoaded();
        categoryManager.fillSelect(selectCategoria);
    } catch (error) {
        selectCategoria.innerHTML = '<option value="">Erro ao carregar categorias</option>';
        console.error('Erro ao carregar categorias:', error);
    }

    modalInstance.show();
}

// Carrega a lista de produtos
async function loadProducts() {
    const filterName = document.getElementById('filterName').value;
    const filterStatus = document.getElementById('filterStatus').value;

    try {
        const response = await fetch(`/website/api/get_products.php?name=${encodeURIComponent(filterName)}&status=${filterStatus}`);
        const products = await response.json();

        const tbody = document.getElementById('productBody');
        tbody.innerHTML = '';

        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum produto encontrado</td></tr>';
            return;
        }

        products.forEach(product => {
            const row = document.createElement('tr');

            // Formata o preço
            let precoFormatado = '0,00';
            if (product.preco) {
                const precoNumerico = typeof product.preco === 'string' 
                    ? parseFloat(product.preco.replace('.', '').replace(',', '.')) 
                    : parseFloat(product.preco);
                precoFormatado = precoNumerico.toFixed(2).replace('.', ',');
            }

            row.innerHTML = `
                <td>
                    ${product.imagem ?
                    `<img src="/website/${product.imagem}" alt="${product.nome}" class="img-thumbnail" width="50">` :
                    '<i class="fas fa-box-open fa-lg text-muted"></i>'}
                </td>
                <td>${product.nome || 'Sem nome'}</td>
                <td class="text-center">${product.quantidade || 0}</td>
                <td class="text-center">
                    ${product.publicado ?
                    '<span class="badge bg-success">Publicado</span>' :
                    '<span class="badge bg-secondary">Não publicado</span>'}
                </td>
                <td class="text-end">${precoFormatado} €</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${product.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${product.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
        });

        // Adiciona eventos aos botões
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', editProduct);
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', deleteProduct);
        });

    } catch (error) {
        console.error('Error:', error);
        const tbody = document.getElementById('productBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar produtos</td></tr>';
    }
}

// Edita um produto
async function editProduct(e) {
    const productId = e.currentTarget.getAttribute('data-id');
    
    try {
        const modalElement = document.getElementById('modalEdit');
        const formEdit = document.getElementById('form-edit');
        
        if (!modalElement || !formEdit) {
            throw new Error('Elementos do modal não encontrados');
        }

        const modalEdit = new bootstrap.Modal(modalElement);
        
        // Busca dados do produto
        const productResponse = await fetch(`/website/api/get_details_product.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: productId })
        });

        const productData = await productResponse.json();
        
        if (!productData.success) {
            throw new Error(productData.message || 'Erro ao carregar produto');
        }

        const product = productData.data;

        // Preenche o formulário
        formEdit.querySelector('[name="id"]').value = product.id;
        formEdit.querySelector('[name="nome"]').value = product.nome || '';
        formEdit.querySelector('[name="quantidade"]').value = product.quantidade || 0;
        formEdit.querySelector('[name="preco"]').value = product.preco || 0.00;
        formEdit.querySelector('#chkPublicadoEdit').checked = product.publicado || false;
        
        // Preenche categoria
        const categoriaSelect = formEdit.querySelector('[name="categoria"]');
        await categoryManager.ensureLoaded();
        const selectedCategory = product.categorias && product.categorias.length > 0 ? product.categorias[0] : null;
        categoryManager.fillSelect(categoriaSelect, selectedCategory);
        
        // Carrega preview das imagens
        const imgPreview = document.getElementById('img-preview-edit');
        imgPreview.innerHTML = '';
        
        if (product.imagens?.length > 0) {
            product.imagens.forEach(imagem => {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'img-preview-item position-relative';
                imgContainer.innerHTML = `
                    <img src="../website/${imagem.caminho_imagem}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-img position-absolute top-0 end-0 m-1" 
                            data-img-id="${imagem.id}" title="Remover imagem">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                imgPreview.appendChild(imgContainer);
            });
        }
        
        // Configura evento de remoção de imagens
        imgPreview.querySelectorAll('.btn-remove-img').forEach(btn => {
            btn.addEventListener('click', async function() {
                const imgId = this.getAttribute('data-img-id');
                try {
                    const response = await fetch('/website/api/remove_product_image.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            image_id: imgId,
                            product_id: productId 
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.message || 'Erro ao remover imagem');
                    }
                    
                    this.parentElement.remove();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Imagem removida com sucesso',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: error.message || 'Falha ao remover imagem'
                    });
                }
            });
        });
        
        // Configura submit do formulário
        const submitHandler = async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                
                const response = await fetch('/website/api/update_product.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Erro ao atualizar produto');
                }
                
                modalEdit.hide();
                loadProducts();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Produto atualizado com sucesso',
                    timer: 1500,
                    showConfirmButton: false
                });
                
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: error.message || 'Falha ao atualizar produto'
                });
            }
        };
        
        formEdit.removeEventListener('submit', submitHandler);
        formEdit.addEventListener('submit', submitHandler);
        
        modalEdit.show();
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Falha ao carregar dados do produto'
        });
    }
}

// Exclui um produto
async function deleteProduct(e) {
    const productId = e.currentTarget.getAttribute('data-id');

    try {
        const result = await Swal.fire({
            title: 'Tem certeza?',
            text: "Você não poderá reverter isso!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            const response = await fetch(`/website/api/delete_product.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: productId })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Erro ao excluir produto');
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Excluído!',
                text: 'O produto foi excluído.',
                timer: 1500,
                showConfirmButton: false
            });
            
            loadProducts();
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Ocorreu um erro ao excluir o produto'
        });
    }
}