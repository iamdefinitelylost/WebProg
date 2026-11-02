<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1.0" name="viewport"/>
    <title>
      F1 MERCH &mdash; Grid to Street
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
          <svg class="brand-badge" viewbox="0 0 240 240" xmlns="http://www.w3.org/2000/svg">
            <rect fill="#14161C" height="240" rx="26" width="240">
            </rect>
            <circle cx="204" cy="34" fill="#E2231A" r="7">
            </circle>
            <text fill="#fff" font-family="Space Grotesk" font-size="96" font-weight="700" text-anchor="middle" x="120" y="118">
              F1
            </text>
            <rect fill="#fff" height="15" width="15" x="60" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="75" y="144">
            </rect>
            <rect fill="#fff" height="15" width="15" x="90" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="105" y="144">
            </rect>
            <rect fill="#fff" height="15" width="15" x="120" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="135" y="144">
            </rect>
            <rect fill="#fff" height="15" width="15" x="150" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="165" y="144">
            </rect>
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
          <a class="active" href="#lineup">
            Shop
          </a>
          <a href="drops.php">
            Drops
          </a>
          <a href="#teams">
            Teams
          </a>
          <a href="#story">
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
    <section class="hero">
      <div class="wrap hero-grid">
        <div>
          <div class="hero-eyebrow">
            <span class="eyebrow">
              2026 Collection
            </span>
          </div>
          <h1>
            Grid to
            <br/>
            <em>
              Street.
            </em>
          </h1>
          <p class="lede">
            Merchandise built with the same precision as the car. Every stitch, cut, and colorway is signed off against the F1 Merch badge &mdash; bold, exact, and built to be recognized from the paddock to the pavement.
          </p>
          <div class="hero-ctas">
            <a class="btn btn-primary" href="#lineup">
              Shop the drop
            </a>
            <a class="btn btn-ghost" href="#story">
              Our mission
            </a>
          </div>
        </div>
        <div class="hero-panel">
          <svg class="badge-huge" viewbox="0 0 240 240" xmlns="http://www.w3.org/2000/svg">
            <rect fill="#14161C" height="240" rx="26" stroke="#2A2E38" width="240">
            </rect>
            <circle cx="204" cy="34" fill="#E2231A" r="7">
            </circle>
            <text fill="#fff" font-family="Space Grotesk" font-size="96" font-weight="700" text-anchor="middle" x="120" y="118">
              F1
            </text>
            <rect fill="#fff" height="15" width="15" x="60" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="75" y="144">
            </rect>
            <rect fill="#fff" height="15" width="15" x="90" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="105" y="144">
            </rect>
            <rect fill="#fff" height="15" width="15" x="120" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="135" y="144">
            </rect>
            <rect fill="#fff" height="15" width="15" x="150" y="144">
            </rect>
            <rect fill="#E2231A" height="15" width="15" x="165" y="144">
            </rect>
            <text fill="#E2231A" font-family="Inter" font-size="18" font-weight="600" letter-spacing="5" text-anchor="middle" x="120" y="196">
              MERCH
            </text>
          </svg>
          <div class="readout">
            <span class="k">
              Drop
            </span>
            <span class="k">
              Stock
            </span>
            <span class="k">
              Ships
            </span>
          </div>
        </div>
      </div>
    </section>
    <div class="checker-bar">
    </div>
    <section id="lineup">
      <div class="wrap">
        <div class="section-head">
          <div>
            <h2>
              This weekend's lineup
            </h2>
            <p id="lineupSubhead">
              Loading this weekend's lineup&hellip;
            </p>
          </div>
          <a class="text-link" href="drops.php">
            See all drops &rarr;
          </a>
        </div>
        <div class="lineup-tools">
          <div class="lineup-search">
            &telrec;
            <input id="searchInput" placeholder="Search products, teams, categories..."/>
          </div>
          <div class="lineup-filters" id="categoryFilters">
            <button class="lineup-filter active" data-category="all">
              All
            </button>
            <button class="lineup-filter" data-category="headwear">
              Headwear
            </button>
            <button class="lineup-filter" data-category="outerwear">
              Outerwear
            </button>
            <button class="lineup-filter" data-category="tees">
              Tees
            </button>
            <button class="lineup-filter" data-category="archive">
              Archive
            </button>
          </div>
        </div>
        <div class="lineup" id="lineupGrid">
        </div>
      </div>
    </section>
    <div class="telemetry">
      <div class="wrap">
        <div class="cell">
          <div class="k">
            Founded
          </div>
          <div class="v">
            20
            <span>
              26
            </span>
          </div>
        </div>
        <div class="cell">
          <div class="k">
            Drops per season
          </div>
          <div class="v">
            1
            <span>
              4
            </span>
          </div>
        </div>
        <div class="cell">
          <div class="k">
            Fabric sourced
          </div>
          <div class="v">
            Trackside
            <span>
              *
            </span>
          </div>
        </div>
        <div class="cell">
          <div class="k">
            Ship time
          </div>
          <div class="v">
            48
            <span>
              hr
            </span>
          </div>
        </div>
      </div>
    </div>
    <section id="teams">
      <div class="wrap">
        <div class="section-head">
          <div>
            <h2>
              Choose your garage
            </h2>
            <p>
              Browse the lineup by team and find your colors.
            </p>
          </div>
        </div>
        <div class="react-team-tools">
          <label class="sr-only" for="reactTeamSearch">Search teams</label>
          <input id="reactTeamSearch" type="search" placeholder="Search a team..." autocomplete="off" />
          <span id="reactTeamCount" aria-live="polite">Loading teams...</span>
        </div>
        <div class="team-rail" id="teamGrid" aria-live="polite">
          <p class="react-team-loading">Loading teams...</p>
        </div>
      </div>
    </section>
    <section id="story">
      <div class="wrap story">
        <div class="story-copy">
          <span class="eyebrow mute">
            Mission
          </span>
          <h2>
            Bold. Precise.
            <br/>
            Recognizable.
          </h2>
          <p>
            We build merchandise that carries the speed and precision of the sport into everyday wear. Every piece stays true to the badge &mdash; whether on a race-day cap or a limited drop, it's the same mark, held to the same standard.
          </p>
        </div>
        <div class="story-visual">
          <svg viewbox="0 0 240 240" xmlns="http://www.w3.org/2000/svg">
            <rect fill="none" height="240" rx="26" stroke="#2A2E38" stroke-width="1.5" width="240">
            </rect>
            <text fill="none" font-family="Space Grotesk" font-size="96" font-weight="700" stroke="#2A2E38" stroke-width="1.5" text-anchor="middle" x="120" y="118">
              F1
            </text>
            <circle cx="204" cy="34" fill="none" r="7" stroke="#E2231A" stroke-width="1.5">
            </circle>
            <text fill="#5A5E6B" font-family="Inter" font-size="18" font-weight="600" letter-spacing="5" text-anchor="middle" x="120" y="196">
              MERCH
            </text>
          </svg>
        </div>
      </div>
    </section>
    <div class="checker-bar dim">
    </div>
    <section class="cta-band" id="join">
      <div class="wrap">
        <h2>
          Join the grid
        </h2>
        <p>
          Get notified the moment a drop goes live. No spam, just start times.
        </p>
        <form class="signup" id="signupForm">
          <input id="signupEmail" placeholder="your@email.com" required="" type="email"/>
          <button class="btn btn-primary" type="submit">
            Notify me
          </button>
        </form>
        <p class="signup-msg" id="signupMsg">
        </p>
      </div>
    </section>
    <footer id="typography">
      <div class="wrap">
        <div class="foot-grid">
          <div class="foot-brand">
            <div class="brand">
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
            </div>
            <p>
              Merchandise built to the same standard as the badge. Bold, precise, instantly recognizable.
            </p>
          </div>
          <div class="foot-col">
            <h4>
              Shop
            </h4>
            <a class="foot-link" data-filter="headwear" href="#lineup">
              Headwear
            </a>
            <a class="foot-link" data-filter="outerwear" href="#lineup">
              Outerwear
            </a>
            <a class="foot-link" data-filter="tees" href="#lineup">
              Tees
            </a>
            <a class="foot-link" data-filter="archive" href="#lineup">
              Archive
            </a>
          </div>
          <div class="foot-col">
            <h4>
              Brand
            </h4>
            <a href="#story">
              Mission
            </a>
            <a href="drops.php">
              Drops
            </a>
            <a href="account.php">
              Account
            </a>
          </div>
          <div class="foot-col">
            <h4>
              Support
            </h4>
            <a class="foot-link" data-modal="shipping" href="#">
              Shipping
            </a>
            <a class="foot-link" data-modal="returns" href="#">
              Returns
            </a>
            <a class="foot-link" data-modal="contact" href="#">
              Contact
            </a>
          </div>
        </div>
        <div class="foot-bottom">
          <span>
            &copy; 2026 F1 Merch. All rights reserved.
          </span>
          <a class="admin-link" href="admin-login.php">
            Admin login
          </a>
        </div>
      </div>
    </footer>
    <div class="modal-overlay" hidden="" id="modalOverlay">
      <div aria-modal="true" class="modal" role="dialog">
        <button class="modal-close" id="modalClose">
          &times;
        </button>
        <h3 id="modalTitle">
        </h3>
        <div id="modalBody">
        </div>
      </div>
    </div>
    <script src="assets/js/auth-client.js?v=27">
    </script>
    <script src="assets/js/auth-nav.js?v=1">
    </script>
    <script src="assets/js/products-data.js?v=27">
    </script>
    <script src="assets/js/script.js?v=20">
    </script>
    <!-- React powers the searchable team collection section. -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="assets/js/react-teams.js?v=1"></script>
  </body>
</html>
