<?php
// settings_reports_content.php

// Lấy danh sách ca
$shifts = [];
if ($res = $mysqli->query("SELECT id, name FROM shifts ORDER BY sort_order ASC")) {
  while ($row = $res->fetch_assoc()) {
    $shifts[] = $row;
  }
  $res->free();
}
?>

<style>
  .pos-report-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
    box-shadow: 0 1px 6px #0002;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
    font-size: 15px;
  }
  .pos-report-table thead tr {
    background: #2574A9;
    color: #fff;
    font-weight: 600;
  }
  .pos-report-table th, .pos-report-table td {
    padding: 10px 16px;
    border-bottom: 1px solid #eee;
    text-align: left;
  }
  .pos-report-table tr:last-child td {
    border-bottom: none;
  }
  .pos-report-table tbody tr:hover {
    background: #F0F8FF;
    transition: background 0.2s;
  }
  .summary-box {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin-top: 20px;
    background: #f9fbfe;
    padding: 18px 24px;
    border-radius: 8px;
    box-shadow: 0 1px 3px #0001;
  }
  .summary-item { min-width: 140px; }
  .filters {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    align-items: flex-end;
    margin-bottom: 20px;
  }
  .filters label {
    font-weight: 500;
  }
  #btn-load-report {
    background: #2574A9;
    color: #fff;
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s;
  }
  #btn-load-report:hover {
    background: #1A5276;
  }
</style>

<h2>Báo cáo POS</h2>

<div class="filters">
  <label>Ca:<br>
    <select id="shift-select" style="padding:8px; font-size:14px;">
      <option value="">Tất cả</option>
      <?php foreach ($shifts as $s): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endforeach ?>
    </select>
  </label>
  <label>Từ ngày:<br>
    <input type="date" id="date-from"
           value="<?= date('Y-m-d', strtotime('-60 days')) ?>"
           style="padding:8px; font-size:14px;">
  </label>
  <label>Đến ngày:<br>
    <input type="date" id="date-to"
           value="<?= date('Y-m-d') ?>"
           style="padding:8px; font-size:14px;">
  </label>
  <button id="btn-load-report">Tải báo cáo</button>
</div>

<table class="pos-report-table" id="tbl-invoices">
  <thead>
    <tr>
      <th>#ID</th>
      <th>Ngày giờ</th>
      <th>Ca</th>
      <th>Thu ngân</th>
      <th>PTTT</th>
      <th>Thanh toán CZK</th>
      <th>Thanh toán EUR</th>
      <th>Tip CZK</th>
      <th>Tip EUR</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<div class="summary-box">
  <div class="summary-item">CZK Cash: <strong><span id="sum-czk-cash">0.00</span></strong></div>
  <div class="summary-item">CZK Card: <strong><span id="sum-czk-card">0.00</span></strong></div>
  <div class="summary-item">EUR Cash: <strong><span id="sum-eur-cash">0.00</span></strong></div>
  <div class="summary-item">EUR Card: <strong><span id="sum-eur-card">0.00</span></strong></div>
  <div class="summary-item">Tip CZK: <strong><span id="sum-tip-czk">0.00</span></strong></div>
  <div class="summary-item">Tip EUR: <strong><span id="sum-tip-eur">0.00</span></strong></div>
</div>



