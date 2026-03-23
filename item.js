// Local storage keys
const CART_COUNT_KEY = 'lsu_cart_count';
const CART_ITEMS_KEY = 'lsu_cart_items';

// Cart count display
let cartCount = parseInt(localStorage.getItem(CART_COUNT_KEY), 10);
if (isNaN(cartCount)) cartCount = 0;
const cartCountSpan = document.getElementById('cartCount');
function updateCartCountDisplay() {
  if (cartCountSpan) cartCountSpan.textContent = `Cart (${cartCount})`;
}
updateCartCountDisplay();

// Load selected product
const product = JSON.parse(localStorage.getItem('lsu_selected_product'));

if (!product) {
  alert("No product selected.");
  window.location.href = 'products.html';
} else {
  // DOM elements for product details
  const productImage = document.getElementById('product-main-image');
  document.getElementById('product-title').textContent = product.title;
  document.getElementById('product-desc').textContent = product.desc;
  document.getElementById('product-price-new').textContent = product.newPrice;
  document.getElementById('product-price-old').textContent = product.oldPrice;
  document.getElementById('product-stock').textContent = product.stock;

  // Size options
  const sizeSection = document.getElementById('size-section');
  const sizeOptions = document.getElementById('size-options');
  if (product.sizes && product.sizes.length > 0) {
    sizeSection.style.display = "block";
    sizeOptions.innerHTML = "";
    product.sizes.forEach((size, i) => {
      const id = `size-${size.toLowerCase()}`;
      sizeOptions.insertAdjacentHTML('beforeend', `
        <input type="radio" name="size" id="${id}" value="${size}" ${i === 0 ? "checked" : ""}>
        <label for="${id}" class="size">${size}</label>
      `);
    });
  }

  // Color options, with image swap
  const colorSection = document.getElementById('color-section');
  const colorOptions = document.getElementById('color-options');
  if (product.colors && product.colors.length > 0) {
    colorSection.style.display = "block";
    colorOptions.innerHTML = "";
    product.colors.forEach((color, i) => {
      const id = `color-${color.toLowerCase()}`;
      colorOptions.insertAdjacentHTML('beforeend', `
        <input type="radio" name="color" id="${id}" value="${color}" ${i === 0 ? "checked" : ""} class="color-input">
        <label for="${id}" class="color" style="background-color: ${color.toLowerCase()}"></label>
      `);
    });

    // Initial image for first-selected color
    const selectedColor = document.querySelector('input[name="color"]:checked')?.value;
    if (selectedColor && product.colorImages && product.colorImages[selectedColor]) {
      productImage.src = product.colorImages[selectedColor];
    } else {
      productImage.src = product.image;
    }

    // Update image on color selection
    document.querySelectorAll('input[name="color"]').forEach(radio => {
      radio.addEventListener('change', e => {
        const chosenColor = e.target.value;
        if (product.colorImages && product.colorImages[chosenColor]) {
          productImage.src = product.colorImages[chosenColor];
        } else {
          productImage.src = product.image;
        }
      });
    });
  } else {
    productImage.src = product.image;
  }

  // Add to Cart handler
  const addToCartBtn = document.querySelector('.add-cart');
  addToCartBtn.addEventListener('click', () => {
    if (product.stock.toLowerCase().includes('stock out')) {
      alert('This item is out of stock and cannot be added to the cart.');
      return;
    }

    // Get selected options
    const selectedSize = document.querySelector('input[name="size"]:checked')?.value || null;
    const selectedColor = document.querySelector('input[name="color"]:checked')?.value || null;
    let items = JSON.parse(localStorage.getItem(CART_ITEMS_KEY)) || [];

    // Find existing cart item (same product, size, color)
    const existingIndex = items.findIndex(item =>
      item.title === product.title &&
      item.selectedSize === selectedSize &&
      item.selectedColor === selectedColor);

    // Determine correct image for this color
    const imageForCart = product.colorImages && product.colorImages[selectedColor]
      ? product.colorImages[selectedColor]
      : product.image;

    if (existingIndex > -1) {
      items[existingIndex].quantity++;
    } else {
      items.push({
        ...product,
        quantity: 1,
        selectedSize,
        selectedColor,
        image: imageForCart // This makes cart.js show the right image!
      });
    }
    localStorage.setItem(CART_ITEMS_KEY, JSON.stringify(items));

    cartCount++;
    localStorage.setItem(CART_COUNT_KEY, String(cartCount));
    updateCartCountDisplay();

    alert("Added to cart.");
  });
}
