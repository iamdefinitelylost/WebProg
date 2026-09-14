# F1 Merch test checklist

## Authentication
- Customer signup with valid/invalid email and short password.
- Customer login and logout.
- Admin login with customer credentials (must be rejected).
- Banned customer login (must be rejected).

## Store
- Search/filter products.
- Add/remove/update cart quantities.
- Checkout with standard/express shipping.
- Verify stock cannot go below zero.
- Verify server-side prices are used at checkout.

## Account
- Save profile name/bio/team.
- Upload a valid avatar and verify it persists.
- Reject oversized/non-image uploads.
- Wishlist add/remove.
- View order tracking.

## Reviews
- Submit 0–5 star ratings.
- Update an existing review.
- Verify customer avatar/name appear publicly.
- Admin hide/show/delete review.

## Admin
- Product add/edit/delete/photo upload.
- Order status changes.
- Customer search and ban/unban.
- Reports and CSV exports.
- Verify non-admin sessions cannot access admin APIs.
