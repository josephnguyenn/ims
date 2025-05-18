document.addEventListener("DOMContentLoaded", function () {
    loadStorageData(); // ✅ Tải dữ liệu kho khi trang được tải

    const storageForm = document.getElementById("storage-form");

    if (storageForm) {
        storageForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addStorage();
        });
    }
});

// ✅ Hàm tải dữ liệu kho
function loadStorageData() {
    fetch(`${BASE_URL}/api/storages`, {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(storages => {
        let storageTable = document.getElementById("storage-table");
        storageTable.innerHTML = ""; // ✅ Xóa dữ liệu cũ

        if (storages.length === 0) {    
            storageTable.innerHTML = "<tr><td colspan='4'>Không tìm thấy kho</td></tr>";
            return;
        }

        storages.forEach(storage => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${storage.id}</td>
                <td>${storage.name}</td>
                <td>${storage.location}</td>
                <td>
                    <button onclick="openEditForm(${storage.id}, '${storage.name}', '${storage.location}')">Sửa</button>
                    <button onclick="deleteStorage(${storage.id})">Xóa</button>
                </td>
            `;
            storageTable.appendChild(row);
        });
    })
    .catch(error => console.error("❌ Lỗi khi tải dữ liệu kho:", error));
}

// ✅ Hàm thêm kho
function addStorage() {
    let name = document.getElementById("storage-name").value;
    let location = document.getElementById("storage-location").value;
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`${BASE_URL}/api/storages`, {
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
            alert("Thêm kho thành công!");
            document.getElementById("storage-form").reset(); // ✅ Reset form
            document.getElementById("addStorageForm").style.display = "none";
            loadStorageData(); // ✅ Làm mới bảng dữ liệu
        } else {
            alert("❌ Lỗi khi thêm kho.");
        }
    });
}

// ✅ Hàm xóa kho
function deleteStorage(id) {
    if (!confirm("Bạn có chắc chắn muốn xóa kho này không?")) return;

    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`${BASE_URL}/api/storages/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
            "X-CSRF-TOKEN": csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Storage deleted successfully") {
            alert("Xóa kho thành công!");
            loadStorageData(); // ✅ Làm mới bảng dữ liệu
        } else {
            alert("❌ Lỗi khi xóa kho.");
        }
    });
}

// Mở form sửa kho
function openEditForm(id, name, location) {
    document.getElementById("edit-storage-id").value = id;
    document.getElementById("edit-storage-name").value = name;
    document.getElementById("edit-storage-location").value = location;
    document.getElementById("editStorageForm").style.display = 'block';
}

// Xử lý cập nhật kho
document.addEventListener("DOMContentLoaded", function () {
    const editForm = document.getElementById("edit-storage-form");
    if (editForm) {
        editForm.addEventListener("submit", function (event) {
            event.preventDefault();
            updateStorage();
        });
    }
});

function updateStorage() {
    const id = document.getElementById("edit-storage-id").value;
    const name = document.getElementById("edit-storage-name").value;
    const location = document.getElementById("edit-storage-location").value;

    fetch(`${BASE_URL}/api/storages/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name, location })
    })
    .then(res => res.json())
    .then(data => {
        alert("Cập nhật kho thành công!");
        window.location.reload();
    })
    .catch(error => {
        console.error("❌ Lỗi khi cập nhật kho:", error);
        alert("❌ Không thể cập nhật kho.");
    });
}