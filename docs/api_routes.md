# API Route Reference

The table below lists the business-facing API routes defined in `routes/api.php`, including their HTTP methods, controllers, purpose, parameters, and expected responses.

| Endpoint | Method | Controller & Method | Description | Parameters | Returns |
| --- | --- | --- | --- | --- | --- |
| `/api/register` | POST | `AuthController@register` | Register a new user account with validation and hashed credentials. | Body: `name`, `email`, `password` | JSON message and created user; 201 on success. |
| `/api/login` | POST | `AuthController@login` | Authenticate and issue Sanctum token with throttling. | Body: `email`, `password` | JSON with auth token/user; 200 or 401. |
| `/api/logout` | POST | `AuthController@logout` | Revoke current user token. | Header: `Authorization` bearer token | JSON message; 200. |
| `/api/dashboard` | GET | Closure | Simple health/welcome payload for authenticated sessions. | Header: bearer token | JSON message; 200. |
| `/api/users` | GET | `UserController@index` | List users (admin only). | Header: bearer token; optional pagination query | JSON collection; 200. |
| `/api/users` | POST | `UserController@store` | Create a user (admin only). | Body: user fields | JSON message and user; 201. |
| `/api/users/{id}` | DELETE | `UserController@destroy` | Delete a user (admin only). | Path: `id` | JSON message; 200 or 404. |
| `/api/storages` | GET | `StorageController@index` | List storages. | Header token; optional pagination/query | JSON collection; 200. |
| `/api/storages/{id}` | GET | `StorageController@show` | Get a storage by id. | Path: `id` | JSON storage; 200 or 404. |
| `/api/storages` | POST | `StorageController@store` | Create storage (admin/manager). | Body: storage fields | JSON message and storage; 201. |
| `/api/storages/{id}` | PUT | `StorageController@update` | Update storage (admin/manager). | Path: `id`; body fields | JSON message and storage; 200/404. |
| `/api/storages/{id}` | DELETE | `StorageController@destroy` | Delete storage (admin/manager). | Path: `id` | JSON message; 200/404. |
| `/api/shipment-suppliers` | GET | `ShipmentSupplierController@index` | List shipment suppliers. | Header token | JSON collection; 200. |
| `/api/shipment-suppliers/{id}` | GET | `ShipmentSupplierController@show` | Show supplier by id. | Path: `id` | JSON supplier; 200/404. |
| `/api/shipment-suppliers` | POST | `ShipmentSupplierController@store` | Create supplier (admin/manager). | Body: supplier fields | JSON message and supplier; 201. |
| `/api/shipment-suppliers/{id}` | PUT | `ShipmentSupplierController@update` | Update supplier (admin/manager). | Path: `id`; body fields | JSON message and supplier; 200/404. |
| `/api/shipment-suppliers/{id}` | DELETE | `ShipmentSupplierController@destroy` | Delete supplier (admin/manager). | Path: `id` | JSON message; 200/404. |
| `/api/shipments` | GET | `ShipmentController@index` | List shipments. | Header token | JSON collection; 200. |
| `/api/shipments/{id}` | GET | `ShipmentController@show` | Show shipment by id. | Path: `id` | JSON shipment; 200/404. |
| `/api/shipments` | POST | `ShipmentController@store` | Create shipment (admin/manager). | Body: shipment fields | JSON message and shipment; 201. |
| `/api/shipments/{id}` | PUT | `ShipmentController@update` | Update shipment (admin/manager). | Path: `id`; body fields | JSON message and shipment; 200/404. |
| `/api/shipments/{id}` | DELETE | `ShipmentController@destroy` | Delete shipment (admin/manager). | Path: `id` | JSON message; 200/404. |
| `/api/products/search` | GET | `ProductController@searchByCode` | Find a product by code/barcode. | Query: `code` | JSON product or null; 200. |
| `/api/products` | GET | `ProductController@index` | List products with filters (category, search, low stock, price). | Query: `category_id`, `search`, `low_stock`, `min_price`, `max_price`, pagination flags | JSON paginated products or collection; 200. |
| `/api/products/{id}` | GET | `ProductController@show` | Get a product by id. | Path: `id` | JSON product; 200/404. |
| `/api/products` | POST | `ProductController@store` | Create product (admin/manager). | Body: product fields | JSON message and product; 201. |
| `/api/products/{id}` | PUT | `ProductController@update` | Update product (admin/manager). | Path: `id`; body fields | JSON message and product; 200/404. |
| `/api/products/{id}` | DELETE | `ProductController@destroy` | Delete product (admin/manager). | Path: `id` | JSON message; 200/404. |
| `/api/delivery-suppliers` | GET | `DeliverySupplierController@index` | List delivery suppliers. | Header token | JSON collection; 200. |
| `/api/delivery-suppliers/{id}` | GET | `DeliverySupplierController@show` | Show delivery supplier. | Path: `id` | JSON supplier; 200/404. |
| `/api/delivery-suppliers` | POST | `DeliverySupplierController@store` | Create delivery supplier (admin/manager). | Body: supplier fields | JSON message and supplier; 201. |
| `/api/delivery-suppliers/{id}` | PUT | `DeliverySupplierController@update` | Update delivery supplier (admin/manager). | Path: `id`; body fields | JSON message and supplier; 200/404. |
| `/api/delivery-suppliers/{id}` | DELETE | `DeliverySupplierController@destroy` | Delete delivery supplier (admin/manager). | Path: `id` | JSON message; 200/404. |
| `/api/customers` | GET | `CustomerController@index` | List customers. | Header token | JSON collection; 200. |
| `/api/customers/{id}` | GET | `CustomerController@show` | Show customer. | Path: `id` | JSON customer; 200/404. |
| `/api/customers` | POST | `CustomerController@store` | Create customer (admin/manager). | Body: customer fields | JSON message and customer; 201. |
| `/api/customers/{id}` | PUT | `CustomerController@update` | Update customer (admin/manager). | Path: `id`; body fields | JSON message and customer; 200/404. |
| `/api/customers/{id}` | DELETE | `CustomerController@destroy` | Delete customer (admin/manager). | Path: `id` | JSON message; 200/404. |
| `/api/orders` | GET | `OrderController@index` | List orders. | Header token | JSON collection; 200. |
| `/api/orders/{id}` | GET | `OrderController@show` | Show order details. | Path: `id` | JSON order; 200/404. |
| `/api/orders` | POST | `OrderController@store` | Create order (POS checkout). | Body: order payload | JSON message and order; 201. |
| `/api/orders/{id}` | PUT | `OrderController@update` | Update order (admin/manager). | Path: `id`; body fields | JSON message and order; 200/404. |
| `/api/orders/{id}` | DELETE | `OrderController@destroy` | Delete order (admin/manager). | Path: `id` | JSON message; 200/404. |
| `/api/order-products` | GET | `OrderProductController@index` | List order line items. | Header token | JSON collection; 200. |
| `/api/order-products/{id}` | GET | `OrderProductController@show` | Show order item. | Path: `id` | JSON item; 200/404. |
| `/api/order-products` | POST | `OrderProductController@store` | Add order item. | Body: item fields | JSON message and item; 201. |
| `/api/order-products/{id}` | PUT | `OrderProductController@update` | Update order item. | Path: `id`; body fields | JSON message and item; 200/404. |
| `/api/order-products/{id}` | DELETE | `OrderProductController@destroy` | Delete order item. | Path: `id` | JSON message; 200/404. |
| `/api/reports/sales` | GET | `ReportController@salesReport` | Summarized sales report. | Header token; optional filters | JSON analytics; 200. |
| `/api/reports/top-products` | GET | `ReportController@topSellingProducts` | Top-selling products report. | Header token | JSON analytics; 200. |
| `/api/reports/monthly-sales` | GET | `ReportController@monthlySalesReport` | Monthly sales trend report. | Header token | JSON analytics; 200. |
| `/api/reports/pos` | GET | `ReportController@posReport` | POS transaction report. | Header token | JSON analytics; 200. |
| `/api/analytics/sales-trends` | GET | `AnalyticsController@salesTrends` | Sales trends with period grouping. | Query: `days` | JSON trends data; 200/500. |
| `/api/analytics/top-products` | GET | `AnalyticsController@topProducts` | Top products by quantity and revenue. | Query: `limit` | JSON analytics; 200/500. |
| `/api/analytics/revenue` | GET | `AnalyticsController@revenueAnalysis` | Revenue aggregates and period breakdowns. | Query: `period` | JSON revenue metrics; 200/500. |
| `/api/analytics/inventory-turnover` | GET | `AnalyticsController@inventoryTurnover` | Inventory turnover metrics across products. | none | JSON turnover ratios; 200/500. |
| `/api/analytics/sales-forecast` | GET | `AnalyticsController@salesForecast` | Forecasted sales using Prophet/ARIMA pipeline. | none | JSON forecast series; 200/500. |
| `/api/analytics/ai-insights` | GET | `AnalyticsController@aiInsights` | Gemini-powered narrative insights (stricter rate limit). | Query: prompt/context | JSON AI insight payload; 200/429/500. |
| `/api/categories` | GET | `CategoryController@index` | List categories (admin/manager). | Header token | JSON collection; 200. |
| `/api/categories` | POST | `CategoryController@store` | Create category (admin/manager). | Body: category fields | JSON category; 201. |
| `/api/categories/{category}` | GET | `CategoryController@show` | Show category (admin/manager). | Path: `category` | JSON category; 200/404. |
| `/api/categories/{category}` | PUT | `CategoryController@update` | Update category (admin/manager). | Path: `category`; body fields | JSON category; 200/404. |
| `/api/categories/{category}` | DELETE | `CategoryController@destroy` | Delete category (admin/manager). | Path: `category` | JSON message; 200/404. |

