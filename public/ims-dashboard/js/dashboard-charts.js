// Dashboard Charts Implementation
document.addEventListener("DOMContentLoaded", () => {
    loadDashboardCharts();
});

function loadDashboardCharts() {
    const token = localStorage.getItem("token");
    
    // 1. Sales Trend Chart (30 days)
    fetch(`${BASE_URL}/api/analytics/sales-trends?days=30`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || [];
        const ctx = document.getElementById('salesTrendChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'Revenue (CZK)',
                    data: data.map(d => parseFloat(d.total_revenue || 0)),
                    borderColor: '#1a4ba8',
                    backgroundColor: 'rgba(26, 75, 168, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { 
                        mode: 'index',
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ' + context.parsed.y.toLocaleString() + ' CZK';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' CZK';
                            }
                        }
                    }
                }
            }
        });
    })
    .catch(err => console.error('Sales trend chart error:', err));
    
    // 2. Top Products Bar Chart
    fetch(`${BASE_URL}/api/analytics/top-products?limit=10`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || [];
        const ctx = document.getElementById('topProductsChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(p => p.name || 'Unknown'),
                datasets: [{
                    label: 'Units Sold',
                    data: data.map(p => parseFloat(p.total_sold || 0)),
                    backgroundColor: '#94B9F1',
                    borderColor: '#1a4ba8',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sold: ' + context.parsed.x + ' units';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    })
    .catch(err => console.error('Top products chart error:', err));
    
    // 3. Revenue vs Debt Pie Chart
    fetch(`${BASE_URL}/api/reports/sales`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(data => {
        const revenue = parseFloat(data.total_revenue || 0);
        const debt = parseFloat(data.total_debt || 0);
        const actual = revenue - debt;
        
        const ctx = document.getElementById('revenueDebtChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Actual Revenue', 'Debt'],
                datasets: [{
                    data: [actual, debt],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.toLocaleString() + ' CZK';
                            }
                        }
                    }
                }
            }
        });
    })
    .catch(err => console.error('Revenue chart error:', err));
    
    // 4. Inventory Status Pie Chart
    fetch(`${BASE_URL}/api/products`, {
        headers: { "Authorization": `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(products => {
        const inStock = products.filter(p => p.actual_quantity > 10).length;
        const lowStock = products.filter(p => p.actual_quantity > 0 && p.actual_quantity <= 10).length;
        const outOfStock = products.filter(p => p.actual_quantity === 0).length;
        
        const ctx = document.getElementById('inventoryPieChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [inStock, lowStock, outOfStock],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    })
    .catch(err => console.error('Inventory chart error:', err));
}

// Call from existing loadDashboard function
if (typeof loadDashboard === 'function') {
    const originalLoadDashboard = loadDashboard;
    window.loadDashboard = function(...args) {
        originalLoadDashboard(...args);
        setTimeout(loadDashboardCharts, 500);
    };
}
