# IMS - Inventory Management System

A comprehensive Laravel-based Inventory Management System with integrated Point of Sale (POS) capabilities.

[![Tests](https://github.com/josephnguyenn/ims/actions/workflows/tests.yml/badge.svg)](https://github.com/josephnguyenn/ims/actions/workflows/tests.yml)
[![Code Style](https://github.com/josephnguyenn/ims/actions/workflows/lint.yml/badge.svg)](https://github.com/josephnguyenn/ims/actions/workflows/lint.yml)
[![Security](https://github.com/josephnguyenn/ims/actions/workflows/security.yml/badge.svg)](https://github.com/josephnguyenn/ims/actions/workflows/security.yml)

## 🚀 Features

### Core Inventory Management
- **Product Management**: Complete CRUD operations with category support, barcode scanning, and expiry tracking
- **FIFO Inventory System**: Automatic First-In-First-Out stock rotation based on shipment dates
- **Multi-batch Support**: Track same product across multiple shipments with different expiry dates
- **Storage Management**: Organize inventory across multiple storage locations
- **Supplier Management**: Manage both shipment suppliers (incoming) and delivery suppliers (outgoing)
- **Shipment Tracking**: Track incoming shipments with costs, dates, and supplier information

### Point of Sale (POS)
- **Multi-currency Support**: CZK and EUR with real-time exchange rates
- **Payment Methods**: Cash, card, and transfer options
- **Smart Rounding**: Automatic CZK rounding to nearest 0.50
- **Barcode Scanning**: Quick product lookup and checkout
- **Receipt Printing**: Professional receipt generation
- **Shift Management**: Track sales by shift and cashier
- **Tip Handling**: Support for tips in both currencies

### Customer & Order Management
- **Customer Profiles**: Track customer information and order history
- **Order Processing**: Complete order lifecycle from creation to delivery
- **Order Products**: Line-item level tracking with pricing and quantities
- **Payment Tracking**: Monitor paid amounts and outstanding balances

### Reporting & Analytics
- **Sales Reports**: Comprehensive sales analysis with date range filtering
- **Revenue Tracking**: Monitor revenue, debt, and payment collection
- **Top Products**: Identify best-selling items
- **Monthly Analysis**: Month-over-month sales trends
- **POS Reports**: Detailed cashier and shift reports with payment method breakdowns

### Security & Authentication
- **Laravel Sanctum**: Token-based API authentication
- **Role-Based Access**: Admin, Manager, and Staff roles with different permissions
- **CSRF Protection**: Built-in security for all forms
- **Password Hashing**: Secure password storage with bcrypt

### API Features
- **RESTful Design**: Clean, predictable API endpoints
- **Pagination**: Efficient data loading for large datasets
- **Search & Filtering**: Advanced query capabilities across all resources
- **Validation**: Comprehensive input validation
- **Error Handling**: Consistent error responses

## 📋 Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js 18+ and npm
- Web server (Apache/Nginx)

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/josephnguyenn/ims.git
cd ims
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install JavaScript Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Build Assets
```bash
npm run build
```

### 8. Start Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## 🧪 Testing

Run the test suite:
```bash
vendor/bin/phpunit
```

Run with coverage:
```bash
vendor/bin/phpunit --coverage-html coverage
```

## 🎨 Code Style

This project follows Laravel's coding standards using Laravel Pint.

Check code style:
```bash
vendor/bin/pint --test
```

Fix code style issues:
```bash
vendor/bin/pint
```

## 📚 API Documentation

### Authentication Endpoints

#### Register
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "role": "staff"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password"
}
```

### Protected Endpoints

All endpoints below require authentication. Include the token in the header:
```http
Authorization: Bearer {your-token}
```

#### Products
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get product details
- `POST /api/products` - Create product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `GET /api/products/search?code={barcode}` - Search by barcode

#### Orders
- `GET /api/orders` - List all orders
- `GET /api/orders/{id}` - Get order details
- `POST /api/orders` - Create order (supports both admin and POS)
- `PUT /api/orders/{id}` - Update order
- `DELETE /api/orders/{id}` - Delete order

#### Reports
- `GET /api/reports/sales?from={date}&to={date}` - Sales report
- `GET /api/reports/top-products` - Top selling products
- `GET /api/reports/monthly-sales` - Monthly sales data
- `GET /api/reports/pos?shift_id={id}` - POS shift reports

## 🏗️ Project Structure

```
ims/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # API controllers
│   │   │   ├── Api/           # Additional API controllers
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   └── ...
│   │   └── Middleware/        # Custom middleware
│   ├── Models/                # Eloquent models
│   │   ├── Product.php
│   │   ├── Order.php
│   │   └── ...
│   └── Services/              # Business logic services
│       └── InventoryService.php
├── database/
│   ├── migrations/            # Database migrations
│   ├── factories/             # Model factories
│   └── seeders/              # Database seeders
├── public/
│   └── ims-dashboard/        # Frontend application
│       ├── pos/              # POS system
│       ├── templates/        # Admin templates
│       └── js/               # JavaScript modules
├── routes/
│   ├── api.php               # API routes
│   └── web.php               # Web routes
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
└── .github/
    └── workflows/            # CI/CD pipelines
        ├── tests.yml
        ├── lint.yml
        └── security.yml
```

## 🔐 Security

- All API endpoints require authentication (except register/login)
- Role-based access control for sensitive operations
- CSRF protection enabled
- SQL injection prevention via Eloquent ORM
- XSS protection via Laravel's Blade templating
- Password hashing with bcrypt
- Weekly security scans via GitHub Actions

## 🚀 Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions.

## 📝 License

This project is licensed under the MIT License.

## 👥 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

For issues and questions, please use the GitHub issue tracker.

## 🙏 Acknowledgments

Built with:
- [Laravel](https://laravel.com/) - The PHP Framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API Authentication
- [Laravel Pint](https://laravel.com/docs/pint) - Code Style
- [PHPUnit](https://phpunit.de/) - Testing Framework
