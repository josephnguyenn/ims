document.getElementById("login-form").addEventListener("submit", function(event) {
    event.preventDefault();

    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    // Log the data being sent
    console.log("Sending login request with data:", { email, password });

    // Update the API endpoint to use the correct path
    fetch("/ims/public/api/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({ email, password })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(JSON.stringify(err));
            });
        }
        return response.json();
    })
    .then(data => {
        console.log("Response from /api/login:", data);
        if (data.token && data.user && data.user.role) {
            localStorage.setItem("token", data.token);

            fetch("session_store.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({ 
                    token: data.token, 
                    role: data.user.role,
                    user_id: data.user.id,
                    name: data.user.name
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Session store failed');
                }
                return response.json();
            })
            .then(sessionData => {
                console.log("Session store response:", sessionData);
                if (sessionData.status === "success") {
                    window.location.href = "templates/dashboard.php";
                } else {
                    document.getElementById("error-message").innerText = sessionData.message || "Session Error!";
                }
            })
            .catch(error => {
                console.error("Session store error:", error);
                document.getElementById("error-message").innerText = "Lỗi khi lưu phiên đăng nhập!";
            });
        } else {
            document.getElementById("error-message").innerText = data.message || "Lỗi Đăng nhập!";
        }
    })
    .catch(error => {
        console.error("Login error:", error);
        try {
            const errorData = JSON.parse(error.message);
            if (errorData.errors) {
                // Display validation errors
                const errorMessages = Object.values(errorData.errors).flat();
                document.getElementById("error-message").innerText = errorMessages.join("\n");
            } else {
                document.getElementById("error-message").innerText = errorData.message || "Lỗi kết nối đến máy chủ!";
            }
        } catch (e) {
            document.getElementById("error-message").innerText = "Lỗi kết nối đến máy chủ!";
        }
    });
});