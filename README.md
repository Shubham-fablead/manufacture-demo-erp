# Fablead ERP System

Fablead ERP System is a Laravel-based manufacturing ERP designed to manage raw materials, production, finished goods, purchase, sales, inventory, accounting, GST reports, and role-based user access from a single platform.

## Features

- Sales management: quotations, sales orders, invoices, and sales returns
- Purchase management: purchase orders, vendor bills, and purchase returns
- Inventory control: raw materials, finished goods, stock tracking, and adjustments
- Manufacturing flow: BOM, production entries, and stock conversion from raw material to finished goods
- Custom invoicing: flexible invoice and bill generation with GST support
- Financial management: cashbook, bankbook, ledger, advance payments, credit notes, and debit notes
- Reporting: sales, purchase, GST, income statement, and balance sheet reports
- User management: role-based access for admin, sub-admin, staff, and branch users
- Export support: PDF and Excel export for major records
- Responsive UI: desktop and mobile friendly screens built with Blade and Bootstrap
- API support: secure API token based actions for AJAX save and load requests

## Technology Stack

- Backend: Laravel 10+
- Frontend: Blade templates, Bootstrap 5, jQuery, AJAX, DataTables, Select2, Moment.js
- Database: MySQL
- Authentication: Laravel session login + Laravel Passport API tokens
- Permissions: Spatie Laravel Permission
- Exports and documents: DomPDF, PhpSpreadsheet, Maatwebsite Excel
- Barcode support: milon/barcode

## Installation

1. Clone the repository.
   ```bash
  git clone https://github.com/Shubham-fablead/manufacture-demo-erp.git
   cd manufacture-demo-erp
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```


3. **Environment setup**:
   Copy `.env.example` to `.env` and configure your database credentials.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Set your application URL and asset base URL (required for CSS, JS, and images):

   ```env
   APP_URL=http://127.0.0.1:8000
   ImagePath=http://127.0.0.1:8000/
   ```

   > `ImagePath` **must end with `/`**. On a live server, use your real domain, for example:
   > `APP_URL=https://erp.yourdomain.com` and `ImagePath=https://erp.yourdomain.com/`
   >
   > If `ImagePath` is omitted, the app falls back to `APP_URL`.


4. Configure your application URL and asset path.
   ```env
   APP_URL=http://127.0.0.1:8000
   ImagePath=http://127.0.0.1:8000/
   ```

   Use the same host consistently. Do not mix `localhost` and `127.0.0.1`.

5. Run migrations and Passport.
   ```bash
   php artisan migrate
   php artisan passport:install --force
   ```

6. Import the demo database if you want ready-made sample data.
   ```text
   database/inventory_and_billing.sql
   ```

7. Link storage.
   ```bash
   php artisan storage:link
   ```

8. Start the application.
   ```bash
   php artisan serve
   ```

## Login

- Open the app in your browser.
- Sign in from the home page.
- If you imported the demo database, use:
  - Email: `admin@gmail.com`
  - Password: `12345678`

After login, the app stores the API token in browser local storage as `authToken`. This token is required for create, update, and delete actions that use protected API routes.

## Authentication

This application uses two layers of authentication:

| Layer | Purpose |
|-------|---------|
| Web session | Page access after login |
| API token (`authToken`) | Save and load actions through API endpoints |

- Pages can open with a valid web session.
- Save actions use API routes and require the `authToken` token.
- If the token is missing, you may see `401 Unauthorized` on form submit.

## Project Flow

1. Log in to the system.
2. Configure company settings, tax rates, currency, and SMTP.
3. Create master data such as brands, categories, units, customers, vendors, and staff.
4. Add raw materials, products, and BOM details.
5. Record raw material purchases and stock inward.
6. Create production entries to move raw material into finished goods.
7. Process quotations, sales, invoices, and returns.
8. Track cashbook, bankbook, ledger, and expenses.
9. Generate GST and business reports.
10. Export records to PDF or Excel when needed.

## Main Modules

### Master Data

- Brands
- Categories
- Units
- Tax rates
- Currency
- Banks
- Customers
- Vendors
- Staff
- Sub-branches

### Inventory and Manufacturing

- Products
- Raw materials
- Stock tracking
- BOM management
- Production entries
- Finished goods flow
- Connected device and scanner support

### Sales

- Quotations
- Sales invoices
- Sales returns
- Custom invoices
- POS style sales entry

### Purchase

- Purchases
- Purchase invoices
- Purchase returns
- Raw material purchases

### Finance

- Cashbook
- Bankbook
- Account ledger
- Advance payments
- Credit notes
- Debit notes
- Expenses

### Reports

- Sales reports
- Purchase reports
- GST reports
- Income statement
- Balance sheet
- PDF and Excel exports

## Requirements

- PHP 8.1 or later
- Composer
- Node.js and npm
- MySQL
- XAMPP, Laragon, or a production web server

## Important Configuration

- `APP_URL` should match your real site URL.
- `ImagePath` should end with a trailing slash.
- Keep the same base URL throughout the session, especially during local development.
- If images or CSS do not load, check `ImagePath` first.

## Troubleshooting

### Client authentication failed on login

Run Passport client reset and try again.

```bash
php artisan passport:reset-clients --force
```

### Save action returns 401 Unauthorized

- Log out and log in again.
- Confirm that `authToken` exists in browser local storage.
- Make sure `php artisan passport:install --force` was run after a fresh database setup.

### Images or CSS are broken

- Check the `ImagePath` value in `.env`.
- Make sure it ends with `/`.
- Use the same host format everywhere, such as `http://127.0.0.1:8000/`.

### Demo login does not work

- Confirm that `database/inventory_and_billing.sql` was imported successfully.
- Verify that the admin user exists in the database.

## Folder Structure

```text
app/            Application controllers, models, middleware, and services
config/         Framework and package configuration
database/       Migrations, SQL dump, seeders, and templates
resources/      Blade views, styles, and frontend assets
routes/         Web and API route definitions
```

## License

This project is proprietary software developed by Fablead Developers Technolab. All rights reserved.
