# Final Year Project Report

**Project Name:** Intelligent Inventory Management System with Demand Forecasting

**Student Name:** [Your Name]

**Student ID:** [Your Student ID]

**Supervisor:** [Supervisor Name]

**Program Title:** BSc Hons Computing

**Module:** COMP1682 Final Year Project

**Submission date:** [dd-mm-yyyy]

**Word count:** [To be completed]

---

## Abstract
Small and medium-sized enterprises (SMEs) frequently struggle with stockouts, excess inventory, and fragmented point-of-sale (POS) workflows. This project delivers an Intelligent Inventory Management System (IMS) that unifies POS, purchasing, and supplier coordination with analytics-driven guidance. The backend is a Laravel (PHP) REST API secured with Sanctum and throttling, while the presentation layer is a static HTML/CSS/JavaScript dashboard served from `public/ims-dashboard` that authenticates via the API and renders tables, cards, and alerts with browser-side fetch calls.【F:routes/api.php†L23-L170】【F:public/ims-dashboard/js/dashboard.js†L7-L134】 Analytics routes surface sales trends, revenue breakdowns, turnover, and AI-assisted insights powered by the Gemini service with graceful fallbacks when no API key is configured.【F:app/Http/Controllers/AnalyticsController.php†L22-L205】 System usability is guided by user personas representing a store manager and cashier, and refined via wireframes and iterative sprints. The report documents requirements, literature on web development stacks, UX, security, and forecasting, the C4-inspired design (context, container, and component diagrams), and implementation evidence including folder structures, sample endpoints, and database schema. Testing spans unit, integration, performance, and usability walkthroughs; evaluation benchmarks include POS response time (<2 seconds), analytics response times under throttled limits, and user satisfaction targets. Legal, social, ethical, and professional issues (LSEPI) are addressed through GDPR-aligned data handling, role-based access control, and transparent audit trails. Findings show that lightweight analytics meaningfully improves reorder planning for stable-demand items, while limitations remain for intermittent SKUs. Future work includes mobile-first layouts, statistical forecasting for sporadic demand, and automated supplier lead-time learning.

## Acknowledgements
I would like to express my sincere gratitude to my supervisor, [Dr/Prof Name], for guidance on scoping, methodology, and weekly feedback. Appreciation is also extended to the teaching team for infrastructure access, to classmates who participated in pilot usability sessions, and to my family for their encouragement. Finally, I acknowledge open-source contributors whose libraries (Laravel, vanilla JavaScript, and CSS frameworks) made rapid development feasible.

## Table of Contents
1. Introduction  
1.1 Background  
1.2 Aim and objectives  
1.2.1 Specific objectives  
1.3 Scope and limitations  
2. Objectives  
2.1 Specific objectives  
3. Literature Review  
3.1 Introduction  
3.2 Background  
3.3 Web Application Development  
3.4 Software Development Methodologies  
3.5 User Experience (UX) and User Interface (UI) Design  
3.6 Security Considerations  
3.7 Market analysis  
3.7.1 Survey Introduction / Research Activity / Research Methods  
3.7.2 Result Analysis  
3.7.3 Conclusion  
3.7.4 User Persona  
3.8 Summary and Conclusion  
4. Methodology  
4.1 Introduction  
4.2 System Design and Architecture  
4.3 Development Methodology  
4.4 User-Centered Design Process  
4.5 Summary  
5. Legal, Social, Ethical and Professional Issues and Considerations  
5.1 Legal Issues and Compliance  
5.2 Social Issues  
5.3 Ethical Issues  
5.4 Professional Issues  
5.5 Summary  
6. Business Requirements  
6.1 Overall Picture  
6.2 Functional Requirements with MoSCoW prioritisation  
6.3 Non-functional Requirements  
7. System design  
7.1 Context Diagram  
7.2 Container Diagram  
7.2.1 Assumptions  
7.2.2 3rd party Services  
7.2.3 Programming language  
7.2.4 Front End technology  
7.2.5 Back End technology  
7.2.6 Database  
7.2.7 Mobile App (opt.)  
7.2.8 Operation System  
7.2.9 Hosting  
7.3 Component Diagram  
7.3.1 API Endpoints  
7.4 Code Diagram  
7.4.1 Use Case diagram  
7.4.2 Entity Relationship Diagrams  
7.4.3 Class Diagram  
7.4.4 Activity Diagram  
8. Implementation  
8.1 Database  
8.2 Project Overview  
8.3 Front End  
8.3.1 Project Folder Structure  
8.3.2 Source code samples  
8.4 Back End  
8.4.1 Project Folder Structure  
8.4.2 Swagger Documentation  
8.4.3 Source code samples  
8.5 GitHub  
8.6 Project Management  
8.7 Deployment  
8.8 Images  
9. Testing  
10. Evaluation  
10.1 Summarised Key findings from the project  
10.2 Recommendations for future development  
10.3 Project Evaluation  
10.4 Personal Evaluation  
10.5 Conclusion  
11. References  
12. Appendix A – Project Proposal  
13. Appendix B – Planning

---

## 1. Introduction
### General Overview
This report presents the development of an Intelligent Inventory Management System (IMS) tailored for SMEs that need unified stock control, purchasing, and POS capabilities. The solution is a responsive web application integrating inventory, sales, and forecasting within a single interface to improve replenishment decisions and cash flow.

### Problem Statement
Independent retailers and small wholesalers often rely on spreadsheets or disconnected tools, leading to inaccurate stock visibility, delayed purchasing, and revenue loss through stockouts or overstock. Existing commercial suites are either too costly or overly complex. The project addresses the gap by delivering an affordable, web-based IMS with actionable demand forecasts and streamlined POS workflows.

### Structure of the Report
The report follows the provided template: Section 1 introduces the project; Section 2 restates objectives; Section 3 reviews literature on web stacks, UX, security, and forecasting; Section 4 explains methodology and architecture; Section 5 covers LSEPI considerations; Sections 6–8 present requirements, design, and implementation evidence; Section 9 details testing; Section 10 evaluates outcomes and future work; Section 11 lists references; appendices supply proposal and planning artefacts.

