document.getElementById("login-form").addEventListener("submit", function(event) {
    event.preventDefault();

    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    fetch("http://localhost/ims/public/api/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ email, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            sessionStorage.setItem("token", data.token);

            // ✅ Send token to PHP session
            fetch("session_store.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ token: data.token })
            })
            .then(() => {
                window.location.href = "templates/dashboard.php";
            });

        } else {
            document.getElementById("error-message").innerText = data.message || "Lỗi Đăng nhập!";
        }
    })
    .catch(error => console.error("Error:", error));
});
