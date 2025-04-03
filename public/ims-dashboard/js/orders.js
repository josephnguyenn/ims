document.addEventListener("DOMContentLoaded", function () {
    loadOrders();

    document.getElementById("order-form").addEventListener("submit", function (event) {
        event.preventDefault();
        addOrder();
    });

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

// ✅ Function to Load Orders
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
                <td>${order.total_price}Kč</td>
                <td>${order.paid_amount}Kč</td>
                <td>
                    <button onclick="window.location.href='order-products.php?order_id=${order.id}'">Quản lý sản phẩm</button>
                    <button onclick="openEditOrderForm(${order.id}, ${order.delivery_supplier.id}, ${order.paid_amount})">Sửa</button>
                    <button onclick="deleteOrder(${order.id})">Xóa</button>
                </td>
            `;
            orderTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading orders:", error));
}

// ✅ Open Modal
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

// ✅ Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
// ✅ Function to Add Order
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
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text) });
        }
        return response.json();
    })
    .then(data => {
        alert("Order added!");
        closeModal('addOrderModal');
        loadOrders();
    })
    .catch(error => {
        console.error("Error adding order:", error);
        alert("Error adding order: " + error.message);
    });
}

// ✅ Function to Open Edit Order Form
function openEditOrderForm(orderId, deliverySupplierId, paidAmount) {
    document.getElementById("edit_order_id").value = orderId;
    document.getElementById("edit_delivery_supplier_id").value = deliverySupplierId;
    document.getElementById("edit_paid_amount").value = paidAmount;
    openModal('editOrderModal');
}

// ✅ Function to Edit Order
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
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text) });
        }
        return response.json();
    })
    .then(data => {
        alert("Order updated!");
        closeModal('editOrderModal');
        loadOrders();
    })
    .catch(error => {
        console.error("Error updating order:", error);
        alert("Error updating order: " + error.message);
    });
}

// ✅ Function to Delete Order
function deleteOrder(orderId) {
    if (!confirm("Are you sure you want to delete this order?")) return;

    fetch(`http://localhost/ims/public/api/orders/${orderId}`, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(() => {
        alert("Order deleted!");
        loadOrders();
    })
    .catch(error => console.error("Error deleting order:", error));
}