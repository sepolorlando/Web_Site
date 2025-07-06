// ============== UTILITIES ==============
class Utils {
  static formatCurrency(value) {
    return new Intl.NumberFormat('pt-CV', {
      style: 'currency',
      currency: 'CVE'
    }).format(value);
  }

  static parseCurrency(value) {
    return parseFloat(value.replace(/[^\d,-]/g, '').replace(',', '.'));
  }

  static showAlert(message, type = 'error') {
    // Implementar um sistema de notificação bonito
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${type}`;
    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
      alertDiv.remove();
    }, 3000);
  }
}

// ============== CART MANAGER ==============
class CartManager {
  constructor() {
    this.cart = this.loadCart();
    this.reservedStock = this.loadReservedStock();
  }

  loadCart() {
    return JSON.parse(localStorage.getItem('cart')) || {};
  }

  loadReservedStock() {
    return JSON.parse(localStorage.getItem('reservedStock')) || {};
  }

  saveCart() {
    localStorage.setItem('cart', JSON.stringify(this.cart));
    localStorage.setItem('reservedStock', JSON.stringify(this.reservedStock));
  }

  addItem(productId, quantity = 1, availableStock) {
    const reserved = this.reservedStock[productId] || 0;
    const available = availableStock - reserved;

    if (available < quantity) {
      throw new Error('Quantidade em estoque insuficiente');
    }

    this.cart[productId] = (this.cart[productId] || 0) + quantity;
    this.reservedStock[productId] = (this.reservedStock[productId] || 0) + quantity;
    this.saveCart();

    return this.cart[productId];
  }

  updateItem(productId, quantity, availableStock) {
    const currentQty = this.cart[productId] || 0;
    const difference = quantity - currentQty;

    if (difference > 0) {
      const reserved = this.reservedStock[productId] || 0;
      const available = availableStock - reserved;

      if (available < difference) {
        throw new Error('Quantidade em estoque insuficiente');
      }
    }

    if (quantity <= 0) {
      delete this.cart[productId];
      this.reservedStock[productId] = (this.reservedStock[productId] || 0) - currentQty;
    } else {
      this.cart[productId] = quantity;
      this.reservedStock[productId] = (this.reservedStock[productId] || 0) + difference;
    }

    this.saveCart();
  }

  removeItem(productId) {
    const currentQty = this.cart[productId] || 0;
    delete this.cart[productId];
    this.reservedStock[productId] = (this.reservedStock[productId] || 0) - currentQty;
    this.saveCart();
  }

  getTotalItems() {
    return Object.values(this.cart).reduce((sum, qty) => sum + qty, 0);
  }

  clearCart() {
    this.cart = {};
    this.reservedStock = {};
    this.saveCart();
  }

  getReservedStockForProduct(productId) {
    return this.reservedStock[productId] || 0;
  }
}

// ============== PRODUCT SERVICE ==============
class ProductService {
  static async fetchCategories() {
    try {
      const response = await fetch('../website/api/get_categorias.php');
      if (!response.ok) throw new Error('Network response was not ok');
      return await response.json();
    } catch (error) {
      console.error('Error fetching categories:', error);
      Utils.showAlert('Não foi possível carregar as categorias');
      return [];
    }
  }

  static async fetchProducts(categoryId = 0) {
    try {
      const response = await fetch(`../website/api/get_products.php?categoria_id=${categoryId}`);
      if (!response.ok) throw new Error('Network response was not ok');
      return await response.json();
    } catch (error) {
      console.error('Error fetching products:', error);
      Swal.fire(
        'Erro!',
        error.error || 'Não foi possível carregar os produtos.',
        'error'
      );
      return [];
    }
  }

  static async confirmOrder(orderData) {
    try {
      const response = await fetch('../website/api/finalizar_encomenda.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
      });

      if (!response.ok) throw new Error('Network response was not ok');
      const data = await response.json();

      if (!data.success) {
        Swal.fire(
          'Erro!',
          data.message || 'Falha ao confirmar',
          'warning'
        );
        throw new Error(data.message || 'Failed to confirm order');
      }

      return data;
    } catch (error) {
      console.error('Error confirming order:', error);
      throw error;
    }
  }
}
// ============== PRODUCT UI ==============
class ProductUI {
  constructor() {
    this.cartManager = new CartManager();
    this.init();
  }

  init() {
    this.loadCategories();
    this.loadProducts();
    this.updateCartCounter();
    this.setupCartIcon();
  }

  async loadCategories() {
    const categories = await ProductService.fetchCategories();
    const list = document.querySelector('.sidebar ul');

    list.innerHTML = `<li><a href="#" data-id="0" class="active">Todos os produtos</a></li>`;

    categories.forEach(category => {
      const li = document.createElement('li');
      li.innerHTML = `<a href="#" data-id="${category.id}">${category.nome} <span>(${category.total})</span></a>`;
      list.appendChild(li);
    });

    this.setupCategoryEvents();
  }

  setupCategoryEvents() {
    document.querySelectorAll('.sidebar a').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
        link.classList.add('active');
        this.loadProducts(link.dataset.id);
      });
    });
  }

  async loadProducts(categoryId = 0) {
    const products = await ProductService.fetchProducts(categoryId);
    const grid = document.querySelector('.products-grid');
    grid.innerHTML = '';

    const fragment = document.createDocumentFragment();

    products.forEach(product => {
      const card = this.createProductCard(product);
      fragment.appendChild(card);
    });

    grid.appendChild(fragment);
    this.setupAddToCartEvents();
  }

  createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'card';

    const reserved = this.cartManager.getReservedStockForProduct(product.id);
    const available = product.quantidade - reserved;

    const actionHTML = available > 0 ?
      `<button class="add-btn" data-id="${product.id}" aria-label="Adicionar ${product.nome} ao carrinho">
        Adicionar
      </button>` :
      `<span class="badge badge-warning">Sem estoque</span>`;

    card.innerHTML = `
      <img src="${product.imagem}" alt="${product.nome}" loading="lazy">
      <div class="card-body">
        <h4>${product.nome}</h4>
        <div class="card-footer">
          <span class="price">${Utils.formatCurrency(Utils.parseCurrency(product.preco))}</span>
          <span class="stock" data-id="${product.id}">Stock - ${available}</span>
          ${actionHTML}
        </div>
      </div>
    `;

    return card;
  }

  setupAddToCartEvents() {
    document.querySelectorAll('.add-btn').forEach(button => {
      button.addEventListener('click', async () => {
        const productId = button.dataset.id;

        if (button.disabled) return;
        button.disabled = true;

        try {
          await this.addProductToCart(productId);
          this.updateButtonFeedback(button);
        } catch (error) {
          console.error('Error adding to cart:', error);
          Utils.showAlert(error.message);
          button.disabled = false;
        }
      });
    });
  }

  async addProductToCart(productId) {
    const stockElement = document.querySelector(`.stock[data-id="${productId}"]`);
    const currentAvailable = parseInt(stockElement.textContent.replace('Stock - ', ''));

    if (currentAvailable <= 0) {
      throw new Error('Produto sem estoque disponível');
    }

    this.cartManager.addItem(productId, 1, currentAvailable + this.cartManager.getReservedStockForProduct(productId));

    const newAvailable = currentAvailable - 1;
    stockElement.textContent = `Stock - ${newAvailable}`;

    if (newAvailable === 0) {
      const addBtn = document.querySelector(`.add-btn[data-id="${productId}"]`);
      if (addBtn) {
        addBtn.outerHTML = '<span class="badge badge-warning">Sem estoque</span>';
      }
    }

    this.updateCartCounter();
  }

  updateButtonFeedback(button) {
    button.style.backgroundColor = '#d1d5db';
    button.textContent = 'Adicionado!';

    setTimeout(() => {
      button.disabled = false;
      button.textContent = 'Adicionar';
      button.style.backgroundColor = '';
    }, 1000);
  }

  updateCartCounter() {
    const badge = document.querySelector('.cart-count');
    if (badge) {
      const totalItems = this.cartManager.getTotalItems();
      badge.textContent = totalItems;
      badge.style.display = totalItems > 0 ? 'flex' : 'none';
    }
  }

  setupCartIcon() {
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
      cartIcon.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = '../website/carrinho.php';
      });
    }
  }
}

// ============== CART UI ==============
class CartUI {
  constructor() {
    this.cartManager = new CartManager();
    this.init();
  }

  init() {
    this.renderCart();
    this.setupCheckoutModal();
  }

  async renderCart() {
    const list = document.querySelector('.cart-list');
    const subtotalEl = document.querySelector('.subtotal');
    const totalEl = document.querySelector('.total');
    const cart = this.cartManager.cart;

    if (!Object.keys(cart).length) {
      list.innerHTML = '<p>Carrinho vazio.</p>';
      subtotalEl.textContent = Utils.formatCurrency(0);
      totalEl.textContent = Utils.formatCurrency(0);
      this.updateCartCounter();
      return;
    }

    try {
      const products = await ProductService.fetchProducts();
      list.innerHTML = '';
      let subtotal = 0;

      Object.keys(cart).forEach(id => {
        const product = products.find(p => p.id == id);
        if (!product) return;

        const quantity = cart[id];
        const price = Utils.parseCurrency(product.preco);
        const productTotal = price * quantity;
        subtotal += productTotal;

        const li = document.createElement('li');
        li.classList.add('cart-item');
        li.innerHTML = `
          <img src="../website/${product.imagem}" alt="${product.nome}" loading="lazy">
          <div class="info">
            <strong>${product.nome}</strong>
            <p>${product.descricao ?? ''}</p>
            <a href="#" class="remover" data-id="${product.id}">Remover</a>
          </div>
          <div class="quantidade">
            <button class="menos" data-id="${product.id}" aria-label="Reduzir quantidade">-</button>
            <span>${quantity}</span>
            <button class="mais" data-id="${product.id}" aria-label="Aumentar quantidade">+</button>
          </div>
          <div class="preco">${Utils.formatCurrency(productTotal)}</div>
        `;
        list.appendChild(li);
      });

      subtotalEl.textContent = Utils.formatCurrency(subtotal);
      totalEl.textContent = Utils.formatCurrency(subtotal);
      this.setupCartEvents();
      this.updateCartCounter();
    } catch (error) {
      console.error('Error rendering cart:', error);
      Utils.showAlert('Não foi possível carregar o carrinho');
    }
  }

  setupCartEvents() {
    document.querySelectorAll('.mais').forEach(btn => {
      btn.addEventListener('click', async () => {
        const productId = btn.dataset.id;
        try {
          const products = await ProductService.fetchProducts();
          const product = products.find(p => p.id == productId);
          if (!product) throw new Error('Produto não encontrado');

          const currentQty = this.cartManager.cart[productId] || 0;
          const reserved = this.cartManager.reservedStock[productId] || 0;
          const available = product.quantidade - reserved;

          if (available <= 0) {
            throw new Error('Quantidade em estoque insuficiente');
          }

          this.cartManager.updateItem(productId, currentQty + 1, product.quantidade);
          this.renderCart();
        } catch (error) {
          Utils.showAlert(error.message);
        }
      });
    });

    document.querySelectorAll('.menos').forEach(btn => {
      btn.addEventListener('click', () => {
        const productId = btn.dataset.id;
        const currentQty = this.cartManager.cart[productId] || 0;

        if (currentQty <= 1) {
          this.cartManager.removeItem(productId);
        } else {
          this.cartManager.updateItem(productId, currentQty - 1);
        }

        this.renderCart();
      });
    });

    document.querySelectorAll('.remover').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        this.cartManager.removeItem(link.dataset.id);
        this.renderCart();
      });
    });
  }

  updateCartCounter() {
    const badge = document.querySelector('.cart-count');
    if (badge) {
      const totalItems = this.cartManager.getTotalItems();
      badge.textContent = totalItems;
      badge.style.display = totalItems > 0 ? 'flex' : 'none';

      // Atualizar também o contador na página de produtos se existir
      const productPageBadge = document.querySelector('.products-page .cart-count');
      if (productPageBadge) {
        productPageBadge.textContent = totalItems;
        productPageBadge.style.display = totalItems > 0 ? 'flex' : 'none';
      }
    }
  }

  setupCheckoutModal() {
    const btnOpen = document.getElementById('btnOpenCheckout');
    const modal = document.getElementById('checkoutModal');
    const btnClose = modal?.querySelector('.modal-close');
    const form = document.getElementById('checkoutModalForm');

    if (!btnOpen || !modal) return;

    btnOpen.addEventListener('click', () => {
      if (!this.cartManager.getTotalItems()) {
        return Utils.showAlert('Seu carrinho está vazio!');
      }
      modal.style.display = 'flex';
    });

    btnClose?.addEventListener('click', () => modal.style.display = 'none');
    modal?.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });

    form?.addEventListener('submit', async e => {
      e.preventDefault();

      const formData = new FormData(form);
      const orderData = {
        customer: Object.fromEntries(formData.entries()),
        products: this.cartManager.cart,
        reservedStock: this.cartManager.reservedStock
      };

      try {
        const result = await ProductService.confirmOrder(orderData);

        if (result.success) {
          this.cartManager.clearCart();
          modal.style.display = 'none';
          this.renderCart();
          Swal.fire(`Encomenda #${result.encomenda_id} criada com sucesso!`, 'success');
        
          window.location.href = '/';
        } else {
          throw new Error(result.message || 'Erro ao processar encomenda');
        }
      } catch (error) {
        console.error('Checkout error:', error);
        Utils.showAlert('Erro ao criar encomenda: ' + error.message);

        if (error.message.includes('estoque')) {
          this.renderCart();
        }
      }
    });
  }
}

// ============== INITIALIZATION ==============
document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('.products-grid')) {
    new ProductUI();
  }

  if (document.querySelector('.cart-list')) {
    new CartUI();
  }
});