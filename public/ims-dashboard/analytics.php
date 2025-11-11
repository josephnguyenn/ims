<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Include configuration
require_once 'define.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - IMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #667eea;
            font-size: 28px;
        }

        .header .controls {
            display: flex;
            gap: 10px;
        }

        .header select, .header button {
            padding: 10px 20px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .header button {
            background: #667eea;
            color: white;
            font-weight: bold;
        }

        .header button:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-card.revenue .icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card.orders .icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .stat-card.products .icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .stat-card.customers .icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .stat-card .change {
            font-size: 14px;
            margin-top: 10px;
        }

        .stat-card .change.up { color: #43e97b; }
        .stat-card .change.down { color: #f5576c; }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .chart-card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .chart-card canvas {
            max-height: 300px;
        }

        .ai-insights {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .ai-insights h2 {
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ai-insights .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .ai-insights .content {
            line-height: 1.8;
            color: #444;
        }

        .ai-insights .insight-type {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .ai-insights .insight-type button {
            padding: 10px 20px;
            border: 2px solid #667eea;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .ai-insights .insight-type button.active {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
            }
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['token'])) {
        header('Location: ../login.php');
        exit();
    }
    include '../define.php';
    ?>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Analytics Dashboard</h1>
            <div class="controls">
                <select id="period-select">
                    <option value="7">Last 7 Days</option>
                    <option value="30" selected>Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
                <button onclick="refreshData()"><i class="fas fa-sync-alt"></i> Refresh</button>
                <button onclick="window.location.href='dashboard.php'"><i class="fas fa-arrow-left"></i> Back</button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card revenue">
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                <h3>Total Revenue</h3>
                <div class="value" id="total-revenue">0 CZK</div>
                <div class="change up"><i class="fas fa-arrow-up"></i> <span id="revenue-change">0%</span></div>
            </div>
            <div class="stat-card orders">
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <h3>Total Orders</h3>
                <div class="value" id="total-orders">0</div>
                <div class="change up"><i class="fas fa-arrow-up"></i> <span id="orders-change">0%</span></div>
            </div>
            <div class="stat-card products">
                <div class="icon"><i class="fas fa-box"></i></div>
                <h3>Products Sold</h3>
                <div class="value" id="products-sold">0</div>
                <div class="change"><span id="products-change">-</span></div>
            </div>
            <div class="stat-card customers">
                <div class="icon"><i class="fas fa-users"></i></div>
                <h3>Avg Order Value</h3>
                <div class="value" id="avg-order-value">0 CZK</div>
                <div class="change"><span id="avg-change">-</span></div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h2><i class="fas fa-chart-area"></i> Sales Trend</h2>
                <canvas id="salesTrendChart"></canvas>
            </div>
            <div class="chart-card">
                <h2><i class="fas fa-chart-bar"></i> Top Products</h2>
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h2><i class="fas fa-chart-pie"></i> Revenue by Month</h2>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="chart-card">
                <h2><i class="fas fa-sync"></i> Inventory Turnover</h2>
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>

        <!-- AI Insights -->
        <div class="ai-insights">
            <h2><i class="fas fa-brain"></i> AI-Powered Insights</h2>
            <div class="insight-type">
                <button class="active" onclick="loadAIInsights('sales')">Sales Analysis</button>
                <button onclick="loadAIInsights('inventory')">Inventory Recommendations</button>
                <button onclick="loadAIInsights('recommendations')">Product Recommendations</button>
            </div>
            <div id="ai-content" class="content">
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Analyzing data with AI...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const AUTH_TOKEN = '<?= $_SESSION["token"] ?>';
        let charts = {};

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboardData();
            loadAIInsights('sales');
        });

        document.getElementById('period-select').addEventListener('change', () => {
            loadDashboardData();
        });

        async function fetchAPI(endpoint) {
            const response = await fetch(`${BASE_URL}/api${endpoint}`, {
                headers: {
                    'Authorization': `Bearer ${AUTH_TOKEN}`,
                    'Accept': 'application/json'
                }
            });
            return response.json();
        }

        async function loadDashboardData() {
            const days = document.getElementById('period-select').value;
            
            try {
                // Load all data
                const [salesTrends, topProducts, revenue, inventory] = await Promise.all([
                    fetchAPI(`/analytics/sales-trends?days=${days}`),
                    fetchAPI('/analytics/top-products?limit=10'),
                    fetchAPI('/analytics/revenue?period=month'),
                    fetchAPI('/analytics/inventory-turnover')
                ]);

                // Update stats cards
                updateStatsCards(revenue);
                
                // Update charts
                updateSalesTrendChart(salesTrends.data);
                updateTopProductsChart(topProducts);
                updateRevenueChart(revenue.by_period);
                updateInventoryChart(inventory);
                
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function updateStatsCards(revenue) {
            document.getElementById('total-revenue').textContent = 
                new Intl.NumberFormat('cs-CZ').format(revenue.total_revenue) + ' CZK';
            document.getElementById('total-orders').textContent = revenue.total_orders;
            document.getElementById('avg-order-value').textContent = 
                new Intl.NumberFormat('cs-CZ').format(revenue.average_order_value) + ' CZK';
        }

        function updateSalesTrendChart(data) {
            const ctx = document.getElementById('salesTrendChart');
            
            if (charts.salesTrend) charts.salesTrend.destroy();
            
            charts.salesTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                        label: 'Revenue (CZK)',
                        data: data.map(d => d.total_revenue),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }

        function updateTopProductsChart(data) {
            const ctx = document.getElementById('topProductsChart');
            
            if (charts.topProducts) charts.topProducts.destroy();
            
            charts.topProducts = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(p => p.name.substring(0, 20)),
                    datasets: [{
                        label: 'Units Sold',
                        data: data.map(p => p.total_sold),
                        backgroundColor: [
                            '#667eea', '#764ba2', '#f093fb', '#f5576c',
                            '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
                            '#fa709a', '#fee140'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        function updateRevenueChart(data) {
            const ctx = document.getElementById('revenueChart');
            
            if (charts.revenue) charts.revenue.destroy();
            
            charts.revenue = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.map(d => d.period),
                    datasets: [{
                        data: data.map(d => d.revenue),
                        backgroundColor: [
                            '#667eea', '#764ba2', '#f093fb', '#f5576c',
                            '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
                            '#fa709a', '#fee140', '#30cfd0', '#330867'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        }

        function updateInventoryChart(data) {
            const ctx = document.getElementById('inventoryChart');
            
            if (charts.inventory) charts.inventory.destroy();
            
            charts.inventory = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(p => p.name.substring(0, 15)),
                    datasets: [{
                        label: 'Turnover Rate',
                        data: data.map(p => p.turnover_rate),
                        backgroundColor: '#43e97b'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        async function loadAIInsights(type) {
            // Update button states
            document.querySelectorAll('.insight-type button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const content = document.getElementById('ai-content');
            content.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>AI is analyzing your data...</p></div>';
            
            try {
                const data = await fetchAPI(`/analytics/ai-insights?type=${type}`);
                content.innerHTML = `<div class="content">${formatAIInsights(data.insights)}</div>`;
            } catch (error) {
                content.innerHTML = '<p>Unable to generate insights at this time.</p>';
                console.error('Error loading AI insights:', error);
            }
        }

        function formatAIInsights(text) {
            if (!text) return '<p>No insights available.</p>';
            
            // Convert markdown-style formatting to HTML
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n\n/g, '</p><p>')
                .replace(/\n- /g, '<br>• ')
                .replace(/^/,'<p>')
                .replace(/$/, '</p>');
        }

        function refreshData() {
            loadDashboardData();
            loadAIInsights('sales');
        }
    </script>
</body>
</html>
