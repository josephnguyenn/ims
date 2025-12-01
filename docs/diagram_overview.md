# IMS Diagrams (Mermaid)

This document provides Mermaid definitions for embedding or exporting system-level diagrams that reflect the Laravel-based inventory management system with a static HTML/CSS/JavaScript dashboard served from `public/ims-dashboard`.

## System Architecture Diagram

```mermaid
graph TD
  subgraph Client
    A[Admin / Cashier Browser]
    B[Static HTML/CSS/JS Dashboard (public/ims-dashboard)]
  end

  subgraph Edge[Edge Routing]
    C[HTTPS / Nginx]
  end

  subgraph Backend[Laravel API]
    D[Laravel Routes / Controllers]
    E[Services & Repositories\n(Inventory, POS, Analytics, Forecasting)]
    F[Validation & Middleware\n(Sanctum Auth, Rate Limits, CORS)]
  end

  subgraph Data[Persistence & Jobs]
    G[(MySQL / MariaDB)]
    H[Queue / Scheduler\n(Forecasting Jobs)]
  end

  A -->|Loads static assets| B
  B -->|Fetch / Axios requests| C
  C --> D
  D --> E
  D --> F
  E --> G
  E --> H
  F --> G
```

## Entity Relationship Diagram

```mermaid
erDiagram
  USER ||--o{ ORDER : "records POS sales"
  USER ||--o{ SHIPMENT : "creates/updates"
  ROLE ||--o{ USER : "grants RBAC"
  CUSTOMER ||--o{ ORDER : "places"
  ORDER ||--|| SHIPMENT : "fulfills"
  ORDER ||--o{ ORDER_ITEM : "contains"
  PRODUCT ||--o{ ORDER_ITEM : "sold as"
  PRODUCT ||--o{ SHIPMENT : "picked for"

  USER {
    int id
    string name
    string email
    string password_hash
    int role_id
    timestamps timestamps
  }

  ROLE {
    int id
    string name
    text permissions
    timestamps timestamps
  }

  CUSTOMER {
    int id
    string name
    string email
    string phone
    text address
    timestamps timestamps
  }

  PRODUCT {
    int id
    string sku
    string name
    text description
    decimal price
    int stock_quantity
    int reorder_level
    timestamps timestamps
  }

  ORDER {
    int id
    int customer_id
    int user_id
    decimal total_amount
    string status
    datetime ordered_at
    timestamps timestamps
  }

  ORDER_ITEM {
    int id
    int order_id
    int product_id
    int quantity
    decimal unit_price
    decimal line_total
    timestamps timestamps
  }

  SHIPMENT {
    int id
    int order_id
    int user_id
    string carrier
    string tracking_number
    string status
    datetime shipped_at
    timestamps timestamps
  }
```

## Use Case Diagram

```mermaid
flowchart TD
  actorAdmin([Admin])
  actorCashier([Cashier])

  ucManageProducts((Manage Products\nCRUD))
  ucInventory((Adjust Stock / Shipments))
  ucPOS((Process POS Sale))
  ucReceipts((Print Receipt))
  ucReports((View Reports & Analytics))
  ucForecast((Run Demand Forecast))
  ucUsers((Manage Users & Roles))

  actorAdmin --> ucManageProducts
  actorAdmin --> ucInventory
  actorAdmin --> ucReports
  actorAdmin --> ucForecast
  actorAdmin --> ucUsers

  actorCashier --> ucManageProducts
  actorCashier --> ucPOS
  actorCashier --> ucReceipts
  actorCashier --> ucReports
```
