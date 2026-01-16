# Database ERD (Derived from Current Migrations)

This document summarizes the database schema defined by the Laravel migrations in `database/migrations`. It lists the primary entities, key columns, and foreign key relationships, then provides a Mermaid ER diagram suitable for inclusion in technical reports.

## Table Summary

- **users** — authentication and roles (`id`, `name`, `email` unique, `password`, `role` enum: admin/staff, timestamps).
- **storages** — storage locations (`id`, `name`, `location`, timestamps).
- **shipment_suppliers** — inbound suppliers (`id`, `name`, timestamps).
- **shipments** — inbound receipts (`id`, `shipment_supplier_id` FK → `shipment_suppliers.id`, `storage_id` FK → `storages.id`, `order_date`, `received_date` nullable, `expired_date` nullable, `cost` decimal, timestamps).
- **products** — catalog and stock (`id`, `name`, `code`, `original_quantity`, `actual_quantity`, `price`, `cost`, `total_cost`, `shipment_id` FK → `shipments.id`, `expired_date`, `tax`, timestamps). *(Code and indexes reference a `category_id` FK to `product_categories`, but the column is absent in the current migrations.)*
- **product_categories** — POS-facing categories (`id`, `name`, `visible_in_pos`).
- **delivery_suppliers** — outbound carriers (`id`, `name`, timestamps).
- **customers** — buyer records (`id`, `name`, `email` unique, `address` nullable, `phone` nullable, `vat_code` nullable, `postal_code` nullable, `city` nullable, timestamps).
- **orders** — sales headers (`id`, `customer_id` FK → `customers.id`, `delivery_supplier_id` FK → `delivery_suppliers.id` nullable, `paid_amount`, timestamps, `shift_id` FK → `shifts.id` nullable). *(Models reference `cashier_id` and payment breakdown fields not present in the migrations.)*
- **order_products** — line items (`id`, `order_id` FK → `orders.id`, `product_id` FK → `products.id`, `quantity`, `price`, timestamps). *(Model also expects a `tax` column not present in the migrations.)*
- **shifts** — cashier shift definitions (`id`, `name`, `start_time`, `end_time`, `sort_order`, timestamps).

## Mermaid ER Diagram

```mermaid
erDiagram
  USERS {
    bigint id PK
    string name
    string email
    string password
    enum role
    timestamp created_at
    timestamp updated_at
  }

  STORAGES {
    bigint id PK
    string name
    string location
    timestamp created_at
    timestamp updated_at
  }

  SHIPMENT_SUPPLIERS {
    bigint id PK
    string name
    timestamp created_at
    timestamp updated_at
  }

  SHIPMENTS {
    bigint id PK
    bigint shipment_supplier_id FK
    bigint storage_id FK
    date order_date
    date received_date
    date expired_date
    decimal cost
    timestamp created_at
    timestamp updated_at
  }

  PRODUCT_CATEGORIES {
    bigint id PK
    string name
    boolean visible_in_pos
  }

  PRODUCTS {
    bigint id PK
    string name
    string code
    int original_quantity
    int actual_quantity
    decimal price
    decimal cost
    decimal total_cost
    bigint shipment_id FK
    date expired_date
    decimal tax
    timestamp created_at
    timestamp updated_at
  }

  DELIVERY_SUPPLIERS {
    bigint id PK
    string name
    timestamp created_at
    timestamp updated_at
  }

  CUSTOMERS {
    bigint id PK
    string name
    string email
    text address
    string phone
    string vat_code
    string postal_code
    string city
    timestamp created_at
    timestamp updated_at
  }

  SHIFTS {
    bigint id PK
    string name
    time start_time
    time end_time
    int sort_order
    timestamp created_at
    timestamp updated_at
  }

  ORDERS {
    bigint id PK
    bigint customer_id FK
    bigint delivery_supplier_id FK
    decimal paid_amount
    bigint shift_id FK
    timestamp created_at
    timestamp updated_at
  }

  ORDER_PRODUCTS {
    bigint id PK
    bigint order_id FK
    bigint product_id FK
    int quantity
    decimal price
    timestamp created_at
    timestamp updated_at
  }

  SHIPMENT_SUPPLIERS ||--o{ SHIPMENTS : supplies
  STORAGES ||--o{ SHIPMENTS : stores
  SHIPMENTS ||--o{ PRODUCTS : delivers
  PRODUCTS }o--|| PRODUCT_CATEGORIES : "categorized as" 
  CUSTOMERS ||--o{ ORDERS : places
  DELIVERY_SUPPLIERS ||--o{ ORDERS : ships
  SHIFTS ||--o{ ORDERS : covers
  ORDERS ||--o{ ORDER_PRODUCTS : contains
  PRODUCTS ||--o{ ORDER_PRODUCTS : "sold as"
  USERS ||--o{ ORDERS : "(optional) cashier" 
```

> Note: Dashed/optional relationships in the diagram (category linkage and cashier reference) reflect model/index expectations. The current migrations lack `products.category_id`, `orders.cashier_id`, and `order_products.tax`; add migrations for those columns if they are required at runtime.