<script>
// 1) Fetch & render bảng báo cáo
function loadReport() {
  const shift = document.getElementById('shift-select').value;
  const from  = document.getElementById('date-from').value;
  const to    = document.getElementById('date-to').value;

  fetch(`api/pos-reports.php?from=${from}&to=${to}&shift_id=${shift}`, {
    credentials: 'same-origin'
  })
  .then(r => {
    if (!r.ok) throw new Error('Network response was not OK');
    return r.json();
  })
  .then(data => {
    const tbody = document.querySelector('#tbl-invoices tbody');
    tbody.innerHTML = '';

    // Sắp xếp theo thời gian mới nhất
    data.invoices.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    data.invoices.forEach(inv => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${inv.id}</td>
        <td>${inv.created_at}</td>
        <td>${inv.shift_name || '-'}</td>
        <td>${inv.cashier_name || inv.cashier_id || '-'}</td>
        <td>${inv.payment_method}</td>
        <td>${parseFloat(inv.amount_tendered_czk || 0).toFixed(2)}</td>
        <td>${parseFloat(inv.amount_tendered_eur || 0).toFixed(2)}</td>
        <td>${parseFloat(inv.tip_czk || 0).toFixed(2)}</td>
        <td>${parseFloat(inv.tip_eur || 0).toFixed(2)}</td>
        <td><button class="btn-view-receipt" data-order-id="${inv.id}">Xem hóa đơn</button></td>
      `;
      tbody.appendChild(tr);
    });

    // render summary
    document.getElementById('sum-czk-cash').textContent = data.summary.sum_czk_cash.toFixed(2);
    document.getElementById('sum-czk-card').textContent = data.summary.sum_czk_card.toFixed(2);
    document.getElementById('sum-eur-cash').textContent = data.summary.sum_eur_cash.toFixed(2);
    document.getElementById('sum-eur-card').textContent = data.summary.sum_eur_card.toFixed(2);
    document.getElementById('sum-tip-czk').textContent = data.summary.sum_tip_czk.toFixed(2);
    document.getElementById('sum-tip-eur').textContent = data.summary.sum_tip_eur.toFixed(2);
  })
  .catch(err => {
    console.error('Error loading report:', err);
    alert('Không thể tải báo cáo. Vui lòng thử lại.');
  });
}

document.getElementById('btn-load-report').addEventListener('click', loadReport);

// 2) Modal popup hóa đơn
function showModal(html) {
  document.getElementById('receipt-modal-content').innerHTML =
    `<button onclick="document.getElementById('receipt-modal').style.display='none'"
      style="position:absolute;top:12px;right:18px;font-size:20px;background:none;border:none;cursor:pointer;">×</button>
     <button id="btn-print-receipt" style="position:absolute;top:12px;left:18px;padding:4px 12px;cursor:pointer;">🖨️ In hóa đơn</button>
     <div id="modal-receipt-scroll" style="overflow-y:auto;max-height:68vh;padding-right:10px;">
       <div id="modal-receipt-content">${html}</div>
     </div>`;
  document.getElementById('receipt-modal').style.display = 'block';

  document.getElementById('btn-print-receipt').onclick = function() {
    printModalReceipt();
  };
}

function printModalReceipt() {
  const html = document.getElementById('modal-receipt-content').innerHTML;
  const w = window.open('', '', 'width=400,height=600');
  w.document.write(`
    <html>
    <head>
      <title>In hóa đơn</title>
      <style>
        body { margin: 0; font-family: monospace; font-size: 13px; }
        .receipt { max-width:330px;margin:0 auto;}
        .header, .footer { text-align:center;margin:8px 0; }
        .info, .totals { margin:4px 0; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:4px 0; }
        th { border-bottom:1px dashed #000; }
        .items td:nth-child(1) { width:12%; }
        .items td:nth-child(2) { width:52%; }
        .items td:nth-child(3), .items td:nth-child(4) { width:18%; text-align:right; }
      </style>
    </head>
    <body>
      ${html}
    </body>
    </html>
  `);
  w.document.close();
  w.focus();
  w.print();
}

// 3) Lắng nghe click nút Xem hóa đơn
document.addEventListener('click', function(e){
  if(e.target.classList.contains('btn-view-receipt')) {
    const orderId = e.target.dataset.orderId;
    fetch(`api/pos-get-receipt.php?id=${orderId}`)
      .then(r => r.json())
      .then(data => {
        const html = generateReceiptHtmlForModal(data.order, data.items);
        showModal(html);
      });
  }
});

// 4) Hàm render HTML hóa đơn
function generateReceiptHtmlForModal(order, items) {
  const now = new Date(order.created_at);
  const cashier = order.cashier_name || order.cashier_id || '-';
  const rows = items.map(item => `
    <tr>
      <td>${item.quantity}</td>
      <td>${item.product_name || item.product_id}</td>
      <td>${parseFloat(item.price).toFixed(2)}</td>
      <td>${(item.quantity * item.price).toFixed(2)}</td>
    </tr>
  `).join('');
  return `
    <div class="receipt" style="font-family:monospace;font-size:13px;max-width:330px;">
      <div class="header" style="text-align:center;margin:8px 0;">
        <strong>Tappo Market</strong><br>
        Đà Nẵng, Vietnam
      </div>
      <div class="info" style="margin:4px 0;">
        <div>Ngày: ${now.toLocaleDateString()} Giờ: ${now.toLocaleTimeString()}</div>
        <div>Thu ngân: ${cashier}</div>
      </div>
      <table class="items" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th>SL</th><th>Sản phẩm</th><th>Giá</th><th>Thành tiền</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
      <div class="totals" style="margin:4px 0;">
        <div><strong>Tổng CZK:</strong> ${order.rounded_total_czk}</div>
        <div><strong>Tổng EUR:</strong> ${(order.rounded_total_czk / window.EUR_RATE).toFixed(2)}</div>
      </div>
      <div class="footer" style="text-align:center;margin:8px 0;">
        Cảm ơn quý khách!<br>Hẹn gặp lại.
      </div>
    </div>
  `;
}

// 5) Auto load báo cáo ngày hôm nay
window.addEventListener('DOMContentLoaded', () => {
  const today = new Date().toISOString().slice(0, 10);
  document.getElementById('date-from').value = today;
  document.getElementById('date-to').value = today;
  loadReport(); // Tự động tải
});
</script>


<!-- Thêm modal popup cuối file (một lần duy nhất trong trang) -->
<div id="receipt-modal" style="display:none;position:fixed;z-index:1000;top:0;left:0;right:0;bottom:0;background:#0005;">
  <div id="receipt-modal-content" style="
    background:#fff;
    margin:40px auto;
    padding:32px;
    width:420px;
    max-width:98vw;
    border-radius:10px;
    box-shadow:0 6px 24px #0003;
    position:relative;
    max-height: 80vh;     /* GIỚI HẠN CHIỀU CAO MODAL */
    overflow: hidden;">
    <!-- ...nút đóng và nút in hóa đơn sẽ được render vào đây bằng JS... -->
    <div id="modal-receipt-scroll"
      style="overflow-y:auto;max-height:68vh;padding-right:10px;">
      <!-- Nội dung hóa đơn sẽ được render vào đây -->
    </div>
  </div>
</div>