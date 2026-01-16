# Backend Overview

## Backend Folder Structure (Laravel)
- `app/` contains the application core with Console commands, Exceptions, Providers, domain Models, Repositories, and Services used across controllers.【4f8467†L1-L10】
- `app/Http/` organises the web layer into Controllers, Middleware, Requests, and Resources for API handling and cross-cutting concerns.【436d8d†L1-L6】
- `app/Http/Controllers/` hosts the business-facing API controllers (analytics, auth, customers, orders/POS, products, reports, suppliers, storage).【4ae117†L1-L13】
- `app/Http/Middleware/` contains authentication, RBAC (`CheckRole`), CORS/CSRF, maintenance, proxy, and signature middleware used by the API kernel.【b60e20†L1-L12】
- `app/Repositories/` centralises reusable data-access patterns such as cached product lookups and FIFO ordering for shared barcodes.【895939†L1-L3】
- `app/Services/` encapsulates domain services including inventory depletion and Gemini-backed analytics/forecasting helpers.【864174†L1-L4】
- `routes/api.php` defines the REST endpoints, while `routes/web.php`, `routes/console.php`, and `routes/channels.php` hold web, CLI, and broadcasting routes respectively.【d56672†L1-L5】

## Backend Code Samples
### Sales Forecasting Endpoint
The `salesForecast` method in `AnalyticsController` aggregates the last 90 days of orders, builds a date/revenue series, and delegates to `GeminiService::predictSalesTrend` before returning a JSON payload with the generated forecast and metadata.【F:app/Http/Controllers/AnalyticsController.php†L253-L274】

### POS / Order Creation Endpoint
The `store` method in `OrderController` branches on `source=pos`, validates cashier, totals, payment, and item details, enforces rounding rules, creates the order, determines the active shift, decrements stock FIFO across shipments for each barcode, and persists `order_products` rows. A legacy admin flow handles back-office order capture when `source` is omitted.【F:app/Http/Controllers/OrderController.php†L57-L197】
