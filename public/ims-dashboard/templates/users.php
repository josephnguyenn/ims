<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Lấy danh sách người dùng
function fetchData($apiUrl) {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$users = fetchData("http://localhost/ims/public/api/users");
?>

<head>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include "../includes/header.php"; ?>
<div class="main">
<?php include "../includes/sidebar.php"; ?>

<div class="main-content">
    <h1>Quản lý Người dùng</h1>

    <!-- Form Tạo Người dùng -->
    <div class="user-form">
        <h2>Tạo Người dùng mới</h2>
        <form id="createUserForm">
            <input type="text" id="name" placeholder="Tên" required><br>
            <input type="email" id="email" placeholder="Email" required><br>
            <input type="password" id="password" placeholder="Mật khẩu" required><br>
            <select id="role" required>
                <option value="">Chọn Vai trò</option>
                <option value="admin">Quản trị viên</option>
                <option value="staff">Nhân viên</option>
                <option value="manager">Quản lý</option>
            </select><br>
            <button type="submit">Tạo Người dùng</button>
        </form>
        <div id="formMessage"></div>
    </div>

    <!-- Bảng Người dùng -->
    <div class="user-list">
        <h2>Tất cả Người dùng</h2>
        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
            <?php foreach ($users as $user): ?>
                <tr data-id="<?= $user['id'] ?>">
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><button onclick="deleteUser(<?= $user['id'] ?>)">Xóa</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>



    
</div>

<script src="../js/users.js"></script>
<script>
const token = "<?= $_SESSION['token'] ?>"; // Chèn token PHP vào JS

document.getElementById("createUserForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const role = document.getElementById("role").value;

    const res = await fetch("http://localhost/ims/public/api/users", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ name, email, password, role })
    });

    const result = await res.json();
    const msg = document.getElementById("formMessage");

    if (res.status === 201) {
        msg.innerText = result.message;
        document.getElementById("createUserForm").reset();
        location.reload(); // Làm mới để hiển thị người dùng mới
    } else {
        msg.innerText = result.message || 'Lỗi khi tạo người dùng';
    }
});

async function deleteUser(id) {
    if (!confirm("Bạn có chắc chắn muốn xóa người dùng này không?")) return;

    const res = await fetch(`http://localhost/ims/public/api/users/${id}`, {
        method: "DELETE",
        headers: {
            'Authorization': 'Bearer ' + token
        }
    });

    const result = await res.json();
    alert(result.message);
    document.querySelector(`tr[data-id="${id}"]`).remove();
}
</script>

<style>
    form#createUserForm{
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .user-form input[type="text"],
.user-form input[type="email"],
.user-form input[type="password"],
.user-form select {
    padding: 10px 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    width: 100%;
    gap: 20px;
}
</style>
    
</body>