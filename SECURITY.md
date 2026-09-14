# Security and validation notes

- Passwords are stored with `password_hash()` and verified with `password_verify()`.
- Admin endpoints enforce the administrator role server-side.
- Customer ban status is rechecked from the database.
- State-changing authenticated API requests use a session-bound CSRF token in `X-CSRF-Token`.
- PDO prepared statements are used for user-controlled SQL values.
- Uploaded product/profile images are MIME-checked and size-limited.
- API exceptions are logged server-side rather than returned as raw stack traces.
- Customer and admin sessions use HttpOnly/SameSite cookies.

Before deployment, use HTTPS and change the development database credentials from the XAMPP defaults.
