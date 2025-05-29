// pos-invoice.js
// File tách riêng logic in hoá đơn

document.addEventListener('DOMContentLoaded', () => {
  // Cấu hình cửa hàng và đường dẫn template
  const STORE_NAME    = 'Tappo Market';
  const STORE_ADDRESS = 'Đà Nẵng, Vietnam';
    const TEMPLATE_PATH = 'pos-receipt.html';

  // Hàm sinh HTML hoá đơn từ template
  async function generateReceiptHtml(cart, eurRate, cashierId) {
    // Load template
    const tpl = await fetch(TEMPLATE_PATH)
      .then(res => {
        if (!res.ok) throw new Error(`Không tìm thấy template tại ${TEMPLATE_PATH} (${res.status})`);
        return res.text();
      });

    // Chuẩn bị dữ liệu
    const now = new Date();
    const items = Object.entries(cart).map(([id, i]) => ({
      QTY:        i.qty,
      NAME:       i.name,
      PRICE:      i.price.toFixed(2),
      LINE_TOTAL: (i.price * i.qty).toFixed(2)
    }));
    const totalCzk = Object.values(cart)
      .reduce((sum, i) => sum + i.price * i.qty, 0)
      .toFixed(2);
    const totalEur = (totalCzk / eurRate).toFixed(2);

    // Thay placeholders tĩnh
    let html = tpl
      .replace('{{STORE_NAME}}', STORE_NAME)
      .replace('{{STORE_ADDRESS}}', STORE_ADDRESS)
      .replace('{{DATE}}', now.toLocaleDateString())
      .replace('{{TIME}}', now.toLocaleTimeString())
      .replace('{{CASHIER}}', `#${cashierId}`)
      .replace('{{TOTAL_CZK}}', totalCzk)
      .replace('{{TOTAL_EUR}}', totalEur);

    // Tạo rows động cho items
    const rows = items.map(item =>
      `<tr>
         <td>${item.QTY}</td>
         <td>${item.NAME}</td>
         <td>${item.PRICE}</td>
         <td>${item.LINE_TOTAL}</td>
       </tr>`
    ).join('');
    html = html.replace(/<tbody>[\s\S]*<\/tbody>/, `<tbody>${rows}</tbody>`);

    return html;
  }

  // Hàm in hoá đơn
  async function printInvoice() {
    if (!window.cart || !Object.keys(window.cart).length) {
      return alert('Giỏ hàng trống!');
    }
    try {
      const receiptHtml = await generateReceiptHtml(
        window.cart,
        window.EUR_RATE,
        CURRENT_USER_ID
      );

      const printWindow = window.open('', '_blank');
      printWindow.document.write(receiptHtml);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();

      // Clear cart sau khi in
      window.cart = {};
      if (typeof window.updateCart === 'function') window.updateCart();
    } catch (err) {
      console.error(err);
      alert('Lỗi khi tải template hoá đơn.');
    }
  }

  // Bind sự kiện cho nút in và phím F11
  const btnPaymentPrint = document.getElementById('pm-print');
  const btnInvoicePrint = document.getElementById('print-invoice');
  if (btnPaymentPrint) btnPaymentPrint.addEventListener('click', printInvoice);
  if (btnInvoicePrint) btnInvoicePrint.addEventListener('click', printInvoice);
  document.addEventListener('keydown', e => {
    if (e.key === 'F11') {
      e.preventDefault();
      printInvoice();
    }
  });
  window.generateReceiptHtml = generateReceiptHtml;
  window.printInvoice        = printInvoice;
});
// Đoạn mã này tách riêng logic in hoá đơn từ các phần khác của POS