# Woodmak Testing Checklist

## Core Acceptance
1. Guest cannot see or open B2B-only products.
2. B2B request form submits and sends admin notification.
3. Pending user can log in but sees regular catalog/prices.
4. Approved B2B user sees B2B-only products and B2B prices.
5. Global discount (example 10%) affects cart, checkout, and order totals.
6. Shop/category filters work for category, color, price, dimensions, and weight.
7. Language switcher works for Macedonian and English storefront pages.
8. Cart sidebar opens via AJAX add-to-cart and shows suggestions.
9. Checkout page has role-aware layout/messaging.
10. Invoice output includes role-specific sections.

## Additional Scenarios
1. Product with `_b2b_price` and without `_b2b_price` behaves correctly for B2C and B2B users.
2. Mobile viewport test for filter panel, cart sidebar, and checkout columns.
3. Unauthorized direct URL access to B2B-only product redirects with notice.
4. Existing customer submitting B2B request transitions to `b2b_pending`.
5. Rejected B2B account returns to `customer` role.
6. Existing admin/shop-manager email submitted through B2B request is rejected with `forbidden_role` notice.
7. Rapid duplicate form submissions are rate-limited for one minute.
8. Variable product with parent `_b2b_only=yes` cannot be added to cart by unauthorized users.
9. Changing `Appearance > Customize > Woodmak Brand Colors > Primary Brand Color` updates primary UI accents site-wide.
10. Homepage renders full section flow (hero, USP strip, product tabs, stats, news, newsletter, rich footer).
11. B2B checkout rejects invalid VAT formats and accepts valid alphanumeric VAT values.
12. Filter ranges with inverted min/max (for example min=200, max=100) still return valid results.
13. Invoice remains B2B-style for historical B2B orders even if the user role changes after order placement.
14. No rounded corners are visible on storefront elements (buttons, cards, inputs, notices, pagination, sidebar).
15. No gradients are visible on storefront backgrounds or section blocks.
