# SetupDino 🎮

Cyberpunk-themed gaming ecommerce class project using **HTML5, CSS3, vanilla JavaScript, PHP and MySQL**.

## Status

The store includes a product catalog, customer accounts and demo checkout, plus separate admin and seller panels. Admins can manage products, categories, accounts and order statuses. Sellers can create and manage only their own listings; seller products appear in the catalog immediately.

## Requirements

- PHP 8.1+ with `pdo_mysql`
- MySQL 8+ (or compatible MariaDB)
- MySQL command line client

## Install

1. For a fresh install, import the database tables and sample products (requires a MySQL account with CREATE permissions):

   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```

2. Create a limited-permission application account in MySQL, replacing the sample password with a strong one:

   ```sql
   CREATE USER IF NOT EXISTS 'SetupDino_app'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_PASSWORD';
   GRANT SELECT, INSERT, UPDATE, DELETE ON SetupDino.* TO 'SetupDino_app'@'localhost';
   ```

3. Copy `.env.example` to `.env`, set `DB_PASSWORD` to the application account password, and run the local development server from the project root:

   ```bash
   cp .env.example .env
   php -S localhost:8000
   ```

4. Create an account through `/register.php`, then promote that account to the first administrator using MySQL:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
   ```

   Sign in again to refresh the account role. Administrators can promote registered customer accounts to sellers from **Administration → Clientes e vendedores**.

5. Open `http://localhost:8000`. Admins use `/admin/`; sellers use `/seller/`.

### Existing database upgrade

If the database was created before seller panels were added, run this migration once after backing up the database:

```bash
mysql -u root -p < database/migrations/001_seller_products.sql
```

Fresh databases created with the current `database/schema.sql` already include seller support and should not run this migration.

The application loads `.env` through `config/database.php`. Optional settings include `DB_HOST`, `DB_PORT`, `DB_NAME`, and `DB_USER`. Keep `.env` private and do not commit real credentials.

## Architecture

- `admin/`: protected product, category, order, and account management
- `seller/`: seller dashboard and owner-scoped product management
- `assets/`: cyberpunk styles, JS placeholders and future product images
- `config/database.php`: PDO connection using environment variables
- `database/`: schema and repeatable seed data
- `includes/`: layout, escaping, sessions and access control
- `src/`: product repository and cart/order services
- Root PHP files: customer-facing pages

## Security and limitations

This is a local classroom starter, **not production-ready**. PHP's built-in server can expose source or configuration files under the project root: bind to localhost only. Before public deployment, use a dedicated web root and keep configuration, SQL, and source files outside it. Passwords are never hardcoded. Product photo uploads are restricted by file type and size; production deployments should additionally configure upload limits and storage policies. Checkout is demonstrative and no real payment processing exists.

## Next steps

Configure production hosting, backups, payment processing, and moderation/business rules before deploying a live marketplace.


## Milestone 2 — Step 2: Storefront

Implemented: responsive navigation, homepage category links, reusable product cards, catalog search/category/price sorting via GET, and product details with image fallback and stock display.

- `/` — homepage and featured products
- `/products.php` — searchable/filterable catalog
- `/products.php?search=keyboard&category=keyboards&sort=price_asc` — example filter URL
- `/product.php?id=1` — product details

No schema migration is required from Step 1. Product image filenames come from `products.image`; add matching files to `assets/images/products/` or a gaming placeholder appears.

Run locally only: the PHP built-in server exposes the project root, including internal files, and must not be deployed publicly.


## Milestone 2 Step 3 — Cart, accounts and demo orders

- `assets/js/cart.js` stores cart items in `localStorage` (browser-only; no server authority).
- `register.php` and `login.php` implement customer accounts with password hashes and CSRF protection.
- `checkout.php` requires login and stores a pending **demo** order. No payment is processed.
- `src/CartService.php` validates product IDs, quantities, live stock, and prices inside a database transaction.
- `src/OrderService.php` saves the order, item snapshots, delivery address and decrements stock atomically.
- `order-success.php` shows only orders belonging to the current customer.

**Important:** The existing `users`, `orders`, `order_items`, and `order_addresses` tables are used. Run the application with `DB_PASSWORD` set in `.env`. Create an account, add products, and place a demo order. Do not deploy the PHP built-in server publicly; use a dedicated public document root and HTTPS for production.

## Admin and seller panels

- Admin panel: `/admin/` — manage all products, categories, customer/seller roles, and order statuses.
- Seller panel: `/seller/` — add, edit, and remove products belonging to the signed-in seller.
- To onboard a seller, have them create a regular customer account, then assign the seller role from the admin account page.
- Seller listings are published immediately. Product images accept JPG, PNG, WebP, or GIF files up to 5 MB.
- Existing databases must run `database/migrations/001_seller_products.sql` once. New databases already include the required schema.
- On the first installation, promote a registered account to `admin` with the SQL statement above.
