document.addEventListener("DOMContentLoaded", function () {
    loadShipmentData(); // ✅ Load shipments on page load

    const shipmentForm = document.getElementById("shipment-form");
    if (shipmentForm) {
        shipmentForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addShipment();
        });
    }
});

// ✅ Load Shipments and Populate Table
function loadShipmentData() {
    fetch("http://localhost/ims/public/api/shipments", {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(shipments => {
        let shipmentTable = document.getElementById("shipment-table");
        shipmentTable.innerHTML = "";

        if (shipments.length === 0) {
            shipmentTable.innerHTML = "<tr><td colspan='8'>No shipments found.</td></tr>";
            return;
        }

        shipments.forEach(shipment => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${shipment.id}</td>
                <td>${shipment.supplier ? shipment.supplier.name : 'Unknown'}</td>
                <td>${shipment.storage ? shipment.storage.name : 'Unknown'}</td>
                <td>${shipment.order_date}</td>
                <td>${shipment.received_date || "N/A"}</td>
                <td>${shipment.expired_date || "N/A"}</td>
                <td>${shipment.cost}</td>
                <td>
                    <button onclick="openEditShipmentModal(${shipment.id}, ${shipment.shipment_supplier_id}, ${shipment.storage_id}, '${shipment.order_date}', '${shipment.received_date}', '${shipment.expired_date}')">Edit</button>
                    <button onclick="deleteShipment(${shipment.id})">Delete</button>
                </td>
            `;
            shipmentTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading shipment data:", error));
}

// ✅ Function to Add Shipment
function addShipment() {
    let supplierId = document.getElementById("shipment_supplier_id").value;
    let storageId = document.getElementById("storage_id").value;
    let orderDate = document.getElementById("order_date").value;
    let receivedDate = document.getElementById("received_date").value || null;
    let expiredDate = document.getElementById("expired_date").value || null;
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch("http://localhost/ims/public/api/shipments", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
            shipment_supplier_id: supplierId,
            storage_id: storageId,
            order_date: orderDate,
            received_date: receivedDate,
            expired_date: expiredDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Shipment created successfully") {
            alert("Shipment added!");
            document.getElementById("shipment-form").reset();
            document.getElementById("addShipmentForm").style.display = "none";
            loadShipmentData();
        } else {
            alert("Error adding shipment.");
        }
    });
}

// ✅ Function to Delete Shipment
function deleteShipment(id) {
    if (!confirm("Are you sure you want to delete this shipment?")) return;

    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`http://localhost/ims/public/api/shipments/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
            "X-CSRF-TOKEN": csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Shipment deleted successfully") {
            alert("Shipment deleted!");
            loadShipmentData();
        } else {
            alert("Error deleting shipment.");
        }
    });
}

// ✅ Open Edit Shipment Modal
function openEditShipmentModal(id, supplierId, storageId, orderDate, receivedDate, expiredDate) {
    document.getElementById("edit_shipment_id").value = id;
    document.getElementById("edit_shipment_supplier_id").value = supplierId;
    document.getElementById("edit_storage_id").value = storageId;
    document.getElementById("edit_order_date").value = orderDate;
    document.getElementById("edit_received_date").value = receivedDate || "";
    document.getElementById("edit_expired_date").value = expiredDate || "";

    document.getElementById("editShipmentForm").style.display = "block";
}

// ✅ Function to Update Shipment
function updateShipment() {
    let id = document.getElementById("edit_shipment_id").value;
    let supplierId = document.getElementById("edit_shipment_supplier_id").value;
    let storageId = document.getElementById("edit_storage_id").value;
    let orderDate = document.getElementById("edit_order_date").value;
    let receivedDate = document.getElementById("edit_received_date").value || null;
    let expiredDate = document.getElementById("edit_expired_date").value || null;
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`http://localhost/ims/public/api/shipments/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
            shipment_supplier_id: supplierId,
            storage_id: storageId,
            order_date: orderDate,
            received_date: receivedDate,
            expired_date: expiredDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Shipment updated successfully") {
            alert("Shipment updated!");
            document.getElementById("editShipmentForm").style.display = "none";
            loadShipmentData();
        } else {
            alert("Error updating shipment.");
        }
    });
}
