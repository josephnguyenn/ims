const broadcast = new BroadcastChannel('pos-cart');

window.currentPaymentMethod = 'cash';
window.currentQrUrl        = null; 

document.addEventListener('DOMContentLoaded', () => {
  const productList    = document.getElementById('product-list');
  const cartTableBody  = document.querySelector('#cart-table tbody');
  const totalCZK       = document.getElementById('total-czk');
  const totalEUR       = document.getElementById('total-eur');
  const printStatus    = document.getElementById('print-status');
  window.cart      = {};
  window.autoPrint = false;
  window.EUR_RATE  = 25;
  

  // 1) Load exchange rate
  fetch(`${BASE_URL}/ims-dashboard/pos/api/get_exchange_rate.php`)
    .then(r => r.json())
    .then(data => { EUR_RATE = parseFloat(data.rate); updateCart(); })
    .catch(console.error);

  // 2) Wire up category tabs (click first on load)
  const tabs = document.querySelectorAll('.category-tab');
  tabs.forEach((btn, idx) => {
    btn.addEventListener('click', () => {
      tabs.forEach(t=>t.classList.remove('active'));
      btn.classList.add('active');
      loadProducts(btn.dataset.categoryId);
    });
    if (idx === 0) btn.click();
  });

  // Fetch + render products
  function loadProducts(categoryId) {
    productList.innerHTML = '<div class="loading">Đang tải sản phẩm…</div>';
    fetch(`${BASE_URL}/api/products?category_id=${categoryId}`, {
      headers: {
        'Authorization': `Bearer ${AUTH_TOKEN}`,
        'Accept':        'application/json'
      }
    })
    .then(r => r.json())
    .then(list => {
      renderProductList(list);
    })
    .catch(err => {
      console.error(err);
      productList.innerHTML = '<div class="error">Không tải được sản phẩm.</div>';
    });
  }

  // Build the cards
function renderProductList(list) {
  productList.innerHTML = '';
  list.forEach(p => {
    if ((p.actual_quantity ?? 0) <= 0) return;
    const card = document.createElement('div');
    card.className     = 'product-card';
    card.dataset.id    = p.id;
    card.dataset.code = p.code;
    card.dataset.name  = p.name;
    card.dataset.price = p.price;
    card.dataset.tax   = p.tax || 0; // ✅ Add tax here 
    card.dataset.maxQty = p.actual_quantity;    // ← dùng maxQty

    card.innerHTML = `
      <div class="product-name">${p.name}</div>
      <div class="product-price">${parseFloat(p.price).toFixed(2)} CZK</div>
      <div class="product-stock">
        <strong>In stock:</strong> ${p.actual_quantity}
      </div>
      <div class="product-shipment"><em>${p.shipment_id ? `Shipment #${p.shipment_id}` : ''}</em></div>
    `;
    productList.appendChild(card);
  });

  attachProductEvents();
  updateCardAvailability();
}


  // “Add to cart” on click
function attachProductEvents() {
  document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', () => {
      const id     = card.dataset.id;
      const name   = card.dataset.name;
      const code = card.dataset.code;
      const price  = parseFloat(card.dataset.price);
      const maxQty = parseInt(card.dataset.maxQty, 10) || 0;  // ← đọc đúng

      if (cart[id]) {
        if (cart[id].qty < maxQty) {
          cart[id].qty++;
        } else {
          alert('Đã đạt giới hạn tồn kho.');
        }
      } else {
        if (maxQty > 0) {
        const tax = parseFloat(card.dataset.tax) || 0;
        cart[id] = { name, price, qty: 1, maxQty, code, tax }; // FIXED: use `card`, not `p`
        } else {
          alert('Sản phẩm đã hết hàng.');
        }
      }
      updateCart();
    });
  });
}

function updateCardAvailability() {
  document.querySelectorAll('.product-card').forEach(card => {
    const id        = card.dataset.id;
    const maxQty    = parseInt(card.dataset.maxQty, 10) || 0;  // <-- đổi here
    const inCartQty = (cart[id]?.qty) || 0;
    if (inCartQty >= maxQty) {
      card.classList.add('disabled');
    } else {
      card.classList.remove('disabled');
    }
  });
}


  // Rebuild cart table & totals
function updateCart() {
  cartTableBody.innerHTML = '';
  let total = 0;

  for (let id in cart) {
    const { name, price, qty } = cart[id];
    const lineTotal = price * qty;
    total += lineTotal;
    cartTableBody.innerHTML += `
      <tr>
        <td>${name}</td>
        <td>${qty}</td>
        <td>${price.toFixed(2)} CZK</td>
        <td>${lineTotal.toFixed(2)} CZK</td>
        <td>
          <button class="remove-item" data-id="${id}">✖</button>
        </td>
      </tr>`;
  }

  const czkRounded = Math.round(total);
  const eurRounded = (total / EUR_RATE).toFixed(2);

  totalCZK.innerText = `${czkRounded} CZK`;
  totalEUR.innerText = `${eurRounded} EUR`;


  // attach removal handlers
  document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', () => {
      delete cart[btn.dataset.id];
      updateCart();     // recursively re-render & broadcast
    });
  });

  // update tied payment panel
  if (typeof updatePaymentDisplay === 'function') {
    updatePaymentDisplay();
  }

  // **broadcast to customer display**
  broadcast.postMessage({
    cart:      window.cart,
    subtotal_czk:   czkRounded,
    subtotal_eur:   eurRounded,
    payment_method: window.currentPaymentMethod,
    qr_url:         window.currentQrUrl,
  });
  updateCardAvailability();
}

window.updateCart = updateCart;



  // Quantity adjustments
function adjustLastQty(delta) {
  const keys = Object.keys(cart);
  if (!keys.length) return;
  const lastKey = keys[keys.length - 1];
  const item    = cart[lastKey];
  const current = item.qty;
  const maxQty  = item.maxQty;

  if (delta > 0) {
    // nếu đã đạt max thì báo và dừng
    if (current >= maxQty) {
      alert('Đã đạt giới hạn tồn kho.');
      return;
    }
    item.qty = current + 1;
  } else {
    // chỉ giảm xuống tối thiểu 1
    item.qty = Math.max(1, current - 1);
  }

  updateCart();
}

  document.getElementById('page-up').addEventListener('click', () => adjustLastQty(1));
  document.getElementById('page-down').addEventListener('click', () => adjustLastQty(-1));

  // Barcode scanning
  // Barcode scanning: lookup via product code
  document.getElementById('barcode-input')
    .addEventListener('keypress', e => {
      if (e.key !== 'Enter') return;
      const code = e.target.value.trim();
      if (!code) return;

      fetch(`${BASE_URL}/api/products/search?code=${encodeURIComponent(code)}`, {
        headers: { 'Authorization': `Bearer ${AUTH_TOKEN}`, 'Accept': 'application/json' }
      })
      .then(r => r.json())
      .then(products => {
        if (!Array.isArray(products) || products.length === 0) {
          return alert('Không tìm thấy sản phẩm với mã: ' + code);
        }

        const available = products
          .filter(p => Number(p.actual_quantity) > 0)
          .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

        const totalStock = available.reduce((sum, lot) => sum + Number(lot.actual_quantity), 0);

        if (totalStock === 0) {
          return alert('Sản phẩm đã hết hàng.');
        }

        const p      = available[0];
        const id     = p.id;
        const name   = p.name;
        const price  = parseFloat(p.price);
        const maxQty = totalStock;

        if (cart[id]) {
          if (cart[id].qty < maxQty) {
            cart[id].qty++;
          } else {
            alert('Đã đạt giới hạn tồn kho.');
          }
        } else {
          const tax = parseFloat(p.tax) || 0;
          cart[id] = { name, price, qty: 1, maxQty, code: p.code, tax };
          console.log('✅ Added via barcode:', cart[id]);

        }

        updateCart();
      })
      .catch(err => {
        console.error('Error fetching product by code:', err);
        alert('Lỗi khi tìm sản phẩm.');
      })
      .finally(() => {
        e.target.value = '';
        e.target.focus();
      });
  });



  document.getElementById('open-payment').addEventListener('click', () => {
  // deactivate product tab
  document.querySelectorAll('.inner-tab-button').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.inner-panel').forEach(p => p.style.display = 'none');

  // activate payment tab
  const btn = document.querySelector('.inner-tab-button[data-target="panel-payment"]');
  btn.classList.add('active');
  document.getElementById('panel-payment').style.display = 'block';
});



  // Numpad
  document.querySelectorAll('.num-button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('barcode-input').value += btn.innerText;
    });
  });

const togglePrintBtn   = document.getElementById('toggle-print');
const printStatusElem  = document.getElementById('print-status');

togglePrintBtn.addEventListener('click', () => {
  window.autoPrint = !window.autoPrint;
  printStatusElem.innerText = window.autoPrint ? 'ON' : 'OFF';
});


  function printInvoice() {
    if (!Object.keys(cart).length) return alert('Cart is empty!');
    if (autoPrint) window.print();
    cart = {};
    updateCart();
  }
  document.getElementById('print-invoice').addEventListener('click', printInvoice);
  document.addEventListener('keydown', e => {
    if (e.key === 'F11')   { e.preventDefault(); printInvoice(); }
    if (e.key === 'PageUp')   { e.preventDefault(); adjustLastQty(1); }
    if (e.key === 'PageDown') { e.preventDefault(); adjustLastQty(-1); }
  });
});


