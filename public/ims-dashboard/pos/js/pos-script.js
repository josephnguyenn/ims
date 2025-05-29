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
      attachProductEvents();
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
        // 👉 Skip items with no stock
        if ((p.actual_quantity ?? 0) <= 0) return;

        const card = document.createElement('div');
        card.className     = 'product-card';
        card.dataset.id    = p.id;
        card.dataset.name  = p.name;
        card.dataset.price = p.price;
        const shipmentText = p.shipment_id
          ? `Shipment #${p.shipment_id}`
          : '';

        card.innerHTML = `
          <div class="product-name">${p.name}</div>
          <div class="product-price">${parseFloat(p.price).toFixed(2)} CZK</div>
          <div class="product-stock">
            <strong>In stock:</strong> ${p.actual_quantity}
          </div>
          <div class="product-shipment"><em>${shipmentText}</em></div>
        `;
        productList.appendChild(card);
      });
    }




  // “Add to cart” on click
  function attachProductEvents() {
    document.querySelectorAll('.product-card').forEach(card => {
      card.addEventListener('click', () => {
        const id    = card.dataset.id;
        const name  = card.dataset.name;
        const price = parseFloat(card.dataset.price);
        if (cart[id]) cart[id].qty++;
        else          cart[id] = { name, price, qty: 1 };
        updateCart();
      });
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

  totalCZK.innerText = `${total.toFixed(2)} CZK`;
  totalEUR.innerText = `${(total / EUR_RATE).toFixed(2)} EUR`;

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
    subtotal:  total,
    grand:     total,   // or include tip/rounded if desired
    payment_method: window.currentPaymentMethod,
    qr_url:         window.currentQrUrl,
  });
}

window.updateCart = updateCart;



  // Quantity adjustments
  function adjustLastQty(delta) {
    const keys = Object.keys(cart);
    if (!keys.length) return;
    const last = keys[keys.length-1];
    cart[last].qty = Math.max(1, cart[last].qty + delta);
    updateCart();
  }
  document.getElementById('page-up').addEventListener('click', () => adjustLastQty(1));
  document.getElementById('page-down').addEventListener('click', () => adjustLastQty(-1));

  // Barcode scanning
  // Barcode scanning: lookup via product code
    document.getElementById('barcode-input')
    .addEventListener('keypress', e => {
        if (e.key === 'Enter') {
        const code = e.target.value.trim();
        if (!code) return;

        fetch(`${BASE_URL}/api/products/search?code=${encodeURIComponent(code)}`, {
            headers: {
            'Authorization': `Bearer ${AUTH_TOKEN}`,
            'Accept':        'application/json'
            }
        })
        .then(r => r.json())
        .then(products => {
            if (Array.isArray(products) && products.length > 0) {
            // take the first matching product
            const p = products[0];
            const id    = p.id;
            const name  = p.name;
            const price = parseFloat(p.price);

            if (cart[id]) cart[id].qty++;
            else          cart[id] = { name, price, qty: 1 };

            updateCart();
            } else {
            alert('Không tìm thấy sản phẩm với mã: ' + code);
            }
        })
        .catch(err => {
            console.error('Error fetching product by code:', err);
            alert('Lỗi khi tìm sản phẩm.');
        })
        .finally(() => {
            e.target.value = '';
            e.target.focus();
        });
        }
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


