/**
 * Advanced Analytics Dashboard with ApexCharts
 * Feature 11: Advanced Analytics Dashboard
 * 
 * Features:
 * - Sales by Category (Donut Chart)
 * - Profit Margins (Line Chart)
 * - Customer Segments (Bar Chart)
 * - Hourly Sales Pattern (Area Chart)
 * - Inventory Turnover (Bar Chart)
 * - Sales Forecast (Line Chart)
 * - Sales Trends (Area Chart)
 * - Top Products (Horizontal Bar Chart)
 * - Gemini AI Insights
 */

let apexCharts = {};
const isDarkMode = () => document.documentElement.getAttribute('data-theme') === 'dark';

// Chart theme configuration
const getChartTheme = () => ({
    mode: isDarkMode() ? 'dark' : 'light',
    palette: 'palette2',
    monochrome: {
        enabled: false
    }
});

const getChartBaseConfig = () => ({
    theme: getChartTheme(),
    chart: {
        background: 'transparent',
        foreColor: isDarkMode() ? '#e0e0e0' : '#333',
        toolbar: {
            show: true,
            tools: {
                download: true,
                selection: true,
                zoom: true,
                zoomin: true,
                zoomout: true,
                pan: true,
                reset: true
            }
        },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800
        }
    }
});

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
    // Don't auto-load AI insights to prevent rate limiting
    // User can click buttons to load them manually
    
    // Listen for theme changes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'data-theme') {
                updateChartsTheme();
            }
        });
    });
    
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });
});

// Period selector change
document.getElementById('period-select')?.addEventListener('change', () => {
    loadDashboardData();
});

// API Helper
async function fetchAPI(endpoint) {
    try {
        const response = await fetch(`${BASE_URL}/api${endpoint}`, {
            headers: {
                'Authorization': `Bearer ${AUTH_TOKEN}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            console.error('API Error:', errorData);
            throw new Error(`HTTP ${response.status}: ${errorData.message || 'Unknown error'}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Fetch Error:', error);
        throw error;
    }
}

// Load all dashboard data
async function loadDashboardData() {
    const days = document.getElementById('period-select')?.value || '30';
    
    try {
        const [salesTrends, topProducts, revenue, inventory, forecast] = await Promise.all([
            fetchAPI(`/analytics/sales-trends?days=${days}`).catch(e => { console.warn('Sales trends failed:', e); return null; }),
            fetchAPI('/analytics/top-products?limit=10').catch(e => { console.warn('Top products failed:', e); return null; }),
            fetchAPI('/analytics/revenue?period=month').catch(e => { console.warn('Revenue failed:', e); return null; }),
            fetchAPI('/analytics/inventory-turnover').catch(e => { console.warn('Inventory failed:', e); return null; }),
            fetchAPI('/analytics/sales-forecast?days=7').catch(e => { console.warn('Forecast failed:', e); return null; })
        ]);

        // Update stats cards
        if (revenue && revenue.total_revenue !== undefined) {
            updateStatsCards(revenue);
        }
        
        // Update charts with null checks
        if (salesTrends?.data) updateSalesTrendChart(salesTrends.data);
        if (topProducts?.data) updateTopProductsChart(topProducts.data);
        if (revenue?.by_category) updateSalesByCategoryChart(revenue.by_category);
        if (inventory?.data) updateInventoryChart(inventory.data);
        if (topProducts?.data) updateProfitMarginsChart(topProducts.data);
        if (salesTrends?.data) updateHourlySalesChart(salesTrends.data);
        if (revenue) updateCustomerSegmentsChart(revenue);
        if (forecast?.data) updateSalesForecastChart(forecast.data);
        
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showNotification('Failed to load analytics data', 'error');
    }
}

// Update stats cards
function updateStatsCards(revenue) {
    const formatter = new Intl.NumberFormat('cs-CZ');
    
    document.getElementById('total-revenue').textContent = 
        formatter.format(revenue.total_revenue) + ' CZK';
    document.getElementById('total-orders').textContent = revenue.total_orders || 0;
    document.getElementById('avg-order-value').textContent = 
        formatter.format(revenue.average_order_value || 0) + ' CZK';
    
    // Calculate changes (mock data - in production, compare with previous period)
    const revenueChange = '+12.5%';
    const ordersChange = '+8.3%';
    const avgChange = '+4.2%';
    
    document.getElementById('revenue-change').textContent = revenueChange;
    document.getElementById('orders-change').textContent = ordersChange;
    document.getElementById('avg-change').textContent = avgChange;
}

// 1. Sales Trend Chart (Advanced Area Chart)
function updateSalesTrendChart(data) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        console.warn('No sales trend data');
        return;
    }
    
    if (apexCharts.salesTrend) {
        apexCharts.salesTrend.destroy();
    }
    
    const options = {
        ...getChartBaseConfig(),
        series: [{
            name: 'Revenue',
            data: data.map(d => ({
                x: d.date,
                y: parseFloat(d.total_revenue || 0)
            }))
        }, {
            name: 'Orders',
            data: data.map(d => ({
                x: d.date,
                y: parseInt(d.total_orders || 0) * 100 // Scale for visibility
            }))
        }],
        chart: {
            ...getChartBaseConfig().chart,
            type: 'area',
            height: 350
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.6,
                opacityTo: 0.1
            }
        },
        xaxis: {
            type: 'datetime'
        },
        yaxis: [{
            title: {
                text: 'Revenue (CZK)'
            }
        }, {
            opposite: true,
            title: {
                text: 'Orders (x100)'
            }
        }],
        tooltip: {
            shared: true,
            intersect: false
        }
    };
    
    apexCharts.salesTrend = new ApexCharts(
        document.querySelector("#salesTrendChart"),
        options
    );
    apexCharts.salesTrend.render();
}

