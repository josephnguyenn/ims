<?php
// report.php
session_start();
require_once __DIR__ . '/../../define.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Báo cáo POS</title>
  <link rel="stylesheet" href="path/to/your/css">
</head>
<body>
  <h1>Báo cáo POS</h1>

  <label>Ca:
    <select id="shift-select">
      <option value="">Tất cả</option>
      <?php
      $sh = $mysqli->query("SELECT id, name FROM shifts ORDER BY sort_order");
      while ($row = $sh->fetch_assoc()) {
        echo "<option value=\"{$row['id']}\">{$row['name']}</option>";
      }
      ?>
    </select>
  </label>
  <label>Từ:
    <input type="date" id="date-from" value="<?= date('Y-m-d', strtotime('-60 days')) ?>">
  </label>
  <label>Đến:
    <input type="date" id="date-to" value="<?= date('Y-m-d') ?>">
  </label>
  <button id="btn-load">Tải báo cáo</button>

  <h2>Danh sách hoá đơn</h2>
  <table id="tbl-invoices">
    <thead>
      <tr><th>#ID</th><th>Ngày giờ</th><th>Ca</th><th>Phương thức</th><th>Tổng CZK</th><th>Tổng EUR</th><th>Tip CZK</th><th>Tip EUR</th></tr>
    </thead>
    <tbody></tbody>
  </table>

  <h2>Tóm tắt</h2>
  <p>CZK Cash: <span id="sum-czk-cash">0</span></p>
  <p>CZK Card: <span id="sum-czk-card">0</span></p>
  <p>EUR Cash: <span id="sum-eur-cash">0</span></p>
  <p>EUR Card: <span id="sum-eur-card">0</span></p>
  <p>Tip CZK: <span id="sum-tip-czk">0</span></p>
  <p>Tip EUR: <span id="sum-tip-eur">0</span></p>

  <script>
    document.getElementById('btn-load').addEventListener('click', () => {
      const shift = document.getElementById('shift-select').value;
      const from  = document.getElementById('date-from').value;
      const to    = document.getElementById('date-to').value;
      fetch(`api/pos-reports.php?from=${from}&to=${to}&shift_id=${shift}`)
        .then(r => r.json())
        .then(data => {
          const tb = document.querySelector('#tbl-invoices tbody');
          tb.innerHTML = '';
          data.invoices.forEach(inv => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${inv.id}</td>
              <td>${inv.created_at}</td>
              <td>${inv.shift_name||'-'}</td>
              <td>${inv.payment_method}</td>
              <td>${inv.rounded_total_czk.toFixed(2)}</td>
              <td>${inv.rounded_total_eur.toFixed(2)}</td>
              <td>${inv.tip_czk.toFixed(2)}</td>
              <td>${inv.tip_eur.toFixed(2)}</td>
            `;
            tb.appendChild(tr);
          });

          document.getElementById('sum-czk-cash').textContent = data.summary.sum_czk_cash.toFixed(2);
          document.getElementById('sum-czk-card').textContent = data.summary.sum_czk_card.toFixed(2);
          document.getElementById('sum-eur-cash').textContent = data.summary.sum_eur_cash.toFixed(2);
          document.getElementById('sum-eur-card').textContent = data.summary.sum_eur_card.toFixed(2);
          document.getElementById('sum-tip-czk').textContent = data.summary.sum_tip_czk.toFixed(2);
          document.getElementById('sum-tip-eur').textContent = data.summary.sum_tip_eur.toFixed(2);
        });
    });
  </script>
</body>
</html>
