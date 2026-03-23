const CART_COUNT_KEY = 'lsu_cart_count';
const CART_ITEMS_KEY = 'lsu_cart_items';

const cartCountSpan = document.getElementById('cartCount');
const cartContainer = document.getElementById('cartContainer');
const cartSummary = document.getElementById('cartSummary');
const discountInput = document.getElementById('discountCode');
const applyDiscountBtn = document.getElementById('applyDiscountBtn');
const emptyCartBtn = document.getElementById('emptyCartBtn');

let cartItems = JSON.parse(localStorage.getItem(CART_ITEMS_KEY)) || [];
let discountPercent = 0;

function updateCartCount() {
  const totalQuantity = cartItems.reduce((sum, item) => sum + item.quantity, 0);
  localStorage.setItem(CART_COUNT_KEY, totalQuantity);
  if (cartCountSpan) {
    cartCountSpan.textContent = `Cart (${totalQuantity})`;
  }
}

function saveCart() {
  localStorage.setItem(CART_ITEMS_KEY, JSON.stringify(cartItems));
  updateCartCount();
}

function formatPrice(priceStr) {
  return parseFloat(priceStr.replace(/[^0-9.]/g, '')) || 0;
}

function renderCart() {
  if (!cartContainer || !cartSummary) return;

  cartContainer.innerHTML = '';

  if (cartItems.length === 0) {
    cartContainer.innerHTML = `<p class="cart-empty">Your cart is empty.</p>`;
    cartSummary.innerHTML = '';
    updateCartCount();
    return;
  }

  cartItems.forEach((item, index) => {
    const price = formatPrice(item.newPrice);
    const subtotal = price * item.quantity;
    const sizeText = item.selectedSize ? `<div>Size: <strong>${item.selectedSize}</strong></div>` : '';
    const colorText = item.selectedColor ? `<div>Color: <strong>${item.selectedColor}</strong></div>` : '';

    const card = document.createElement('div');
    card.className = 'card';
    card.style.width = '260px';
    card.innerHTML = `
      <div class="tilt">
        <div class="img">
          <img src="${item.image}" alt="${item.title} - ${item.selectedColor || ''}">
        </div>
      </div>
      <div class="info">
        <h2 class="title">${item.title}</h2>
        ${sizeText}
        ${colorText}
        <div class="price">
	
          <span class="new"> ${item.newPrice}</span>
        </div>
        <div class="cart-controls">
          <label>Quantity:
            <input type="number" min="1" value="${item.quantity}" data-index="${index}" class="qty-input" />
          </label>
          <button class="btn-small btn-danger remove-btn" data-index="${index}">Remove</button>
        </div>
        <div><strong>Subtotal: $${subtotal.toFixed(2)}</strong></div>
      </div>
    `;
    cartContainer.appendChild(card);
  });

  renderSummary();
  attachEventListeners();
}

function renderSummary() {
  let totalPrice = cartItems.reduce((sum, item) => sum + (formatPrice(item.newPrice) * item.quantity), 0);
  const discountAmount = (totalPrice * discountPercent) / 100;
  const finalPrice = totalPrice - discountAmount;

  cartSummary.innerHTML = `
    <p><strong>Total:</strong> $${totalPrice.toFixed(2)}</p>
    <p><strong>Discount:</strong> ${discountPercent}% (-$${discountAmount.toFixed(2)})</p>
    <p><strong>Amount to Pay:</strong> $${finalPrice.toFixed(2)}</p>
  `;
}

function attachEventListeners() {
  document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', e => {
      const idx = parseInt(e.target.dataset.index, 10);
      let qty = parseInt(e.target.value, 10);
      if (isNaN(qty) || qty < 1) qty = 1;
      cartItems[idx].quantity = qty;
      saveCart();
      renderCart();
    });
  });

  document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      const idx = parseInt(e.target.dataset.index, 10);
      cartItems.splice(idx, 1);
      saveCart();
      renderCart();
    });
  });
}

applyDiscountBtn.addEventListener('click', () => {
  const val = discountInput.value.trim().replace('%', '');
  const num = parseInt(val, 10);
  if (isNaN(num) || num < 0 || num > 100) {
    alert("Please enter a valid discount percentage between 0 and 100.");
    return;
  }
  discountPercent = num;
  renderSummary();
  alert(`Discount of ${discountPercent}% applied.`);
});

emptyCartBtn.addEventListener('click', () => {
  if (confirm("Are you sure you want to empty your cart?")) {
    cartItems = [];
    discountPercent = 0;
    saveCart();
    renderCart();
  }
});

updateCartCount();
renderCart();
