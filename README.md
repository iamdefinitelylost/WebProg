# F1 Merch — Web Programming Project

A responsive F1-inspired merchandise storefront with PHP/MariaDB backend, customer accounts, cart and checkout, order tracking, reviews, wishlist, inventory, and a role-protected admin command center.

## Requirements
- XAMPP (Apache + MariaDB/MySQL)
- PHP 8+ recommended
- Browser with JavaScript enabled

## Setup
1. Copy the project into `htdocs`.
2. Start Apache and MySQL in XAMPP.
3. Import `schema.sql` into phpMyAdmin, or use an existing `f1_merch` database.
4. Open the project through `http://localhost/...`, not `file://`.
5. If an existing database is used, `db.php` performs backward-compatible column checks.

## Architecture
- HTML/CSS/JS: storefront and responsive UI.
- `api/`: JSON endpoints for authentication, products, orders, reviews, wishlist, and analytics.
- `db.php`: PDO connection and compatibility checks.
- `database/migrations/`: versioned reference schema.
- `SECURITY.md`: security controls and deployment notes.
- `TESTING.md`: functional test checklist.

## Security
Passwords use PHP password hashing. PDO prepared statements are used for SQL values. Authenticated state-changing API requests require a session-bound CSRF token. Admin APIs verify the administrator role server-side. Uploaded images are MIME-checked and size-limited.

## GitHub submission
Use the commit history to show development progress. Do not commit `.env`, database passwords, uploads containing real customer data, or private credentials.
