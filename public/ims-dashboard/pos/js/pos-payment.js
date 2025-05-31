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
  const sub     = computeSubtotal();              // subtotal hàng hóa
  const tip     = parseFloat(tipInput.value) || 0;
  const rate    = window.EUR_RATE;
  let grand, rounded, tender, change, unit;

  if (currency === 'CZK') {
    grand   = sub; // KHÔNG cộng tip!
    rounded = roundHalf(grand);
    tender  = parseFloat(tenderInput.value) || 0;
    change  = tender - rounded;
    unit    = 'CZK';

    subtotalEl.textContent = `${sub.toFixed(2)} CZK`;
    grandEl.textContent    = `${grand.toFixed(2)} CZK`;
    roundedEl.textContent  = `${rounded.toFixed(2)} CZK`;
    changeEl.textContent   = `${change.toFixed(2)} CZK`;
  } else {
    grand   = sub / rate; // KHÔNG cộng tip!
    rounded = roundHalf(grand);
    tender  = parseFloat(tenderInput.value) || 0;
    change  = tender - rounded;
    unit    = 'EUR';

    subtotalEl.textContent = `${(sub / rate).toFixed(2)} EUR`;
    grandEl.textContent    = `${grand.toFixed(2)} EUR`;
    roundedEl.textContent  = `${rounded.toFixed(2)} EUR`;
    changeEl.textContent   = `${change.toFixed(2)} EUR`;
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

    // Chuẩn bị payload
    // Chuẩn bị payload
    const sub     = computeSubtotal();                       // Tổng tiền hàng
    const tip     = parseFloat(tipInput.value) || 0;         // Tip nhập từ người dùng
    const rate    = window.EUR_RATE;
    let grand, rounded, tender;

    if (currency === 'CZK') {
      grand   = sub;                                         // KHÔNG cộng tip!
      rounded = roundHalf(grand);
      tender  = parseFloat(tenderInput.value) || 0;
    } else {
      grand   = sub / rate;                                  // KHÔNG cộng tip!
      rounded = roundHalf(grand);
      tender  = parseFloat(tenderInput.value) || 0;
    }

    const payload = {
      source: 'pos',
      cashier_id: CURRENT_USER_ID,
      customer_id: null,
      paid_amount: rounded,
      subtotal_czk: sub,                   // Chỉ tổng hàng hóa (CZK)
      tip_czk: currency === 'CZK' ? tip : null,
      tip_eur: currency === 'EUR' ? tip : null,
      grand_total_czk: sub,                // KHÔNG cộng tip!
      rounded_total_czk: rounded,          // KHÔNG cộng tip!
      payment_currency: currency,
      amount_tendered_czk: currency === 'CZK' ? tender : null,
      amount_tendered_eur: currency === 'EUR' ? tender : null,
      change_due_czk: currency === 'CZK' ? (tender - rounded) : null,
      change_due_eur: currency === 'EUR' ? (tender - rounded / rate) : null,
      payment_method: method,
      items: Object.entries(cart).map(([id, i]) => ({
        product_id: +id,
        quantity: i.qty,
        unit_price: i.price
      }))
    };


    fetch('api/pos-orders.php', {
      credentials: 'same-origin',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${AUTH_TOKEN}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    })
    .then(res => res.ok ? res.json() : res.json().then(e => { throw e; }))
    .then(data => {
      showToast(`Thanh toán thành công! Mã đơn #${data.id}`);
      
      const shiftName = data.shift_name;

      // Giữ snapshot của cart trước khi clear
      const cartSnapshot = { ...window.cart };

      // Lưu lại snapshot cho hóa đơn cuối cùng (đầy đủ thông tin cần thiết)
      window.lastReceipt = {
        cart: { ...cartSnapshot },
        eurRate: window.EUR_RATE,
        cashierId: CURRENT_USER_ID,
        shiftName: shiftName,
        tip: parseFloat(tipInput.value) || 0,
        currency: currency,
        rounded: rounded,
        grand: grand,
        tender: tender,
        payment_method: method,
        // có thể bổ sung các field khác nếu cần
      };

      // Reset inputs và UI
      tipInput.value    = '';
      tenderInput.value = '';
      currency = 'CZK';
      method   = 'cash';
      curCzkBtn.classList.add('on');
      curEurBtn.classList.remove('on');
      tenderInput.placeholder = '0.00 CZK';
      methodCashBtn.classList.add('on');
      methodTransferBtn.classList.remove('on');
      methodCardBtn.classList.remove('on');
      qrContainer.style.display = 'none';
      updatePaymentDisplay();

      // In hoá đơn nếu cần
      if (printWindow) {
          generateReceiptHtml(cartSnapshot, window.EUR_RATE, CURRENT_USER_ID, shiftName)
          .then(html => {
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();

            // Sau khi in xong mới clear cart chính thức
            window.cart = {};
            window.updateCart();
            updatePaymentDisplay();
          })
          .catch(err => {
            console.error('Lỗi tạo hóa đơn:', err);
            printWindow.close();
            alert('Không thể tạo hóa đơn.');
          });
      } else {
        // Nếu không in: clear cart luôn
        window.cart = {};
        window.updateCart();
        updatePaymentDisplay();
      }

      // Đóng panel payment
      document.getElementById('open-payment').click();
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
