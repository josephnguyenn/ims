document.addEventListener("DOMContentLoaded", function () {
    loadStorageData(); // ✅ Load storage data when page loads

    const storageForm = document.getElementById("storage-form");

    if (storageForm) {
        storageForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addStorage();
        });
    }
});

// ✅ Function to Load Storage Data
function loadStorageData() {
    fetch("http://localhost/ims/public/api/storages", {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(storages => {
        let storageTable = document.getElementById("storage-table");
        storageTable.innerHTML = ""; // ✅ Clear previous data

        if (storages.length === 0) {
            storageTable.innerHTML = "<tr><td colspan='4'>No storage locations found.</td></tr>";
            return;
        }

        storages.forEach(storage => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${storage.id}</td>
                <td>${storage.name}</td>
                <td>${storage.location}</td>
                <td>
                    <button onclick="editStorage(${storage.id}, '${storage.name}', '${storage.location}')">Edit</button>
                    <button onclick="deleteStorage(${storage.id})">Delete</button>
                </td>
            `;
            storageTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading storage data:", error));
}

// ✅ Function to Add Storage
function addStorage() {
    let name = document.getElementById("storage-name").value;
    let location = document.getElementById("storage-location").value;
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch("http://localhost/ims/public/api/storages", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({ name, location })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Storage created successfully") {
            alert("Storage added!");
            document.getElementById("storage-form").reset(); // ✅ Reset form
            document.getElementById("addStorageForm").style.display = "none";
            loadStorageData(); // ✅ Refresh table dynamically
        } else {
            alert("Error adding storage.");
        }
    });
}

// ✅ Function to Delete Storage
function deleteStorage(id) {
    if (!confirm("Are you sure you want to delete this storage?")) return;

    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`http://localhost/ims/public/api/storages/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
            "X-CSRF-TOKEN": csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Storage deleted successfully") {
            alert("Storage deleted!");
            loadStorageData(); // ✅ Refresh table dynamically
        } else {
            alert("Error deleting storage.");
        }
    });
}

// ✅ Function to Edit Storage
function editStorage(id, name, location) {
    let newName = prompt("Edit Storage Name:", name);
    let newLocation = prompt("Edit Storage Location:", location);

    if (newName !== null && newLocation !== null) {
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`http://localhost/ims/public/api/storages/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + sessionStorage.getItem("token"),
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ name: newName, location: newLocation })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === "Storage updated successfully") {
                alert("Storage updated!");
                loadStorageData(); // ✅ Refresh table dynamically
            } else {
                alert("Error updating storage.");
            }
        });
    }
}
