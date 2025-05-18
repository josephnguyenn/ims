<?php
session_start();
if (isset($_SESSION['token'])) {
    header("Location: templates/dashboard.php");
    exit();
}
include "define.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
            <h2>Hệ Thống Quản Lý Kho Hàng</h2>
            <img src="uploads/images/logo.png" alt="Tappo Market" class="logo">

            <form id="login-form">

                <label for="email">Tên đăng nhập</label>
                <input type="text" id="email" required>

                <label for="password">Mật khẩu</label>
                <input type="password" id="password" required>

                <button type="submit">Đăng nhập</button>
            </form>

            <p id="error-message" class="error"></p>
    </div>
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
        console.log('BASE_URL is:', BASE_URL);
    </script>
    <script src="js/login.js"></script>


</body>
</html>