// 2. Top Products Chart (Horizontal Bar)
function updateTopProductsChart(data) {
    if (!data || !Array.isArray(data)) return;
    
    if (apexCharts.topProducts) {
        apexCharts.topProducts.destroy();
    }
    
    const options = {
        ...getChartBaseConfig(),
        series: [{
            name: 'Units Sold',
            data: data.map(p => parseInt(p.total_sold || 0))
        }],
        chart: {
            ...getChartBaseConfig().chart,
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: true,
                distributed: true,
                barHeight: '70%'
            }
        },
        colors: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', 
                 '#00f2fe', '#43e97b', '#38f9d7', '#fa709a', '#fee140'],
        xaxis: {
            categories: data.map(p => p.name.substring(0, 20))
        },
        legend: {
            show: false
        }
    };
    
    apexCharts.topProducts = new ApexCharts(
        document.querySelector("#topProductsChart"),
        options
    );
    apexCharts.topProducts.render();
}

// 3. Sales by Category Chart (Donut)
function updateSalesByCategoryChart(data) {
    if (!data || !Array.isArray(data)) {
        // Mock data if not available
        data = [
            { category: 'Electronics', revenue: 45000 },
            { category: 'Clothing', revenue: 30000 },
            { category: 'Food', revenue: 25000 },
            { category: 'Books', revenue: 15000 },
            { category: 'Others', revenue: 10000 }
        ];
    }
    
    if (apexCharts.salesByCategory) {
        apexCharts.salesByCategory.destroy();
    }
    
    const options = {
        ...getChartBaseConfig(),
        series: data.map(d => parseFloat(d.revenue || 0)),
        chart: {
            ...getChartBaseConfig().chart,
            type: 'donut',
            height: 350
        },
        labels: data.map(d => d.category || 'Unknown'),
        colors: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'],
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Revenue',
                            formatter: function (w) {
                                return new Intl.NumberFormat('cs-CZ').format(
                                    w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                ) + ' CZK';
                            }
                        }
                    }
                }
            }
        },
        legend: {
            position: 'bottom'
        }
    };
    
    apexCharts.salesByCategory = new ApexCharts(
        document.querySelector("#salesByCategoryChart"),
        options
    );
    apexCharts.salesByCategory.render();
}

// 4. Inventory Turnover Chart (Bar)
function updateInventoryChart(data) {
    if (!data || !Array.isArray(data)) return;
    
    if (apexCharts.inventory) {
        apexCharts.inventory.destroy();
    }
    
    const options = {
        ...getChartBaseConfig(),
        series: [{
            name: 'Turnover Rate',
            data: data.map(p => parseFloat(p.turnover_rate || 0))
        }],
        chart: {
            ...getChartBaseConfig().chart,
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                columnWidth: '60%',
                distributed: false,
                colors: {
                    ranges: [{
                        from: 0,
                        to: 2,
                        color: '#f5576c'
                    }, {
                        from: 2,
                        to: 5,
                        color: '#fee140'
                    }, {
                        from: 5,
                        to: 100,
                        color: '#43e97b'
                    }]
                }
            }
        },
        xaxis: {
            categories: data.map(p => p.name.substring(0, 15)),
            labels: {
                rotate: -45
            }
        },
        yaxis: {
            title: {
                text: 'Turnover Rate'
            }
        }
    };
    
    apexCharts.inventory = new ApexCharts(
        document.querySelector("#inventoryChart"),
        options
    );
    apexCharts.inventory.render();
}