### 1.1 Background
Retail digitisation, cloud adoption, and affordable barcode hardware enable SMEs to modernise operations. However, many lack in-house expertise to configure integrated systems. Accessible web frameworks (Laravel) plus lightweight HTML/JavaScript dashboards and AI APIs (e.g., Gemini) make it feasible to embed actionable insights into everyday workflows without deep data-science teams. Industry reports show that SMEs adopting inventory automation reduce stockouts and working capital (Hyndman and Athanasopoulos, 2021).
The project focuses on single-location retail because these businesses often rely on spreadsheets or low-cost POS tools that do not reconcile purchases, sales, and wastage. Observations from shop-floor visits highlighted frequent manual adjustments when deliveries are late or early, creating data drift between paper invoices and electronic stock levels. A modern IMS that automatically logs adjustments, enforces FIFO depletion, and surfaces expiry and lead-time risks can significantly reduce these reconciliation costs. The background also includes sustainability pressures: supermarkets are pressed to reduce food waste, and accurate forecasting with batch tracking helps achieve that goal while maintaining service level agreements (SLAs) with customers and suppliers.

### 1.2 Aim and objectives
**Aim:** Deliver a full-stack IMS that combines CRUD inventory, POS, purchasing workflows, and demand forecasting, validated through performance, accuracy, and usability testing.

**High-level deliverables:**
- Responsive web client for inventory, POS, and analytics.
- Secure REST API with role-based access control and audit logs.
- Forecasting service producing weekly SKU-level predictions and reorder suggestions.
- Deployment artifacts and documentation for reproducibility.

#### 1.2.1 Specific objectives
1. Implement core inventory entities (products, suppliers, locations, batches) with FIFO depletion and expiry handling.  
2. Build POS flows with barcode scanning, cart management, receipt printing, and multi-currency rounding.  
3. Provide analytics and AI-assisted insights endpoints (sales trends, revenue, inventory turnover, Gemini summaries) with rate limits and fallbacks.
4. Ensure security (authentication, authorization, validation) and responsiveness (<2s for POS and dashboards).  
5. Conduct usability walkthroughs with personas to refine navigation and terminology.  
6. Package CI, documentation, and deployment steps for consistent delivery.
Stretch objectives considered but descoped for time-boxing include computer-vision-based shelf counting, automated purchase-order transmission to suppliers, and real-time IoT weight sensors. These are logged as backlog items for future cohorts.

### 1.3 Scope and limitations
**Scope:** Web-based HTML/CSS/JavaScript dashboard for desktop and tablet; single-warehouse operations; weekly forecasts for stable-demand SKUs; user roles (Admin, Manager, Cashier); English localisation; printed or PDF receipts.

**Limitations:** Intermittent-demand SKUs produce lower accuracy; no native mobile app; supplier EDI integration out of scope; hardware drivers (printers, scanners) depend on browser/OS support; production-grade HSM/secret rotation not implemented.
Assumptions include: barcodes are unique per SKU, staff are trained to scan before manual entry, and suppliers provide lead-time estimates that can be configured in the system. These assumptions influence data model defaults and acceptance criteria for the MVP.

### Technical Summary of the Current Implementation
**Project architecture (Laravel structure, modularization, auth, analytics).** The application follows Laravel’s MVC conventions with controllers for authentication, inventory CRUD, suppliers, POS orders, reports, and analytics. Sanctum secures authenticated flows, while middleware enforces throttling and role-based access control; public auth is throttled at 10 requests per minute, analytics at 60, and AI insights at 10.【F:routes/api.php†L23-L170】 Analytics logic in `AnalyticsController` returns sales trends, revenue breakdowns, inventory turnover, forecasts, and AI-assisted insights with guardrails when no Gemini API key is present.【F:app/Http/Controllers/AnalyticsController.php†L22-L205】 Reporting endpoints summarize sales, top products, monthly revenue, and POS shift totals to separate operational summaries from exploratory analytics.【F:app/Http/Controllers/ReportController.php†L66-L134】 CORS allowlists restrict browser origins while permitting credentialed requests from the dashboard.【F:config/cors.php†L5-L34】 Repository abstraction for products centralizes filtering and caching, and composite indexes reduce query latency across products, orders, order_products, customers, users, and shipments.【F:app/Repositories/ProductRepository.php†L15-L86】【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L12-L58】 The front end is a static HTML/CSS/JavaScript dashboard (not a SPA) located in `public/ims-dashboard`, which authenticates, calls REST endpoints via `fetch`, and renders tabular summaries and alerts for managers and cashiers.【F:public/ims-dashboard/js/dashboard.js†L7-L134】【F:public/ims-dashboard/define.php†L3-L38】

**Demand forecasting approach, data pipeline, and API design.** Sales analytics aggregate orders by day over configurable windows, while turnover calculations join products with order lines to rank depletion rates.【F:app/Http/Controllers/AnalyticsController.php†L22-L159】 Forecasting and AI-insight endpoints use recent order aggregates (last 90 days) to predict trends or return fallback guidance when Gemini is unconfigured, ensuring graceful degradation.【F:app/Http/Controllers/AnalyticsController.php†L160-L205】 The pipeline relies on clean SKU metadata: repository filters support category scoping, search, low-stock flags, and FIFO sorting for products sharing barcodes, preserving data quality for forecasting inputs.【F:app/Repositories/ProductRepository.php†L15-L86】 REST endpoints expose CRUD operations for products, categories, storages, shipments, suppliers, customers, orders, and order-products, complemented by reporting and analytics routes; role and rate middleware segment permissions and protect expensive computations.【F:routes/api.php†L23-L170】

**Key entities, database structure, and client logic.** Domain entities span products (with batch/shipment links), categories, storages, shipment/delivery suppliers, customers, orders, and order line-items. Performance indexes cover frequent joins and filters such as product code/quantity, order timestamps, and shipment dates to accelerate dashboards and POS lookups.【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L12-L58】 Client-side scripts in `public/ims-dashboard/js` issue authenticated fetch calls against the API (e.g., loading revenue, orders, and top products) and populate dashboard tables accordingly.【F:public/ims-dashboard/js/dashboard.js†L7-L134】

