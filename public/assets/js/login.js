document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Autenticando...';

    try {
        const response = await fetch('../../website/api/authenticate.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (!data.success) {
            switch (data.code) {

                case 0: // User not found
                    showAlert('error', 'Usuário não encontrado. Por favor verifique suas credenciais.', 'Erro de Login');
                    document.getElementById('username').focus();
                    break;
                case 2: // Wrong password
                    showAlert('warning', 'Senha incorreta. Tente novamente.', 'Erro de Login');
                    document.getElementById('password').focus();
                    document.getElementById('password').value = '';
                    break;
                case 400: // Missing fields
                    showAlert('warning', 'Por favor preencha todos os campos.', 'Dados Incompletos');
                    break;
                case 500: // Server error
                    showAlert('error', 'Erro no servidor. Por favor tente mais tarde.', 'Erro do Sistema');
                    break;
                default:
                    showAlert('error', data.message || 'Erro desconhecido durante o login', 'Erro');
            }
        } else if (data.success) {
            // Successful login
            showAlert('success', 'Login realizado com sucesso! Redirecionando...', 'Bem-vindo');

            // Small delay before redirect to allow user to see the success message
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        }
    } catch (error) {
        showAlert('error', 'Falha na conexão com o servidor. Verifique sua internet.', 'Erro de Rede');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    }
});