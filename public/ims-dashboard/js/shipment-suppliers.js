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

// ✅ Load Shipment Suppliers into Table
function loadShipmentSuppliers() {
    fetch("http://localhost/ims/public/api/shipment-suppliers", {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(suppliers => {
        let supplierTable = document.getElementById("shipment-supplier-table");
        supplierTable.innerHTML = "";

        if (suppliers.length === 0) {
            supplierTable.innerHTML = "<tr><td colspan='3'>No suppliers found.</td></tr>";
            return;
        }

        suppliers.forEach(supplier => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${supplier.id}</td>
                <td>${supplier.name}</td>
                <td>
                    <button onclick="openEditSupplierModal(${supplier.id}, '${supplier.name}')">Edit</button>
                    <button onclick="deleteShipmentSupplier(${supplier.id})">Delete</button>
                </td>
            `;
            supplierTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading shipment suppliers:", error));
}

// ✅ Function to Add Shipment Supplier
function addShipmentSupplier() {
    let name = document.getElementById("supplier_name").value;

    fetch("http://localhost/ims/public/api/shipment-suppliers", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name })
    })
    .then(response => response.json())
    .then(() => {
        alert("Supplier added!");
        document.getElementById("addShipmentSupplierForm").style.display = "none";
        loadShipmentSuppliers();
    });
}

// ✅ Function to Open Edit Shipment Supplier Modal
function openEditSupplierModal(id, name) {
    document.getElementById("edit_supplier_id").value = id;
    document.getElementById("edit_supplier_name").value = name;

    document.getElementById("editShipmentSupplierForm").style.display = "block";
}

// ✅ Function to Update Shipment Supplier
function updateShipmentSupplier() {
    let id = document.getElementById("edit_supplier_id").value;
    let name = document.getElementById("edit_supplier_name").value;

    fetch(`http://localhost/ims/public/api/shipment-suppliers/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ name })
    })
    .then(() => {
        alert("Supplier updated!");
        document.getElementById("editShipmentSupplierForm").style.display = "none";
        loadShipmentSuppliers();
    });
}

// ✅ Function to Delete Shipment Supplier
function deleteShipmentSupplier(id) {
    if (!confirm("Are you sure you want to delete this supplier?")) return;

    fetch(`http://localhost/ims/public/api/shipment-suppliers/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Shipment Supplier deleted successfully") {
            alert("Supplier deleted!");
            loadShipmentSuppliers();
        } else {
            alert("Error deleting supplier: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => console.error("Error deleting supplier:", error));
}
