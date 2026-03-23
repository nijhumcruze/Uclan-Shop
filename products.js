const CART_COUNT_KEY = 'lsu_cart_count';
const CART_ITEMS_KEY = 'lsu_cart_items';
const DETAIL_KEY = 'lsu_selected_product';

const products = [
  {
    id: 1,
    title: "Full zip Sweatshirt Unisex",
    image: "assets/BK_E-SHOP_WHITE_FULL-ZIP_FRONT.jpg",
    desc: "Cotton hoodie with a napped interior, adjustable hood and long sleeves, front pouch pocket, and ribbed trims.",
    feats: ["Cotton", "Adjustable", "Long sleeves"],
    oldPrice: "$24.99",
    newPrice: "$19.99",
    stock: "In Stock",
    reviews: "45 Reviews",
    sizes: ["S", "M", "L", "XL", "XXL"],
    colors: ["Black", "Orange"],
    colorImages: {
      "Black": "assets/BK_E-SHOP_WHITE_FULL-ZIP_FRONT.jpg",
      "Orange": "assets/BK_E-SHOP_YELLOW_FULL-ZIP_FRONT.jpg"
    }
  },
  {
    id: 2,
    title: "Travel Mug",
    image: "assets/Travel-Mug-Plastic-1.jpg",
    desc: "Insulated travel mug, double-walled, stainless steel outside and plastic inside, with lid and silicone O-ring.",
    feats: ["Mug", "Stainless", "Plastic"],
    oldPrice: "$35",
    newPrice: "$15.99",
    stock: "Stock Out",
    reviews: "15 Reviews",
    sizes: [],
    colors: []
  },
  {
    id: 3,
    title: "Backpack",
    image: "assets/Grey-Backpack.jpg",
    desc: "Backpack with horizontal and vertical zip compartments, main zip section, and padded pocket organiser.",
    feats: ["Horizontal pocket", "Zip", "Padded"],
    oldPrice: "$34.99",
    newPrice: "$29.99",
    stock: "In Stock",
    reviews: "5 Reviews",
    sizes: [],
    colors: ["lightgray", "darkgray", "navy"],
    colorImages: {
      "lightgray": "assets/Grey-Backpack.jpg",
      "darkgray": "assets/Black-Packpack.jpg",
      "navy": "assets/Blue-Backpack.jpg"
    }
  },
  {
    id: 4,
    title: "Sweatshirt Unisex",
    image: "assets/BK_FRONT-SIDE.jpg",
    desc: "Cotton hoodie with a napped interior. Adjustable hood and long sleeves. Front pouch pocket.",
    feats: ["Cotton", "napped", "Adjustable"],
    oldPrice: "$39.99",
    newPrice: "$25.99",
    stock: "In Stock",
    reviews: "120 Reviews",
    sizes: [],
    colors: ["cream", "grey"],
    colorImages: {
      "cream": "assets/BK_FRONT-SIDE.jpg",
      "grey": "assets/SL_E-SHOP-front-SIDE.jpg"
    }
  },
  {
    id: 5,
    title: "Pocket Umbrella",
    image: "assets/Pocket-Umbrella_open.jpg",
    desc: "Pocket umbrella.Features UCLan Cyprus logo in white colour.",
    feats: ["Pocket","UCLan logo"],
    oldPrice: "$18.50",
    newPrice: "$12.99",
    stock: "In Stock",
    reviews: "30 Reviews",
    sizes: [],
    colors: ["Black"],
    colorImages: {
      "Black": "assets/Pocket-Umbrella_open.jpg",
      
    }
  }
];



// Update cart count display
let cartCount = parseInt(localStorage.getItem(CART_COUNT_KEY), 10);
if (isNaN(cartCount)) cartCount = 0;
const cartCountSpan = document.getElementById('cartCount');
function updateCartCountDisplay() {
  if (cartCountSpan) cartCountSpan.textContent = `Cart (${cartCount})`;
}
updateCartCountDisplay();

const filterSelect = document.getElementById('filterSelect');
const cardsContainer = document.getElementById('cards-container');

function renderProducts(filter = 'all') {
  if (!cardsContainer) return;
  cardsContainer.innerHTML = '';

  products.forEach(product => {
    let show = true;
    if (filter === 'in') show = product.stock.toLowerCase().includes('in stock');
    else if (filter === 'out') show = product.stock.toLowerCase().includes('stock out');
    if (!show) return;

    const card = document.createElement('div');
    card.className = 'card';

    card.innerHTML = `
      <div class="badge">${product.stock.toLowerCase().includes('in stock') ? 'HOT SALE' : ''}</div>
      <div class="tilt">
        <div class="img">
          <a href="item.html" data-id="${product.id}">
            <img src="${product.image}" alt="${product.title}">
          </a>
        </div>
      </div>
      <div class="info">
        <h2 class="title">${product.title}</h2>
        <p class="desc">${product.desc}</p>
        <div class="feats">
          ${product.feats.map(f => `<span class="feat">${f}</span>`).join('')}
        </div>
        <div class="bottom">
          <div class="price">
            <span class="old">${product.oldPrice}</span>
            <span class="new">${product.newPrice}</span>
          </div>
          <button class="btn" ${product.stock.toLowerCase().includes('stock out') ? "enabled" : ""}>
            <span>Add to Cart</span>
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 01-8 0"></path>
            </svg>
          </button>
        </div>
        <div class="meta">
          <div class="rating">
            ${'<svg width="16" height="16" viewBox="0 0 24 24" fill="#FFD700" stroke="#FFD700" stroke-width="0.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>'.repeat(5)}
            <span class="rcount">${product.reviews}</span>
          </div>
          <div class="stock">${product.stock}</div>
        </div>
      </div>
    `;

    const button = card.querySelector('button.btn');
    button?.addEventListener('click', () => {
      if (product.stock.toLowerCase().includes('stock out')) {
        alert('This item is out of stock and cannot be added to the cart.');
        return;
      }

      let items = JSON.parse(localStorage.getItem(CART_ITEMS_KEY)) || [];
      const existing = items.find(i => i.id === product.id);
      if (existing) {
        existing.quantity++;
      } else {
        items.push({...product, quantity: 1});
      }
      localStorage.setItem(CART_ITEMS_KEY, JSON.stringify(items));

      cartCount++;
      localStorage.setItem(CART_COUNT_KEY, String(cartCount));
      updateCartCountDisplay();

      alert("Added to cart.");  
    });

    // Store selected product for item page on image/title click
    const link = card.querySelector('a');
    link.addEventListener('click', () => {
      localStorage.setItem(DETAIL_KEY, JSON.stringify(product));
    });

    cardsContainer.appendChild(card);
  });
}

filterSelect?.addEventListener('change', e => {
  renderProducts(e.target.value);
});

renderProducts();

