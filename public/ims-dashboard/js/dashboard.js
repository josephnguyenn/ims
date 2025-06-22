document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});



function loadDashboard(from = null, to = null) {
    const token = localStorage.getItem("token");

    // Build query params
    let params = '';
    if (from && to) {
        params = `?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
    }

    // ✅ Revenue, Sales, Debt
    fetch(`${BASE_URL}/api/reports/sales${params}`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(data => {
        const estimated = parseFloat(data.total_sales) || 0;
        const revenue = parseFloat(data.total_revenue) || 0;
        const debt = parseFloat(data.total_debt) || 0;
        const actual = estimated - debt;

        document.getElementById("dashboard-revenue").textContent = `${estimated.toLocaleString("en-US")} CZK`;
        document.getElementById("dashboard-debt").textContent = `${debt.toLocaleString("en-US")} CZK`;
        document.getElementById("dashboard-actual").textContent = `${actual.toLocaleString("en-US")} CZK`;

    });

    // ✅ Actual Order Count (with filtering if available)
    fetch(`${BASE_URL}/api/orders${params}`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(orders => {
        const count = Array.isArray(orders) ? orders.length : 0;
        document.getElementById("dashboard-orders").textContent = count;
    });

    // ✅ Top Selling Products
    fetch(`${BASE_URL}/api/reports/top-products`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(products => {
        const tbody = document.querySelector("#top-products tbody");
        tbody.innerHTML = "";
        products.forEach(p => {
            const row = `<tr><td>${p.product.name}</td><td>${p.total_sold}</td></tr>`;
            tbody.innerHTML += row;
        });
    });

    // ✅ Most Imported Products
    fetch(`${BASE_URL}/api/products`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(products => {
        const sorted = [...products].sort((a, b) => b.original_quantity - a.original_quantity);
        const topImported = sorted.slice(0, 5);
        const tbody = document.querySelector("#most-imported tbody");
        tbody.innerHTML = "";
        topImported.forEach(p => {
            const row = `<tr><td>${p.name}</td><td>${p.original_quantity}</td></tr>`;
            tbody.innerHTML += row;
        });
    });

    // ✅ Nearly Expired Shipments
    // ✅ Nearly Expired Products (actual_quantity > 0 only)
    fetch(`${BASE_URL}/api/products`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(products => {
        const tbody = document.querySelector("#expired-products tbody");
        tbody.innerHTML = "";

        const now = new Date();
        const in30days = new Date();
        in30days.setDate(now.getDate() + 30);

        products
            .filter(p => {
                if (!p.expired_date || !p.actual_quantity || p.actual_quantity <= 0) return false;
                const expiry = new Date(p.expired_date);
                return expiry > now && expiry <= in30days;
            })
            .forEach(p => {
                const row = `
                    <tr>
                        <td>${p.name}</td>
                        <td>${p.code}</td>
                        <td>${p.shipment_id ? `Shipment #${p.shipment_id}` : "N/A"}</td>
                        <td>${new Date(p.expired_date).toLocaleDateString("en-GB")}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
    });

}
// ✅ Reset filter
// ✅ Apply date filter
function filterDashboard() {
    const from = document.getElementById("from_date").value;
    const to = document.getElementById("to_date").value;
    loadDashboard(from, to);
}

