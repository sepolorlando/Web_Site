<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuário</title>
    <link rel="stylesheet" href="../public/assets/css/manage_acount.css">
</head>
<body>
    <div class="register-container">
        <h2>Criar Conta</h2>
        <form id="registerForm">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required>
                <span class="error-message" id="nome-error"></span>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required minlength="3">
                <span class="error-message" id="username-error"></span>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <span class="error-message" id="email-error"></span>
            </div>
            
            <div class="form-group">
                <label for="palavra_passe">Senha</label>
                <input type="password" id="palavra_passe" name="palavra_passe" required minlength="6">
                <span class="error-message" id="palavra_passe-error"></span>
            </div>
            
            <div class="form-group">
                <label for="perfil">Tipo de Perfil</label>
                <select id="perfil" name="perfil" required>
                    <option value="">Selecione...</option>
                    <option value="cliente">Cliente</option>
                    <option value="admin">Administrador</option>
                </select>
                <span class="error-message" id="perfil-error"></span>
            </div>
            
            <button type="submit" class="submit-btn">Registrar</button>
        </form>
        
        <p class="login-link">Já tem conta? <a href="login.php">Faça login</a></p>
    </div>

    <script src="../public/assets/js/manage_acount.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/assets/js/alert.js"></script>

</body>
</html>