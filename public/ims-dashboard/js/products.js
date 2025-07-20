function updateQuantityUnit() {
    const isWeighted = document.getElementById("is_weighted").checked;
    const quantityUnit = document.getElementById("quantity_unit");
    if (quantityUnit) {
        quantityUnit.textContent = isWeighted ? 'kg' : 'sản phẩm';
    }
}

function updateEditQuantityUnit() {
    const isWeighted = document.getElementById("edit_is_weighted").checked;
    const quantityUnit = document.getElementById("edit_quantity_unit");
    if (quantityUnit) {
        quantityUnit.textContent = isWeighted ? 'kg' : 'sản phẩm';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const isWeightedCheckbox = document.getElementById("is_weighted");
    if (isWeightedCheckbox) {
        isWeightedCheckbox.addEventListener("change", updateQuantityUnit);
    }

    const editIsWeightedCheckbox = document.getElementById("edit_is_weighted");
    if (editIsWeightedCheckbox) {
        editIsWeightedCheckbox.addEventListener("change", updateEditQuantityUnit);
    }

    const productForm = document.getElementById("product-form");
    if (productForm) {
        productForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addProduct();
        });
    }

    const editForm = document.getElementById("edit-product-form");
    if (editForm) {
        editForm.addEventListener("submit", function (event) {
            event.preventDefault();
            updateProduct();
        });
    }  
    loadCategoryOptions("category_id");
    loadCategoryOptions("edit_category_id");  
});


function getShipmentIdFromURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('shipment_id');
}

    function loadProductData() {
        const shipmentId = getShipmentIdFromURL();
        const productTable = document.getElementById("product-table");

        if (!productTable) {
            console.error("❌ Product table element not found.");
            return;
        }

        const url = shipmentId
            ? `${BASE_URL}/api/products?shipment_id=${shipmentId}`
            : `${BASE_URL}/api/products`;

        fetch(url, {
            headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
        })
        .then(response => response.json())
        .then(products => {
            productTable.innerHTML = "";

            if (!products || products.length === 0) {
                productTable.innerHTML = "<tr><td colspan='10'>No products found.</td></tr>";
                return;
            }

            products.forEach(product => {
                let row = document.createElement("tr");
                row.innerHTML = `
                    <td>${product.id}</td>
                    <td>${product.name}</td>
                    <td>${product.code}</td>
                    <td>${product.original_quantity}</td>
                    <td>${product.actual_quantity}</td>
                    <td>${product.price}</td>
                    <td>${product.cost}</td>
                    <td>${product.total_cost}</td>
                    <td>Shipment #${product.shipment_id}</td>
                    <td>${product.expired_date || "N/A"}</td>
                    <td>
                        <button onclick="openEditModal(${product.id})">Edit</button>
                        <button onclick="deleteProduct(${product.id})">Delete</button>
                    </td>
                `;
                productTable.appendChild(row);
            });
        })
        .catch(error => console.error("❌ Error loading product data:", error));
    }


function loadCategoryOptions(selectId) {
  console.log(`▶️ Loading categories into #${selectId}`);               // ← add this
  fetch(`${BASE_URL}/api/categories`, {
    headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
  })
  .then(res => {
    console.log(`GET /api/categories →`, res.status, res.statusText); // ← and this
    return res.json();
  })
  .then(categories => {
    console.log("📦 categories:", categories);                        // ← and this
    const sel = document.getElementById(selectId);
    categories.forEach(cat => {
      const opt = document.createElement("option");
      opt.value = cat.id;
      opt.textContent = cat.name;
      sel.appendChild(opt);
    });
  })
  .catch(err => console.error("⛔ Failed to load categories:", err));
}


