// ============== FUNÇÕES UTILITÁRIAS ==============

// Mostrar popup de carrinho vazio
function showEmptyCartPopup() {
  const popup = document.createElement('div');
  popup.id = 'emptyCartPopup';
  popup.className = 'popup-overlay';
  popup.style.display = 'flex';
  
  popup.innerHTML = `
    <div class="popup-container">
      <div class="popup-content">
        <h3>Carrinho Vazio</h3>
        <p>Seu carrinho está vazio. Adicione produtos antes de finalizar sua compra.</p>
        <div class="popup-buttons">
          <a href="index.php" class="btn btn-continue">Continuar comprando</a>
          <button class="btn btn-close-popup">Fechar</button>
        </div>
      </div>
    </div>
  `;
  
  document.body.appendChild(popup);
  
  // Fechar popup
  popup.addEventListener('click', function(e) {
    if (e.target === popup || e.target.classList.contains('btn-close-popup')) {
      popup.remove();
    }
  });
}

// Verificar carrinho vazio
function verificarCarrinhoVazio() {
  const carrinho = JSON.parse(localStorage.getItem('carrinho')) || {};
  return Object.keys(carrinho).length === 0;
}

// Calcular idade a partir da data de nascimento
function calcularIdade(dataNascimento) {
  if (!dataNascimento) return null;
  
  const nascimento = new Date(dataNascimento);
  const hoje = new Date();
  
  let idade = hoje.getFullYear() - nascimento.getFullYear();
  const mesAtual = hoje.getMonth();
  const mesNascimento = nascimento.getMonth();
  
  if (mesAtual < mesNascimento || 
      (mesAtual === mesNascimento && hoje.getDate() < nascimento.getDate())) {
    idade--;
  }
  
  return idade;
}

// ============== EVENT LISTENERS ==============

// Validação da idade no formulário principal
document.getElementById('data_nascimento')?.addEventListener('change', function() {
  const idade = calcularIdade(this.value);
  document.getElementById('idade').value = idade || '';
});

// Validação da idade no modal
document.getElementById('data_nascimento')?.addEventListener('change', function() {
  const idade = calcularIdade(this.value);
  document.getElementById('idade').value = idade || '';
});

// Validação manual da idade
document.getElementById('idade')?.addEventListener('change', function() {
  if (this.value < 18) {
    alert('É necessário ter pelo menos 18 anos para finalizar a compra.');
    this.focus();
  }
});

// Formulário principal de checkout
document.getElementById('checkout-form')?.addEventListener('submit', function(e) {
  e.preventDefault();
  
  // Verificar carrinho vazio
  if (verificarCarrinhoVazio()) {
    showEmptyCartPopup();
    return;
  }

  // Validar idade
  const idade = parseInt(document.getElementById('idade').value);
  if (isNaN(idade) || idade < 18) {
    alert('É necessário ter pelo menos 18 anos.');
    return;
  }

  // Preparar dados
  const carrinho = JSON.parse(localStorage.getItem('carrinho')) || {};
  const formData = new FormData(this);
  formData.append('carrinho', JSON.stringify(carrinho));
  
  // Feedback visual
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = 'Processando...';

  // Enviar dados
  fetch(this.action, {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) throw new Error('Erro na rede');
    return response.json();
  })
  .then(data => {
    if (data.success) {
      localStorage.removeItem('carrinho');
      document.getElementById('mensagem-sucesso').textContent = '✅ Encomenda #' + (data.encomenda_id || '') + ' realizada com sucesso!';
      document.getElementById('mensagem-sucesso').style.display = 'block';
      this.reset();
      document.getElementById('idade').value = '';
      
      window.location.href = 'index.php';
      
    } else {
      throw new Error(data.message || 'Erro ao finalizar encomenda');
    }
  })
  .catch(error => {
    console.error('Erro:', error);
    alert(error.message);
  })
  .finally(() => {
    submitBtn.disabled = false;
    submitBtn.textContent = originalBtnText;
  });
});

// Formulário modal de checkout
document.getElementById('checkoutModalForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  
  if (verificarCarrinhoVazio()) {
    showEmptyCartPopup();
    document.getElementById('checkoutModal').style.display = 'none';
    return;
  }

  // Restante da lógica do modal...
  // (Manter similar ao formulário principal)
});

// Verificar carrinho ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
  if (verificarCarrinhoVazio() && window.location.pathname.includes('carrinho.php')) {
    setTimeout(showEmptyCartPopup, 500);
  }
  
  // Atualizar campo oculto do carrinho
  const carrinhoField = document.getElementById('carrinhoField');
  if (carrinhoField) {
    carrinhoField.value = localStorage.getItem('carrinho') || '{}';
  }
});