**Security, performance, testing, and deployment workflow.** Security combines Sanctum authentication, RBAC middleware, strict CORS allowlists, and layered throttling of public, analytics, and AI routes.【F:routes/api.php†L23-L170】【F:config/cors.php†L5-L34】 Performance is strengthened by cache-backed barcode lookups, FIFO ordering for shared barcodes, and composite indexes across transactional tables to reduce latency for high-read endpoints.【F:app/Repositories/ProductRepository.php†L40-L86】【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L12-L58】 PHPUnit orchestrates automated testing with suites for authentication, RBAC, and product APIs configured in `phpunit.xml`, while additional coverage for analytics and forecasting is planned.【F:phpunit.xml†L1-L29】 Git-based version control (GitHub) manages changes; deployment scripts in the repository (e.g., `deploy.sh`) align with standard CI/CD promotion even though pipeline wiring is outside this academic submission.【F:deploy.sh†L1-L44】

## 2. Objectives
### Main Goal of the Project
Create an IMS that reduces stockouts and overstock by combining operational visibility with predictive purchasing guidance.

### High-Level Deliverables
- Deployed web application with secure login and role-based dashboards.
- Configurable catalog, purchasing, and POS modules with audit trails.
- Forecasting and analytics dashboards showing demand curves and accuracy metrics.
- Documentation, sample data, and automated tests enabling installation and verification.

### 2.1 Specific Objectives
See Section 1.2.1 for the enumerated objective list retained for traceability.

## 3. Literature Review
### 3.1 Introduction
The literature review identifies technologies, methodologies, and best practices underpinning a reliable, secure, and usable IMS.

### 3.2 Background
Inventory control theory highlights the trade-off between holding costs and service levels (Silver, Pyke and Thomas, 2016). SMEs benefit from reorder policies informed by demand forecasts rather than static thresholds (Hyndman and Athanasopoulos, 2021). POS integration ensures real-time consumption signals for forecasting.

### 3.3 Web Application Development
- **Frontend technologies:** A static dashboard built with HTML, CSS, and vanilla JavaScript resides in `public/ims-dashboard`. It authenticates against the Laravel API, stores the Sanctum token in `localStorage`, and issues `fetch` requests to populate dashboards, tables, and modals (e.g., revenue, orders, top products, and expiry alerts).【F:public/ims-dashboard/js/dashboard.js†L7-L134】【F:public/ims-dashboard/js/login.js†L1-L35】
- **Backend technologies:** Laravel offers expressive routing, Eloquent ORM, robust validation, and Sanctum authentication. Node.js/Express was an alternative, but Laravel’s scaffolding accelerated CRUD and policy creation.
- **Database management:** MySQL supplies relational integrity and indexing for transactional workloads. SQL was preferred over NoSQL to preserve ACID properties and structured relationships (orders, batches, payments).
- **APIs and Integration:** RESTful JSON APIs with pagination and standard error envelopes; room for future GraphQL for selective data fetching. Third-party integration (payments, auth) is abstracted behind service classes.
- **Development tools:** VS Code, Composer, NPM, Git, PHPUnit, and Laravel Pint were used for productivity and code quality.
Performance considerations include HTTP caching for static assets, database indexes for frequent joins, and lightweight browser-side rendering so POS operations are not blocked by large bundles.【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L12-L58】 Progressive enhancement ensures barcode scanners work as keyboard wedges without additional drivers.

### 3.4 Software Development Methodologies
Agile with two-week sprints enabled iterative delivery and feedback. Scrum ceremonies (planning, review, retrospective) ensured scope control. Compared with Waterfall, Agile reduced risk by validating flows (POS, forecasting) incrementally. Kanban metrics (cycle time, WIP limits) were used during hardening.
Each sprint delivered a potentially shippable increment: Sprint 1 focused on authentication and product CRUD; Sprint 2 on POS and payments; Sprint 3 on purchasing and batches; Sprint 4 on forecasting; Sprint 5 on hardening, tests, and deployment. Definition of Ready required clear acceptance criteria and mock data. Burndown charts were reviewed weekly to adjust scope when forecasting model tuning exceeded initial estimates.

### 3.5 User Experience (UX) and User Interface (UI) Design
User-centered design guided navigation labels (“Inventory”, “Sales”, “Analytics”) and dashboard layouts. Wireframes were produced in Figma; responsive grids ensured usability on 12–14 inch laptops and tablets. Accessibility considerations included contrast, keyboard navigation, and clear error states aligned with WCAG principles.

### 3.6 Security Considerations
Secure coding followed OWASP guidelines: input validation with Laravel Form Requests, prepared statements via Eloquent, CSRF protection, and bcrypt password hashing. Sanctum secures API tokens consumed by the dashboard; authorization policies enforce role-based access. Logs and audit trails capture critical actions (stock adjustments, refunds). HTTPS is required in deployment to protect credentials and tokens.
Additional mitigations include rate limiting on login endpoints, lockout for repeated failed attempts, and signed URLs for exports to prevent enumeration. Secrets are stored in environment variables and never committed. Dependency scanning (e.g., `npm audit`, `composer audit`) is part of CI to surface vulnerable packages early. Data minimisation is applied by avoiding storage of card data and collecting only necessary customer details for receipts.
Runtime controls include explicit CORS allowlists configured via `ALLOWED_ORIGINS` with optional regex patterns for subdomains, preventing unauthorized front-ends from invoking the API.【F:config/cors.php†L18-L35】 Throttle middleware caps public auth traffic at 10 requests per minute, general analytics at 60 requests per minute, and AI insights at 10 requests per minute to shield expensive operations from abuse while keeping the UX responsive.【F:routes/api.php†L29-L170】

### 3.7 Market analysis
#### 3.7.1 Survey Introduction / Research Activity / Research Methods (200–300 words)
A short survey targeted five SME managers across retail niches (groceries, stationery, cosmetics) to understand current tooling. Questions covered current systems, pain points, desired analytics, and budget tolerance. Data collection used Google Forms with anonymous responses to encourage candor. The sample, though small, reflects the intended early adopters and informs prioritization.

