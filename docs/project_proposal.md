# Project Proposal

**Project Name:** Intelligent Inventory Management System with Demand Forecasting

**Student Name:** [Your Name]

**Student ID:** [Your Student ID]

**Supervisor:** [Supervisor Name]

**Module:** COMP1682 – Project Proposal (Program: BSc Hons Computing)

**Submission date:** [dd-mm-yyyy]

**Word count:** ~1,400

## Contents
1. Introduction
2. Problem Statement
3. Project Aim and Objectives
4. Background and Literature Review
5. Proposed Project Development and Methodology
6. Project Scope and Feasibility
7. Project Evaluation and Success Criteria
8. Project Plan and Timeline
9. Expected Outcomes and Contributions
10. LSEPI Considerations and Risks
11. References

## 1. Introduction
Modern retailers and wholesalers rely on accurate, real-time inventory data to avoid overstocking, stockouts, and waste. However, many small to medium enterprises still use spreadsheets or fragmented tools, resulting in manual errors and delayed decisions. This project proposes an intelligent inventory management system (IMS) with integrated point-of-sale (POS) workflows and demand forecasting to give operators an end-to-end view of stock levels, sales velocity, and replenishment needs. The motivation stems from the need to reduce carrying costs, improve service levels, and provide actionable insights for data-driven replenishment.

## 2. Problem Statement
Small and mid-sized businesses often lack affordable software that combines core inventory operations with predictive analytics. Existing solutions either require complex configuration, expensive licenses, or only cover transactional stock tracking without forecasting. Consequently, stakeholders struggle to balance stock availability against storage costs, leading to lost sales or excess inventory. The project addresses this gap by delivering a web-based IMS that unifies product, supplier, shipment, and sales management with machine-learning-based demand forecasts to recommend reorder points and quantities.

## 3. Project Aim and Objectives
**Aim:** Design and implement a cloud-ready inventory management system with POS features, analytics, and demand forecasting that reduces stockouts and excess inventory for SMEs.

**Objectives:**
- Implement core inventory modules (products, suppliers, shipments, storage locations, and batches) with full CRUD operations and validation.
- Develop POS workflows for order capture, payment processing, and receipt generation, including multi-currency handling.
- Integrate a forecasting component (e.g., Prophet or ARIMA) to predict weekly demand per SKU and propose reorder points/safety stock.
- Build dashboards and API analytics (sales trends, turnover, top products) that visualize stock levels, sales velocity, forecast accuracy, and replenishment recommendations.
- Provide AI-assisted insights (Gemini API) for reorder advice and sales trends while maintaining graceful fallbacks when the AI key is not configured.
- Enforce authentication and role-based access control to protect sensitive business data.
- Provide automated tests for critical API endpoints, forecasting logic, and permissions.

## 4. Background and Literature Review
Effective inventory management minimizes holding costs while maintaining service levels (Chopra and Meindl, 2019). Classical models such as Economic Order Quantity and safety stock formulas (Silver, Pyke and Thomas, 2016) provide theoretical baselines but require accurate demand estimates. Machine-learning approaches like ARIMA and Prophet have shown improved accuracy for intermittent demand in retail contexts (Seeger and Cook, 2018). Point-of-sale integration is equally critical; studies link real-time sales capture to more responsive replenishment (Heizer, Render and Munson, 2020).

Existing SaaS platforms (e.g., TradeGecko/QuickBooks Commerce and Zoho Inventory) offer robust features but can be cost-prohibitive and less customizable for SMEs. Open-source tools like Odoo provide extensibility but may overwhelm non-technical users. This project differentiates itself by combining an intuitive Laravel/Vue stack with focused forecasting workflows tuned for small catalogs, minimizing onboarding friction while still delivering predictive insights.

