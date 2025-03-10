document.addEventListener("DOMContentLoaded", function () {
    loadOrders();

    document.getElementById("openAddOrderForm").addEventListener("click", function () {
        document.getElementById("addOrderForm").style.display = "block";
    });

    document.getElementById("order-form").addEventListener("submit", function (event) {
        event.preventDefault();
        addOrder();
    });

    // ✅ Add Event Listener for Edit Form Submission
    const editOrderForm = document.getElementById("edit-order-form");
    if (editOrderForm) {
        editOrderForm.addEventListener("submit", function (event) {
            event.preventDefault();
            editOrder();
        });
    } else {
        console.error("❌ Edit Order Form not found!");
    }
});

// ✅ Load Orders
function loadOrders() {
    fetch("http://localhost/ims/public/api/orders", {
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(orders => {
        let orderTable = document.getElementById("order-table");
        orderTable.innerHTML = "";

        if (orders.length === 0) {
            orderTable.innerHTML = "<tr><td colspan='6'>No orders found.</td></tr>";
            return;
        }

        orders.forEach(order => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${order.id}</td>
                <td>${order.customer.name}</td>
                <td>${order.delivery_supplier.name}</td>
                <td>$${order.total_price}</td>
                <td>$${order.paid_amount}</td>
                <td>
                    <button onclick="window.location.href='order-products.php?order_id=${order.id}'">Manage Products</button>
                    <button onclick="openEditOrderForm(${order.id}, ${order.delivery_supplier.id}, ${order.paid_amount})">Edit</button>
                    <button onclick="deleteOrder(${order.id})">Delete</button>
                </td>
            `;
            orderTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading orders:", error));
}

// ✅ Add Order
function addOrder() {
    let customerId = document.getElementById("customer_id").value;
    let deliverySupplierId = document.getElementById("delivery_supplier_id").value;

    fetch("http://localhost/ims/public/api/orders", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ customer_id: customerId, delivery_supplier_id: deliverySupplierId })
    })
    .then(() => {
        alert("Order added!");
        document.getElementById("addOrderForm").style.display = "none";
        loadOrders();
    });
}

// ✅ Open Edit Order Form
function openEditOrderForm(orderId, deliverySupplierId, paidAmount) {
    document.getElementById("edit_order_id").value = orderId;
    document.getElementById("edit_delivery_supplier_id").value = deliverySupplierId;
    document.getElementById("edit_paid_amount").value = paidAmount;
    document.getElementById("editOrderForm").style.display = "block";
}

// ✅ Edit Order
function editOrder() {
    let orderId = document.getElementById("edit_order_id").value;
    let deliverySupplierId = document.getElementById("edit_delivery_supplier_id").value;
    let paidAmount = document.getElementById("edit_paid_amount").value;

    fetch(`http://localhost/ims/public/api/orders/${orderId}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ delivery_supplier_id: deliverySupplierId, paid_amount: paidAmount })
    })
    .then(() => {
        alert("Order updated!");
        document.getElementById("editOrderForm").style.display = "none";
        loadOrders();
    });
}

// ✅ Delete Order
function deleteOrder(orderId) {
    if (!confirm("Are you sure you want to delete this order?")) return;

    fetch(`http://localhost/ims/public/api/orders/${orderId}`, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(() => {
        alert("Order deleted!");
        loadOrders();
    });
}