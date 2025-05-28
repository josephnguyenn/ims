<?php
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',             // <— make it valid site-wide
  'domain'   => $_SERVER['HTTP_HOST'],
  'secure'   => isset($_SERVER['HTTPS']),
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();
include "../define.php";

// Redirect to POS if already logged in
if (isset($_SESSION['token']) && isset($_SESSION['role'])) {
    header("Location: pos.php");
    exit;
}

$base_url = BASE_URL; // Ensure this is correctly set in define.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Login</title>
    <style>
        body { font-family: Arial; text-align: center; background: #E8F1FF; padding: 100px; }
        .login-box { background: #fff; padding: 30px; border-radius: 8px; display: inline-block; }
        input { padding: 10px; width: 200px; margin-bottom: 10px; }
        button { padding: 10px 20px; background: #94B9F1; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #7da9ee; }
        .error { color: red; margin-top: 20px; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>POS Login</h2>
    <form id="login-form">
        <input type="text" id="email" placeholder="Email" required><br>
        <input type="password" id="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p id="error-message" class="error"></p>
</div>

<script>
    const BASE_URL = "<?= $base_url; ?>";

    document.getElementById("login-form").addEventListener("submit", function(event) {
        event.preventDefault();

        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        fetch(`${BASE_URL}/api/login`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.token && data.user && data.user.role) {
                // Save session in PHP via AJAX
                fetch("session_store.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({
                        token: data.token,
                        role: data.user.role,
                        name: data.user.name
                    })
                })
                .then(response => response.json())
                .then(sessionData => {
                    if (sessionData.status === "success") {
                        window.location.href = "pos.php";
                    } else {
                        document.getElementById("error-message").innerText = "Session Error!";
                    }
                });
            } else {
                document.getElementById("error-message").innerText = data.message || "Invalid Login!";
            }
        })
        .catch(error => {
            console.error("Error:", error);
            document.getElementById("error-message").innerText = "Login Failed!";
        });
    });
</script>

</body>
</html>