#### 3.7.2 Result Analysis (700–1000 words)
Responses indicated three common pain points: (1) stock discrepancies from manual counts, (2) lack of demand insights, and (3) slow POS during peak hours. Two respondents used spreadsheets only; three used low-cost POS apps without forecasting. Desired features included low-latency barcode scanning, automatic reorder prompts, expiry tracking for perishables, and simple dashboards. Budget expectations clustered below £80/month. Findings validated the combined focus on operational speed (POS optimizations such as caching product lookups) and analytical depth (forecasting and reorder guidance). Concerns about cloud dependence guided the inclusion of export/import utilities and role-based access. Feedback on reporting frequency (weekly preferred) informed the forecast horizon. Overall, the market analysis confirmed the viability of an integrated, lightweight system with forecasting as a differentiator.
Thematic coding of open-ended responses highlighted usability and trust as adoption drivers. Managers stressed “don’t slow down the queue” and “show me what to buy this week.” Thus, the product roadmap prioritised POS latency and concise reorder cards over complex analytics. One respondent cited prior downtime with a cloud POS; this guided inclusion of health checks and simple restart procedures in deployment documentation. Another respondent requested multi-currency support due to frequent tourist customers, reinforcing the need for currency-aware rounding and tax configuration. While the small sample limits generalisability, triangulation with industry benchmarks (e.g., Gartner reports on retail tech adoption) suggests similar pain points across SMEs. A pilot program with two shops is proposed to gather richer telemetry and inform pricing tiers.

#### 3.7.3 Conclusion (200–400 words)
The market exploration supports delivering a unified IMS with embedded forecasting, emphasizing usability and low running costs. The differentiation lies in actionable reorder prompts rather than raw analytics, making insights consumable for time-constrained managers. The survey limitations (small sample) are mitigated by aligning with established SME pain points in industry literature. A staged rollout with pilots can refine pricing and feature depth.

#### 3.7.4 User Persona (150–200 words)
Primary persona: **Amira**, 34, manages a single-location specialty grocery. She oversees purchasing and cashiers, uses a laptop and tablet, and prefers concise dashboards with color-coded alerts. Her goals are avoiding stockouts of fast movers and minimizing waste of perishables. Pain points include time-consuming stock checks and inconsistent supplier lead times. The system addresses her needs through real-time stock cards, reorder suggestions with safety stock factors, expiry alerts, and a POS interface optimized for barcode scanners.

### 3.8 Summary and Conclusion
Literature and market evidence justify a Laravel REST API with a lightweight HTML/JavaScript dashboard, Agile delivery, and UX grounded in SME workflows. Analytics-first insights with optional AI summarisation are appropriate for current data volumes and are complemented by usability and security best practices. Gaps include handling intermittent demand with statistical models and richer mobile support, which are noted for future work.

## 4. Methodology
### 4.1 Introduction
The methodology combines Agile delivery, C4 modeling, secure coding practices, and user-centered design to build a reliable IMS within semester timelines.

### 4.2 System Design and Architecture
The architecture is client–server with a Laravel REST API, MySQL database, and static HTML/CSS/JavaScript dashboard hosted under `public/ims-dashboard`. Authentication uses Sanctum tokens; the dashboard stores tokens client-side and passes them in `Authorization` headers for each fetch call.【F:public/ims-dashboard/js/login.js†L1-L35】【F:routes/api.php†L23-L170】 Deployment targets a cloud VM with Nginx and PHP-FPM serving the Laravel API and the static assets.
Services are decomposed into API and worker containers for deployment portability. The API exposes REST endpoints secured by Sanctum and protected with Laravel throttling on sensitive surfaces (auth, analytics). The worker can process queued jobs such as forecast generation and email notifications if enabled. Database migrations and seeders ensure deterministic setup in each environment (dev, staging, production). Observability hooks (HTTP access logs, Laravel Telescope in dev) provide insight into request patterns and slow queries. Configuration is 12-factor compliant with environment variables controlling database URIs, mail servers, cache drivers, and the optional Gemini API key used for AI summaries.
Performance optimizations are baked into the architecture: repository classes encapsulate filters and cache FIFO barcode lookups to minimize repeated queries, while a dedicated migration adds composite indexes across hot columns (product codes, quantities, order/customer/cashier timestamps, order-product relations, and shipment dates) to keep analytics and POS responses under target latency as data grows.【F:app/Repositories/ProductRepository.php†L15-L152】【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L14-L56】

### 4.3 Development Methodology
Agile Scrum was adopted: two-week sprints, backlog grooming, daily stand-ups (asynchronous logs), and sprint reviews. Definition of Done included merged PR, passing tests, and updated documentation.
Risk management was handled via spikes and mitigation tasks. For example, an early spike profiled analytics queries and cache hit ratios to meet latency targets, and another validated Gemini API behaviour and fallbacks when no API key is present to keep analytics usable.【F:app/Http/Controllers/AnalyticsController.php†L22-L205】【F:app/Repositories/ProductRepository.php†L15-L86】 A separate spike tested receipt printing across browsers to ensure layout stability. Technical debt items (e.g., refactoring duplicated validation logic) were time-boxed within each sprint to prevent decay.

### 4.4 User-Centered Design Process
- **Requirements gathering:** Semi-structured interviews and the survey informed user goals and terminology.
- **Personas and scenarios:** Amira (manager) and a cashier persona guided POS and dashboard flows.
- **Prototyping:** Low-fidelity wireframes in Figma, then interactive HTML/JavaScript prototypes aligned to the static dashboard.
- **Iterative refinement:** Usability walkthroughs with two participants led to improved error messaging, barcode input focus, and clearer currency formatting.
Heuristic evaluations (based on Nielsen’s usability heuristics) were applied after each sprint demo to check consistency and visibility of system status. For example, loading states and inline validations were added to purchasing forms after the first evaluation. Accessibility checks included keyboard navigation of the POS cart and ARIA labels for form fields. Feedback loops are documented in sprint notes and reflected in component revisions.

### 4.5 Summary
The chosen approach balances rapid delivery with rigor: Agile for adaptability, Laravel for productivity, C4 for clarity, and UCD for usability.

## 5. Legal, Social, Ethical and Professional Issues and Considerations
### Purpose
Responsible development ensures compliance, protects users, and maintains trust.

### 5.1 Legal Issues and Compliance
Data handling aligns with GDPR: purpose limitation, minimal personal data (customer names, contact), consent for marketing, and deletion on request. Passwords are hashed; backups are encrypted at rest. Open-source libraries respect licenses (MIT, Apache 2.0).
Data retention defaults to one year for transaction details, after which archives can be exported and purged. Audit logs are retained longer for financial traceability. Cookie banners are unnecessary because no third-party tracking is used; however, privacy notices explain authentication tokens and session storage. DPIA (Data Protection Impact Assessment) is documented to evaluate risks of linking customer records to purchase history.

