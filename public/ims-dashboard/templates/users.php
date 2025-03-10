<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch users data
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>User Management</h1>
        <button id="openAddUserForm">Add User</button>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="user-table"></tbody>
        </table>

        <!-- Add User Form -->
        <div id="addUserForm" style="display: none;">
            <h2>Add User</h2>
            <form id="user-form">
                <input type="text" id="name" placeholder="Name" required>
                <input type="email" id="email" placeholder="Email" required>
                <select id="role" required>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
                <input type="password" id="password" placeholder="Password" required>
                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addUserForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- Edit User Form -->
        <div id="editUserForm" style="display: none;">
            <h2>Edit User</h2>
            <form id="edit-user-form">
                <input type="hidden" id="edit_user_id">
                <input type="text" id="edit_name" placeholder="Name" required>
                <input type="email" id="edit_email" placeholder="Email" required>
                <select id="edit_role" required>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit">Update</button>
                <button type="button" onclick="document.getElementById('editUserForm').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../js/users.js"></script>
</body>
</html>
