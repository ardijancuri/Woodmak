<?php
/**
 * My Account page template override.
 *
 * Mirrors WooCommerce template with a stable layout wrapper for Woodmak styling.
 *
 * @package Woodmak_Store
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="ws-my-account-layout">
	<aside class="ws-my-account-layout__nav">
		<?php do_action( 'woocommerce_account_navigation' ); ?>
	</aside>

	<section class="ws-my-account-layout__content">
		<div class="woocommerce-MyAccount-content">
			<?php do_action( 'woocommerce_account_content' ); ?>
		</div>
	</section>
</div>
