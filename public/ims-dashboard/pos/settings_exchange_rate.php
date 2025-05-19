<?php
$mysqli = new mysqli("localhost", "root", "", "tappo_market");
$mysqli->set_charset("utf8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rate = floatval($_POST['exchange_rate']);
    $check = $mysqli->query("SELECT * FROM settings WHERE name = 'exchange_rate'");

    if ($check->num_rows > 0) {
        $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE name = 'exchange_rate'");
    } else {
        $stmt = $mysqli->prepare("INSERT INTO settings (name, value) VALUES ('exchange_rate', ?)");
    }
    $stmt->bind_param("d", $rate);
    $stmt->execute();
    $success = true;
}

$result = $mysqli->query("SELECT value FROM settings WHERE name = 'exchange_rate'");
$current_rate = $result->fetch_assoc()['value'] ?? 25;
?>

<h2>Chuyển Đổi Ngoại Tệ</h2>
<form method="post">
    <label>Tỷ giá (CZK per EUR):</label><br><br>
    <input type="number" step="0.01" name="exchange_rate" value="<?= htmlspecialchars($current_rate) ?>" required><br><br>
    <button type="submit">Lưu</button>
</form>

<?php if (isset($success)): ?>
    <div class="success">Cập nhật tỷ giá thành công!</div>
<?php endif; ?>
