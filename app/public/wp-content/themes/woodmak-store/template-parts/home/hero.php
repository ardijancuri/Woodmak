<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url           = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$hero_title         = ws_get_theme_mod_text( 'ws_home_hero_title', __( 'New Collection 2026', 'woodmak-store' ) );
$hero_subtitle      = ws_get_theme_mod_text( 'ws_home_hero_subtitle', __( 'Premium furniture solutions for retail and wholesale buyers.', 'woodmak-store' ) );
$hero_cta_label     = ws_get_theme_mod_text( 'ws_home_hero_cta_label', __( 'Shop Now', 'woodmak-store' ) );
$hero_cta_url       = ws_get_theme_mod_url( 'ws_home_hero_cta_url', $shop_url );
$hero_desktop_image = ws_get_theme_mod_image_url( 'ws_home_hero_image_desktop' );
$hero_mobile_image  = ws_get_theme_mod_image_url( 'ws_home_hero_image_mobile' );
if ( '' === $hero_cta_url ) {
	$hero_cta_url = esc_url( $shop_url );
}

$fallback_image = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_single' ) : '';
if ( ! $hero_desktop_image && $fallback_image ) {
	$hero_desktop_image = esc_url( $fallback_image );
}
if ( ! $hero_mobile_image ) {
	$hero_mobile_image = $hero_desktop_image;
}
?>
<section class="ws-home-hero">
	<div class="ws-home-hero__media">
		<?php if ( $hero_desktop_image ) : ?>
			<picture>
				<?php if ( $hero_mobile_image ) : ?>
					<source media="(max-width: 760px)" srcset="<?php echo esc_url( $hero_mobile_image ); ?>" />
				<?php endif; ?>
				<img src="<?php echo esc_url( $hero_desktop_image ); ?>" alt="<?php echo esc_attr( $hero_title ); ?>" loading="eager" />
			</picture>
		<?php endif; ?>
		<div class="ws-container ws-home-hero__overlay">
			<div class="ws-home-hero__content">
				<p class="ws-kicker"><?php esc_html_e( 'New Arrivals', 'woodmak-store' ); ?></p>
				<h1><?php echo esc_html( $hero_title ); ?></h1>
				<p><?php echo esc_html( $hero_subtitle ); ?></p>
				<div class="ws-home-hero__actions">
					<a class="button alt" href="<?php echo esc_url( $hero_cta_url ); ?>"><?php echo esc_html( $hero_cta_label ); ?></a>
				</div>
			</div>
		</div>
	</div>
</section>
