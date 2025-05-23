// pos-payment.js
document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const subtotalEl      = document.getElementById('pm-subtotal');
  const grandEl         = document.getElementById('pm-grand');
  const roundedEl       = document.getElementById('pm-rounded');
  const changeEl        = document.getElementById('pm-change');
  const tipInput        = document.getElementById('pm-tip');
  const tenderInput     = document.getElementById('pm-tender');
  const curCzkBtn       = document.getElementById('pm-currency-czk');
  const curEurBtn       = document.getElementById('pm-currency-eur');
  const methodCashBtn   = document.getElementById('pm-method-cash');
  const methodTransferBtn = document.getElementById('pm-method-transfer');
  const methodCardBtn   = document.getElementById('pm-method-card');
  const qrContainer     = document.getElementById('pm-qr-code-container');
  const completeBtn     = document.getElementById('pm-complete');
  const printBtn        = document.getElementById('pm-print');
  let cart      = window.cart;
  let autoPrint = window.autoPrint;
  let EUR_RATE  = window.EUR_RATE;

  // State
  let currency = 'CZK';
  let method   = 'cash';
  // `cart`, `EUR_RATE`, `autoPrint`, `BASE_URL`, `AUTH_TOKEN` come from pos-script.js / pos.php

  function computeSubtotal() {
    return Object.values(cart).reduce((sum, i) => sum + i.price * i.qty, 0);
  }

  function roundHalf(x) {
    return Math.ceil(x * 2) / 2;
  }

  function updatePaymentDisplay() {
    const sub     = computeSubtotal();
    const tip     = parseFloat(tipInput.value) || 0;
    const grand   = sub + tip;
    const rounded = roundHalf(grand);
    subtotalEl.textContent = `${sub.toFixed(2)} CZK`;
    grandEl.textContent    = `${grand.toFixed(2)} CZK`;
    roundedEl.textContent  = `${rounded.toFixed(2)} CZK`;
    const rate = window.EUR_RATE;
    const tender = parseFloat(tenderInput.value) || 0;
    let change;
    if (currency === 'CZK') {
      change = tender - rounded;
    } else {
      change = tender - (rounded / rate);
    }
    changeEl.textContent = `${change.toFixed(2)} ${currency}`;
  }
  window.updatePaymentDisplay = updatePaymentDisplay;


  // Currency toggles
  curCzkBtn.addEventListener('click', () => {
    currency = 'CZK';
    curCzkBtn.classList.add('on');
    curEurBtn.classList.remove('on');
    tenderInput.placeholder = '0.00 CZK';
    updatePaymentDisplay();
  });
  curEurBtn.addEventListener('click', () => {
    currency = 'EUR';
    curEurBtn.classList.add('on');
    curCzkBtn.classList.remove('on');
    tenderInput.placeholder = '0.00 EUR';
    updatePaymentDisplay();
  });

  // Method toggles + QR code handling
  methodCashBtn.addEventListener('click', () => {
    method = 'cash';
    methodCashBtn.classList.add('on');
    methodTransferBtn.classList.remove('on');
    methodCardBtn.classList.remove('on');
    qrContainer.style.display = 'none';
  });
  methodTransferBtn.addEventListener('click', () => {
    method = 'transfer';
    methodTransferBtn.classList.add('on');
    methodCashBtn.classList.remove('on');
    methodCardBtn.classList.remove('on');
    qrContainer.style.display = 'block';
  });
  methodCardBtn.addEventListener('click', () => {
    method = 'card';
    methodCardBtn.classList.add('on');
    methodCashBtn.classList.remove('on');
    methodTransferBtn.classList.remove('on');
    qrContainer.style.display = 'none';
  });

  // Recompute whenever tip or tender changes
  [tipInput, tenderInput].forEach(el =>
    el.addEventListener('input', updatePaymentDisplay)
  );

  // Complete payment handler
  completeBtn.addEventListener('click', () => {
    const sub     = computeSubtotal();
    const tip     = parseFloat(tipInput.value) || 0;
    const grand   = sub + tip;
    const rounded = roundHalf(grand);
    const tender  = parseFloat(tenderInput.value) || 0;
    const rate = window.EUR_RATE;
    const change = currency === 'CZK'
      ? tender - rounded
      : tender - (rounded / rate);
    // Build items payload
    const items = Object.entries(cart).map(([id, i]) => ({
      product_id: parseInt(id),
      quantity:   i.qty,
      unit_price: i.price
    }));

    // Order payload
    const payload = {
      source:               'pos',
      cashier_id:           CURRENT_USER_ID,
      subtotal_czk:         sub,
      tip_czk:              tip,
      grand_total_czk:      grand,
      rounded_total_czk:    rounded,
      payment_currency:     currency,
      amount_tendered_czk:  currency==='CZK'? tender: null,
      amount_tendered_eur:  currency==='EUR'? tender: null,
      change_due_czk:       currency==='CZK'? change: null,
      change_due_eur:       currency==='EUR'? change: null,
      payment_method:       method,
      items
    };

    fetch(`${BASE_URL}/api/pos-orders.php`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${AUTH_TOKEN}`,
        'Content-Type':  'application/json'
      },
      body: JSON.stringify(payload)
    })
    .then(r => r.json().then(b=>({ status: r.status, body: b })))
    .then(({ status, body }) => {
      if (status === 201) {
        alert('Payment successful! Order #'+ body.id);
        if (autoPrint) window.print();
        // Clear cart and reset
        cart = {};
        updatePaymentDisplay();
        document.getElementById('panel-product')
                .querySelector('.category-tab.active')
                .click();
      } else {
        alert('Error: ' + (body.message||JSON.stringify(body)));
      }
    })
    .catch(err => {
      console.error(err);
      alert('Network or server error.');
    });
  });

  // Print only
  printBtn.addEventListener('click', () => {
    if (autoPrint) window.print();
  });

  // Initial display
  updatePaymentDisplay();
  
});
