<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <title>
      Product &mdash; F1 Merch
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
    <main class="wrap product-page">
      <div id="productRoot">
        <div class="loading">
          Loading product&hellip;
        </div>
      </div>
      <section class="reviews-section">
        <div class="section-head">
          <div>
            <span class="eyebrow">
              COMMUNITY
            </span>
            <h2>
              Reviews
            </h2>
          </div>
          <span class="review-average" id="reviewAverage">
            &mdash;
          </span>
        </div>
        <div id="reviewList">
        </div>
        <form class="review-form" hidden="" id="reviewForm">
          <h3>
            Leave a review
          </h3>
          <div aria-label="Choose a rating" class="rating-picker" id="starsInput" role="radiogroup">
            <button aria-label="1 star" class="rating-star" data-rating="1" type="button">
              &starf;
            </button>
            <button aria-label="2 stars" class="rating-star" data-rating="2" type="button">
              &starf;
            </button>
            <button aria-label="3 stars" class="rating-star" data-rating="3" type="button">
              &starf;
            </button>
            <button aria-label="4 stars" class="rating-star" data-rating="4" type="button">
              &starf;
            </button>
            <button aria-label="5 stars" class="rating-star" data-rating="5" type="button">
              &starf;
            </button>
            <button aria-label="Set rating to zero" class="rating-clear" id="ratingClear" type="button">
              0 / 5
            </button>
            <span class="rating-value" id="ratingValue">
              0 / 5
            </span>
          </div>
          <input id="rating" type="hidden" value="0"/>
          <textarea id="reviewComment" maxlength="500" placeholder="Tell other racers what you think..." required=""></textarea>
          <button class="btn btn-primary">
            Publish review
          </button>
          <p id="reviewMsg">
          </p>
        </form>
      </section>
    </main>
    <script src="assets/js/auth-client.js">
    </script>
    <script src="assets/js/auth-nav.js?v=1">
    </script>
    <script src="assets/js/products-data.js">
    </script>
    <script src="assets/js/product.js">
    </script>
  </body>
</html>
