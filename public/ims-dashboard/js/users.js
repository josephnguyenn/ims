document.addEventListener("DOMContentLoaded", function () {
    loadUsers();

    document.getElementById("openAddUserForm").addEventListener("click", function () {
        document.getElementById("addUserForm").style.display = "block";
    });

    document.getElementById("user-form").addEventListener("submit", function (event) {
        event.preventDefault();
        addUser();
    });

    document.getElementById("edit-user-form").addEventListener("submit", function (event) {
        event.preventDefault();
        editUser();
    });
});

// ✅ Function to Load Users
function loadUsers() {
    fetch(`${BASE_URL}/api/users`, {
        headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(users => {
        let userTable = document.getElementById("user-table");
        userTable.innerHTML = "";

        users.forEach(user => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td>
                    <button onclick="openEditUserForm(${user.id}, '${user.name}', '${user.email}', '${user.role}')">Edit</button>
                    <button onclick="deleteUser(${user.id})">Delete</button>
                </td>
            `;
            userTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading users:", error));
}

// ✅ Function to Add User
function addUser() {
    let name = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let role = document.getElementById("role").value;
    let password = document.getElementById("password").value;

    fetch(`${BASE_URL}/api/users`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({ name, email, role, password })
    })
    .then(async response => {
        if (!response.ok) {
            const error = await response.text();
            console.error("Error response:", error);
            throw new Error("Failed to add user: " + response.status);
        }
        return response.json();
    })
    .then(() => {
        alert("User added!");
        document.getElementById("addUserForm").style.display = "none";
        loadUsers();
    })
    .catch(error => alert("Error adding user: " + error));
}


// ✅ Function to Open Edit User Form
function openEditUserForm(userId, name, email, role) {
    document.getElementById("edit_user_id").value = userId;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_role").value = role;

    document.getElementById("editUserForm").style.display = "block";
}

// ✅ Function to Edit User
function editUser() {
    let userId = document.getElementById("edit_user_id").value;
    let name = document.getElementById("edit_name").value;
    let email = document.getElementById("edit_email").value;
    let role = document.getElementById("edit_role").value;

    fetch(`${BASE_URL}/api/users/${userId}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({ name, email, role })
    })
    .then(() => {
        alert("User updated!");
        document.getElementById("editUserForm").style.display = "none";
        loadUsers();
    })
    .catch(error => console.error("Error updating user:", error));
}

// ✅ Function to Delete User
function deleteUser(userId) {
    if (!confirm("Are you sure you want to delete this user?")) return;

    fetch(`${BASE_URL}/api/users/${userId}`, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
    })
    .then(() => {
        alert("User deleted!");
        loadUsers();
    })
    .catch(error => console.error("Error deleting user:", error));
}


