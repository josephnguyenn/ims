<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch users
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
    <h1>User Management</h1>

    <!-- Create User Form -->
    <div class="user-form">
        <h2>Create New User</h2>
        <form id="createUserForm">
            <input type="text" id="name" placeholder="Name" required><br>
            <input type="email" id="email" placeholder="Email" required><br>
            <input type="password" id="password" placeholder="Password" required><br>
            <select id="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
            </select><br>
            <button type="submit">Create User</button>
        </form>
        <div id="formMessage"></div>
    </div>

    <!-- User Table -->
    <div class="user-list">
        <h2>All Users</h2>
        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
            <?php foreach ($users as $user): ?>
                <tr data-id="<?= $user['id'] ?>">
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><button onclick="deleteUser(<?= $user['id'] ?>)">Delete</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const token = "<?= $_SESSION['token'] ?>"; // Inject PHP token into JS

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
        location.reload(); // Refresh to show new user
    } else {
        msg.innerText = result.message || 'Error creating user';
    }
});

async function deleteUser(id) {
    if (!confirm("Are you sure you want to delete this user?")) return;

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
</body>
