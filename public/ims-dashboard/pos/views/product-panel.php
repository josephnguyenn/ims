<?php
// Assumes $categories is in scope
?>
<div class="category-tabs">
  <?php foreach ($categories as $cat) { ?>
    <button class="category-tab" data-category-id="<?= $cat['id'] ?>">
      <?= htmlspecialchars($cat['name']) ?>
    </button>
  <?php } ?>
</div>

<div class="product-list" id="product-list">
  <div class="loading">Đang tải sản phẩm…</div>
</div>
