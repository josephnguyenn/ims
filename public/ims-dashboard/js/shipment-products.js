document.addEventListener("DOMContentLoaded", function () {
    const shipmentId = document.getElementById("shipment_id").value;

    document.getElementById("add-shipment-product-form").addEventListener("submit", function (event) {
        event.preventDefault();
        addShipmentProduct();
    });
});

// ✅ Add Product to Shipment
function addShipmentProduct() {
    let shipmentId = document.getElementById("shipment_id").value;
    let productId = document.getElementById("product_id").value;
    let quantity = document.getElementById("quantity").value;
    let price = document.getElementById("price").value;

    fetch(`${BASE_URL}/api/shipment-products`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({
            shipment_id: shipmentId,
            product_id: productId,
            quantity: quantity,
            price: price
        })
    })
    .then(() => {
        alert("Product added to shipment!");
        location.reload();
    })
    .catch(error => console.error("Error adding product:", error));
}

// ✅ Delete Product from Shipment
function deleteShipmentProduct(productId, shipmentId) {
    if (!confirm("Are you sure you want to delete this product from the shipment?")) return;

    fetch(`${BASE_URL}/api/shipment-products/${productId}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("token")
        }
    })
    .then(() => {
        alert("Product removed from shipment.");
        location.reload();
    })
    .catch(error => console.error("Error deleting product:", error));
}
