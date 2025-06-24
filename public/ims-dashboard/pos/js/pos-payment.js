// pos-payment.js
// Xử lý logic thanh toán và Auto Print

document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const subtotalEl        = document.getElementById('pm-subtotal');
  const grandEl           = document.getElementById('pm-grand');
  const roundedEl         = document.getElementById('pm-rounded');
  const changeEl          = document.getElementById('pm-change');
  const tipInput          = document.getElementById('pm-tip');
  const tenderInput       = document.getElementById('pm-tender');
  const curCzkBtn         = document.getElementById('pm-currency-czk');
  const curEurBtn         = document.getElementById('pm-currency-eur');
  const methodCashBtn     = document.getElementById('pm-method-cash');
  const methodTransferBtn = document.getElementById('pm-method-transfer');
  const methodCardBtn     = document.getElementById('pm-method-card');
  const qrContainer       = document.getElementById('pm-qr-code-container');
  const completeBtn       = document.getElementById('pm-complete');

  let currency = 'CZK';
  let method   = 'cash';

  // Tính subtotal, round, và update hiển thị
  function computeSubtotal() {
    return Object.values(cart).reduce((sum, i) => sum + i.price * i.qty, 0);
  }
  function roundHalf(x) { return Math.ceil(x * 2) / 2; }
  // Cập nhật hiển thị thanh toán
function updatePaymentDisplay() {
  const sub     = computeSubtotal();
  const tip     = parseFloat(tipInput.value) || 0;
  const rate    = window.EUR_RATE;
  let grand, rounded, tender, change, unit;

  if (currency === 'CZK') {
    grand   = sub;
    rounded = roundHalf(grand);
    tender  = parseFloat(tenderInput.value) || 0;
    change  = tender - rounded;
    unit    = 'CZK';

    subtotalEl.textContent = `${sub.toFixed(2)} CZK`;
    grandEl.textContent    = `${grand.toFixed(2)} CZK`;
    roundedEl.textContent  = `${rounded.toFixed(2)} CZK`;
    changeEl.textContent   = `${change.toFixed(2)} CZK`;

    // ✅ Hiện dòng rounded
    document.getElementById('pm-rounded-label').style.display = 'inline';
    roundedEl.style.display = 'inline';
    } else {
      grand   = sub / rate;
      tender  = parseFloat(tenderInput.value) || 0;
      change  = tender - grand;
      unit    = 'EUR';

      subtotalEl.textContent = `${grand.toFixed(2)} EUR`;
      grandEl.textContent    = `${grand.toFixed(2)} EUR`;
      changeEl.textContent   = `${change.toFixed(2)} EUR`;

      // ❌ Ẩn dòng rounded
      document.getElementById('pm-rounded-label').style.display = 'none';
      roundedEl.style.display = 'none';
    }
}


