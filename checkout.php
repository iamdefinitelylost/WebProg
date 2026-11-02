<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <title>
      Checkout &mdash; F1 Merch
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
          <a class="nav-cart-link" href="cart.php">
            Cart &middot;
            <span id="cartCount">
              0
            </span>
          </a>
        </div>
      </nav>
    </header>
    <main class="wrap checkout-page">
      <div class="page-title">
        <span class="eyebrow">
          PIT WALL
        </span>
        <h1>
          Checkout.
        </h1>
      </div>
      <div class="checkout-layout">
        <form class="checkout-card" id="checkoutForm">
          <h2>
            Shipping details
          </h2>
          <div class="form-grid">
            <label>
              Full name
              <input id="fullName" name="full_name" required=""/>
            </label>
            <label>
              Email
              <input id="email" name="email" readonly=""/>
            </label>
            <label>
              Phone
              <input name="phone" placeholder="09XXXXXXXXX" required=""/>
            </label>
            <label>
              Postal code
              <input name="postal_code" required=""/>
            </label>
            <label class="wide">
              Street address
              <input name="address" required=""/>
            </label>
            <label>
              City / Municipality
              <input name="city" required=""/>
            </label>
            <label>
              Province
              <input name="province" required=""/>
            </label>
          </div>
          <h2>
            Delivery
          </h2>
          <div class="choice-grid">
            <label class="choice">
              <input checked="" name="shipping_method" type="radio" value="standard"/>
              <span>
                <b>
                  Standard
                </b>
                <small>
                  FREE &middot; 3&ndash;5 business days
                </small>
              </span>
            </label>
            <label class="choice">
              <input name="shipping_method" type="radio" value="express"/>
              <span>
                <b>
                  Express
                </b>
                <small>
                  ₱450 &middot; priority delivery
                </small>
              </span>
            </label>
          </div>
          <h2>
            Payment
          </h2>
          <div class="choice-grid">
            <label class="choice">
              <input checked="" name="payment_method" type="radio" value="cod"/>
              <span>
                <b>
                  Cash on Delivery
                </b>
                <small>
                  Pay when your package arrives
                </small>
              </span>
            </label>
            <label class="choice">
              <input name="payment_method" type="radio" value="gcash"/>
              <span>
                <b>
                  GCash / E-wallet
                </b>
                <small>
                  Payment confirmation simulated for demo
                </small>
              </span>
            </label>
          </div>
          <label class="check-row">
            <input id="terms" required="" type="checkbox"/>
            I confirm my shipping details and order.
          </label>
          <p class="form-message" id="checkoutMessage">
          </p>
          <button class="btn btn-primary full" id="placeOrderBtn">
            Place order
          </button>
        </form>
        <aside class="summary-card">
          <h2>
            Your order
          </h2>
          <div id="checkoutItems">
          </div>
          <div>
            <span>
              Subtotal
            </span>
            <strong id="checkoutSubtotal">
              ₱0.00
            </strong>
          </div>
          <div>
            <span>
              Shipping
            </span>
            <strong id="checkoutShipping">
              FREE
            </strong>
          </div>
          <div class="summary-total">
            <span>
              Total
            </span>
            <strong id="checkoutTotal">
              ₱0.00
            </strong>
          </div>
        </aside>
      </div>
    </main>
    <script src="assets/js/auth-client.js">
    </script>
    <script src="assets/js/auth-nav.js?v=1">
    </script>
    <script src="assets/js/products-data.js">
    </script>
    <script src="assets/js/checkout.js?v=27">
    </script>
  </body>
</html>
