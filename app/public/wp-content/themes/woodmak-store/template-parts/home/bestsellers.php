<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$products = wc_get_products(
	array(
		'status'   => 'publish',
		'limit'    => 4,
		'orderby'  => 'total_sales',
		'order'    => 'DESC',
	)
);
if ( empty( $products ) ) {
	return;
}
?>
<section class="ws-home-section ws-home-section--bestsellers">
	<div class="ws-container">
		<div class="ws-section-heading">
			<h2><?php esc_html_e( 'Bestsellers', 'woodmak-store' ); ?></h2>
		</div>
		<ul class="products ws-home-products ws-home-products--compact">
			<?php foreach ( $products as $product ) : ?>
				<?php $post_object = get_post( $product->get_id() ); ?>
				<?php if ( ! $post_object ) { continue; } ?>
				<?php $GLOBALS['post'] = $post_object; ?>
				<?php setup_postdata( $GLOBALS['post'] ); ?>
				<?php wc_get_template_part( 'content', 'product' ); ?>
			<?php endforeach; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
</section>
