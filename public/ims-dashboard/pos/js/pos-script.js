const broadcast = new BroadcastChannel('pos-cart');

window.currentPaymentMethod = 'cash';
window.currentQrUrl        = null; 

let currentWeightProduct = null;
const weightModal = document.getElementById('weightModal');
const weightInput = document.getElementById('weight-input');

window.openWeightModal = function(productIndex) {
  if (typeof productIndex === 'undefined') return;
  currentWeightProduct = productIndex;
  const item = window.cart[productIndex];
  if (item && item.isWeighted) {
    weightInput.value = item.qty;
    weightModal.style.display = 'flex';
    weightInput.focus();
  }
}

window.closeWeightModal = function() {
  weightModal.style.display = 'none';
  currentWeightProduct = null;
}

window.saveWeight = function() {
  if (currentWeightProduct === null) return;
  
  const item = window.cart[currentWeightProduct];
  if (!item || !item.isWeighted) return;
  
  const weight = parseFloat(weightInput.value);
  if (isNaN(weight) || weight <= 0) {
    alert('Vui lòng nhập khối lượng hợp lệ');
    return;
  }

  if (weight > item.maxQty) {
    alert('Khối lượng vượt quá tồn kho');
    return;
  }

  item.qty = weight;
  updateCart();
  closeWeightModal();
}

document.addEventListener('DOMContentLoaded', () => {
  const productList    = document.getElementById('product-list');
  const cartTableBody  = document.querySelector('#cart-table tbody');
  const totalCZK       = document.getElementById('total-czk');
  const totalEUR       = document.getElementById('total-eur');
  const printStatus    = document.getElementById('print-status');
  window.cart      = [];
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

  const weightButton = document.getElementById('weight');
  if (weightButton) {
    weightButton.addEventListener('click', openWeightModal);
  }

  // Add event listener for Enter key in weight input
  if (weightInput) {
    weightInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        saveWeight();
      }
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
    card.dataset.tax   = p.tax || 0;
    card.dataset.maxQty = p.actual_quantity;
    card.dataset.isWeighted = p.is_weighted ? 'true' : 'false';

    card.innerHTML = `
      <div class="product-name">${p.name}</div>
      <div class="product-price">${parseFloat(p.price).toFixed(2)} CZK</div>
      <div class="product-stock">
        <strong>In stock:</strong> ${p.actual_quantity}${p.is_weighted ? ' kg' : ''}
      </div>
      <div class="product-shipment"><em>${p.shipment_id ? `Shipment #${p.shipment_id}` : ''}</em></div>
    `;
    productList.appendChild(card);
  });

  attachProductEvents();
  updateCardAvailability();
}

function attachProductEvents() {
  document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', () => {
      const id = card.dataset.id;
      const name = card.dataset.name;
      const code = card.dataset.code;
      const price = parseFloat(card.dataset.price);
      const maxQty = parseInt(card.dataset.maxQty, 10) || 0;
      const isWeighted = card.dataset.isWeighted === 'true';

      const existingItemIndex = window.cart.findIndex(item => item.code === code);
      
      if (existingItemIndex !== -1) {
        const item = window.cart[existingItemIndex];
        if (item.qty < maxQty) {
          if (isWeighted) {
            openWeightModal(existingItemIndex);
          } else {
            item.qty++;
          }
        } else {
          alert('Đã đạt giới hạn tồn kho.');
        }
      } else {
        if (maxQty > 0) {
          const tax = parseFloat(card.dataset.tax) || 0;
          window.cart.push({ 
            id,
            name, 
            price, 
            qty: isWeighted ? 0 : 1, 
            maxQty, 
            code, 
            tax,
            isWeighted 
          });
          if (isWeighted) {
            openWeightModal(window.cart.length - 1);
          }
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
    const id = card.dataset.id;
    const maxQty = parseInt(card.dataset.maxQty, 10) || 0;
    const cartItem = window.cart.find(item => item.id === id);
    const inCartQty = cartItem ? cartItem.qty : 0;
    
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

  window.cart.forEach((item, index) => {
    const { name, price, qty, isWeighted } = item;
    const lineTotal = price * qty;
    total += lineTotal;
    const row = document.createElement('tr');
    row.className = (isWeighted && qty === 0) ? 'weighted-product' : '';
    row.innerHTML = `
      <td>${name}</td>
      <td>${qty}${isWeighted ? ' kg' : ''}</td>
      <td>${price.toFixed(2)} CZK</td>
      <td>${lineTotal.toFixed(2)} CZK</td>
      <td>
        <button class="remove-item" data-index="${index}">✖</button>
      </td>
    `;
    cartTableBody.appendChild(row);
  });

  const czkRounded = Math.round(total);
  const eurRounded = (total / EUR_RATE).toFixed(2);

  totalCZK.innerText = `${czkRounded} CZK`;
  totalEUR.innerText = `${eurRounded} EUR`;

  // attach removal handlers
  document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', () => {
      window.cart.splice(parseInt(btn.dataset.index), 1);
      updateCart();
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
  if (!window.cart.length) return;
  const item = window.cart[window.cart.length - 1];
  const current = item.qty;
  const maxQty = item.maxQty;

  if (delta > 0) {
    if (current >= maxQty) {
      alert('Đã đạt giới hạn tồn kho.');
      return;
    }
    item.qty = current + 1;
  } else {
    item.qty = Math.max(1, current - 1);
  }

  updateCart();
}

  document.getElementById('page-up').addEventListener('click', () => adjustLastQty(1));
  document.getElementById('page-down').addEventListener('click', () => adjustLastQty(-1));

  // Barcode scanning
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

        const p = available[0];
        const existingItemIndex = window.cart.findIndex(item => item.code === p.code);

        if (existingItemIndex !== -1) {
          const item = window.cart[existingItemIndex];
          if (item.qty < totalStock) {
            if (p.is_weighted) {
              openWeightModal(existingItemIndex);
            } else {
              item.qty++;
            }
          } else {
            alert('Đã đạt giới hạn tồn kho.');
          }
        } else {
          const tax = parseFloat(p.tax) || 0;
          window.cart.push({ 
            id: p.id,
            name: p.name, 
            price: parseFloat(p.price), 
            qty: p.is_weighted ? 0 : 1,
            maxQty: totalStock, 
            isWeighted: p.is_weighted ? true : false,
            code: p.code, 
            tax 
          });
          if (p.is_weighted) {
            openWeightModal(window.cart.length - 1);
          }
          console.log('✅ Added via barcode:', window.cart[window.cart.length - 1]);
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

  document.addEventListener('keydown', function (e) {
    const key = e.key;
    if (['F11', 'PageUp', 'PageDown'].includes(key)) {
      e.preventDefault();
      e.stopImmediatePropagation();

      if (key === 'F11') {
        printInvoice(); // calls the shared version
      } else if (key === 'PageUp') {
        adjustLastQty(1);
      } else if (key === 'PageDown') {
        adjustLastQty(-1);
      }
    }
  }, { passive: false });

  function printInvoice() {
    if (!window._printing) {
      window._printing = true;
      window.printInvoice?.(); // call the version from pos-invoice.js
      setTimeout(() => window._printing = false, 500);
    }
  }
  document.getElementById('print-invoice').addEventListener('click', printInvoice);
});

