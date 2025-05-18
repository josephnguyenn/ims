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

    fetch(`${BASE_URL}/api/order-products/${orderId}`, {
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

        orderProducts.forEach(item => {
            const product = item.product ?? null;
            const outOfStock = product?.actual_quantity === 0;
        
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${product ? product.name : 'N/A'}</td>
                <td>${item.quantity}</td>
                <td>${product ? product.price : '0.00'}CZK</td>
                <td>${product ? (product.price * item.quantity).toFixed(2) : '0.00'}CZK</td>
                <td>${product ? 'Shipment #' + product.shipment_id : 'N/A'}</td>
                <td>
                    <button ${outOfStock ? "disabled title='Out of Stock'" : `onclick="openEditOrderProductForm(${item.id}, ${item.quantity})"`}>
                        Chỉnh sửa
                    </button>
                    <button onclick="deleteOrderProduct(${item.id}, ${orderId})">Xóa</button>
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

    fetch(`${BASE_URL}/api/order-products`, {
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
        // loadOrderProducts();
        window.location.reload(); // ✅ full page reload
        //load the table
    });
}

// ✅ Function to Open Edit Order Product Form
// ✅ Unified Function to Open and Optionally Fetch Edit Order Product Form
function openEditOrderProductForm(orderProductId, quantity, fetchDetails = false) {
    const editForm = document.getElementById("editProductForm");
    if (!editForm) {
        console.error("❌ Edit Product Form Not Found!");
        return;
    }

    // Fetch product details if required
    if (fetchDetails) {
        fetch(`${BASE_URL}/api/order-products/${orderProductId}`, {
            headers: {
                "Authorization": "Bearer " + sessionStorage.getItem("token")
            }
        })
        .then(res => res.json())
        .then(data => {
            const product = data.product ?? null;
            if (!product) {
                alert("❌ Cannot edit this product. Data not found.");
                return;
            }
            if (product.actual_quantity === 0) {
                alert("⚠️ This product is out of stock and cannot be edited.");
                return;
            }
            setupEditForm(orderProductId, quantity, product);
        })
        .catch(error => console.error("Error fetching product details:", error));
    } else {
        // Directly setup the form
        setupEditForm(orderProductId, quantity);
    }
}

function setupEditForm(orderProductId, quantity, product = null) {
    document.getElementById("edit_order_product_id").value = orderProductId;
    document.getElementById("edit_quantity").value = quantity;
    document.getElementById("editProductForm").style.display = "block";

    // Additional setup if product data is fetched
    if (product) {
        // Populate form fields if needed
    }
}

// ✅ Function to Edit Product in Order
function editProductInOrder() {
    const orderId = document.getElementById("edit_order_id").value;
    const orderProductId = document.getElementById("edit_order_product_id").value;
    const quantity = document.getElementById("edit_quantity").value;
    
    // Assuming you have inputs for price or other attributes you wish to update
    // const price = document.getElementById("edit_price").value; 

    fetch(`${BASE_URL}/api/order-products/${orderProductId}`, {
        method: "PUT", // or "PATCH" depending on how your API is setup
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({
            order_id: orderId,
            quantity: quantity
            // price: price // include this if you're updating price or any other details
        })
    })
    .then(response => response.json())
    .then(data => {
        alert("Product updated successfully!");
        window.location.reload(); // Reload the page to reflect the changes
    })
    .catch(error => {
        console.error("Error updating product:", error);
        alert("Failed to update product.");
    });
}




// ✅ Function to Delete Product from Order
function deleteOrderProduct(orderProductId, orderId) {
    if (!confirm("Are you sure you want to remove this product from the order?")) return;

    fetch(`${BASE_URL}/api/order-products/${orderProductId}`, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(() => {
        alert("Product removed!");
        // loadOrderProducts();
        window.location.reload(); // ✅ full page reload

    })
    .catch(error => console.error("❌ Error deleting product:", error));
}

// ✅ Function to Update Total Price Dynamically
function updateTotalPrice() {
    let quantity = document.getElementById("quantity").value;
    let price = document.getElementById("price").value;
    let totalPrice = document.getElementById("total_price");

    if (quantity && price) {
        totalPrice.textContent = `${(quantity * price).toFixed(2)}CZK`;
    } else {
        totalPrice.textContent = "0.00CZK";
    }
}

// ✅ Function to Update Edit Total Price Dynamically
function updateEditTotalPrice() {
    let quantity = document.getElementById("edit_quantity").value;
    let price = document.getElementById("edit_price").value;
    let totalPrice = document.getElementById("edit_total_price");

    if (quantity && price) {
        totalPrice.textContent = `${(quantity * price).toFixed(2)}CZK`;
    } else {
        totalPrice.textContent = "0.00CZK";
    }
}