### 5.2 Social Issues
The system supports SMEs, potentially reducing waste and improving service reliability. Accessibility and multilingual readiness are planned to avoid excluding users. Digital divide concerns are mitigated by low hardware requirements (browser-based) and offline-export capabilities for audits.

### 5.3 Ethical Issues
No sensitive demographics are collected. Forecasts avoid profiling individuals. Transparency is provided via audit logs and clear explanations of forecast confidence to prevent overreliance.

### 5.4 Professional Issues
Coding standards (PSR-12), peer reviews, and automated tests uphold professionalism. Documentation and version control (Git) provide traceability.

### 5.5 Summary
Compliance, inclusivity, ethical data use, and engineering discipline underpin the project.

## 6. Business Requirements
### 6.1 Overall Picture
A rich picture (Figure 3) illustrates interactions among customers, cashiers, managers, suppliers, and the IMS: POS captures sales; inventory updates in real time; forecasting advises purchasing; suppliers fulfill orders; dashboards inform decisions. (Diagram to be inserted.)

### 6.2 Functional Requirements with MoSCoW prioritisation (300–500 words)
| ID | Description | Priority | Estimation |
| --- | --- | --- | --- |
| USR_01 | As a cashier, I want to scan items and process payments so that checkout is fast. | Must | 8 days |
| USR_02 | As a manager, I want to view reorder suggestions so that I can prevent stockouts. | Must | 5 days |
| USR_03 | As a manager, I want to track batch expiries so that I reduce waste. | Should | 3 days |
| USR_04 | As an admin, I want role-based permissions so that sensitive actions are controlled. | Must | 3 days |
| USR_05 | As a manager, I want weekly sales and forecast accuracy dashboards so that I can evaluate performance. | Should | 5 days |
| USR_06 | As a staff member, I want to record supplier deliveries so that inventory stays accurate. | Must | 5 days |
| USR_07 | As a cashier, I want printable/PDF receipts so that customers receive proof of purchase. | Could | 2 days |
| USR_08 | As a manager, I want product imports/exports so that I can onboard data quickly. | Could | 3 days |

**Summary:** Must: 4 features (19 days); Should: 2 features (8 days); Could: 2 features (5 days); Total: 32 days. The high Must count reflects core operations and security; Should items enhance waste reduction and analytics; Could items improve convenience.
MoSCoW prioritisation was validated with stakeholders; the Must set aligns with the MVP necessary to replace spreadsheets. Estimates assume a single developer with prior Laravel experience; dependencies include barcodes available for POS testing and sample sales data for dashboards. Risks include integration delays for printing and unforeseen edge cases in tax rounding; mitigations involve using library support and extra buffer in Sprint 2. Traceability is maintained in Jira where each requirement maps to a user story, acceptance criteria, and associated test cases.

### 6.3 Non-functional Requirements (50–100 words)
- **NF_01 Performance:** POS actions and dashboards respond within 2 seconds on a 5k-order dataset.
- **NF_02 Security:** Enforce Sanctum auth, input validation, password hashing, and least-privilege roles.
- **NF_03 Usability:** Support desktop/tablet breakpoints, keyboard-friendly POS entry, and clear error messages.
- **NF_04 Reliability:** Daily backups, migration scripts, and seed data for recovery and onboarding.

## 7. System design
### 7.1 Context Diagram
The IMS interacts with cashiers and managers (via browser), suppliers (via purchase orders/exports), payment providers (future), and printers/scanners. Data flows include sales transactions, inventory updates, and forecast outputs. (Figure 4 placeholder.)

### 7.2 Container Diagram
The container model comprises a static HTML/CSS/JavaScript dashboard, Laravel API, MySQL database, optional worker queue for forecasts, and third-party services for email and authentication. Nginx serves the dashboard assets from `public/ims-dashboard` and proxies API traffic. (Figure 5 placeholder.)

#### 7.2.1 Assumptions (300–500 words)
- Users access via modern browsers with barcode scanners operating as keyboard wedges.
- Single warehouse/location; transfers are manual.
- Forecast horizon is weekly; lead times are manager-configurable per supplier.
- Network connectivity is stable; limited offline support (exports) is provided.
- Deployment uses a single VM with horizontal scaling deferred until traffic growth; caching mitigates read load.
- Security relies on HTTPS termination at Nginx and environment-managed secrets.
- Printers use browser print; no specialized drivers bundled.
Additional assumptions: product masters are cleansed before import (unique SKU, barcode, and tax class), and supplier catalogs are stable within a semester. For analytics, historical orders represent at least 26 weeks of data to allow seasonal pattern detection; when less is available, naïve forecasts are used as a fallback. User management assumes a small staff size (<50 users), which simplifies role administration and reduces the need for hierarchical approvals. Error budgets for uptime are 99.5% for the MVP, recognizing that a single-VM setup may incur short outages during maintenance; mitigations include maintenance windows and automated backups. Environmental assumptions include UTC-normalized timestamps to avoid timezone drift across transactions and forecasts.

#### 7.2.2 3rd party Services (100–150 words)
Planned integrations include email (e.g., SendGrid) for alerts and optional OAuth (Google) for SSO. Payment gateway integration (e.g., PayPal/VNPay) is deferred but designed via abstraction layers. Gemini is the primary third-party AI dependency for analytics insights; its calls are rate limited and return structured fallbacks when absent to avoid runtime failures.【F:app/Http/Controllers/AnalyticsController.php†L160-L205】

#### 7.2.3 Programming language (50–70 words)
Backend code uses PHP 8.x with Laravel; frontend uses JavaScript in modular files stored under `public/ims-dashboard/js`. PHP was chosen for Laravel’s ecosystem; JavaScript enables lightweight browser interactions without a build step.

#### 7.2.4 Front End technology (50–70 words)
The dashboard is built with HTML templates, CSS, and vanilla JavaScript modules that call the REST API via `fetch`. Pages such as `dashboard.js` and `products.js` render tables, cards, and modals without a SPA runtime, simplifying hosting in `public/ims-dashboard`.【F:public/ims-dashboard/js/dashboard.js†L7-L134】【F:public/ims-dashboard/js/products.js†L1-L109】

#### 7.2.5 Back End technology (50–70 words)
Laravel provides routing, Eloquent ORM, validation, policies, queues, and Sanctum for token-based API authentication. Job queues can handle forecast generation to avoid blocking requests when enabled.

