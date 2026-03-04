# Woodmak Admin Usage

## Required Plugins
- WooCommerce
- Polylang (for MK/EN)
- WooCommerce PDF Invoices & Packing Slips (WP Overnight)
- Woodmak B2B Core (custom plugin from this repo)
- Woodmak Store (custom theme from this repo)

## Version Compatibility (validated in this repo)
- WooCommerce: `10.5.3`
- Polylang: `3.7.8`
- PDF Invoices & Packing Slips for WooCommerce: `5.8.1`

## Compatibility Notices
- The plugin checks active versions and shows an admin notice when versions differ from the validated set above.
- HPOS (`custom_order_tables`) compatibility is declared for WooCommerce.

## Brand Color in Customizer
1. Go to `Appearance > Customize > Woodmak Brand Colors`.
2. Update `Primary Brand Color`.
3. Save and publish.
4. The theme automatically applies the color to primary CTAs, links, badges, and key UI accents.
5. Storefront style is sharp-edge (`no rounded corners`) with flat backgrounds (`no gradients`).

## Halmar-Style Homepage Setup
1. Go to `Settings > Reading`.
2. Set `Your homepage displays` to `A static page`.
3. Select the page you want as homepage (theme uses `front-page.php` layout automatically).
4. Add product categories, products, and blog posts so all homepage sections populate.
5. Assign menus in `Appearance > Menus` for `Utility Menu`, `Category Menu`, and `Footer Menu`.
6. Configure hero/stats/newsletter visuals in `Appearance > Customize > Homepage Visuals`.

## Footer Logo in Customizer
1. Go to `Appearance > Customize > Footer Branding`.
2. Set `Footer Logo`.
3. Save and publish.
4. If no footer logo is set, the theme falls back to Site Identity logo, then site name text.

## Approve or Reject B2B Users
1. Go to Users.
2. Find the requester.
3. Use row action `Approve B2B` or `Reject B2B`.
4. Alternative path: open user profile and change `B2B Status`.

## Set Global B2B Discount (0/5/10/15)
1. Open user profile.
2. In `B2B Account Settings`, set `B2B Discount Percent`.
3. Save profile.

## Mark Product as B2B-only
1. Open product edit screen.
2. In pricing panel, enable `B2B only product`.
3. Update product.

## Set Product-level B2B Price
1. For simple products: set `B2B price` in product pricing panel.
2. For variable products: set `B2B price` per variation.
3. Update product.

## B2B Request Page
1. Create/edit page with slug `b2b-request`.
2. Use page template `B2B Request` or place shortcode `[wm_b2b_request_form]`.
3. Country is selected from WooCommerce country list (fallback text field appears only if country data is unavailable).

## Invoice Role Behavior
- B2C orders show B2C invoice type row.
- B2B orders show company, VAT, and discount rows.