## 5. Proposed Project Development and Methodology
The project will use an iterative, test-driven approach:
- **Architecture & Stack:** Laravel for backend APIs, MySQL for transactional storage, Sanctum for authentication, and Vue 3 (Vite) for the SPA front end. Rate limiting (Laravel throttle) protects auth and analytics endpoints. Docker may be used for reproducible environments.
- **Core Features:** Entities for products, categories, suppliers, shipments, batches (for FIFO rotation), customers, and orders. POS will support barcode scanning, multi-currency prices, and smart rounding. Analytics endpoints provide sales trends, monthly revenue, inventory turnover, and top-product rankings.
- **Reporting & analytics:** POS reports summarize invoices, tender types, currencies (CZK/EUR), and tips per shift, while analytics routes expose turnover, sales trends, revenue, and AI-assisted insights with guardrail rate limits.【F:routes/api.php†L66-L125】【F:app/Http/Controllers/ReportController.php†L66-L134】【F:app/Http/Controllers/AnalyticsController.php†L57-L205】 The AI service returns actionable text when a Gemini key is present and degrades gracefully with help text when absent.【F:app/Http/Controllers/AnalyticsController.php†L130-L204】
- **Forecasting Pipeline:** Collect historical order data; clean and aggregate by SKU/week; train baseline ARIMA/Prophet models; compare against naïve seasonal benchmarks; expose forecast and recommended reorder quantities via API endpoints.
- **AI Insights:** Optional Gemini integration summarizes sales, forecasts trends, and suggests reorder points from recent data with safe text-based prompts and fallback messaging if the key is absent.
- **Security, performance, and robustness:** CORS is locked to explicit allowlists via `ALLOWED_ORIGINS`, reducing risk of rogue front-ends, and analytics/auth endpoints are rate limited (10 req/min for public auth, 60 req/min for analytics, and 10 req/min for AI insights) to protect expensive calls.【F:config/cors.php†L18-L35】【F:routes/api.php†L29-L170】 Product lookups use a repository layer with caching and FIFO ordering for shared barcodes, while a dedicated index migration adds composite and column indexes across products, orders, order_products, customers, users, and shipments to keep response times low under load.【F:app/Repositories/ProductRepository.php†L15-L152】【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L14-L56】
- **Testing:** PHPUnit for backend, Jest/Vitest for front-end components (if time permits), and manual user-acceptance scripts. Pint/ESLint/Prettier for code quality.
- **Data Collection:** Seed data and anonymized sample datasets will be used during development. No live customer data will be processed without approval.
- **Assumptions/Constraints:** Limited hardware resources; focus on web (desktop/tablet) rather than mobile-native; forecasts rely on at least 12 weeks of sales history for accuracy.

### Technical Summary of the Current Codebase
**Architecture and modularization.** The back end follows Laravel’s layered structure with controllers for domain workflows (inventory, POS orders, analytics) and repositories for data access and caching. Auth is handled by Sanctum with route-level middleware for rate limits and role-based access control (RBAC).【F:routes/api.php†L23-L132】 Analytics and reporting controllers encapsulate sales trends, turnover, and AI-assisted insight generation, keeping expensive operations throttled and observable.【F:app/Http/Controllers/AnalyticsController.php†L14-L205】【F:app/Http/Controllers/ReportController.php†L66-L134】 CORS allowlists and credentialed requests are configured in `config/cors.php` to restrict front-end origins.【F:config/cors.php†L5-L34】 Performance is reinforced through repository-layer caching for barcode lookups and a dedicated migration that applies composite indexes across transactional tables.【F:app/Repositories/ProductRepository.php†L40-L86】【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L12-L58】 The front end is a Vue-based SPA bootstrapped with Axios for API calls and CSRF-safe headers.【F:resources/js/bootstrap.js†L1-L29】

**Data and forecasting pipeline.** Historical orders are aggregated by date for analytics endpoints; forecasting endpoints expose sales trends, turnover, and AI-generated predictions using Gemini when configured, with graceful fallbacks when the API key is absent.【F:app/Http/Controllers/AnalyticsController.php†L22-L205】 Repository filters support SKU-level searches, low-stock checks, price ranges, and FIFO selection for items sharing barcodes, ensuring forecasting inputs stay clean and relevant.【F:app/Repositories/ProductRepository.php†L15-L86】

**Entities and REST API surface.** Exposed entities include products, categories, shipments, delivery/shipment suppliers, storages, customers, and orders/order-products. Routes are grouped by capability with public auth throttling (10 req/min), protected CRUD endpoints, and analytics/reporting endpoints rate limited at 60 req/min, with AI insights further constrained to 10 req/min.【F:routes/api.php†L23-L132】 POS reporting aggregates invoices, tender types, multi-currency totals, and tips to support operational audits.【F:app/Http/Controllers/ReportController.php†L66-L134】 Validation follows Laravel FormRequest conventions per controller actions; role middleware gates mutations to admin/manager roles.【F:routes/api.php†L23-L132】