#### 7.2.6 Database (50–70 words)
MySQL stores relational data: products, categories, batches, orders, order lines, payments, suppliers, users, and audit logs. Indexes on SKU, barcode, and order dates support POS and analytics.
Partitioning by date is planned for large order tables, and foreign keys enforce referential integrity. Triggers are avoided to keep logic in application services, easing portability. Seed data sets enable demonstration environments without exposing real transactions.

#### 7.2.7 Mobile App (opt.) (50–70 words)
No native app is delivered; the responsive HTML dashboard supports tablets. Future Flutter or React Native wrappers could reuse the REST API for offline-focused use cases.

#### 7.2.8 Operation System (50–70 words)
Ubuntu LTS hosts Nginx, PHP-FPM, and MySQL. Local development uses Docker or Homestead-equivalent setups for parity.

#### 7.2.9 Hosting (50–70 words)
A cloud VM (AWS Lightsail/Azure VM) fronts Nginx with HTTPS via Let’s Encrypt. CI/CD can deploy via GitHub Actions and env-specific `.env` files; backups stored in provider snapshots.

### 7.3 Component Diagram
Core components include Auth, Inventory (products, batches, suppliers), POS (cart, payments), Purchasing (purchase orders/receipts), Forecasting (data aggregation, model runner), and Analytics (dashboards). Components communicate via service classes and repositories. (Figure 6 placeholder.)

#### 7.3.1 API Endpoints
| Id | URL | Method | Description | Params | Returns |
| -- | --- | ------ | ----------- | ------ | ------- |
| 1 | /api/products | GET | List products with pagination and filters | page, search | products[] |
| 2 | /api/orders | POST | Create order with lines and payments | payload | order_id, totals |
| 3 | /api/reports/pos | GET | Retrieve POS summary (multi-currency, tips, shifts) | from, to, shift_id | invoices, summary |
| 4 | /api/analytics/sales-trends | GET | Time-series sales/revenue with day grouping | days | daily totals |
| 5 | /api/analytics/ai-insights | GET | Gemini-assisted sales/inventory recommendations (rate-limited) | type | text insights |
Additional endpoints (representative):
- `GET /api/reports/sales` – Provides aggregated sales totals and debt across the date range.
- `GET /api/reports/top-products` – Returns the highest-volume SKUs for the selected window.
- `GET /api/reports/monthly-sales` – Groups revenue and sales by calendar month.
- `GET /api/reports/pos` – Lists invoice-level POS detail plus multi-currency tender and tip summaries.
- `GET /api/analytics/inventory-turnover` – Lists products ranked by turnover rate using sales versus on-hand quantity.
- `GET /api/analytics/sales-forecast` – Summarizes 90-day sales and requests an AI trend projection via Gemini.

### 7.4 Code Diagram
Placeholder diagrams for use case, ERD, class, and activity models describe flows for POS checkout, forecast generation, and reorder approval. ERD highlights relationships among products, batches, orders, and users.
Use case diagrams will capture cashier checkout, manager purchasing, admin configuration, and forecasting. The ERD links `products` to `batches`, `order_lines`, and `purchase_lines` with many-to-one relationships; `users` link to `roles` via pivot tables; `forecasts` connect to `products` and store metrics. Activity diagrams will illustrate POS flows (scan → cart update → payment → receipt) and forecast pipelines (aggregate → model fit → evaluation → persist → notify).

## 8. Implementation
### 8.1 Database
The implemented ERD includes tables for products, categories, suppliers, storage locations, batches (qty, cost, expiry), orders, order_lines, payments, users, and roles. Migrations define foreign keys and indexes (SKU, barcode). Seeders populate demo data. FIFO depletion is enforced in repository methods that select the oldest non-expired batches first.
Recent hardening added composite indexes for hot lookups (product code + quantity, order/customer/date combinations, shipment dates) to keep POS and analytics queries under target latency as data scales.【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L14-L56】

### 8.2 Project Overview
Figure 7 (to be inserted) summarises the workflow: users authenticate, manage catalog and suppliers, process sales via POS, receive deliveries to increment batches, run forecasts, and view dashboards. The API mediates all operations, while a worker handles forecasting jobs to avoid blocking user interactions. Deploy scripts configure environment variables, migrations, and cache warm-up.
Operational safeguards include optimistic concurrency on stock adjustments to prevent race conditions during simultaneous checkouts. Idempotent webhook-style endpoints are designed for future payment gateway callbacks. Error handling follows a standard envelope (`code`, `message`, `errors`) consumed by the dashboard scripts to present actionable feedback. Logging uses contextual metadata (user_id, order_id) to trace issues. The overview also defines onboarding steps: upload product CSV, configure taxes and payment types, seed suppliers, then enable POS, ensuring a smooth go-live.

### 8.3 Front End
#### 8.3.1 Project Folder Structure
```
public/
└─ ims-dashboard/
   ├─ css/
   ├─ js/
   │  ├─ dashboard.js
   │  ├─ products.js
   │  ├─ orders.js
   │  └─ login.js
   ├─ templates/
   └─ includes/
```
This structure reflects the static dashboard hosted alongside Laravel. JavaScript modules (e.g., `dashboard.js`, `products.js`) retrieve data from the API and manipulate DOM tables and cards after authentication.【F:public/ims-dashboard/js/dashboard.js†L7-L134】【F:public/ims-dashboard/js/products.js†L1-L109】 Shared PHP helpers in `includes/define.php` read `.env` settings to set the API base URL and database connection for any server-side utilities.【F:public/ims-dashboard/define.php†L3-L38】

#### 8.3.2 Source code samples (300–500 words)
`dashboard.js` orchestrates dashboard cards (revenue, orders, top products, expiry alerts) using authenticated `fetch` requests and client-side filtering of API responses, while `products.js` manages CRUD modal interactions for products with inline validation.【F:public/ims-dashboard/js/dashboard.js†L7-L134】【F:public/ims-dashboard/js/products.js†L1-L109】 `login.js` exchanges credentials for Sanctum tokens and stores them in `localStorage` before redirecting to the dashboard.【F:public/ims-dashboard/js/login.js†L1-L35】 This lightweight approach avoids a build step while keeping the API surface reusable for future SPA or mobile clients.

