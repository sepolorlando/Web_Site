<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Utilizadores</title>
    <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) ?>/../public/assets/css/management_user.css">
</head>
<body>
    <?php include '../includes/_barra_lateral.php'; ?>
    <?php include '../includes/header_backofice.php'; ?>
    
    <div class="container">
        <h1>Gestão de Utilizadores</h1>
        
        <div class="filters">
            <input type="text" id="search-input" placeholder="Filtrar por nome...">
            <select id="profile-filter">
                <option value="">Todos os perfis</option>
                <option value="admin">Administrador</option>
                <option value="cliente">Cliente</option>
            </select>
            <button id="add-user" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Utilizador
            </button>
            <button id="refresh-data" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i> Recarregar Dados
            </button>
        </div>
        
        <div id="loading" style="text-align: center; padding: 20px; display: none;">
            <i class="fas fa-spinner fa-spin"></i> Carregando utilizadores...
        </div>
        
        <div id="users-table-container">
            <table id="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Perfil</th>
                        <th>Username</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="users-data">
                    <!-- Dados serão carregados via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Editar/Adicionar Utilizador -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modalTitle">Adicionar Utilizador</h2>
            <form id="userForm">
                <input type="hidden" id="userId">
                
                <div class="form-group">
                    <label for="userProfile">Perfil</label>
                    <select id="userProfile" required>
                        <option value="">Selecione o perfil</option>
                        <option value="admin">Administrador</option>
                        <option value="cliente">Cliente</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="userUsername">Username</label>
                    <input type="text" id="userUsername" required>
                </div>
                
                <div class="form-group">
                    <label for="userName">Nome Completo</label>
                    <input type="text" id="userName" required>
                </div>
                
                <div class="form-group">
                    <label for="userEmail">Email</label>
                    <input type="email" id="userEmail" required>
                </div>
                
                <div class="form-group">
                    <label for="userPassword">Password</label>
                    <input type="password" id="userPassword">
                    <small id="passwordHelp">Deixe em branco para manter a password atual (ao editar)</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/website/public/assets/js/management_user.js"></script>
</body>
</html>