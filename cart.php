<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <title>
      Cart &mdash; F1 Merch
    </title>
    <link href="assets/css/styles.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  </head>
  <body>
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
              GRID TO STREET
            </small>
          </span>
        </a>
        <button aria-expanded="false" class="mobile-menu" id="menuBtn">
          ☰
        </button>
        <div class="nav-links" id="navLinks">
          <a class="" href="index.php#lineup">
            Shop
          </a>
          <a class="" href="drops.php">
            Drops
          </a>
          <a class="" href="index.php#teams">
            Teams
          </a>
          <a class="" href="index.php#story">
            Story
          </a>
        </div>
        <div class="nav-actions">
          <a href="login.php" id="accountLink">
            Sign in
          </a>
          <a class="nav-cart-link active" href="cart.php">
            Cart &middot;
            <span id="cartCount">
              0
            </span>
          </a>
        </div>
      </nav>
    </header>
    <main class="wrap cart-page">
      <div class="page-title">
        <span class="eyebrow">
          YOUR GARAGE
        </span>
        <h1>
          Shopping cart.
        </h1>
      </div>
      <div class="cart-layout">
        <section>
          <div id="cartItems">
          </div>
          <div class="cart-links">
            <a href="index.php#shop">
              &larr; Continue shopping
            </a>
            <button class="text-button" id="clearCart">
              Clear cart
            </button>
          </div>
        </section>
        <aside class="summary-card">
          <h2>
            Order summary
          </h2>
          <div>
            <span>
              Subtotal
            </span>
            <strong id="subtotal">
              ₱0.00
            </strong>
          </div>
          <div>
            <span>
              Standard shipping
            </span>
            <strong>
              FREE
            </strong>
          </div>
          <div class="summary-total">
            <span>
              Total
            </span>
            <strong id="total">
              ₱0.00
            </strong>
          </div>
          <button class="btn btn-primary full" id="checkoutBtn">
            Proceed to checkout
          </button>
          <small>
            Taxes and final delivery options are calculated at checkout.
          </small>
        </aside>
      </div>
    </main>
    <script src="assets/js/auth-client.js">
    </script>
    <script src="assets/js/auth-nav.js?v=1">
    </script>
    <script src="assets/js/products-data.js">
    </script>
    <script src="assets/js/cart.js?v=21">
    </script>
  </body>
</html>
