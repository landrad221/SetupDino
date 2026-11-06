# GamerSell 🎮

Cyberpunk-themed gaming ecommerce class project using **HTML5, CSS3, vanilla JavaScript, PHP and MySQL**.

## Status

**Milestone 2, Step 1 foundation.** Homepage, catalog and product details render real MySQL data. Cart, checkout, customer authentication, admin CRUD, and payment integration are **not implemented yet**. Admin pages correctly return HTTP 403 until authentication is added. Product art currently uses placeholders.

## Requirements

- PHP 8.1+ with `pdo_mysql`
- MySQL 8+ (or compatible MariaDB)
- MySQL command line client

## Install

1. Import database tables and sample products (requires a MySQL account with CREATE permissions):

   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```

2. Create a limited-permission application account in MySQL, replacing the sample password with a strong one:

   ```sql
   CREATE USER IF NOT EXISTS 'gamersells_app'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_PASSWORD';
   GRANT SELECT, INSERT, UPDATE, DELETE ON gamersells.* TO 'gamersells_app'@'localhost';
   ```

3. Export the application password and run the local development server from the project root:

   ```bash
   read -rsp 'Database password: ' DB_PASSWORD; echo
   export DB_PASSWORD
   php -S localhost:8000
   ```

4. Open `http://localhost:8000`. Visit `/products.php` and click a product to view details. Visit `/admin/` to confirm access is denied.

Other optional environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`. PHP does not automatically load `.env` files in this project.

## Architecture

- `admin/`: protected administration pages (future CRUD)
- `assets/`: cyberpunk styles, JS placeholders and future product images
- `config/database.php`: PDO connection using environment variables
- `database/`: schema and repeatable seed data
- `includes/`: layout, escaping, sessions and access control
- `src/`: product repository and future cart/order services
- Root PHP files: customer-facing pages

## Security and limitations

This is a local classroom starter, **not production-ready**. PHP's built-in server can expose source or configuration files under the project root: bind to localhost only. Before public deployment, use a dedicated web root and keep configuration, SQL, and source files outside it. Passwords are never hardcoded. Admin routes check PHP sessions, but login and user management are not yet implemented. No real payment processing exists.

## Next steps

Build product search and filters, cart, login/registration, checkout simulation, admin product CRUD, and order management.


## Milestone 2 — Step 2: Storefront

Implemented: responsive navigation, homepage category links, reusable product cards, catalog search/category/price sorting via GET, and product details with image fallback and stock display.

- `/` — homepage and featured products
- `/products.php` — searchable/filterable catalog
- `/products.php?search=keyboard&category=keyboards&sort=price_asc` — example filter URL
- `/product.php?id=1` — product details

No schema migration is required from Step 1. Product image filenames come from `products.image`; add matching files to `assets/images/products/` or a gaming placeholder appears.

**Important:** Cart, checkout, login, and admin CRUD are still placeholders for later steps. Run locally only: the PHP built-in server exposes the project root, including internal files, and must not be deployed publicly.


## Milestone 2 Step 3 — Cart, accounts and demo orders

- `assets/js/cart.js` stores cart items in `localStorage` (browser-only; no server authority).
- `register.php` and `login.php` implement customer accounts with password hashes and CSRF protection.
- `checkout.php` requires login and stores a pending **demo** order. No payment is processed.
- `src/CartService.php` validates product IDs, quantities, live stock, and prices inside a database transaction.
- `src/OrderService.php` saves the order, item snapshots, delivery address and decrements stock atomically.
- `order-success.php` shows only orders belonging to the current customer.

**Important:** No database migration is required from Step 2. The existing `users`, `orders`, `order_items`, and `order_addresses` tables are used. Run the application with `DB_PASSWORD` set as in the previous milestone. Create an account, add products, and place a demo order. Admin pages remain protected and CRUD is planned for the next milestone. Do not deploy the PHP built-in server publicly; use a dedicated public document root and HTTPS for production.