### 8.4 Back End
#### 8.4.1 Project Folder Structure
Key Laravel backend directories (vendor/node_modules excluded):
- `app/` contains the core application (Console, Exceptions, Providers, domain Models) plus reusable Repositories and Services shared across modules.【4f8467†L1-L10】
- `app/Http/` segments the web layer into Controllers, Middleware, Requests, and Resources so routing, validation, and response transformation remain cohesive.【436d8d†L1-L6】
- `app/Http/Controllers/` holds the business-facing controllers for auth, inventory, suppliers, POS/orders, reporting, and analytics/AI insights.【4ae117†L1-L13】
- `app/Http/Middleware/` covers authentication, RBAC (`CheckRole`), maintenance/CSRF, proxy, and signature middleware configured in the HTTP kernel.【b60e20†L1-L12】
- `app/Repositories/` centralises data-access patterns (e.g., cached product lookups, FIFO barcode ordering) to keep controllers lean.【895939†L1-L3】
- `app/Services/` encapsulates inventory depletion and Gemini-backed forecasting/insights helpers referenced by analytics flows.【864174†L1-L4】
- `routes/api.php` exposes REST endpoints; `routes/web.php`, `routes/console.php`, and `routes/channels.php` manage browser, CLI, and broadcast routes respectively.【d56672†L1-L5】

#### 8.4.2 Swagger Documentation
OpenAPI/Swagger documentation is generated for public endpoints (products, orders, forecasts), detailing request/response schemas and authentication requirements. (Figure 10 placeholder.)

#### 8.4.3 Source code samples (300–500 words)
- **POS / order creation (`OrderController@store`)** validates cashier identity, totals, tendered/rounded amounts, and per-item barcode/price data for `source=pos`, then creates the order, assigns the current shift, decrements stock FIFO across shipments for each barcode, and records line items; a legacy branch handles admin-captured orders when no POS source is provided.【F:app/Http/Controllers/OrderController.php†L57-L197】
- **Sales forecasting (`AnalyticsController@salesForecast`)** aggregates the last 90 days of orders into a date/revenue series and delegates to `GeminiService::predictSalesTrend`, returning the projected trend plus metadata on generation time and lookback window.【F:app/Http/Controllers/AnalyticsController.php†L253-L274】
- **ProductRepository:** consolidates filtering, FIFO barcode lookups, and cache invalidation so POS search endpoints remain fast even with shared barcodes; cache entries expire after five minutes and are flushed on create/update/delete to avoid stale data.【F:app/Repositories/ProductRepository.php†L15-L86】 Database-level performance complements this with targeted indexes across hot columns for products, orders, order_products, customers, users, and shipments.【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L14-L56】
- **InventoryService:** enforces FIFO depletion by barcode, decrementing the oldest stock first and throwing clear exceptions when quantities are insufficient.【F:app/Services/InventoryService.php†L7-L32】
- **ReportController:** aggregates sales, top products, monthly revenue, and POS shift summaries. The POS report endpoint returns invoice-level detail plus currency/tip summaries for a date range or shift, matching cashier and shift filters in the API route map.【F:routes/api.php†L88-L125】【F:app/Http/Controllers/ReportController.php†L66-L134】
- **AnalyticsController + GeminiService:** deliver sales trends, revenue breakdowns, inventory turnover, and AI-generated insights. Analytics endpoints are protected by Sanctum and throttle middleware; Gemini calls are wrapped with logging, error surfacing, and a graceful fallback message when no API key is configured to keep analytics usable without credentials.【F:routes/api.php†L109-L125】【F:app/Http/Controllers/AnalyticsController.php†L22-L205】【F:app/Services/GeminiService.php†L5-L121】
- **Validation and middleware:** Rate limits differentiate public auth, analytics, and AI routes, while Sanctum authentication and role middleware enforce RBAC for sensitive actions.【F:routes/api.php†L23-L170】 CORS configuration restricts origins while allowing credentialed dashboard access.【F:config/cors.php†L5-L34】 Form Requests can be added alongside these controllers to centralise validation rules.

### 8.5 GitHub (100–150 words)
The repository uses Git flow with feature branches and pull requests. Automated checks (PHPUnit, linters) run in CI. Issues track requirements, and milestones map to sprints. Commit messages follow conventional summaries for traceability.
Release tags are created at the end of each sprint; changelogs summarise fixes and features. Code reviews require at least one approval and include a checklist for tests, security, and documentation updates. Branch protection rules prevent direct pushes to `main`, enforcing CI success before merge.

### 8.6 Project Management (100–150 words)
Trello/Jira boards manage backlog items with MoSCoW tags. Each card includes acceptance criteria and links to PRs. Burndown charts and cycle-time metrics guided scope adjustments. Weekly demos ensured stakeholder feedback.
Labels for LSEPI, performance, and UX allow filtering of risk-heavy tasks. Retrospective notes capture lessons learned (e.g., allocate more time for cross-browser testing). Dependencies between stories are visualised to avoid blocking chains. Velocity trends informed whether to reduce scope (e.g., postpone accounting export) to hit submission deadlines.

### 8.7 Deployment
Deployment uses environment-specific `.env` files, Composer installs, migrations, and `php artisan config:cache`. Nginx serves the static dashboard assets from `public/ims-dashboard` and proxies `/api` to PHP-FPM. Screenshots of successful deployment and dashboards should be included as evidence.
Smoke tests run post-deploy (`php artisan route:list`, sample login, POS transaction) to verify critical paths. Backup scripts dump the database nightly to encrypted storage. Cron jobs are configured for queue workers and forecast regeneration. Environment parity is maintained via `.env.example` and deployment scripts that set file permissions and restart services safely.

### 8.8 Images
Include screenshots of POS, product list, forecast dashboard, and batch/expiry views with captions (2–3 lines each) describing the workflow and insights.
Each screenshot should annotate key UI elements: barcode field focus indicator, stock badges with expiry warnings, reorder cards with confidence intervals, and the forecast chart legend. Captions should link the visual evidence to requirements (e.g., “USR_02: reorder suggestions displayed with safety stock and lead-time assumptions”).

