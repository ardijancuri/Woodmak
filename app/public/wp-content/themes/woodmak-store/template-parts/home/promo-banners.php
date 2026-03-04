<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<section class="ws-home-section ws-home-section--promos">
	<div class="ws-container ws-promo-grid">
		<article class="ws-promo ws-promo--primary">
			<p class="ws-kicker"><?php esc_html_e( 'Living Room', 'woodmak-store' ); ?></p>
			<h3><?php esc_html_e( 'Timeless Comfort, Contemporary Form', 'woodmak-store' ); ?></h3>
			<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Explore', 'woodmak-store' ); ?></a>
		</article>
		<article class="ws-promo">
			<p class="ws-kicker"><?php esc_html_e( 'Dining', 'woodmak-store' ); ?></p>
			<h3><?php esc_html_e( 'Crafted Tables for Daily Rituals', 'woodmak-store' ); ?></h3>
			<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop now', 'woodmak-store' ); ?></a>
		</article>
		<article class="ws-promo">
			<p class="ws-kicker"><?php esc_html_e( 'Office', 'woodmak-store' ); ?></p>
			<h3><?php esc_html_e( 'Professional Spaces with Character', 'woodmak-store' ); ?></h3>
			<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Discover', 'woodmak-store' ); ?></a>
		</article>
	</div>
</section>
