<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <title>
      Admin Command &mdash; F1 Merch
    </title>
    <link href="assets/css/styles.css?v=20" rel="stylesheet"/>
  </head>
  <body class="admin-page">
    <header>
      <nav class="wrap subnav">
        <a class="brand" href="index.php">
          <svg class="brand-badge" viewbox="0 0 240 240" xmlns="http://www.w3.org/2000/svg">
            <rect fill="#14161C" height="240" rx="26" width="240">
            </rect>
            <circle cx="204" cy="34" fill="#E2231A" r="7">
            </circle>
            <text fill="#fff" font-family="Space Grotesk" font-size="96" font-weight="700" text-anchor="middle" x="120" y="118">
              F1
            </text>
            <text fill="#E2231A" font-family="Inter" font-size="18" font-weight="600" letter-spacing="5" text-anchor="middle" x="120" y="196">
              MERCH
            </text>
          </svg>
          <span class="brand-copy">
            <strong>
              F1 MERCH
            </strong>
            <small>
              COMMAND CENTER
            </small>
          </span>
        </a>
        <div class="nav-links" style="display:flex">
          <a href="index.php">
            Storefront
          </a>
        </div>
        <div class="nav-actions">
          <span class="nav-admin-name" id="adminName">
            Admin
          </span>
          <button class="text-button" id="adminLogout">
            Sign out
          </button>
        </div>
      </nav>
    </header>
    <div class="checker-bar dim">
    </div>
    <main class="wrap admin-main">
      <div class="page-title">
        <span class="eyebrow">
          ADMINISTRATOR
        </span>
        <h1>
          Command center.
        </h1>
        <p>
          Manage products, stock, orders, customers and store performance.
        </p>
      </div>
      <div class="admin-tabs">
        <button class="active" data-tab="dashboard">
          Dashboard
        </button>
        <button data-tab="products">
          Products
        </button>
        <button data-tab="orders">
          Orders
        </button>
        <button data-tab="customers">
          Customers
        </button>
        <button data-tab="reviews">
          Reviews
        </button>
        <button data-tab="reports">
          Reports
        </button>
      </div>
      <section class="admin-tab" id="dashboardTab">
        <div class="stats-grid" id="statGrid">
        </div>
        <div class="admin-two">
          <section class="admin-card">
            <h2>
              Sales performance
            </h2>
            <div class="sales-chart" id="salesChart">
            </div>
          </section>
          <section class="admin-card">
            <h2>
              Inventory watch
            </h2>
            <div id="inventoryWatch">
            </div>
          </section>
        </div>
      </section>
      <section class="admin-tab" hidden="" id="productsTab">
        <div class="admin-two">
          <section class="admin-card">
            <h2 id="formTitle">
              Add product
            </h2>
            <form class="account-form" id="productForm">
              <input id="productId" type="hidden"/>
              <label>
                Name
                <input id="fieldName" required=""/>
              </label>
              <label>
                Description
                <textarea id="fieldDesc" required=""></textarea>
              </label>
              <div class="form-grid">
                <label>
                  Price
                  <input id="fieldPrice" min="0" required="" step="0.01" type="number"/>
                </label>
                <label>
                  Stock
                  <input id="fieldStock" min="0" required="" type="number" value="25"/>
                </label>
                <label>
                  Category
                  <select id="fieldCategory">
                    <option>
                      headwear
                    </option>
                    <option>
                      outerwear
                    </option>
                    <option>
                      tees
                    </option>
                    <option>
                      archive
                    </option>
                  </select>
                </label>
                <label>
                  Team
                  <input id="fieldTeam" placeholder="Ferrari"/>
                </label>
                <label>
                  Sizes
                  <input id="fieldSizes" placeholder="S,M,L,XL"/>
                </label>
                <label>
                  Photo
                  <input accept="image/jpeg,image/png,image/webp" id="fieldImage" type="file"/>
                </label>
              </div>
              <label class="check-row">
                <input id="fieldLimited" type="checkbox"/>
                Limited edition
              </label>
              <label class="check-row">
                <input id="fieldFeatured" type="checkbox"/>
                Featured product
              </label>
              <div>
                <button class="btn btn-primary">
                  Save product
                </button>
                <button class="btn btn-ghost" id="cancelEdit" type="button">
                  Clear
                </button>
              </div>
              <p id="productMsg">
              </p>
            </form>
          </section>
          <section class="admin-card">
            <div class="admin-list-head">
              <h2>
                Catalog
              </h2>
              <button class="btn btn-ghost" id="resetProducts">
                Reset defaults
              </button>
            </div>
            <div id="productList">
            </div>
          </section>
        </div>
      </section>
      <section class="admin-tab" hidden="" id="ordersTab">
        <div class="admin-card">
          <div class="admin-list-head">
            <h2>
              All orders
            </h2>
            <button class="btn btn-ghost" id="refreshOrders">
              Refresh
            </button>
          </div>
          <div class="admin-filters">
            <input id="orderSearch" placeholder="Search order, customer or email..."/>
            <select id="orderStatus">
              <option value="all">
                All statuses
              </option>
              <option value="pending">
                Pending
              </option>
              <option value="processing">
                Processing
              </option>
              <option value="shipped">
                Shipped
              </option>
              <option value="out_for_delivery">
                Out for delivery
              </option>
              <option value="delivered">
                Delivered
              </option>
              <option value="cancelled">
                Cancelled
              </option>
            </select>
          </div>
          <div id="ordersAdminList">
          </div>
        </div>
      </section>
      <section class="admin-tab" hidden="" id="customersTab">
        <div class="admin-card">
          <div class="admin-list-head">
            <h2>
              Customer accounts
            </h2>
            <button class="btn btn-ghost" id="refreshCustomers">
              Refresh
            </button>
          </div>
          <div class="admin-filters">
            <input id="customerSearch" placeholder="Search name or email..."/>
            <select id="customerStatus">
              <option value="all">
                All customers
              </option>
              <option value="active">
                Active
              </option>
              <option value="banned">
                Banned
              </option>
            </select>
          </div>
          <div id="userList">
          </div>
        </div>
      </section>
      <section class="admin-tab" hidden="" id="reviewsTab">
        <div class="admin-card">
          <div class="admin-list-head">
            <h2>
              Review moderation
            </h2>
            <button class="btn btn-ghost" id="refreshReviews">
              Refresh
            </button>
          </div>
          <div class="admin-filters">
            <input id="reviewSearch" placeholder="Search customer, product or review..."/>
            <select id="reviewStatus">
              <option value="all">
                All reviews
              </option>
              <option value="visible">
                Visible
              </option>
              <option value="hidden">
                Hidden
              </option>
            </select>
          </div>
          <div id="reviewAdminList">
          </div>
        </div>
      </section>
      <section class="admin-tab" hidden="" id="reportsTab">
        <div class="admin-card">
          <div class="admin-list-head">
            <h2>
              Reports &amp; exports
            </h2>
            <div>
              <button class="btn btn-ghost" id="exportOrders">
                Export orders
              </button>
              <button class="btn btn-ghost" id="exportCustomers">
                Export customers
              </button>
              <button class="btn btn-ghost" id="exportProducts">
                Export products
              </button>
              <button class="btn btn-ghost" id="exportReviews">
                Export reviews
              </button>
            </div>
          </div>
          <div class="report-grid" id="reportSummary">
          </div>
        </div>
      </section>
    </main>
    <script src="assets/js/auth-client.js">
    </script>
    <script src="assets/js/products-data.js">
    </script>
    <script src="assets/js/admin.js?v=27">
    </script>
  </body>
</html>