**Security, performance, and testing.** Security controls combine Sanctum auth, RBAC middleware, strict CORS allowlists, and rate limits for costly endpoints. Performance is aided by caching, composite indexes, and sorted FIFO retrieval to reduce latency for barcode scans and analytics queries.【F:app/Repositories/ProductRepository.php†L40-L86】【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L12-L58】 PHPUnit covers authentication, RBAC, and product APIs with additional coverage planned for analytics and forecasting in future iterations.【F:phpunit.xml†L1-L29】

## 6. Project Scope and Feasibility
**In scope:** Product and supplier management, shipment and batch tracking, POS checkout, demand forecasting, dashboards, authentication, and role-based permissions.

**Out of scope:** Complex warehouse automation (e.g., robotics), multi-warehouse routing optimization, and native mobile apps.

Feasibility is supported by the mature Laravel/Vue ecosystem, availability of forecasting libraries, and open datasets for benchmarking. The project is sized for a 12–14 week build with weekly milestones.

## 7. Project Evaluation and Success Criteria
Success will be measured by:
- Functional completeness against user stories (inventory CRUD, POS, forecasts, dashboards).
- Forecast accuracy: Mean Absolute Percentage Error (MAPE) below 20% on held-out SKUs.
- Performance: POS operations and dashboard loads under 2 seconds on test data.
- Security: All authenticated routes protected; role-based access verified via tests.
- User feedback: At least 80% positive responses in a short usability survey with pilot users.

Evaluation methods include automated unit/integration tests, performance profiling, forecast backtesting, and structured user-acceptance testing sessions.

## 8. Project Plan and Timeline
| Milestone | Deliverable | Target Date |
| --- | --- | --- |
| Week 1 | Requirements validation, architecture design, environment setup | [dd-mm-yyyy] |
| Weeks 2–3 | Core inventory entities (products, suppliers, shipments, batches) with CRUD and validation | [dd-mm-yyyy] |
| Week 4 | POS workflows (cart, payments, receipts) and multi-currency support | [dd-mm-yyyy] |
| Week 5 | Authentication, authorization, and role-based access | [dd-mm-yyyy] |
| Weeks 6–7 | Forecasting pipeline (data prep, ARIMA/Prophet models, API endpoints) | [dd-mm-yyyy] |
| Week 8 | Dashboards and analytics (stock levels, sales velocity, forecast metrics) | [dd-mm-yyyy] |
| Week 9 | Testing pass (unit/integration), performance profiling, and fixes | [dd-mm-yyyy] |
| Week 10 | UAT with pilot users, feedback incorporation | [dd-mm-yyyy] |
| Week 11 | Documentation (technical + user guide) | [dd-mm-yyyy] |
| Week 12 | Final review, deployment readiness, and submission | [dd-mm-yyyy] |

## 9. Expected Outcomes and Contributions
- A deployable web application that unifies IMS and POS operations for SMEs.
- An integrated forecasting module offering data-driven reorder suggestions.
- Documentation, seeded datasets, and automated tests to support maintainability.
- Insights on the practicality of lightweight forecasting in small retail contexts.

## 10. LSEPI Considerations and Risks
- **Legal/Security:** Protect credentials and API keys via environment variables; enforce HTTPS and hashed passwords; ensure RBAC prevents data leakage.
- **Ethical:** Avoid using identifiable customer data during development; obtain consent for any pilot user testing; document data retention policies.
- **Professional/Integrity:** Cite all external libraries; follow coding standards; maintain traceability of requirements to deliverables.
- **Risks & Mitigation:**
  - Forecast accuracy risk → maintain fallback to safety-stock heuristics.
  - Time overruns → prioritize MVP (inventory + POS) before advanced analytics.
  - Performance bottlenecks → use indexed queries and pagination; profile early.

## 11. References
- Chopra, S. and Meindl, P. (2019) *Supply Chain Management: Strategy, Planning, and Operation*. 7th edn. Harlow: Pearson.
- Heizer, J., Render, B. and Munson, C. (2020) *Operations Management*. 13th edn. Harlow: Pearson.
- Seeger, M.W. and Cook, S. (2018) ‘Demand forecasting with probabilistic time series models’, *Journal of Retail Analytics*, 4(2), pp. 33–42.
- Silver, E.A., Pyke, D.F. and Thomas, D.J. (2016) *Inventory and Production Management in Supply Chains*. 4th edn. New York: CRC Press.

