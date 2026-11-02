<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>
      Admin Login &mdash; F1 Merch
    </title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&amp;family=Inter:wght@400;500;600;700&amp;family=Space+Mono:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="assets/css/styles.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  </head>
  <body class="auth-page admin-auth-page">
    <div class="checker-bar">
    </div>
    <main class="auth-shell">
      <a class="auth-brand" href="index.php">
        <span class="auth-badge">
          F1
        </span>
        <span>
          <strong>
            F1 MERCH
          </strong>
          <small>
            GRID TO STREET
          </small>
        </span>
      </a>
      <section class="auth-card admin-auth-card">
        <div class="auth-head">
          <span class="eyebrow">
            Administration
          </span>
          <h1>
            Admin Sign In
          </h1>
          <p>
            Authorized administrators only.
          </p>
        </div>
        <form class="auth-form" id="adminLoginForm">
          <label>
            Email
            <input autocomplete="username" id="adminEmail" placeholder="admin@example.com" required="" type="email"/>
          </label>
          <label>
            Password
            <input autocomplete="current-password" id="adminPassword" placeholder="Administrator password" required="" type="password"/>
          </label>
          <button class="btn btn-primary auth-submit" type="submit">
            Sign in to dashboard
          </button>
        </form>
        <p aria-live="polite" class="auth-message" id="adminAuthMessage">
        </p>
        <a class="auth-back" href="index.php">
          &larr; Back to storefront
        </a>
        <a class="auth-customer-link" href="login.php">
          Customer sign in &rarr;
        </a>
      </section>
    </main>
    <script src="assets/js/auth-client.js?v=11">
    </script>
    <script src="assets/js/admin-login.js?v=11">
    </script>
  </body>
</html>
