document.addEventListener("DOMContentLoaded", function () {
    loadOrderProducts();

    let addProductButton = document.getElementById("openAddProductForm");
    if (addProductButton) {
        addProductButton.addEventListener("click", openAddProductForm);
    } else {
        console.error("❌ Add Product Button Not Found!");
    }

    let orderProductForm = document.getElementById("order-product-form");
    if (orderProductForm) {
        orderProductForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addProductToOrder();
        });
    } else {
        console.error("❌ Order Product Form Not Found!");
    }

    let editOrderProductForm = document.getElementById("edit-order-product-form");
    if (editOrderProductForm) {
        editOrderProductForm.addEventListener("submit", function (event) {
            event.preventDefault();
            editProductInOrder();
        });
    } else {
        console.error("❌ Edit Order Product Form Not Found!");
    }
});

// ✅ Function to Load Order Products
function loadOrderProducts() {
    let orderId = document.getElementById("order_id").value;

    fetch(`http://localhost/ims/public/api/order-products/${orderId}`, {
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(orderProducts => {
        let productTable = document.getElementById("order-product-table");
        productTable.innerHTML = "";

        if (!Array.isArray(orderProducts) || orderProducts.length === 0) {
            productTable.innerHTML = "<tr><td colspan='5'>No products in this order.</td></tr>";
            return;
        }

        orderProducts.forEach(product => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${product.product.name}</td>
                <td>${product.quantity}</td>
                <td>$${product.price}</td>
                <td>$${(product.quantity * product.price).toFixed(2)}</td>
                <td>
                    <button onclick="openEditOrderProductForm(${product.id}, ${product.quantity})">Edit</button>
                    <button onclick="deleteOrderProduct(${product.id}, ${orderId})">Delete</button>
                </td>
            `;
            productTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading order products:", error));
}

// ✅ Function to Open Add Product Form
function openAddProductForm() {
    let addForm = document.getElementById("addProductForm");
    if (addForm) {
        addForm.style.display = "block";
        console.log("✅ Add Product Form Opened");
    } else {
        console.error("❌ Add Product Form Not Found!");
    }
}

// ✅ Function to Add Product to Order
function addProductToOrder() {
    let orderId = document.getElementById("order_id").value;
    let productId = document.getElementById("product_id").value;
    let quantity = document.getElementById("quantity").value;

    if (!productId || !quantity) {
        alert("Please select a product and enter a quantity.");
        return;
    }

    fetch("http://localhost/ims/public/api/order-products", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ order_id: orderId, product_id: productId, quantity: quantity })
    })
    .then(() => {
        alert("Product added!");
        document.getElementById("addProductForm").style.display = "none";
        loadOrderProducts();
    });
}

// ✅ Function to Open Edit Order Product Form
function openEditOrderProductForm(orderProductId, quantity) {
    let editForm = document.getElementById("editProductForm");
    if (!editForm) {
        console.error("❌ Edit Product Form Not Found!");
        return;
    }

    document.getElementById("edit_order_product_id").value = orderProductId;
    document.getElementById("edit_quantity").value = quantity;

    editForm.style.display = "block";
}

// ✅ Function to Edit Product in Order
function editProductInOrder() {
    let orderProductId = document.getElementById("edit_order_product_id").value;
    let quantity = document.getElementById("edit_quantity").value;

    if (!quantity) {
        alert("Please enter a quantity.");
        return;
    }

    fetch(`http://localhost/ims/public/api/order-products/${orderProductId}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        alert("Product updated!");
        document.getElementById("editProductForm").style.display = "none";
        loadOrderProducts();
    })
    .catch(error => console.error("❌ Error updating product:", error));
}

// ✅ Function to Delete Product from Order
function deleteOrderProduct(orderProductId, orderId) {
    if (!confirm("Are you sure you want to remove this product from the order?")) return;

    fetch(`http://localhost/ims/public/api/order-products/${orderProductId}`, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(() => {
        alert("Product removed!");
        loadOrderProducts();
    })
    .catch(error => console.error("❌ Error deleting product:", error));
}