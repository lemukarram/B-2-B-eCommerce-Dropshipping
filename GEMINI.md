# EMAG.PK - B2B Dropshipping Platform

EMAG.PK is a custom-built B2B dropshipping platform designed for administrative oversight and seller management. It features a robust custom PHP core, focusing on performance, security, and simplicity.

## 🚀 Deployment Strategy: cPanel / Shared Hosting

This project is optimized for simple web hosting environments. It does not require Docker, Node.js, or NPM. The entry point is now at the root directory for maximum compatibility.

### 📋 Prerequisites
- **PHP**: ^8.3 (Required for modern features and security)
- **MySQL**: 8.0 or equivalent
- **Composer**: Used for PHP library management (e.g., PhpSpreadsheet)

### 🛠️ Manual Installation (No Docker)
1. **Database Setup**:
   - Create a MySQL database on your hosting panel (e.g., cPanel MySQL Database Wizard).
   - Import the schema from `database/schema.sql`.
   - Optionally, run any seed files found in `database/seeds/` if you need initial data.

2. **Configuration**:
   - Rename `.env.example` to `.env` in the root directory.
   - Update `.env` with your production database credentials:
     ```env
     DB_HOST=localhost
     DB_NAME=your_db_name
     DB_USER=your_db_user
     DB_PASSWORD=your_db_password
     ```

3. **Dependencies**:
   - Run `composer install --no-dev --optimize-autoloader` locally or on your server to generate the `vendor/` directory.

4. **Web Server Setup**:
   - Simply upload all files (including `src/`, `assets/`, `uploads/`, and `index.php`) to your `public_html` folder.
   - The application will automatically detect the root `index.php` and load the `.env` configuration.

---

## 📂 Project Structure

- `index.php` - Main entry point (Root).
- `assets/` - Public CSS, JS, and Images.
- `uploads/` - User-uploaded files (Products, Categories).
- `src/` - The core application source (Protected).
  - `app/` - MVC (Controllers, Models, Services, Middleware).
  - `config/` - Application and database configuration.
  - `core/` - The engine (Router, Request, Response, Database, etc.).
  - `views/` - PHP templates for Admin, Seller, and Guest areas.
  - `storage/` - Logs and temporary files (must be writable).
- `database/` - SQL schemas and seeds.
- `vendor/` - Third-party PHP libraries (managed by Composer).

---

## 🛡️ Security
- **No Direct Access**: All logic is inside the `src/` folder. While `src/` is in the root, the application only routes requests through `index.php`.
- **Input Validation**: Centralized `Validator.php` for all incoming requests.
- **XSS Protection**: Global `e()` helper for safe output rendering.
- **CSRF Protection**: Integrated middleware and session-based tokens.
- **SQLi Prevention**: Strict use of PDO prepared statements with `ATTR_EMULATE_PREPARES => false`.

---

## ❌ No NPM / Node.js
This project intentionally avoids complex build tools. Assets in `assets/` are pure CSS and JavaScript, allowing for direct modification and instant updates without a build step.
