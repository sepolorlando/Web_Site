// Variável global para armazenar categorias
let categorias = [];

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

    // Preview de imagens antes do upload
    document.getElementById('img-input-modal').addEventListener('change', function (e) {
        const preview = document.getElementById('img-preview-modal');
        preview.innerHTML = '';

        if (this.files) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();

                reader.onload = function (event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.classList.add('img-thumbnail');
                    img.style.maxHeight = '100px';
                    preview.appendChild(img);
                }

                reader.readAsDataURL(file);
            });
        }
    });

    // Submit do formulário
    document.getElementById('form-add').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalAdd.hide();
                    loadProducts();
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Produto adicionado com sucesso'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message || 'Ocorreu um erro ao adicionar o produto'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Ocorreu um erro ao processar a requisição'
                });
            });
    });
});

async function loadCategoriesAndProducts() {
    try {
        // Carrega categorias
        const categoriesResponse = await fetch('/api/get_categorias2.php');
        const categoriesData = await categoriesResponse.json();

        if (categoriesData.success) {
            categorias = categoriesData.categories;
            console.log('Categorias carregadas:', categorias);
        } else {
            console.error('Erro ao carregar categorias:', categoriesData.error);
        }

        // Carrega produtos
        loadProducts();
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
        loadProducts(); // Tenta carregar produtos mesmo com erro
    }
}

function prepareModal(modalInstance) {
    // Limpa o formulário
    const form = document.getElementById('form-add');
    form.reset();
    document.getElementById('img-preview-modal').innerHTML = '';

    // Preenche o select de categorias
    const selectCategoria = document.getElementById('selectCategoria');
    selectCategoria.innerHTML = '<option value="">Selecione uma categoria</option>';

    if (categorias && categorias.length > 0) {
        categorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id;
            option.textContent = categoria.nome;
            selectCategoria.appendChild(option);
        });
    } else {
        selectCategoria.innerHTML = '<option value="">Nenhuma categoria disponível</option>';
        console.warn('Nenhuma categoria foi carregada');

        // Tenta recarregar categorias se estiverem vazias
        fetch('/api/get_categorias.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    categorias = data.categories;
                    // Atualiza o select novamente
                    data.categories.forEach(categoria => {
                        const option = document.createElement('option');
                        option.value = categoria.id;
                        option.textContent = categoria.nome;
                        selectCategoria.appendChild(option);
                    });
                }
            });
    }

    // Mostra o modal
    modalInstance.show();
}

function loadProducts() {
    const filterName = document.getElementById('filterName').value;
    const filterStatus = document.getElementById('filterStatus').value;

    fetch(`/api/get_products.php?name=${encodeURIComponent(filterName)}&status=${filterStatus}`)
        .then(response => response.json())
        .then(products => {
            const tbody = document.getElementById('productBody');
            tbody.innerHTML = '';

            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum produto encontrado</td></tr>';
                return;
            }

            products.forEach(product => {
                const row = document.createElement('tr');

                // Converte preço de "990,00" para 990.00
                const precoNumerico = parseFloat(product.preco.replace('.', '').replace(',', '.'));

                row.innerHTML = `
                    <td>
                        ${product.imagem ?
                        `<img src="${product.imagem}" alt="${product.nome}" class="img-thumbnail" width="50">` :
                        '<i class="fas fa-box-open fa-lg text-muted"></i>'}
                    </td>
                    <td>${product.nome}</td>
                    <td class="text-center">${product.quantidade}</td>
                    <td class="text-center">
                        <span class="badge bg-success">
                            Publicado
                        </span>
                    </td>
                    <td class="text-end">€ ${precoNumerico.toFixed(2)}</td>
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
        })
        .catch(error => {
            console.error('Error:', error);
            const tbody = document.getElementById('productBody');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar produtos</td></tr>';
        });
}
async function editProduct(e) {
    const productId = e.currentTarget.getAttribute('data-id');
    
    try {
        // 1. Verificar se os elementos existem
        const modalElement = document.getElementById('modalEdit');
        const formEdit = document.getElementById('form-edit');
        
        if (!modalElement || !formEdit) {
            throw new Error('Elementos do modal não encontrados');
        }

        // 2. Inicializar o modal
        const modalEdit = new bootstrap.Modal(modalElement);
        
        // 3. Buscar dados do produto (usando POST conforme sua API)
        const productResponse = await fetch(`/api/get_details_product.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: productId })
        });

        const productData = await productResponse.json();
console.log(productData)
        if (!productData.success) {
            throw new Error(productData.message || 'Erro ao carregar produto');
        }

        const product = productData.data;

        // 4. Preencher o formulário
        formEdit.querySelector('[name="id"]').value = product.id;
        formEdit.querySelector('[name="nome"]').value = product.nome;
        // formEdit.querySelector('[name="quantidade"]').value = product.quantidade;
        //formEdit.querySelector('[name="preco"]').value = product.preco;
        
        // Preencher categorias
        const categoriaSelect = formEdit.querySelector('[name="categorias[]"]');
        if (categoriaSelect) {
            Array.from(categoriaSelect.options).forEach(option => {
                option.selected = product.categorias?.includes(parseInt(option.value)) || false;
            });
        }
        
        // 5. Carregar preview das imagens
        const imgPreview = document.getElementById('img-preview-edit');
        imgPreview.innerHTML = '';
        
        if (product.imagens?.length > 0) {
            product.imagens.forEach(imagem => {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'img-preview-item position-relative';
                imgContainer.innerHTML = `
                    <img src="${imagem.caminho}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-img position-absolute top-0 end-0 m-1" 
                            data-img-id="${imagem.id}" title="Remover imagem">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                imgPreview.appendChild(imgContainer);
            });
        }
        
        // 6. Configurar evento de remoção de imagens
        imgPreview.querySelectorAll('.btn-remove-img').forEach(btn => {
            btn.addEventListener('click', async function() {
                const imgId = this.getAttribute('data-img-id');
                try {
                    const response = await fetch('/api/remove_product_image.php', {
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
                    
                    // Remove visualmente a imagem
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
        
        // 7. Configurar submit do formulário
        const submitHandler = async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                
                const response = await fetch('/api/update_product.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Erro ao atualizar produto');
                }
                
                modalEdit.hide();
                formEdit.removeEventListener('submit', submitHandler); // Remove o listener
                loadProducts(); // Recarrega a lista de produtos
                
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
        
        // Remove listener antigo se existir e adiciona o novo
        formEdit.removeEventListener('submit', submitHandler);
        formEdit.addEventListener('submit', submitHandler);
        
        // 8. Mostrar o modal
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
function deleteProduct(e) {
    const productId = e.currentTarget.getAttribute('data-id');

    Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/delete_product.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: productId.toString() }) // Envia como string
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire('Excluído!', 'O produto foi excluído.', 'success');
                        loadProducts();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Erro!',
                        error.error || 'Ocorreu um erro ao excluir o produto.',
                        'error'
                    );
                });
        }
    });
}