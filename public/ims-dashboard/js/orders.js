let allOrders = [];
const ORDERS_PER_PAGE = 10;

document.addEventListener("DOMContentLoaded", function () {
    const currentPage = getPageFromURL();
    loadOrders(currentPage);

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
    }
});

function getPageFromURL() {
    const params = new URLSearchParams(window.location.search);
    return parseInt(params.get("page")) || 1;
}

function loadOrders(page = 1) {
    fetch(`${BASE_URL}/api/orders`, {
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(res => res.json())
    .then(data => {
        allOrders = data.reverse(); // ✅ Newest orders first
        renderOrders(page);
        renderPagination(page);
    });
}


function renderOrders(page) {
    const orderTable = document.getElementById("order-table");
    orderTable.innerHTML = "";

    const start = (page - 1) * ORDERS_PER_PAGE;
    const paginated = allOrders.slice(start, start + ORDERS_PER_PAGE);

    if (paginated.length === 0) {
        orderTable.innerHTML = "<tr><td colspan='6'>No orders found.</td></tr>";
        return;
    }

    paginated.forEach(order => {
        const row = document.createElement("tr");
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
}

function renderPagination(currentPage) {
    const totalPages = Math.ceil(allOrders.length / ORDERS_PER_PAGE);
    const container = document.querySelector(".pagination");
    container.innerHTML = "";

    for (let i = 1; i <= totalPages; i++) {
        const link = document.createElement("a");
        link.href = `?page=${i}`;
        link.textContent = i;
        if (i === currentPage) link.classList.add("active");
        container.appendChild(link);
    }
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

    fetch(`${BASE_URL}/api/orders`, {
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

    fetch(`${BASE_URL}/api/orders/${orderId}`, {
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
function deleteOrder(orderId) {
    if (!confirm("Are you sure you want to delete this order?")) return;

    fetch(`${BASE_URL}/api/orders/${orderId}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token"),
            "Accept": "application/json"
        }
    })
    .then(async response => {
        const contentType = response.headers.get("content-type");

        if (!response.ok) {
            let message = "Something went wrong.";

            if (contentType && contentType.includes("application/json")) {
                const errorData = await response.json();
                message = errorData.message || message;
            } else {
                const errorText = await response.text();
                console.error("Server error:", errorText);
            }

            alert(message);
            return;
        }

        const data = await response.json();
        alert(data.message || "Order deleted!");
        loadOrders(); // reload orders after success
    })
    .catch(error => {
        console.error("Error deleting order:", error);
        alert("Error deleting order: " + error.message);
    });
}
