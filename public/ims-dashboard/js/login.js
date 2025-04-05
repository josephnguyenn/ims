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
        console.log("Response from /public/api/login:", data); // Make sure you see the role here
        if (data.token && data.user && data.user.role) { // Check if user and role are present
            sessionStorage.setItem("token", data.token);

            fetch("session_store.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ token: data.token, role: data.user.role }) // Send both token and role
            })
            .then(response => response.json()) // Expect JSON response from session_store.php
            .then(sessionData => {
                console.log("Response from session_store.php:", sessionData); // Check the response
                if (sessionData.status === "success") {
                    window.location.href = "templates/dashboard.php";
                } else {
                    document.getElementById("error-message").innerText = sessionData.message || "Session Error!";
                }
            });
        } else {
            document.getElementById("error-message").innerText = data.message || "Lỗi Đăng nhập!";
        }
    })
    .catch(error => console.error("Error:", error));
});