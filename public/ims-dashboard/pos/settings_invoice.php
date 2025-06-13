<?php
// Đường dẫn file lưu cấu hình
$settingsFile = "../data/invoice_settings.json";

// Mặc định ban đầu
$invoiceSettings = [
    "storeName" => "Tappo Market",
    "ico"       => "28872380",
    "dic"       => "CZ8002201944",
    "address"   => "Đà Nẵng, Vietnam",
    "thankYou1" => "Cảm ơn quý khách!",
    "thankYou2" => "Hẹn gặp lại!"
];

// Load từ file nếu đã có
if (file_exists($settingsFile)) {
    $json = file_get_contents($settingsFile);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $invoiceSettings = $decoded;
    }
}

// Nếu submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceSettings = [
        "storeName" => $_POST['storeName'] ?? '',
        "ico"       => $_POST['ico'] ?? '',
        "dic"       => $_POST['dic'] ?? '',
        "address"   => $_POST['address'] ?? '',
        "thankYou1" => $_POST['thankYou1'] ?? '',
        "thankYou2" => $_POST['thankYou2'] ?? '',
    ];

    // Tạo thư mục nếu chưa có
    if (!file_exists("../data")) {
        mkdir("../data", 0777, true);
    }

    // Lưu
    file_put_contents($settingsFile, json_encode($invoiceSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo '<div class="success">✅ Đã lưu cấu hình hoá đơn thành công.</div>';
}
?>

<h2>Cấu hình hoá đơn in</h2>
<form method="POST">
    <p><strong>Tên cửa hàng:</strong><br>
        <input type="text" name="storeName" value="<?= htmlspecialchars($invoiceSettings['storeName']) ?>"></p>

    <p><strong>Mã số thuế (ICO):</strong><br>
        <input type="text" name="ico" value="<?= htmlspecialchars($invoiceSettings['ico']) ?>"></p>

    <p><strong>Mã số DPH (DIC):</strong><br>
        <input type="text" name="dic" value="<?= htmlspecialchars($invoiceSettings['dic']) ?>"></p>

    <p><strong>Địa chỉ:</strong><br>
        <input type="text" name="address" value="<?= htmlspecialchars($invoiceSettings['address']) ?>"></p>

    <p><strong>Dòng cảm ơn 1:</strong><br>
        <input type="text" name="thankYou1" value="<?= htmlspecialchars($invoiceSettings['thankYou1']) ?>"></p>

    <p><strong>Dòng cảm ơn 2:</strong><br>
        <input type="text" name="thankYou2" value="<?= htmlspecialchars($invoiceSettings['thankYou2']) ?>"></p>

    <p><button type="submit">Lưu cấu hình</button></p>
</form>
