# Mermaid Diagram Templates (IMS)

Use these Mermaid snippets to embed architecture and workflow visuals for the Intelligent Inventory Management System (IMS). Each diagram is self-contained and can be copied into markdown that supports Mermaid rendering.

## 1. Use Case Diagram (Admin and Cashier)
```mermaid
%% Store Owner -> Admin, Store Clerk -> Cashier
usecaseDiagram
  actor "Store Owner" as Admin
  actor "Store Clerk" as Cashier

  rectangle IMS {
    usecase "Manage Products" as UCManageProducts
    usecase "Record Sales" as UCRecordSales
    usecase "View Sales Dashboard" as UCSalesDashboard
    usecase "View Forecast" as UCForecast
    usecase "Print Receipt" as UCPrintReceipt
    usecase "Login" as UCLogin
  }

  Admin --> UCManageProducts
  Admin --> UCSalesDashboard
  Admin --> UCForecast
  Admin --> UCPrintReceipt
  Admin --> UCLogin

  Cashier --> UCRecordSales
  Cashier --> UCPrintReceipt
  Cashier --> UCLogin
```

## 2. Entity Relationship Diagram (ERD)
```mermaid
erDiagram
  PRODUCTS {
    int id PK
    string name
    string sku
    int supplier_id FK
    decimal price
    int quantity
    timestamp created_at
    timestamp updated_at
  }
  SUPPLIERS {
    int id PK
    string name
    string contact_email
    string phone
    timestamp created_at
    timestamp updated_at
  }
  USERS {
    int id PK
    string name
    string email
    string role
    timestamp created_at
    timestamp updated_at
  }
  TRANSACTIONS {
    int id PK
    int product_id FK
    int user_id FK
    int quantity
    decimal total
    date transaction_date
    timestamp created_at
  }
  FORECASTS {
    int id PK
    int product_id FK
    date period
    decimal forecast_value
    string model
    timestamp created_at
  }

  SUPPLIERS ||--o{ PRODUCTS : supplies
  PRODUCTS ||--o{ TRANSACTIONS : "appears in"
  USERS ||--o{ TRANSACTIONS : "records"
  PRODUCTS ||--o{ FORECASTS : "forecasted for"
```

## 3. Class Diagram (Controller Responsibilities)
```mermaid
classDiagram
  class AuthController {
    +login(request)
    +logout(request)
    +register(request)
  }

  class InventoryController {
    +listProducts()
    +showProduct(id)
    +createProduct(request)
    +updateProduct(id, request)
    +deleteProduct(id)
  }

  class POSController {
    +createOrder(request)
    +addLineItem(orderId, request)
    +applyPayment(orderId, request)
    +printReceipt(orderId)
  }

  class ForecastController {
    +generateForecast(productId)
    +listForecasts(productId)
    +getForecast(productId, period)
  }

  class DashboardController {
    +salesSummary(range)
    +inventoryHealth()
    +forecastOverview()
  }

  AuthController <.. InventoryController : secures
  AuthController <.. POSController : secures
  AuthController <.. ForecastController : secures
  AuthController <.. DashboardController : secures

  InventoryController --> POSController : "shares product data"
  POSController --> ForecastController : "sends demand data"
  ForecastController --> DashboardController : "feeds insights"
```

## 4. Activity Diagram (POS Workflow)
```mermaid
flowchart TD
  A[Clerk logs in] --> B{Credentials valid?}
  B -- No --> A
  B -- Yes --> C[Search or scan product]
  C --> D{Product found?}
  D -- No --> C
  D -- Yes --> E[Add product to cart]
  E --> F[Confirm transaction]
  F --> G[Inventory updated]
  G --> H[Receipt generated]
  H --> I{Update forecast?}
  I -- Yes --> J[Forecast updated]
  I -- No --> K[Process complete]
  J --> K[Process complete]
```
