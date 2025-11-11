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
    .then(response => {
        // Handle both paginated and non-paginated responses
        const orders = Array.isArray(response) ? response : (response.data || []);
        const count = orders.length;
        document.getElementById("dashboard-orders").textContent = count;
    })
    .catch(error => {
        console.error('Error loading orders:', error);
        document.getElementById("dashboard-orders").textContent = '0';
    });

    // ✅ Top Selling Products
    fetch(`${BASE_URL}/api/reports/top-products`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(response => {
        // Handle both paginated and non-paginated responses
        const products = Array.isArray(response) ? response : (response.data || []);
        const tbody = document.querySelector("#top-products tbody");
        tbody.innerHTML = "";
        products.forEach(p => {
            const productName = p.product ? p.product.name : (p.name || 'Unknown');
            const totalSold = p.total_sold || 0;
            const row = `<tr><td>${productName}</td><td>${totalSold}</td></tr>`;
            tbody.innerHTML += row;
        });
    })
    .catch(error => {
        console.error('Error loading top products:', error);
        const tbody = document.querySelector("#top-products tbody");
        tbody.innerHTML = "<tr><td colspan='2'>Error loading data</td></tr>";
    });

    // ✅ Most Imported Products
    fetch(`${BASE_URL}/api/products?paginate=false`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(response => {
        // Handle both paginated and non-paginated responses
        const products = Array.isArray(response) ? response : (response.data || []);
        const sorted = [...products].sort((a, b) => b.original_quantity - a.original_quantity);
        const topImported = sorted.slice(0, 5);
        const tbody = document.querySelector("#most-imported tbody");
        tbody.innerHTML = "";
        topImported.forEach(p => {
            const row = `<tr><td>${p.name}</td><td>${p.original_quantity}</td></tr>`;
            tbody.innerHTML += row;
        });
    })
    .catch(error => {
        console.error('Error loading most imported products:', error);
        const tbody = document.querySelector("#most-imported tbody");
        tbody.innerHTML = "<tr><td colspan='2'>Error loading data</td></tr>";
    });

    // ✅ Nearly Expired Shipments
    // ✅ Nearly Expired Products (actual_quantity > 0 only)
    fetch(`${BASE_URL}/api/products?paginate=false`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(response => {
        // Handle both paginated and non-paginated responses
        const products = Array.isArray(response) ? response : (response.data || []);
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
    })
    .catch(error => {
        console.error('Error loading expired products:', error);
        const tbody = document.querySelector("#expired-products tbody");
        tbody.innerHTML = "<tr><td colspan='4'>Error loading data</td></tr>";
    });

}
// ✅ Reset filter
// ✅ Apply date filter
function filterDashboard() {
    const from = document.getElementById("from_date").value;
    const to = document.getElementById("to_date").value;
    loadDashboard(from, to);
}