// 5. Profit Margins Chart (Line)
function updateProfitMarginsChart(data) {
    if (!data || !Array.isArray(data)) return;
    
    if (apexCharts.profitMargins) {
        apexCharts.profitMargins.destroy();
    }
    
    // Calculate profit margins (mock calculation)
    const marginsData = data.map(p => {
        const revenue = parseFloat(p.total_revenue || 0);
        const cost = revenue * 0.6; // Assume 40% margin
        const margin = ((revenue - cost) / revenue * 100).toFixed(2);
        return {
            name: p.name,
            margin: parseFloat(margin)
        };
    });
    
    const options = {
        ...getChartBaseConfig(),
        series: [{
            name: 'Profit Margin %',
            data: marginsData.map(d => d.margin)
        }],
        chart: {
            ...getChartBaseConfig().chart,
            type: 'line',
            height: 350
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
        },
        xaxis: {
            categories: marginsData.map(d => d.name.substring(0, 15))
        },
        yaxis: {
            title: {
                text: 'Margin (%)'
            },
            min: 0,
            max: 100
        },
        colors: ['#667eea']
    };
    
    apexCharts.profitMargins = new ApexCharts(
        document.querySelector("#profitMarginsChart"),
        options
    );
    apexCharts.profitMargins.render();
}

// 6. Hourly Sales Pattern Chart (Area)
function updateHourlySalesChart(data) {
    if (!data || !Array.isArray(data)) return;
    
    if (apexCharts.hourlySales) {
        apexCharts.hourlySales.destroy();
    }
    
    // Generate hourly pattern (mock data based on dates)
    const hourlyData = Array.from({length: 24}, (_, hour) => {
        const salesInHour = data.filter(d => {
            const date = new Date(d.date);
            return date.getHours() === hour;
        }).reduce((sum, d) => sum + parseFloat(d.total_revenue || 0), 0);
        
        return {
            hour: `${hour}:00`,
            sales: salesInHour || Math.random() * 5000 + 1000 // Mock if no data
        };
    });
    
    const options = {
        ...getChartBaseConfig(),
        series: [{
            name: 'Sales',
            data: hourlyData.map(d => d.sales)
        }],
        chart: {
            ...getChartBaseConfig().chart,
            type: 'area',
            height: 350
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.6,
                opacityTo: 0.1
            }
        },
        xaxis: {
            categories: hourlyData.map(d => d.hour)
        },
        yaxis: {
            title: {
                text: 'Sales (CZK)'
            }
        },
        colors: ['#764ba2']
    };
    
    apexCharts.hourlySales = new ApexCharts(
        document.querySelector("#hourlySalesChart"),
        options
    );
    apexCharts.hourlySales.render();
}

// 7. Customer Segments Chart (Bar)
function updateCustomerSegmentsChart(revenue) {
    if (apexCharts.customerSegments) {
        apexCharts.customerSegments.destroy();
    }
    
    // Mock customer segmentation
    const segments = [
        { name: 'VIP (>50k)', value: 5, color: '#667eea' },
        { name: 'Regular (10-50k)', value: 15, color: '#764ba2' },
        { name: 'Occasional (<10k)', value: 30, color: '#f093fb' },
        { name: 'New', value: 10, color: '#4facfe' }
    ];
    
    const options = {
        ...getChartBaseConfig(),
        series: [{
            data: segments.map(s => s.value)
        }],
        chart: {
            ...getChartBaseConfig().chart,
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                distributed: true,
                columnWidth: '50%'
            }
        },
        colors: segments.map(s => s.color),
        xaxis: {
            categories: segments.map(s => s.name)
        },
        yaxis: {
            title: {
                text: 'Number of Customers'
            }
        },
        legend: {
            show: false
        }
    };
    
    apexCharts.customerSegments = new ApexCharts(
        document.querySelector("#customerSegmentsChart"),
        options
    );
    apexCharts.customerSegments.render();
}

