// pos-invoice.js
// File tách riêng logic in hoá đơn

document.addEventListener('DOMContentLoaded', () => {
  if (!window.lastReceipt) {
    const saved = localStorage.getItem('lastReceiptData');
    if (saved) {
      try {
        window.lastReceipt = JSON.parse(saved);
      } catch (e) {generateReceiptHtml

        console.warn('Không thể khôi phục hóa đơn cuối:', e);
      }
    }
  }

  const TEMPLATE_PATH = 'pos-receipt.html';

async function generateReceiptHtml(data) {
  const {
    cart,
    eurRate,
    cashierId,
    invoiceNumber,
    settings,
    tip,
    tender,
    paymentCurrency
  } = data;

  console.log('🧾 Invoice number inside generator:', data.invoiceNumber);

  // ✅ Safely coerce values
  const safeTip = parseFloat(tip) || 0;
  const safeTender = parseFloat(tender) || 0;
  const safeCurrency = paymentCurrency || 'CZK';

  let tpl = await fetch(TEMPLATE_PATH)
    .then(res => {
      if (!res.ok) throw new Error(`Không tìm thấy template tại ${TEMPLATE_PATH}`);
      return res.text();
    });

  const roundHalf = x => Math.ceil(x * 2) / 2;

  const rawCzk = Object.values(cart).reduce((sum, i) => sum + i.price * i.qty, 0);
  const roundingDiff = (roundHalf(rawCzk) - rawCzk).toFixed(2);
  const totalCzk = roundHalf(rawCzk).toFixed(2);
  const totalEur = (rawCzk / eurRate).toFixed(2);
  const totalUnits = Object.values(cart).reduce((sum, i) => sum + i.qty, 0);

  let changeDisplay = '';
  let tenderDisplay = '';

  if (safeCurrency === 'EUR') {
    const grandEur = rawCzk / eurRate + safeTip; // ✅ include tip
    const changeEur = Math.max(0, safeTender - grandEur).toFixed(2);
    changeDisplay = `${changeEur} EUR`;
    tenderDisplay = `${safeTender.toFixed(2)} EUR`;
  } else {
    const grandCzk = rawCzk + safeTip;
    const changeCzk = Math.max(0, safeTender - roundHalf(grandCzk)).toFixed(2);
    changeDisplay = `${changeCzk} CZK`;
    tenderDisplay = `${safeTender.toFixed(2)} CZK`;
  }

const rows = `
  <div class="items">
    <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:11px;border-bottom:1px dashed #000;margin-bottom:4px;padding:0 4px; align-items: flex-end;">
      <span style="flex:2;">Název<br>Ks</span>
      <span style="flex:1;text-align:right;">Cena</span>
      <span style="flex:1;text-align:right;">DPH</span>
      <span style="flex:1;text-align:right;">Celkem</span>
    </div>
    ${Object.values(cart).map(item => {
      const total = (item.price * item.qty).toFixed(2);
      const tax = (item.tax || 0).toFixed(0);
      return `
        <div style="padding:4px 4px 0 4px;">
          <div style="font-weight:bold;">${item.name}</div>
          <div style="display:flex;justify-content:space-between;margin-top:2px;">
            <span style="flex:2;">${item.qty}x</span>
            <span style="flex:1;text-align:right;">${item.price.toFixed(2)}</span>
            <span style="flex:1;text-align:right;">${tax ? tax + '%' : '-'}</span>
            <span style="flex:1;text-align:right;">${total}</span>
          </div>
        </div>
      `;
    }).join('')}
  </div>
`;


  tpl = tpl
    .replace('{{STORE_NAME}}', settings.storeName)
    .replace('{{ICO}}', settings.ico)
    .replace('{{DIC}}', settings.dic)
    .replace('{{STORE_ADDRESS}}', settings.address)
    .replace('{{INVOICE_NUMBER}}', invoiceNumber || '—')
    .replace('{{DATE}}', new Date().toLocaleDateString())
    .replace('{{TIME}}', new Date().toLocaleTimeString())
    .replace('{{CASHIER}}', `#${cashierId}`)
    .replace('{{ITEM_ROWS}}', rows)
    .replace('{{ROUNDING_DIFF}}', roundingDiff)
    .replace('{{TOTAL_CZK}}', totalCzk)
    .replace('{{TOTAL_EUR}}', totalEur)
    .replace('{{AMOUNT_TENDERED}}', tenderDisplay)
    .replace('{{CHANGE}}', changeDisplay)
    .replace('{{TIP}}', (parseFloat(tip) || 0).toFixed(2))
    .replace('{{TOTAL_UNITS}}', totalUnits)
    .replace('{{THANK_YOU_LINE1}}', settings.thankYou1)
    .replace('{{THANK_YOU_LINE2}}', settings.thankYou2);

  // ✅ Wrap it with HTML and full style
  return `
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>POS Receipt</title>
  <style>
    @media print {
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      body {
        font-family: 'Courier New', monospace;
        font-size: 11px;
        line-height: 1.4;
      }
      .receipt {
        width: 80mm;
        padding: 0;
        margin: 0 auto;
      }
      .header, .footer {
        text-align: center;
        margin: 8px 0;
      }
      .info, .totals {
        margin: 4px 0;
        padding: 0 4px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
      }
      th, td {
        padding: 2px 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      th {
        border-bottom: 1px dashed #000;
        text-align: left;
      }
      .items td:nth-child(1) { width: 38%; text-align: left; }
      .items td:nth-child(2) { width: 10%; text-align: center; }
      .items td:nth-child(3),
      .items td:nth-child(4),
      .items td:nth-child(5) { width: 16%; text-align: right; }
    }
  </style>
</head>
<body>
  ${tpl}
</body>
</html>`;
}


async function printInvoice() {
  let printData;

  if (window.lastReceipt) {
    printData = {
      ...window.lastReceipt,
      settings: window.lastReceipt.settings || window.SETTINGS || {},
      invoiceNumber: window.lastReceipt.invoiceNumber || '—',
      eurRate: window.lastReceipt.eurRate || window.EUR_RATE,
    };
  } else if (window.cart && Object.keys(window.cart).length) {
    printData = {
      cart: window.cart,
      eurRate: window.EUR_RATE,
      cashierId: CURRENT_USER_ID,
      invoiceNumber: '—',
      settings: SETTINGS,
      tip: 0,
      tender: 0,
      paymentCurrency: 'CZK'
    };
  } else {
    return alert('Không có hóa đơn nào để in lại!');
  }

  try {
    const receiptHtml = await generateReceiptHtml(printData);
    const w = window.open('', '_blank');
    w.document.write(receiptHtml);
    w.document.close();
    w.focus();
    w.print();
  } catch (err) {
    console.error('Lỗi khi tạo hóa đơn:', err);
    alert('Lỗi khi tạo hóa đơn.');
  }
}

  document.getElementById('pm-print')?.addEventListener('click', printInvoice);
  document.getElementById('print-invoice')?.addEventListener('click', printInvoice);
  document.addEventListener('keydown', e => {
    if (e.key === 'F11') {
      e.preventDefault();
      printInvoice();
    }
  });

  window.generateReceiptHtml = generateReceiptHtml;
  window.printInvoice = printInvoice;
});
