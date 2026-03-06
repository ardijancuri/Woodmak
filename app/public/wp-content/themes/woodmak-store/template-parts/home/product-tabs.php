<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tabs = array(
	'new'         => __( 'New', 'woodmak-store' ),
	'recommended' => __( 'Recommended', 'woodmak-store' ),
	'bestsellers' => __( 'Bestsellers', 'woodmak-store' ),
);

$products_by_tab                 = array();
$products_by_tab['new']          = ws_get_home_tab_products( 'new', 5 );
$products_by_tab['recommended']  = ws_get_home_tab_products( 'recommended', 5 );
$recommended_ids                 = array_values(
	array_filter(
		array_map(
			static function ( $product ) {
				return $product instanceof WC_Product ? absint( $product->get_id() ) : 0;
			},
			$products_by_tab['recommended']
		)
	)
);
$products_by_tab['bestsellers']  = ws_get_home_tab_products( 'bestsellers', 5, $recommended_ids );

$has_products = false;
foreach ( $products_by_tab as $products ) {
	if ( ! empty( $products ) ) {
		$has_products = true;
		break;
	}
}

if ( ! $has_products ) {
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<section class="ws-home-section ws-home-section--product-tabs">
	<div class="ws-container">
		<div class="ws-product-tabs" data-ws-product-tabs>
			<div class="ws-product-tabs__head" role="tablist" aria-label="<?php esc_attr_e( 'Homepage product tabs', 'woodmak-store' ); ?>">
				<?php $is_first = true; ?>
				<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
					<button
						type="button"
						class="ws-product-tabs__tab<?php echo $is_first ? ' is-active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( 'ws-tab-panel-' . $tab_key ); ?>"
						data-ws-tab-trigger="<?php echo esc_attr( $tab_key ); ?>"
					>
						<?php echo esc_html( $tab_label ); ?>
					</button>
					<?php $is_first = false; ?>
				<?php endforeach; ?>
			</div>

			<?php $is_first = true; ?>
			<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
				<div
					id="<?php echo esc_attr( 'ws-tab-panel-' . $tab_key ); ?>"
					class="ws-product-tabs__panel<?php echo $is_first ? ' is-active' : ''; ?>"
					role="tabpanel"
					<?php if ( ! $is_first ) : ?>hidden<?php endif; ?>
					data-ws-tab-panel="<?php echo esc_attr( $tab_key ); ?>"
				>
					<?php ws_render_home_products( $products_by_tab[ $tab_key ], 'ws-home-products--tabs' ); ?>
				</div>
				<?php $is_first = false; ?>
			<?php endforeach; ?>
		</div>

		<p class="ws-home-section__cta">
			<a class="button alt ws-see-all-button" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'See All', 'woodmak-store' ); ?></a>
		</p>
	</div>
</section>
