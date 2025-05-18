document.addEventListener("DOMContentLoaded", function () {
    loadDeliverySuppliers();

    const deliverySupplierForm = document.getElementById("delivery-supplier-form");
    if (deliverySupplierForm) {
        deliverySupplierForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addDeliverySupplier();
        });
    }
});

// ✅ Load Delivery Suppliers into Table
function loadDeliverySuppliers() {
    fetch(`${BASE_URL}/api/delivery-suppliers`, {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(suppliers => {
        let supplierTable = document.getElementById("delivery-supplier-table");
        supplierTable.innerHTML = "";

        if (suppliers.length === 0) {
            supplierTable.innerHTML = "<tr><td colspan='3'>No delivery suppliers found.</td></tr>";
            return;
        }

        suppliers.forEach(supplier => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${supplier.id}</td>
                <td>${supplier.name}</td>
                <td>
                    <button onclick="openEditDeliverySupplierModal(${supplier.id}, '${supplier.name}')">Sửa</button>
                    <button onclick="deleteDeliverySupplier(${supplier.id})">Xóa</button>
                </td>
            `;
            supplierTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading suppliers:", error));
}

// ✅ Function to Add Delivery Supplier
function addDeliverySupplier() {
    let name = document.getElementById("delivery_supplier_name").value;

    fetch(`${BASE_URL}/api/delivery-suppliers`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name })
    })
    .then(response => response.json())
    .then(() => {
        alert("Delivery Supplier added!");
        document.getElementById("addDeliverySupplierForm").style.display = "none";
        loadDeliverySuppliers();
    });
}

// ✅ Function to Open Edit Delivery Supplier Modal
function openEditDeliverySupplierModal(id, name) {
    document.getElementById("edit_delivery_supplier_id").value = id;
    document.getElementById("edit_delivery_supplier_name").value = name;
    document.getElementById("editDeliverySupplierForm").style.display = "block";
}

// ✅ Function to Update Delivery Supplier
function updateDeliverySupplier() {
    let id = document.getElementById("edit_delivery_supplier_id").value;
    let name = document.getElementById("edit_delivery_supplier_name").value;

    fetch(`${BASE_URL}/api/delivery-suppliers/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name })
    })
    .then(() => {
        alert("Delivery Supplier updated!");
        document.getElementById("editDeliverySupplierForm").style.display = "none";
        loadDeliverySuppliers();
    });
}

// ✅ Function to Delete Delivery Supplier
function deleteDeliverySupplier(id) {
    if (!confirm("Are you sure you want to delete this supplier?")) return;

    fetch(`${BASE_URL}/api/delivery-suppliers/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(() => {
        alert("Delivery Supplier deleted!");
        loadDeliverySuppliers();
    });
}
