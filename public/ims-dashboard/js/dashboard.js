document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

function loadDashboard(from = null, to = null) {
    const token = sessionStorage.getItem("token");

    // Build query params
    let params = '';
    if (from && to) {
        params = `?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
    }

    // ✅ Revenue, Sales, Debt
    fetch(`http://localhost/ims/public/api/reports/sales${params}`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(data => {
        const estimated = parseFloat(data.total_sales) || 0;
        const revenue = parseFloat(data.total_revenue) || 0;
        const debt = parseFloat(data.total_debt) || 0;
        const actual = estimated - debt;

        document.getElementById("dashboard-revenue").textContent = `$${revenue.toFixed(2)}`;
        document.getElementById("dashboard-debt").textContent = `$${debt.toFixed(2)}`;
        document.getElementById("dashboard-actual").textContent = `$${actual.toFixed(2)}`;
    });

    // ✅ Actual Order Count (with filtering if available)
    fetch(`http://localhost/ims/public/api/orders${params}`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(orders => {
        const count = Array.isArray(orders) ? orders.length : 0;
        document.getElementById("dashboard-orders").textContent = count;
    });

    // ✅ Top Selling Products
    fetch("http://localhost/ims/public/api/reports/top-products", {
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
    fetch("http://localhost/ims/public/api/products", {
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
    fetch("http://localhost/ims/public/api/shipments", {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(shipments => {
        const tbody = document.querySelector("#expired-shipments tbody");
        tbody.innerHTML = "";
        const now = new Date();
        const in30days = new Date();
        in30days.setDate(now.getDate() + 30);

        shipments.filter(s => s.expired_date && new Date(s.expired_date) <= in30days)
        .forEach(s => {
            const row = `<tr><td>#${s.id}</td><td>${s.storage?.name || 'Unknown'}</td><td>${s.supplier?.name || 'Unknown'}</td><td>${s.expired_date}</td></tr>`;
            tbody.innerHTML += row;
        });
    });
}

// ✅ Apply date filter
function filterDashboard() {
    const from = document.getElementById("from_date").value;
    const to = document.getElementById("to_date").value;
    loadDashboard(from, to);
}
