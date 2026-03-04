<?php
/**
 * WooCommerce product archive.
 *
 * @package woodmak-store
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

if ( function_exists( 'woocommerce_breadcrumb' ) ) {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

if ( class_exists( 'WM_Catalog_Filters' ) ) {
	remove_action( 'woocommerce_before_shop_loop', array( 'WM_Catalog_Filters', 'render_filters_form' ), 5 );
}

do_action( 'woocommerce_before_main_content' );
?>
<section class="ws-shop-hero">
	<div class="ws-container">
		<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
			<?php woocommerce_breadcrumb(); ?>
		<?php endif; ?>
		<h1><?php woocommerce_page_title(); ?></h1>
		<?php do_action( 'woocommerce_archive_description' ); ?>
	</div>
</section>

<div class="ws-container ws-shop-layout">
	<aside class="ws-shop-sidebar">
		<?php if ( class_exists( 'WM_Catalog_Filters' ) ) : ?>
			<?php WM_Catalog_Filters::render_filters_form(); ?>
		<?php endif; ?>
	</aside>

	<section class="ws-shop-content">
		<?php if ( woocommerce_product_loop() ) : ?>
			<?php do_action( 'woocommerce_before_shop_loop' ); ?>
			<div class="wm-shop-results-wrap">
				<?php woocommerce_result_count(); ?>
				<?php woocommerce_catalog_ordering(); ?>
			</div>
			<div id="wm-products-loop">
				<?php woocommerce_product_loop_start(); ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php wc_get_template_part( 'content', 'product' ); ?>
				<?php endwhile; ?>
				<?php woocommerce_product_loop_end(); ?>
			</div>
			<?php do_action( 'woocommerce_after_shop_loop' ); ?>
		<?php else : ?>
			<?php do_action( 'woocommerce_no_products_found' ); ?>
		<?php endif; ?>
	</section>
</div>

<?php
do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );
