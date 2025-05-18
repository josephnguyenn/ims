document.addEventListener("DOMContentLoaded", function () {
    loadShipmentSuppliers();

    const supplierForm = document.getElementById("shipment-supplier-form");
    if (supplierForm) {
        supplierForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addShipmentSupplier();
        });
    }
});

// ✅ Tải danh sách Nhà cung cấp lô hàng vào bảng
function loadShipmentSuppliers() {
    fetch(`${BASE_URL}/api/shipment-suppliers`, {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(suppliers => {
        let supplierTable = document.getElementById("shipment-supplier-table");
        supplierTable.innerHTML = "";

        if (suppliers.length === 0) {
            supplierTable.innerHTML = "<tr><td colspan='3'>Không tìm thấy Nhà cung cấp nào.</td></tr>";
            return;
        }

        suppliers.forEach(supplier => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${supplier.id}</td>
                <td>${supplier.name}</td>
                <td>
                    <button onclick="openEditSupplierModal(${supplier.id}, '${supplier.name}')">Sửa</button>
                    <button onclick="deleteShipmentSupplier(${supplier.id})">Xóa</button>
                </td>
            `;
            supplierTable.appendChild(row);
        });
    })
    .catch(error => console.error("❌ Lỗi khi tải danh sách Nhà cung cấp lô hàng:", error));
}

// ✅ Hàm thêm Nhà cung cấp lô hàng
function addShipmentSupplier() {
    let name = document.getElementById("supplier_name").value;

    fetch(`${BASE_URL}/api/shipment-suppliers`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name })
    })
    .then(response => response.json())
    .then(() => {
        alert("Thêm Nhà cung cấp thành công!");
        document.getElementById("addShipmentSupplierForm").style.display = "none";
        loadShipmentSuppliers();
    });
}

// ✅ Hàm mở modal chỉnh sửa Nhà cung cấp lô hàng
function openEditSupplierModal(id, name) {
    document.getElementById("edit_supplier_id").value = id;
    document.getElementById("edit_supplier_name").value = name;

    document.getElementById("editShipmentSupplierForm").style.display = "block";
}

// ✅ Hàm cập nhật Nhà cung cấp lô hàng
function updateShipmentSupplier() {
    let id = document.getElementById("edit_supplier_id").value;
    let name = document.getElementById("edit_supplier_name").value;

    fetch(`${BASE_URL}/api/shipment-suppliers/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name })
    })
    .then(() => {
        alert("Cập nhật Nhà cung cấp thành công!");
        document.getElementById("editShipmentSupplierForm").style.display = "none";
        loadShipmentSuppliers();
    });
}

// ✅ Hàm xóa Nhà cung cấp lô hàng
function deleteShipmentSupplier(id) {
    if (!confirm("Bạn có chắc chắn muốn xóa Nhà cung cấp này không?")) return;

    fetch(`${BASE_URL}/api/shipment-suppliers/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Shipment Supplier deleted successfully") {
            alert("Xóa Nhà cung cấp thành công!");
            loadShipmentSuppliers();
        } else {
            alert("❌ Lỗi khi xóa Nhà cung cấp: " + (data.message || "Lỗi không xác định"));
        }
    })
    .catch(error => console.error("❌ Lỗi khi xóa Nhà cung cấp:", error));
}