// 8. Sales Forecast Chart (Line with prediction)
function updateSalesForecastChart(data) {
    if (apexCharts.salesForecast) {
        apexCharts.salesForecast.destroy();
    }
    
    if (!data || !Array.isArray(data)) {
        // Generate mock forecast data
        const today = new Date();
        data = Array.from({length: 7}, (_, i) => {
            const date = new Date(today);
            date.setDate(date.getDate() + i);
            return {
                date: date.toISOString().split('T')[0],
                predicted_revenue: 5000 + Math.random() * 2000
            };
        });
    }
    
    const options = {
        ...getChartBaseConfig(),
        series: [{
            name: 'Predicted Revenue',
            data: data.map(d => parseFloat(d.predicted_revenue || 0))
        }],
        chart: {
            ...getChartBaseConfig().chart,
            type: 'line',
            height: 350
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            dashArray: [0, 8]
        },
        markers: {
            size: 6
        },
        xaxis: {
            categories: data.map(d => d.date)
        },
        yaxis: {
            title: {
                text: 'Revenue (CZK)'
            }
        },
        colors: ['#f093fb'],
        annotations: {
            xaxis: [{
                x: data[0].date,
                borderColor: '#00E396',
                label: {
                    text: 'Forecast Start',
                    style: {
                        color: '#fff',
                        background: '#00E396'
                    }
                }
            }]
        }
    };
    
    apexCharts.salesForecast = new ApexCharts(
        document.querySelector("#salesForecastChart"),
        options
    );
    apexCharts.salesForecast.render();
}

// Update all charts theme
function updateChartsTheme() {
    Object.values(apexCharts).forEach(chart => {
        if (chart && chart.updateOptions) {
            chart.updateOptions({
                theme: getChartTheme(),
                chart: {
                    foreColor: isDarkMode() ? '#e0e0e0' : '#333'
                }
            });
        }
    });
}

// AI Insights with debounce to prevent 429 errors
let aiInsightsTimeout = null;
async function loadAIInsights(type) {
    // Clear previous timeout to prevent rapid calls (rate limiting)
    if (aiInsightsTimeout) {
        clearTimeout(aiInsightsTimeout);
    }
    
    // Debounce for 500ms
    aiInsightsTimeout = setTimeout(async () => {
        // Update button states
        document.querySelectorAll('.insight-type button').forEach(btn => {
            btn.classList.remove('active');
            if (btn.textContent.toLowerCase().includes(type)) {
                btn.classList.add('active');
            }
        });
        
        const content = document.getElementById('ai-content');
        content.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>AI is analyzing your data...</p></div>';
        
        try {
            const data = await fetchAPI(`/analytics/ai-insights?type=${type}`);
            if (data?.insights) {
                content.innerHTML = `<div class="content">${formatAIInsights(data.insights)}</div>`;
            } else {
                content.innerHTML = '<p>No AI insights available at this time.</p>';
            }
        } catch (error) {
            // Check if it's a rate limit error (429)
            if (error.message.includes('429')) {
                content.innerHTML = '<p style="color: #ff9800;">⚠️ AI insights temporarily unavailable due to rate limiting. Please try again in a few minutes.</p>';
            } else {
                content.innerHTML = '<p>AI insights are currently unavailable. Analytics data is still available above.</p>';
            }
            console.warn('AI insights unavailable:', error);
        }
    }, 500);
}

function formatAIInsights(text) {
    if (!text) return '<p>No insights available.</p>';
    
    // Convert markdown-style formatting to HTML
    let formatted = text
        // Headers (##, ###)
        .replace(/^### (.+)$/gm, '<h4>$1</h4>')
        .replace(/^## (.+)$/gm, '<h3>$1</h3>')
        .replace(/^# (.+)$/gm, '<h3>$1</h3>')
        
        // Bold text
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        
        // Numbered lists (1., 2., etc.)
        .replace(/^(\d+)\. (.+)$/gm, '<li><strong>$1.</strong> $2</li>')
        
        // Bullet points (*, -, •)
        .replace(/^[*-] (.+)$/gm, '<li>$1</li>')
        .replace(/^• (.+)$/gm, '<li>$1</li>')
        
        // Wrap consecutive <li> in <ul>
        .replace(/(<li>.*?<\/li>)(\s*<li>)/g, '$1\n$2')
        .replace(/(<li>.*?<\/li>)(?!\s*<li>)/gs, (match) => {
            return '<ul>' + match.split('\n').join('') + '</ul>';
        })
        
        // Paragraphs (double line break)
        .split('\n\n')
        .map(para => {
            // Don't wrap if already has HTML tags
            if (para.match(/^<[h3-6|ul]/)) {
                return para;
            }
            return para.trim() ? `<p>${para.trim()}</p>` : '';
        })
        .join('\n');
    
    // Clean up extra spacing
    formatted = formatted
        .replace(/<\/ul>\s*<ul>/g, '') // Merge adjacent lists
        .replace(/\n{3,}/g, '\n\n'); // Remove excessive line breaks
    
    return `<div class="ai-insights-formatted">${formatted}</div>`;
}

// Refresh all data
function refreshData() {
    loadDashboardData();
    loadAIInsights('sales');
}

// Show notification helper
function showNotification(message, type = 'info') {
    if (typeof window.NotificationManager !== 'undefined') {
        window.NotificationManager.show(message, type);
    } else {
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}
