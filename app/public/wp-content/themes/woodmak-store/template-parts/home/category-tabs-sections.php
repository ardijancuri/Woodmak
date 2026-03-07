<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ranked_categories = ws_get_ranked_product_categories();
if ( empty( $ranked_categories ) ) {
	return;
}

$section_a_categories = ws_select_product_categories( $ranked_categories, 10, 5 );
$section_a_ids        = wp_list_pluck( $section_a_categories, 'term_id' );
$section_b_categories = ws_select_product_categories( $ranked_categories, 15, 5, $section_a_ids );

$sections = array(
	array(
		'id'         => 'ws-home-category-tabs-a',
		'title'      => ws_get_theme_mod_text( 'ws_home_category_tabs_section_a_title', __( 'Category Highlights', 'woodmak-store' ) ),
		'categories' => $section_a_categories,
	),
	array(
		'id'         => 'ws-home-category-tabs-b',
		'title'      => ws_get_theme_mod_text( 'ws_home_category_tabs_section_b_title', __( 'More Category Picks', 'woodmak-store' ) ),
		'categories' => $section_b_categories,
	),
);

foreach ( $sections as $section ) :
	$categories = $section['categories'];
	if ( empty( $categories ) ) {
		continue;
	}

	$products_by_tab = array();
	$has_products    = false;

	foreach ( $categories as $category ) {
		if ( ! $category instanceof WP_Term ) {
			continue;
		}

		$products = ws_get_home_category_products( $category, 5 );
		if ( ! empty( $products ) ) {
			$has_products = true;
		}
		$products_by_tab[ absint( $category->term_id ) ] = $products;
	}

	if ( ! $has_products ) {
		continue;
	}
	?>
	<section class="ws-home-section ws-home-section--product-tabs ws-home-section--category-tabs">
		<div class="ws-container">
			<div class="ws-section-heading">
				<h2><?php echo esc_html( $section['title'] ); ?></h2>
			</div>
			<div class="ws-product-tabs" data-ws-product-tabs>
				<div class="ws-product-tabs__head" role="tablist" aria-label="<?php esc_attr_e( 'Homepage category tabs', 'woodmak-store' ); ?>">
					<?php $is_first = true; ?>
					<?php foreach ( $categories as $category ) : ?>
						<?php if ( ! $category instanceof WP_Term ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php
						$tab_id = $section['id'] . '-tab-' . absint( $category->term_id );
						?>
						<button
							type="button"
							class="ws-product-tabs__tab<?php echo $is_first ? ' is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr( $tab_id ); ?>"
							data-ws-tab-trigger="<?php echo esc_attr( $tab_id ); ?>"
						>
							<?php echo esc_html( $category->name ); ?>
						</button>
						<?php $is_first = false; ?>
					<?php endforeach; ?>
				</div>

				<?php $is_first = true; ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php if ( ! $category instanceof WP_Term ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php
					$tab_id = $section['id'] . '-tab-' . absint( $category->term_id );
					?>
					<div
						id="<?php echo esc_attr( $tab_id ); ?>"
						class="ws-product-tabs__panel<?php echo $is_first ? ' is-active' : ''; ?>"
						role="tabpanel"
						<?php if ( ! $is_first ) : ?>hidden<?php endif; ?>
						data-ws-tab-panel="<?php echo esc_attr( $tab_id ); ?>"
					>
						<?php ws_render_home_products( $products_by_tab[ absint( $category->term_id ) ], 'ws-home-products--tabs' ); ?>
					</div>
					<?php $is_first = false; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endforeach; ?>
