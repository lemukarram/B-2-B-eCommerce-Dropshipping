# EMAG.PK - B2B Dropshipping Platform

EMAG.PK is a custom-built B2B dropshipping platform designed for administrative oversight and seller management. It features a robust custom PHP core, focusing on performance, security, and simplicity.

## 🚀 Key Features

- **Admin Dashboard**: Full control over products, categories, sellers, and orders.
- **Seller Portal**: Manage product catalogue, set markups, and track orders/wallet.
- **Storefronts**: Referral-based storefronts for sub-sellers/stores.
- **Bulk Import/Export**: 
  - WooCommerce/WordPress CSV import for products.
  - Export to Shopify and WordPress/WooCommerce CSV formats.
- **Wallet System**: Integrated wallet for tracking earnings and withdrawals.
- **Security**: Centralized validation, CSRF protection, and SQL injection prevention.

## 📋 Prerequisites

- **PHP**: ^8.3
- **MySQL**: 8.0 or equivalent
- **Composer**: For PHP library management (e.g., PhpSpreadsheet)
- **Web Server**: Apache (with `mod_rewrite`) or Nginx.

## 🛠️ Installation

1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd ecom
   ```

2. **Database Setup**:
   - Create a MySQL database (e.g., `emag_pk`).
   - Import the schema from `database/install.sql`.

3. **Configuration**:
   - Copy `config.example.php` to `config.php`:
     ```bash
     cp config.example.php config.php
     ```
   - Update `config.php` with your database credentials and application settings.

4. **Dependencies**:
   - Install PHP dependencies:
     ```bash
     composer install --no-dev --optimize-autoloader
     ```

5. **Permissions**:
   - Ensure `uploads/` and `src/storage/` directories are writable by the web server.

## 📂 Project Structure

- `index.php` - Main entry point.
- `assets/` - Public CSS, JS, and Images.
- `uploads/` - User-uploaded files (Products, Categories).
- `src/` - Core application logic.
  - `app/` - MVC (Controllers, Models, Services, Middleware).
  - `config/` - App and database configurations.
  - `core/` - System engine (Router, Request, Database, etc.).
  - `views/` - PHP templates.
  - `storage/` - Logs and temp files.
- `database/` - SQL schemas and migration files.

## 🛡️ Security

- **No Direct Access**: All logic is inside the `src/` folder, routed through `index.php`.
- **Input Validation**: Centralized `Validator.php` for all requests.
- **XSS Protection**: Global `e()` helper for safe output rendering.
- **CSRF Protection**: Integrated middleware for state-changing requests.
- **SQLi Prevention**: Use of PDO prepared statements with `ATTR_EMULATE_PREPARES => false`.

## 📄 License

This project is proprietary and custom-built for EMAG.PK.
