document.addEventListener('DOMContentLoaded', () => {
    console.log("Script de registro carregado");
    const form = document.getElementById('registerForm');

    // Validação em tempo real
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('blur', () => validarCampo(input));
    });

    // Função para validar campos
    function validarCampo(campo) {
        const elementoErro = document.getElementById(`${campo.id}-error`);

        if (campo.required && !campo.value.trim()) {
            mostrarErro(elementoErro, 'Este campo é obrigatório');
            return false;
        }

        switch (campo.id) {
            case 'email':
                if (!/^\S+@\S+\.\S+$/.test(campo.value)) {
                    mostrarErro(elementoErro, 'Email inválido');
                    return false;
                }
                if (campo.value.length > 50) {
                    mostrarErro(elementoErro, 'Email não pode exceder 50 caracteres');
                    return false;
                }
                break;

            case 'username':
                if (campo.value.length < 3) {
                    mostrarErro(elementoErro, 'Username deve ter pelo menos 3 caracteres');
                    return false;
                }
                if (campo.value.length > 50) {
                    mostrarErro(elementoErro, 'Username não pode exceder 50 caracteres');
                    return false;
                }
                break;

            case 'palavra_passe':
                if (campo.value.length < 6) {
                    mostrarErro(elementoErro, 'Senha deve ter pelo menos 6 caracteres');
                    return false;
                }
                break;

            case 'perfil':
                if (!campo.value) {
                    mostrarErro(elementoErro, 'Selecione um perfil');
                    return false;
                }
                break;

            case 'nome':
                if (campo.value.length > 65535) {
                    mostrarErro(elementoErro, 'Nome muito longo');
                    return false;
                }
                break;
        }

        esconderErro(elementoErro);
        return true;
    }

    function mostrarErro(elemento, mensagem) {
        if (elemento) {
            elemento.textContent = mensagem;
            elemento.style.display = 'block';
        }
    }

    function esconderErro(elemento) {
        if (elemento) {
            elemento.textContent = '';
            elemento.style.display = 'none';
        }
    }

    // Evento de submit do formulário
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log("Formulário de registro submetido");

        let formularioValido = true;
        form.querySelectorAll('input, select').forEach(input => {
            if (!validarCampo(input)) formularioValido = false;
        });

        if (formularioValido) {
            const formData = new FormData(form);
            const botaoSubmit = form.querySelector('button[type="submit"]');

            botaoSubmit.disabled = true;
            botaoSubmit.textContent = 'Registrando...';

            try {
                const response = await fetch('../api/create_user.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message, 'Sucesso');
                    window.location.href = 'login.php';
                } else {
                    showAlert('error', (data.message || 'Erro desconhecido'), 'Erro');
                }
            } catch (error) {
                showAlert('error', 'Erro ao conectar com o servidor', 'Erro');
                console.error('Erro:', error);
            } finally {
                botaoSubmit.disabled = false;
                botaoSubmit.textContent = 'Registrar';
            }
        }
    });
});
