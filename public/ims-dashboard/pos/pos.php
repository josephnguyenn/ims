<?php
session_start();
if (!isset($_SESSION['token']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
include "../define.php";  // defines BASE_URL and $mysqli

// Fetch categories
$categories = [];
$res = $mysqli->query("
    SELECT id, name
      FROM product_categories
     WHERE visible_in_pos = 1
     ORDER BY name ASC
");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>POS System</title>
  <link rel="stylesheet" href="css/pos-style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- Main Tabs (optional global tabs if you want) -->
<!-- <div class="tab-bar">…</div> -->

<div class="pos-wrapper">

  <!-- LEFT: product / payment-detail panels -->
  <div class="pos-left">
    <div class="inner-tabs">
      <button class="inner-tab-button active" data-target="panel-product">
        Product
      </button>
      <button class="inner-tab-button" data-target="panel-payment">
        Payment Detail
      </button>
    </div>

    <div id="panel-product" class="inner-panel">
      <?php include __DIR__ . '/views/product-panel.php'; ?>
    </div>

    <div id="panel-payment" class="inner-panel" hidden>
        <?php include __DIR__ . '/views/payment-panel.php'; ?>
    </div>
  </div>

  <!-- RIGHT: Payment Summary (cart, totals, numpad, controls) -->
  <div class="pos-right">
    <div class="barcode-scan">
      <input 
        type="text" 
        id="barcode-input" 
        placeholder="Scan barcode or type..." 
        autofocus
      >
    </div>

    <div class="controls">
      <button id="page-up">▲ Qty</button>
      <button id="page-down">▼ Qty</button>
      <button id="weigh">Weigh</button>
      <button id="toggle-print">
        Auto Print: <span id="print-status">OFF</span>
      </button>
      <button id="open-payment">Payment</button>
    </div>

    <div class="numpad">
      <?php foreach ([1,2,3,4,5,6,7,8,9,0] as $n): ?>
        <button class="num-button"><?= $n ?></button>
      <?php endforeach; ?>
    </div>

    <table class="cart-table" id="cart-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Total</th>
          <th></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <div class="total-area">
      Total: <span id="total-czk">0 CZK</span>
      | <span id="total-eur">0 EUR</span>
    </div>
  </div>

</div>

<!-- Define your globals first -->
<script>
  const BASE_URL        = "<?= BASE_URL ?>";
  const AUTH_TOKEN      = "<?php echo $_SESSION['token']; ?>";
  const CURRENT_USER_ID = <?= json_encode($_SESSION['user_id'] ?? null) ?>;

  // Now it’s safe to log them:
  console.log('BASE_URL is', BASE_URL);
  console.log('AUTH_TOKEN is', AUTH_TOKEN);
  console.log('CURRENT_USER_ID is', CURRENT_USER_ID);
</script>
<!-- Then load your JS files -->
 <script src="js/pos-script.js"></script>
<script src="js/pos-invoice.js"></script>
<script src="js/pos-payment.js"></script>

<script>
  // Inner‐tab switching
  document.querySelectorAll('.inner-tab-button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.inner-tab-button')
              .forEach(b=>b.classList.remove('active'));
      document.querySelectorAll('.inner-panel')
              .forEach(p=>p.style.display='none');
      btn.classList.add('active');
      document.getElementById(btn.dataset.target)
              .style.display='block';
    });
  });
</script>
</body>
</html>
