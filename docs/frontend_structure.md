# Frontend Structure and Key Components

## Directory Structure (public/ims-dashboard)
- `css/`
  - `style.css`, `filter.css`, `invoice.css`, `shipment-product.css`, `add.css`
- `js/`
  - `dashboard.js` (dashboard data fetching and rendering)
  - `products.js`, `products-fix.js`, `categories.js`, `orders.js`, `order-products.js`, `storage.js`, `shipments.js`, `shipment-products.js`, `shipment-suppliers.js`, `delivery-suppliers.js`, `customers.js`, `users.js`, `login.js`, `performance.js`
- `templates/`
  - `dashboard.php`, `dashboard-optimized.php`, `analytics.php`, `revenue.php`
  - CRUD pages: `products.php`, `orders.php`, `order-products.php`, `customers.php`, `users.php`, `category.php`, `storage.php`, `shipments.php`, `shipment-products.php`, `shipment-suppliers.php`, `delivery-suppliers.php`
  - Invoice tooling: `generate-invoice.php`
- `includes/`
  - Shared layout fragments: `header.php`, `sidebar.php`
- `data/`
  - `invoice_settings.json` (client-side invoice defaults)
- `pos/`
  - Entry screens: `pos.php`, `login.php`
  - Views: `views/product-panel.php`, `views/payment-panel.php`, `customer-panel.html`, `pos-receipt.html`
  - Client scripts: `js/pos-script.js`, `js/pos-payment.js`, `js/pos-invoice.js`
  - Styles: `css/pos-style.css`
  - API bridges: `api/fetch_products.php`, `api/pos-orders.php`, `api/save-invoice.php`, `api/pos-get-receipt.php`, `api/pos-reports.php`, `api/get_exchange_rate.php`, `api/get-invoice-html.php`
  - Settings and helpers: `settings.php`, `settings_invoice.php`, `settings_exchange_rate.php`, `settings_reports_content.php`, `settings_shifts_content.php`, `session_store.php`, `tools/backfill_product_shipments.php`, `api/debug_invoice.log`
  - Assets: `uploads/`
- Root utilities: `define.php` (DB/API constants), `login.php`, `logout.php`, `performance-test.html`, `performance-monitor.php`, `api-test.html`, `session_store.php`, `uploads/images/`

## POS Screen Components
- **Product grid and cart interactions** (`pos/js/pos-script.js`)
  - Loads exchange rates and category tabs on DOMContentLoaded, fetches products by category from the Laravel API, renders product cards with stock and shipment badges, and manages cart quantities with max-quantity checks. Products broadcast to a customer display channel and totals update in CZK/EUR along with removal handlers.
- **Payment/receipt flow** (`pos/js/pos-payment.js`, `pos/js/pos-invoice.js`, `views/payment-panel.php`, `pos-receipt.html`)
  - Supports tender selection, optional QR links, and auto-print toggles while submitting orders through `pos/api/pos-orders.php` and rendering receipts.

### Example: Product list to cart workflow
- Product cards are rendered from API data with stock and shipment metadata; clicking a card increments the cart up to available quantity, recalculates totals in CZK/EUR, and broadcasts the state for the customer panel.

```javascript
fetch(`${BASE_URL}/api/products?category_id=${categoryId}`, { headers: { 'Authorization': `Bearer ${AUTH_TOKEN}` } })
  .then(r => r.json())
  .then(response => {
    const list = response.data || response;
    renderProductList(list);
  });
```
```javascript
list.forEach(p => {
  if ((p.actual_quantity ?? 0) <= 0) return;
  const card = document.createElement('div');
  card.className = 'product-card';
  card.dataset.id = p.id;
  card.dataset.name = p.name;
  card.dataset.price = p.price;
  card.dataset.maxQty = p.actual_quantity;
  // ...
});
```

```javascript
for (let id in cart) {
  const { name, price, qty } = cart[id];
  const lineTotal = price * qty;
  cartTableBody.innerHTML += `
    <tr>
      <td>${name}</td>
      <td>${qty}</td>
      <td>${price.toFixed(2)} CZK</td>
      <td>${lineTotal.toFixed(2)} CZK</td>
      <td><button class="remove-item" data-id="${id}">✖</button></td>
    </tr>`;
}
```

## Dashboard Interface Components
- **Dashboard templates** (`templates/dashboard.php`, `templates/dashboard-optimized.php`)
  - PHP templates gate access via session tokens, load quick stats, and expose tabbed content for products, orders, customers, and reports.
- **Dashboard data loader** (`js/dashboard.js`)
  - Fetches sales/revenue metrics, order counts, top products, imported quantities, and soon-to-expire products through Bearer-authenticated REST calls, then injects the results into tables/cards with date filter support.

### Example: Dashboard data fetch and render
```javascript
fetch(`${BASE_URL}/api/reports/sales${params}`, { headers: { 'Authorization': `Bearer ${token}` } })
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
```
```javascript
fetch(`${BASE_URL}/api/reports/top-products`, { headers: { 'Authorization': `Bearer ${token}` } })
  .then(res => res.json())
  .then(response => {
    const products = Array.isArray(response) ? response : (response.data || []);
    const tbody = document.querySelector("#top-products tbody");
    tbody.innerHTML = "";
    products.forEach(p => {
      const productName = p.product ? p.product.name : (p.name || 'Unknown');
      const totalSold = p.total_sold || 0;
      const row = `<tr><td>${productName}</td><td>${totalSold}</td></tr>`;
      tbody.innerHTML += row;
    });
  });
```