window.updatePaymentDisplay = updatePaymentDisplay;


  // Toast không block UI
  function showToast(msg) {
    const toast = document.createElement('div');
    toast.className = 'my-toast';
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  
  function getCurrentShiftId() {
    return parseInt(localStorage.getItem('current_shift_id')) || null;
  }

  

  // Toggle currency
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

  // Toggle payment method
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

  // Khi tip hoặc tender thay đổi
  [tipInput, tenderInput].forEach(el => el.addEventListener('input', updatePaymentDisplay));

  // Xử lý nút Hoàn tất
completeBtn.addEventListener('click', () => {


  console.log('AutoPrint flag:', autoPrint);

  let printWindow = null;
  if (autoPrint) {
    printWindow = window.open('', '_blank');
    if (!printWindow) {
      return alert('Pop-up bị chặn hoặc không thể mở. Vui lòng cho phép.');
    }
  }

  const sub     = computeSubtotal();
  const tip     = parseFloat(tipInput.value) || 0;
  const rate    = window.EUR_RATE;
  let grand, rounded, tender, roundedInCzk;

  if (currency === 'CZK') {
    grand = sub + tip;
    rounded = roundHalf(grand);
    roundedInCzk = rounded;
    tender  = parseFloat(tenderInput.value) || 0;
  } else {
    const tipEur = tip;
    grand   = sub / rate + tipEur; // ✅ include tip
    rounded = roundHalf(grand);   // ✅ round full amount (subtotal + tip)
    roundedInCzk = Math.round(rounded * rate); // ✅ still use CZK for reports
    tender  = parseFloat(tenderInput.value) || 0;
  }


const payload = {

  source: 'pos',
  cashier_id: CURRENT_USER_ID,
  customer_id: null,
  shift_id: getCurrentShiftId(),
  paid_amount: roundedInCzk,
  subtotal_czk: +sub,
  tip_czk: currency === 'CZK' ? +tip : 0,
  tip_eur: currency === 'EUR' ? +tip : 0,
  grand_total_czk: currency === 'EUR' ? sub + tip * rate : sub + tip,
  rounded_total_czk: +rounded,
  payment_currency: currency || 'CZK', // ✅ Ensure this is always set
  amount_tendered_czk: currency === 'CZK' ? +tender : 0,
  amount_tendered_eur: currency === 'EUR' ? +tender : 0,
  change_due_czk: currency === 'CZK' ? +(tender - rounded) : 0,
  change_due_eur: currency === 'EUR' ? +(tender - grand) : 0,
  payment_method: method,
  items: Object.entries(cart).map(([id, i]) => ({
    product_id: +id,
    quantity: +i.qty,
    unit_price: +i.price,
    code: i.code || '',
    tax: +i.tax || 0,   // ✅ add this

  }))
  
};
console.log("🧾 tip:", tip, "sub:", sub, "grand_total_czk:", payload.grand_total_czk);

  console.log('Payload to submit:', payload);
  fetch(`${BASE_URL}/api/orders`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${localStorage.getItem("token")}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  .then(res => res.ok ? res.json() : res.json().then(e => { throw e; }))
  .then(data => {
    console.log('✅ Order response:', data);
    showToast(`Thanh toán thành công! Mã đơn #${data.order.id}`);
    const orderId = data.order.id;          // ✅ Get the actual order ID
    const shiftName = data.order.shift_name; // ✅ If available (check if exists first)
    const cartSnapshot = {};
    Object.entries(cart).forEach(([id, i]) => {
      cartSnapshot[id] = {
        name: i.name,
        qty: i.qty,
        price: i.price,
        code: i.code || '',
        tax: i.tax || 0  // ✅ added tax here too
      };
    });
    // Fetch cấu hình in hóa đơn rồi tạo lastReceipt và in nếu cần
    return fetch('api/get_invoice_settings.php')
      .then(res => res.json())
      .then(config => {
        window.lastReceipt = {
          cart: cartSnapshot,
          eurRate: window.EUR_RATE,
          cashierId: CURRENT_USER_ID,
          shiftName,
          tip,
          tender,
          currency,
          paymentCurrency: currency, // ✅ <-- THIS FIXES IT
          settings: config,
          rounded,
          grand,
          payment_method: method,
          invoiceNumber: orderId
        };
        try {
          localStorage.setItem('lastReceiptData', JSON.stringify(window.lastReceipt));
        } catch (e) {
          console.warn('Không thể lưu hóa đơn vào localStorage:', e);
        }

        // Always generate the receipt HTML and save it
      generateReceiptHtml({
        cart: cartSnapshot,
        eurRate: window.EUR_RATE,
        cashierId: CURRENT_USER_ID,
        invoiceNumber: data.order.id,
        settings: config,
        tip,
        tender,
        paymentCurrency: currency
      }).then(html => {
        // ✅ Save to DB even if autoPrint is off

        console.log("Saving invoice...", {
          order_id: orderId,
          html: html
        });
        fetch('api/save-invoice.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem("token")}`
          },
          body: JSON.stringify({
            order_id: orderId,    // ✅ This is the order ID returned from Laravel
            html: html            // ✅ This is the receipt HTML you just generated
          })
        });


        // 🖨️ Only print if autoPrint is on
        if (autoPrint && printWindow) {
          printWindow.document.write(html);
          printWindow.document.close();
          printWindow.focus();
          printWindow.print();
          printWindow.close();
        }
      });

      })

      .finally(() => {
        // Clear cart & UI
        window.cart = {};
        window.updateCart();
        updatePaymentDisplay();

        changeEl.textContent = ''; // 🔄 Clear the amount
        document.getElementById('pm-change-label').textContent = 'Change Due'; // (Optional) reset label if dynamic

        tipInput.value = '';
        tenderInput.value = '';
        currency = 'CZK';
        method = 'cash';
        curCzkBtn.classList.add('on');
        curEurBtn.classList.remove('on');
        tenderInput.placeholder = '0.00 CZK';
        methodCashBtn.classList.add('on');
        methodTransferBtn.classList.remove('on');
        methodCardBtn.classList.remove('on');
        qrContainer.style.display = 'none';

        // Đóng popup
        document.getElementById('open-payment').click();
      });
  })
  .catch(err => {
    console.error('Payment error:', err);
    alert('Lỗi thanh toán.');
    if (printWindow) printWindow.close();
  });
});

  // Hiển thị lần đầu
  updatePaymentDisplay();
});
