<?php
/**
 * Product card template.
 *
 * @package woodmak-store
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$category_name = '';
$terms         = get_the_terms( get_the_ID(), 'product_cat' );
if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
	$first_term = reset( $terms );
	if ( $first_term instanceof WP_Term ) {
		$category_name = $first_term->name;
	}
}
?>
<li <?php wc_product_class( 'ws-product-card', $product ); ?>>
	<a href="<?php the_permalink(); ?>" class="ws-product-card__image">
		<?php echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
	<div class="ws-product-card__body">
		<?php if ( '' !== $category_name ) : ?>
			<p class="ws-product-card__category"><?php echo esc_html( $category_name ); ?></p>
		<?php endif; ?>
		<h2 class="woocommerce-loop-product__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="ws-product-card__footer">
			<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			<?php woocommerce_template_loop_add_to_cart(); ?>
		</div>
	</div>
</li>
