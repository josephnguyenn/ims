<?php
if (!isset($_SESSION)) {
    session_start();
}
?>  


<div class="header-bar">
    <a href="../templates/dashboard.php" class="header-logo-link">
        <img src="../uploads/images/logo.png" alt="Tappo Market" class="header-logo">
    </a>
    <div class="header-actions">
        <span class="user-greeting">
            Xin chào, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></strong>
        </span>
        <div class="header-dropdown">
            <button class="dropdown-btn">Tùy chọn</button>
            <div class="dropdown-content">
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
    </div>
</div>


<style>
.header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #CCE0FF;
    padding: 15px 30px;
    color: #1a4ba8;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.header-logo{
width: 200px;
    height: 80px;
    object-fit: cover;
}

.header-bar .header-actions {
    display: flex;
    gap: 20px;
    align-items: center;
    font-size: 16px;
}

.header-dropdown {
    position: relative;
}

.dropdown-btn {
    background-color: #1a4ba8;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    z-index: 1;
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.header-dropdown:hover .dropdown-content {
    display: block;
}

.user-greeting {
    font-size: 16px;
}
</style>