function addProduct() {
    let name = document.getElementById("product_name").value;
    let code = document.getElementById("product_code").value;
    let originalQuantity = document.getElementById("original_quantity").value;
    let price = document.getElementById("price").value;
    let cost = document.getElementById("cost").value;
    let expiredDate = document.getElementById("expired_date").value;
    let expiryMode = document.getElementById("expiry_mode").value;
    let taxInput = document.getElementById("tax").value.trim();
    let tax = taxInput === "" ? null : parseFloat(taxInput);
    let category_id = document.getElementById("category_id").value;
    let shipmentId = document.getElementById("shipment_id").value;
    let isWeighted = document.getElementById("is_weighted").checked;

    if (!name || !code || !originalQuantity || !price || !cost || !category_id || !shipmentId) {
        alert("Please fill in all required fields.");
        return;
    }

    fetch(`${BASE_URL}/api/products`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({ 
            name, 
            code, 
            original_quantity: originalQuantity, 
            price, 
            cost, 
            tax,
            expired_date: expiredDate || null,
            expiry_mode: expiryMode,
            category_id, 
            shipment_id: shipmentId,
            is_weighted: isWeighted
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Product created successfully") {
            alert("Product added successfully!");
            window.location.reload();
        } else {
            alert("Error adding product: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => console.error("Error:", error));
}

function openEditModal(productId) {
    console.log("edit_product_id", document.getElementById("edit_product_id")); // should not be null
    fetch(`${BASE_URL}/api/products/${productId}`, {
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("token")
        }
    })
    .then(res => res.json())
    .then(product => {
        document.getElementById("edit_product_id").value = product.id;
        document.getElementById("edit_product_name").value = product.name;
        document.getElementById("edit_product_code").value = product.code;
        document.getElementById("edit_original_quantity").value = product.original_quantity;
        document.getElementById("edit_price").value = product.price;
        document.getElementById("edit_cost").value = product.cost;
        document.getElementById("edit_tax").value = product.tax || 0;
        document.getElementById("edit_category_id").value = product.category_id;
        document.getElementById("edit_shipment_id").value = product.shipment_id;
        document.getElementById("edit_is_weighted").checked = product.is_weighted;

        // Update quantity unit display
        const editQuantityUnit = document.getElementById("edit_quantity_unit");
        if (editQuantityUnit) {
            editQuantityUnit.textContent = product.is_weighted ? 'kg' : 'cái';
        }

        // Handle expiry mode
        const expiredDate = product.expired_date;
        const shipmentDate = product.shipment?.expired_date || null;

        if (!expiredDate) {
            document.getElementById("edit_expiry_mode").value = "none";
            handleExpiryModeChangeEdit("none");
        } else if (expiredDate === shipmentDate) {
            document.getElementById("edit_expiry_mode").value = "inherit";
            handleExpiryModeChangeEdit("inherit");
        } else {
            document.getElementById("edit_expiry_mode").value = "custom";
            document.getElementById("edit_expired_date").value = expiredDate;
            handleExpiryModeChangeEdit("custom");
        }

        openModal("editProductForm");
    })
    .catch(err => {
        console.error("❌ Failed to load product for edit", err);
        alert("Failed to load product for editing.");
    });
}


function updateProduct() {
    let id = document.getElementById("edit_product_id").value;
    console.log("Updating product with ID:", id);
    
    let name = document.getElementById("edit_product_name").value;
    let code = document.getElementById("edit_product_code").value;
    let originalQuantity = document.getElementById("edit_original_quantity").value;
    let price = document.getElementById("edit_price").value;
    let cost = document.getElementById("edit_cost").value;
    let taxInput = document.getElementById("edit_tax").value.trim();
    let expiredDate = document.getElementById("edit_expired_date").value;
    let expiryMode = document.getElementById("edit_expiry_mode").value;
    let tax = taxInput === "" ? null : parseFloat(taxInput);
    let category_id = document.getElementById("edit_category_id").value;
    let shipmentId = document.getElementById("edit_shipment_id").value;
    let isWeighted = document.getElementById("edit_is_weighted").checked;

    fetch(`${BASE_URL}/api/products/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token"),
            "Accept": "application/json",
        },
        body: JSON.stringify({ 
            name, 
            code, 
            original_quantity: originalQuantity, 
            price, 
            cost, 
            tax,
            category_id, 
            expired_date: expiredDate || null,
            expiry_mode: expiryMode,
            shipment_id: shipmentId,
            is_weighted: isWeighted
        })               
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Product updated successfully") {
            alert("Product updated successfully!");
            window.location.reload();
        } else {
            alert("Error updating product: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Failed to update product. Please try again.");
    });
}

function deleteProduct(id) {
    if (!confirm("Are you sure you want to delete this product?")) return;

    fetch(`${BASE_URL}/api/products/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("token"),
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
        alert(data.message || "Product deleted successfully");
        window.location.reload();
    })
    .catch(error => {
        console.error("Error deleting product:", error);
        alert("Error deleting product: " + error.message);
    });
}


function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`❌ Modal with ID '${modalId}' not found.`);
        return;
    }
    modal.style.display = 'flex';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`❌ Modal with ID '${modalId}' not found.`);
        return;
    }
    modal.style.display = 'none';
}

function suggestProductCode() {
    const input = document.getElementById("product_code").value;
    const suggestionBox = document.getElementById("suggestions");
    if (input.length < 2) {
        suggestionBox.innerHTML = '';
        return;
    }

    fetch(`${BASE_URL}/api/products/search?code=${encodeURIComponent(input)}`, {
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("token")
        }
    })
    .then(res => res.json())
    .then(products => {
        suggestionBox.innerHTML = '';
        if (!Array.isArray(products)) return;
    
        products.forEach(p => {
            const div = document.createElement("div");
            div.classList.add("suggestion-item");
            div.innerHTML = [
            p.code,
            p.name,
            `${p.price}Kč`,
            `${p.cost}Kč`,
            `Tax: ${p.tax}%`,
            `Category: ${p.category.name}`
            ].join(' | ');

            div.onclick = () => {
            document.getElementById("product_name").value      = p.name;
            document.getElementById("product_code").value      = p.code;
            document.getElementById("price").value             = p.price;
            document.getElementById("cost").value              = p.cost;
            document.getElementById("tax").value               = p.tax;
            document.getElementById("category_id").value       = p.category_id;  // ← set the select
            suggestionBox.innerHTML = '';
            };
            suggestionBox.appendChild(div);
        });
    })
    
}

function handleExpiryModeChange(mode) {
    const dateField = document.getElementById("expired_date");

    if (mode === "custom") {
        dateField.style.display = "block";
    } else {
        dateField.style.display = "none";
        dateField.value = ""; // clear it if mode is inherit or none
    }
}

function handleExpiryModeChangeEdit(mode) {
    const dateField = document.getElementById("edit_expired_date");

    if (mode === "custom") {
        dateField.style.display = "block";
    } else {
        dateField.style.display = "none";
        dateField.value = ""; // clear it if mode is inherit or none
    }
}