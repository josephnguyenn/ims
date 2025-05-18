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
// Add this inside the `loadShipmentData()` function
function loadShipmentData() {
    fetch(`${BASE_URL}/api/shipments`, {
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(shipments => {
        let shipmentTable = document.getElementById("shipment-table");
        shipmentTable.innerHTML = "";

        shipments.forEach(shipment => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${shipment.id}</td>
                <td>${shipment.supplier?.name || 'Unknown'}</td>
                <td>${shipment.storage?.name || 'Unknown'}</td>
                <td>${shipment.order_date}</td>
                <td>${shipment.received_date || "N/A"}</td>
                <td>${shipment.expired_date || "N/A"}</td>
                <td>${shipment.cost}</td>
                <td>
                    <button onclick="window.location.href='products.php?shipment_id=${shipment.id}'">Quản lý hàng hóa</button>
                    <button onclick="deleteShipment(${shipment.id})">Xóa</button>
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

    fetch(`${BASE_URL}/api/shipments`, {
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
            closeModal('addShipmentForm');
            loadShipmentData();
        } else {
            alert("Error adding shipment.");
        }
    });
}

function deleteShipment(id) {
    if (!confirm("Are you sure you want to delete this shipment?")) return;

    fetch(`${BASE_URL}/api/shipments/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(async (response) => {
        const data = await response.json();

        if (response.ok) {
            alert(data.message || "Shipment deleted!");
            loadShipmentData();
        } else {
            alert(data.message || "Failed to delete shipment.");
        }
    })
    .catch(() => {
        alert("Something went wrong while trying to delete the shipment.");
    });
}


// ✅ Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
