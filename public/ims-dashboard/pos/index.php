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

<div class="pos-wrapper">

  <!-- LEFT: Category Tabs & Product List -->
  <div class="pos-left">

    <div class="category-tabs">
      <?php foreach ($categories as $cat): ?>
        <button class="category-tab"
                data-category-id="<?= $cat['id'] ?>">
          <?= htmlspecialchars($cat['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="product-list" id="product-list">
      <div class="loading">Chọn danh mục để xem sản phẩm…</div>
    </div>

  </div>

  <!-- RIGHT: Cart & Payment -->
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
      <button id="print-invoice">Print (F11)</button>
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

<script>
  const BASE_URL   = "<?= BASE_URL ?>";
  const AUTH_TOKEN = "<?=$_SESSION['token']?>";
</script>
<script src="js/pos-script.js"></script>
</body>
</html>