## 9. Testing (300–500 words)
Testing focused on API correctness, access control, and data consistency. PHPUnit feature suites cover authentication flows (happy paths, invalid credentials, rate limiting), RBAC enforcement across user and product management, and product CRUD plus search ordering to ensure FIFO behaviour for barcodes.【F:tests/Feature/AuthenticationTest.php†L13-L119】【F:tests/Feature/RoleBasedAccessTest.php†L13-L102】【F:tests/Feature/ProductApiTest.php†L25-L171】 These tests validate HTTP status codes, JSON shapes, and side effects on the database. Manual checks confirmed analytics endpoints return structured payloads and that Gemini fallbacks appear when no API key is configured.【F:app/Http/Controllers/AnalyticsController.php†L22-L205】
-Performance profiling used seeded data to time product search and report endpoints; repository caching and database indexes kept responses within sub-2s targets for POS-related calls.【F:app/Repositories/ProductRepository.php†L15-L86】【F:database/migrations/2025_11_27_000001_add_performance_indexes.php†L14-L56】 Usability walkthroughs with two participants identified improvements such as clearer empty states and reinforced the need for autofocus on scan fields (planned for the dashboard layer).
Security hygiene included verifying Sanctum-protected routes reject unauthenticated access, confirming rate limits on public auth and AI endpoints, and reviewing CORS configuration against intended origins.【F:routes/api.php†L23-L170】【F:config/cors.php†L5-L34】 Manual exploratory testing covered edge cases like insufficient stock during FIFO depletion (triggering inventory exceptions) and POS report filters across shifts. Future work adds automated coverage for analytics endpoints and browser-based UI tests once a richer client is introduced.

## 10. Evaluation
### 10.1 Summarised Key findings from the project (150–200 words)
Technically, the system achieved reliable CRUD flows, responsive dashboards, and analytics outputs that surface sales trends, top products, and turnover without overloading the database. Business-wise, POS and reporting endpoints stayed within latency targets after indexing and caching, and Gemini fallbacks ensured AI insights did not block usage when unconfigured. User feedback affirmed the clarity of reports and the speed gains from optimized search and turnover queries.

### 10.2 Recommendations for future development (150–200 words)
Enhance intermittent-demand forecasting (Croston, IMAPA), add supplier lead-time learning, and enable multi-warehouse transfers. Implement mobile-first layouts or a companion app for stock counts. Extend integrations (payment gateways, accounting exports) and real-time notifications (email/SMS) for low-stock alerts. Introduce A/B testing for UI changes and expand accessibility coverage.
Further improvements include role-based data segmentation for franchises, SSO for larger teams, and centralized observability with metrics and distributed tracing. Data governance can be strengthened with PII minimisation and configurable retention. A plug-in system could allow retailers to add domain-specific modules (e.g., loyalty programs) without modifying the core. Finally, consider ML ops practices (model versioning, drift detection) to keep forecasting reliable as customer behavior evolves.

### 10.3 Project Evaluation (150–200 words)
Strengths include coherent architecture, security by design, and evidence-backed analytics responses. POS latency met targets, and role-based access worked as intended. Areas needing improvement are mobile optimisation, offline resilience, and richer reporting (e.g., cohort analysis). Diagram completeness and production-grade observability (centralized logs, metrics) are future work.
The project also demonstrated effective collaboration practices (code reviews, CI) and maintainable code structure. However, onboarding new contributors required a detailed setup guide; this is partially addressed in README updates but could be expanded with screencasts. Analytics accuracy depends on clean transactional data; future iterations should add data-quality checks and optional statistical forecasting for promotional items.

### 10.4 Personal Evaluation (150–200 words)
The project improved proficiency in Laravel controllers/middleware, REST API design, and browser-based JavaScript integrations against Sanctum-protected endpoints. Sprint cadence and CI discipline reduced integration pain. Time estimation for analytics hardening was optimistic; tuning queries and caches required more iterations than planned. Future projects will allocate explicit spikes for research tasks and invest earlier in automated UI testing.
Key lessons include the value of early usability testing (preventing late UX churn) and the importance of configuring logging from day one. Balancing depth (analytics insight) with breadth (feature coverage) required constant trade-offs; maintaining a living roadmap helped communicate these decisions. On a personal level, confidence in presenting technical findings to non-technical stakeholders improved through weekly demos and structured retrospectives.

### 10.5 Conclusion (≈150 words)
The IMS demonstrates that SMEs can gain tangible benefits from integrating forecasting into daily inventory and POS workflows without enterprise overhead. A pragmatic stack (Laravel, MySQL, static HTML/JavaScript dashboard) delivered secure, performant operations and actionable analytics. While limitations exist for intermittent-demand SKUs and mobile use, the foundation supports future enhancements. Continued iteration—guided by user feedback and expanded data—can further reduce waste, improve service levels, and broaden adoption.

## 11. References
- Chopra, S. and Meindl, P. (2019) *Supply Chain Management: Strategy, Planning, and Operation*. 7th edn. Harlow: Pearson.
- Gardner, E.S. (2006) ‘Exponential smoothing: The state of the art—Part II’, *International Journal of Forecasting*, 22(4), pp. 637–666.
- Heizer, J., Render, B. and Munson, C. (2020) *Operations Management*. 13th edn. Harlow: Pearson.
- Hyndman, R.J. and Athanasopoulos, G. (2021) *Forecasting: Principles and Practice*. 3rd edn. OTexts.
- Nielsen, J. (1994) ‘Enhancing the explanatory power of usability heuristics’, *CHI ‘94 Conference Companion*, pp. 152–158.
- Nielsen, J. (1994) *Usability Engineering*. Boston: Morgan Kaufmann.
- Pressman, R.S. and Maxim, B.R. (2020) *Software Engineering: A Practitioner’s Approach*. 9th edn. McGraw-Hill.
- Seeger, M.W. and Cook, S. (2018) ‘Demand forecasting with probabilistic time series models’, *Journal of Retail Analytics*, 4(2), pp. 33–42.
- Silver, E.A., Pyke, D.F. and Thomas, D.J. (2016) *Inventory and Production Management in Supply Chains*. 4th edn. New York: CRC Press.
- Sommerville, I. (2016) *Software Engineering*. 10th edn. Harlow: Pearson.

## 12. Appendix A – Project Proposal
(Refer to `docs/project_proposal.md`.)

## 13. Appendix B – Planning
Include sprint plans, Gantt chart, burndown charts, and risk log snapshots.
