# F1 MERCH — Grid to Street

## Project Structure

- `*.php` — storefront, customer, checkout, success, and admin pages
- `api/` — backend API endpoints
- `assets/css/` — stylesheet
- `assets/js/` — frontend JavaScript
- `database/migrations/` — database schema and migration SQL
- `uploads/products/` — product images
- `uploads/profiles/` — profile images
- `db.php` — PDO database connection

## Main Pages

- `index.php` — storefront
- `drops.php` — new releases / drops
- `product.php` — product details
- `cart.php` — shopping cart
- `checkout.php` — checkout
- `login.php` — customer login
- `account.php` — customer account
- `admin-login.php` — administrator login
- `admin.php` — administrator dashboard
- `server-status.php` — server/API status

## XAMPP

Place the `WebProg` folder inside `htdocs`, start Apache and MySQL, import the SQL schema, and open:

`http://localhost/WebProg/index.php`

Admin:

`http://localhost/WebProg/admin-login.php`


## React integration

The storefront's **Choose your garage** section uses React 18 to render a searchable team collection. React and ReactDOM are loaded from the unpkg CDN, while the component source is maintained in `assets/js/react-teams.js`. The rest of the PHP pages, API endpoints, sessions, and database remain in place.

- The team search filters the collection as you type.
- Team tiles link to `drops.php?team=...`.
- The React feature does not require Node.js or a build step.
- An internet connection is required for the React CDN scripts to load.

## Tailwind CSS integration

Tailwind CSS is integrated through the Tailwind browser CDN. The project continues to use the existing `assets/css/styles.css` for the main F1 Merch design, while Tailwind utility classes are used in the React-powered team collection and can be used for additional responsive UI components.

- Tailwind CSS browser build is loaded in the PHP pages.
- No Node.js or npm build step is required.
- React and Tailwind can be used together in the same frontend.
- An internet connection is required for the CDN resources.
