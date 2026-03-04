<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

$fallback_image = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_single' ) : '';

$banners = array(
	array(
		'title' => ws_get_theme_mod_text( 'ws_home_promo_1_title', __( 'Living Room Collection', 'woodmak-store' ) ),
		'url'   => ws_get_theme_mod_url( 'ws_home_promo_1_url', $shop_url ),
		'image' => ws_get_theme_mod_image_url( 'ws_home_promo_1_image' ),
	),
	array(
		'title' => ws_get_theme_mod_text( 'ws_home_promo_2_title', __( 'Dining and Kitchen Picks', 'woodmak-store' ) ),
		'url'   => ws_get_theme_mod_url( 'ws_home_promo_2_url', $shop_url ),
		'image' => ws_get_theme_mod_image_url( 'ws_home_promo_2_image' ),
	),
);

$has_banner = false;
foreach ( $banners as &$banner ) {
	if ( ! $banner['url'] ) {
		$banner['url'] = $shop_url;
	}
	if ( ! $banner['image'] && $fallback_image ) {
		$banner['image'] = esc_url( $fallback_image );
	}
	if ( $banner['title'] || $banner['image'] ) {
		$has_banner = true;
	}
}
unset( $banner );

if ( ! $has_banner ) {
	return;
}
?>
<section class="ws-home-section ws-home-section--promo-banners">
	<div class="ws-container">
		<div class="ws-promo-banners">
			<?php foreach ( $banners as $banner ) : ?>
				<?php
				$style = '';
				if ( $banner['image'] ) {
					$style = ' style="background-image:url(' . esc_url( $banner['image'] ) . ');"';
				}
				?>
				<a class="ws-promo-banner" href="<?php echo esc_url( $banner['url'] ); ?>"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<span class="ws-promo-banner__title"><?php echo esc_html( $banner['title'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
