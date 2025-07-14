// Função para exibir alertas
function showAlert(type, message, title) {
    Swal.fire({
        icon: type,
        title: title,
        text: message,
        timer: 4000,
        showConfirmButton: type !== 'success'
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const usersTable = document.getElementById('users-table');
    const usersData = document.getElementById('users-data');
    const loading = document.getElementById('loading');
    const searchInput = document.getElementById('search-input');
    const profileFilter = document.getElementById('profile-filter');
    const addUserBtn = document.getElementById('add-user');
    const refreshBtn = document.getElementById('refresh-data');

    // Carrega os dados dos utilizadores
    function loadUsers() {
        loading.style.display = 'block';
        usersData.innerHTML = '';

        fetch('../api/get_all_users.php')  // Corrigido para get_all_users.php
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede');
                }
                return response.json();
            })
            .then(data => {
                loading.style.display = 'none';

                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${user.id}</td>
                            <td>${user.perfil}</td>
                            <td>${user.username}</td>
                            <td>${user.nome}</td>
                            <td>${user.email}</td>
                            <td>
                                <button class="action-btn edit-btn" data-id="${user.id}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" data-id="${user.id}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        usersData.appendChild(row);
                    });

                    addButtonEvents();
                } else {
                    usersData.innerHTML = '<tr><td colspan="6" class="no-results">Nenhum utilizador encontrado</td></tr>';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                console.error('Erro:', error);
                showAlert('error', 'Falha ao carregar utilizadores', 'Erro');
                usersData.innerHTML = '<tr><td colspan="6" class="error-message">Erro ao carregar dados</td></tr>';
            });
    }

    // Filtra os utilizadores
    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        const profileValue = profileFilter.value.toLowerCase();
        const rows = usersData.getElementsByTagName('tr');

        for (let row of rows) {
            if (row.cells.length > 0) { // Verifica se é uma linha de dados
                const name = row.cells[3]?.textContent?.toLowerCase() || '';
                const profile = row.cells[1]?.textContent?.toLowerCase() || '';
                
                const nameMatch = name.includes(searchTerm);
                const profileMatch = profileValue === '' || profile === profileValue;
                
                row.style.display = (nameMatch && profileMatch) ? '' : 'none';
            }
        }
    }

    // Adiciona eventos aos botões de ação
    function addButtonEvents() {
         document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                openEditModal(userId);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Esta ação não pode ser desfeita!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, eliminar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`../api/delete_user.php?id=${userId}`, {
                            method: 'DELETE'
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Erro na rede');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showAlert('success', data.message, 'Sucesso');
                                loadUsers();
                            } else {
                                showAlert('error', data.message, 'Erro');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            showAlert('error', 'Falha ao eliminar utilizador', 'Erro');
                        });
                    }
                });
            });
        });
    }

    // Event listeners
    searchInput.addEventListener('input', filterUsers);
    profileFilter.addEventListener('change', filterUsers);
    addUserBtn.addEventListener('click', () => {
        window.location.href = 'add_user.php';
    });
    refreshBtn.addEventListener('click', loadUsers);

    // Carrega os dados iniciais
    loadUsers();
    
});