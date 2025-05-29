<?php
// settings_shifts_content.php
// Nếu bạn cần xử lý POST/DELETE, vẫn đặt code ở đây giống trong trước
$mysqli = new mysqli("localhost", "root", "", "tappo_market");
$mysqli->set_charset("utf8");

// Xử lý Thêm / Sửa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action']; // 'add' hoặc 'edit'
    $name       = $mysqli->real_escape_string(trim($_POST['name']));
    $start_time = $_POST['start_time'];
    $end_time   = $_POST['end_time'];
    $sort_order = intval($_POST['sort_order']);

    if ($action === 'add') {
        $stmt = $mysqli->prepare("
            INSERT INTO shifts (name, start_time, end_time, sort_order)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("sssi", $name, $start_time, $end_time, $sort_order);
    } else {
        $id   = intval($_POST['id']);
        $stmt = $mysqli->prepare("
            UPDATE shifts
            SET name = ?, start_time = ?, end_time = ?, sort_order = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssii", $name, $start_time, $end_time, $sort_order, $id);
    }
    $stmt->execute();
    $stmt->close();
    // Trả về cùng URL, giữ section=shifts
    echo '<script>window.location.href="settings.php?section=shifts";</script>';
    exit;
}

// Xử lý Xóa
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $stmt = $mysqli->prepare("DELETE FROM shifts WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    echo '<script>window.location.href="settings.php?section=shifts";</script>';
    exit;
}

// Lấy danh sách shifts
$result = $mysqli->query("SELECT * FROM shifts ORDER BY sort_order ASC");
$shifts = $result->fetch_all(MYSQLI_ASSOC);
$result->free();
?>

<h2>Quản lý Ca làm việc</h2>

<!-- Form Thêm mới -->
<form method="post">
  <input type="hidden" name="action" value="add">
  <label>
    Tên ca:<br>
    <input type="text" name="name" required>
  </label><br><br>
  <label>
    Giờ bắt đầu:<br>
    <input type="time" name="start_time" value="06:00:00" required>
  </label><br><br>
  <label>
    Giờ kết thúc:<br>
    <input type="time" name="end_time" value="14:00:00" required>
  </label><br><br>
  <label>
    Thứ tự hiển thị:<br>
    <input type="number" name="sort_order" value="0" required>
  </label><br><br>
  <button type="submit">Thêm Ca</button>
</form>

<!-- Danh sách Ca -->
<table>
  <thead>
    <tr>
      <th>ID</th><th>Tên ca</th><th>Bắt đầu</th><th>Kết thúc</th><th>Thứ tự</th><th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($shifts as $s): ?>
      <tr>
        <td><?= $s['id'] ?></td>
        <td>
          <form method="post" class="inline">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($s['name']) ?>" required style="width:120px">
        </td>
        <td>
            <input type="time" name="start_time" value="<?= $s['start_time'] ?>" required>
        </td>
        <td>
            <input type="time" name="end_time" value="<?= $s['end_time'] ?>" required>
        </td>
        <td>
            <input type="number" name="sort_order" value="<?= $s['sort_order'] ?>" required style="width:50px">
        </td>
        <td>
            <button type="submit">Lưu</button>
          </form>
          &nbsp;
          <a href="settings.php?section=shifts&delete_id=<?= $s['id'] ?>"
             class="btn-delete"
             onclick="return confirm('Bạn có chắc muốn xoá ca này?')">
            Xóa
          </a>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
