// Product page initialization and fixes
document.addEventListener("DOMContentLoaded", function () {
    // Fix for products loading - ensure we call the right function
    if (typeof displayProducts === 'function') {
        // If we have a display function, call it
        displayProducts();
    } else {
        // Otherwise, try to load products manually
        loadProductsCompatibility();
    }
});

function loadProductsCompatibility() {
    const productTable = document.getElementById("product-table");
    if (!productTable) return;
    
    const token = localStorage.getItem("token");
    if (!token) {
        console.error("No authentication token found");
        return;
    }
    
    // Show loading
    productTable.innerHTML = "<tr><td colspan='11'>Loading products...</td></tr>";
    
    fetch(`${BASE_URL}/api/products?paginate=false`, {
        headers: { "Authorization": "Bearer " + token }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(response => {
        // Handle both paginated and non-paginated responses
        const products = Array.isArray(response) ? response : (response.data || []);
        productTable.innerHTML = "";

        if (!products || products.length === 0) {
            productTable.innerHTML = "<tr><td colspan='11'>No products found.</td></tr>";
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
        
        console.log(`Loaded ${products.length} products successfully`);
    })
    .catch(error => {
        console.error('Error loading products:', error);
        productTable.innerHTML = `<tr><td colspan='11'>Error loading products: ${error.message}</td></tr>`;
    });
}

// Also expose this function globally so it can be called from other scripts
window.loadProductsCompatibility = loadProductsCompatibility;