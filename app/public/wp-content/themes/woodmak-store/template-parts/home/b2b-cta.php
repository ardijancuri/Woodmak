<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="ws-home-section ws-home-section--b2b-cta">
	<div class="ws-container ws-b2b-cta">
		<div>
			<p class="ws-kicker"><?php esc_html_e( 'For Retailers and Projects', 'woodmak-store' ); ?></p>
			<h2><?php esc_html_e( 'Apply for Woodmak Wholesale Program', 'woodmak-store' ); ?></h2>
			<p><?php esc_html_e( 'Get access to B2B-only products, role-based pricing, and streamlined ordering for your business.', 'woodmak-store' ); ?></p>
		</div>
		<div class="ws-b2b-cta__actions">
			<a class="button alt" href="<?php echo esc_url( home_url( '/b2b-request/' ) ); ?>"><?php esc_html_e( 'Start B2B Request', 'woodmak-store' ); ?></a>
			<a class="button" href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>"><?php esc_html_e( 'My Account', 'woodmak-store' ); ?></a>
		</div>
	</div>
</section>
