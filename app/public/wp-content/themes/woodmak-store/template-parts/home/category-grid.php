<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 8,
	)
);
if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<section class="ws-home-section ws-home-section--categories">
	<div class="ws-container">
		<div class="ws-section-heading">
			<h2><?php esc_html_e( 'Browse by Category', 'woodmak-store' ); ?></h2>
			<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'View all', 'woodmak-store' ); ?></a>
		</div>
		<div class="ws-category-grid">
			<?php foreach ( $terms as $term ) : ?>
				<?php $term_link = get_term_link( $term ); ?>
				<?php if ( is_wp_error( $term_link ) ) { continue; } ?>
				<a class="ws-category-tile" href="<?php echo esc_url( $term_link ); ?>">
					<span class="ws-category-tile__name"><?php echo esc_html( $term->name ); ?></span>
					<span class="ws-category-tile__count"><?php echo esc_html( (string) $term->count ); ?> <?php esc_html_e( 'products', 'woodmak-store' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
