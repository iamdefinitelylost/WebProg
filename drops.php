<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1.0" name="viewport"/>
    <title>
      Drops &mdash; F1 Merch
    </title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&amp;family=Inter:wght@400;500;600;700&amp;family=Space+Mono:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="assets/css/styles.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  </head>
  <body>
    <header>
      <nav class="wrap subnav">
        <a class="brand" href="index.php">
          <svg class="brand-badge" viewbox="0 0 240 240">
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
        <button class="mobile-menu" id="menuBtn">
          ☰
        </button>
        <div class="nav-links" id="navLinks">
          <a href="index.php#lineup">
            Shop
          </a>
          <a class="active" href="drops.php">
            Drops
          </a>
          <a href="index.php#teams">
            Teams
          </a>
          <a href="index.php#story">
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
    <section class="page-hero">
      <div class="wrap">
        <span class="eyebrow">
          RELEASE CONTROL
        </span>
        <h1>
          Latest drops.
        </h1>
        <p>
          Limited pieces, fresh releases, and archive reissues. Once they're gone, they're gone.
        </p>
        <div class="countdown">
          <div>
            <b id="days">
              00
            </b>
            <small>
              DAYS
            </small>
          </div>
          <div>
            <b id="hours">
              00
            </b>
            <small>
              HOURS
            </small>
          </div>
          <div>
            <b id="minutes">
              00
            </b>
            <small>
              MIN
            </small>
          </div>
          <div>
            <b id="seconds">
              00
            </b>
            <small>
              SEC
            </small>
          </div>
        </div>
      </div>
    </section>
    <div class="checker-bar">
    </div>
    <section>
      <div class="wrap">
        <div class="section-head">
          <div>
            <h2>
              Fresh off the grid
            </h2>
            <p>
              Everything here is pulled from the same live catalog as the main storefront.
            </p>
          </div>
        </div>
        <div class="lineup-tools">
          <div class="lineup-search">
            &telrec;
            <input id="search" placeholder="Search releases, teams, categories..."/>
          </div>
          <div class="lineup-filters">
            <button class="lineup-filter active" data-cat="all">
              All
            </button>
            <button class="lineup-filter" data-cat="limited">
              Limited
            </button>
            <button class="lineup-filter" data-cat="headwear">
              Headwear
            </button>
            <button class="lineup-filter" data-cat="outerwear">
              Outerwear
            </button>
            <button class="lineup-filter" data-cat="tees">
              Tees
            </button>
            <button class="lineup-filter" data-cat="archive">
              Archive
            </button>
          </div>
        </div>
        <div class="lineup" id="releaseGrid">
        </div>
      </div>
    </section>
    <div class="checker-bar dim">
    </div>
    <section class="cta-band">
      <div class="wrap">
        <h2>
          Don't miss the next lap.
        </h2>
        <p>
          Sign in to save products, build a wishlist, and track your orders.
        </p>
        <a class="btn btn-primary" href="login.php">
          Create account
        </a>
      </div>
    </section>
    <script src="assets/js/auth-client.js?v=27">
    </script>
    <script src="assets/js/auth-nav.js?v=1">
    </script>
    <script src="assets/js/products-data.js?v=27">
    </script>
    <script src="assets/js/drops.js?v=19">
    </script>
  </body>
</html>
