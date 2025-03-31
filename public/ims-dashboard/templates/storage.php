<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch storage data from API
$apiUrl = "http://localhost/ims/public/api/storages";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $_SESSION['token'] // ✅ Include JWT token
]);
$response = curl_exec($ch);
curl_close($ch);

$storages = json_decode($response, true);

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>"> <!-- Add CSRF Token -->
</head>
<body>    
    <?php include "../includes/header.php"; ?>


<div class="main">
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <div class="main-content-header">
            <h1>Storage Management</h1>
            <button class="add-button" onclick="document.getElementById('addStorageForm').style.display='block'">Add Storage</button>
        </div>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="storage-table">
                <?php if (!empty($storages)): ?>
                    <?php foreach ($storages as $storage): ?>
                        <tr>
                            <td><?= htmlspecialchars($storage['id']) ?></td>
                            <td><?= htmlspecialchars($storage['name']) ?></td>
                            <td><?= htmlspecialchars($storage['location']) ?></td>
                            <td>
                                <button onclick="openEditForm(
                                    <?= $storage['id'] ?>,
                                    '<?= htmlspecialchars($storage['name'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($storage['location'], ENT_QUOTES) ?>'
                                )">Edit</button>
                                <button onclick="deleteStorage(<?= $storage['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No storage found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- Add Storage Form (Hidden) -->
        <div id="addStorageForm" style="display: none;">
            <h2>Add Storage</h2>
            <form id="storage-form">
                <input type="text" id="storage-name" placeholder="Storage Name" required>
                <input type="text" id="storage-location" placeholder="Location" required>
                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addStorageForm').style.display='none'">Cancel</button>
            </form>
        </div>

                <!-- Edit Storage Form (Hidden) -->
        <div id="editStorageForm" style="display: none;">
            <h2>Edit Storage</h2>
            <form id="edit-storage-form">
                <input type="hidden" id="edit-storage-id">
                <input type="text" id="edit-storage-name" placeholder="Storage Name" required>
                <input type="text" id="edit-storage-location" placeholder="Location" required>
                <button type="submit">Update</button>
                <button type="button" onclick="document.getElementById('editStorageForm').style.display='none'">Cancel</button>
            </form>
        </div>

    </div>
</div>
    <script src="../js/storage.js"></script>
</body>
</html>
