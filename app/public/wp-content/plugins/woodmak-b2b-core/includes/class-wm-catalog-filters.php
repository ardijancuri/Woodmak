<?php
/**
 * Catalog filters and archive UI.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Catalog_Filters {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'pre_get_posts', array( __CLASS__, 'apply_filters_to_main_query' ), 30 );
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'render_filters_form' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue filter assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		if ( ! ( is_shop() || is_product_category() ) ) {
			return;
		}

		$script_path = WM_B2B_CORE_PATH . 'assets/js/catalog-filters.js';
		$script_ver  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : WM_B2B_CORE_VERSION;
		wp_enqueue_script( 'wm-catalog-filters', WM_B2B_CORE_URL . 'assets/js/catalog-filters.js', array(), $script_ver, true );
		wp_localize_script(
			'wm-catalog-filters',
			'wmCatalogFilters',
			array(
				'restUrl'           => esc_url_raw( rest_url( 'woodmak/v1/catalog' ) ),
				'mobileCollapseMax' => 920,
				'mobilePaginationMax' => 760,
			)
		);
	}

	/**
	 * Apply filters to archive main query.
	 *
	 * @param WP_Query $query Query.
	 * @return void
	 */
	public static function apply_filters_to_main_query( $query ) {
		if ( ! $query instanceof WP_Query || ! $query->is_main_query() ) {
			return;
		}

		if ( ! ( $query->is_post_type_archive( 'product' ) || $query->is_tax( 'product_cat' ) ) ) {
			return;
		}

		$parts = self::build_query_parts_from_params( wp_unslash( $_GET ) );
		if ( ! empty( $parts['meta_query'] ) ) {
			$meta_query = WM_Utils::merge_query_clauses( (array) $query->get( 'meta_query' ), $parts['meta_query'] );
			$query->set( 'meta_query', $meta_query );
		}

		if ( ! empty( $parts['tax_query'] ) ) {
			$tax_query = WM_Utils::merge_query_clauses( (array) $query->get( 'tax_query' ), $parts['tax_query'] );
			$query->set( 'tax_query', $tax_query );
		}
	}

	/**
	 * Build query constraints from URL params.
	 *
	 * @param array $params Params.
	 * @return array
	 */
	public static function build_query_parts_from_params( $params ) {
		$meta_query = array();
		$tax_query  = array();

		$wm_cat = self::get_csv_param( $params, 'wm_cat' );
		if ( ! empty( $wm_cat ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $wm_cat,
			);
		}

		$wm_color = self::get_csv_param( $params, 'wm_color' );
		if ( ! empty( $wm_color ) && taxonomy_exists( 'pa_color' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'pa_color',
				'field'    => 'slug',
				'terms'    => $wm_color,
			);
		}

		list( $price_min, $price_max )   = self::normalize_range_pair( self::get_float_param( $params, 'wm_price_min' ), self::get_float_param( $params, 'wm_price_max' ) );
		list( $width_min, $width_max )   = self::normalize_range_pair( self::get_float_param( $params, 'wm_width_min' ), self::get_float_param( $params, 'wm_width_max' ) );
		list( $height_min, $height_max ) = self::normalize_range_pair( self::get_float_param( $params, 'wm_height_min' ), self::get_float_param( $params, 'wm_height_max' ) );
		list( $depth_min, $depth_max )   = self::normalize_range_pair( self::get_float_param( $params, 'wm_depth_min' ), self::get_float_param( $params, 'wm_depth_max' ) );
		list( $weight_min, $weight_max ) = self::normalize_range_pair( self::get_float_param( $params, 'wm_weight_min' ), self::get_float_param( $params, 'wm_weight_max' ) );

		self::append_range( $meta_query, '_price', $price_min, $price_max );
		self::append_range( $meta_query, '_width', $width_min, $width_max );
		self::append_range( $meta_query, '_height', $height_min, $height_max );
		self::append_range( $meta_query, '_length', $depth_min, $depth_max );
		self::append_range( $meta_query, '_weight', $weight_min, $weight_max );

		return array(
			'meta_query' => $meta_query,
			'tax_query'  => $tax_query,
		);
	}

	/**
	 * Render archive filter form.
	 *
	 * @return void
	 */
	public static function render_filters_form() {
		if ( ! ( is_shop() || is_product_category() ) ) {
			return;
		}

		$current = wp_unslash( $_GET );
		$selected_categories = self::get_csv_param( $current, 'wm_cat' );
		$has_category_param  = ! empty( $selected_categories );
		if ( empty( $selected_categories ) && is_product_category() ) {
			$queried = get_queried_object();
			if ( $queried instanceof WP_Term ) {
				$selected_categories = array( sanitize_title( $queried->slug ) );
			}
		}
		$selected_colors = self::get_csv_param( $current, 'wm_color' );
		$terms   = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
			)
		);
		$colors  = taxonomy_exists( 'pa_color' ) ? get_terms(
			array(
				'taxonomy'   => 'pa_color',
				'hide_empty' => true,
			)
		) : array();
		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}
		if ( is_wp_error( $colors ) ) {
			$colors = array();
		}
		$current_link_params = self::get_preserved_filter_params( $current, array( 'wm_cat', 'paged', 'product-page' ) );

		$reset_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		if ( is_product_category() ) {
			$queried_term = get_queried_object();
			if ( $queried_term instanceof WP_Term ) {
				$term_link = get_term_link( $queried_term );
				if ( ! is_wp_error( $term_link ) ) {
					$reset_url = $term_link;
				}
			}
		}
		?>
		<form class="wm-catalog-filters" method="get" data-wm-catalog-filters>
			<?php if ( $has_category_param ) : ?>
				<?php foreach ( $selected_categories as $selected_category ) : ?>
					<input type="hidden" name="wm_cat[]" value="<?php echo esc_attr( $selected_category ); ?>" />
				<?php endforeach; ?>
			<?php endif; ?>
			<div class="wm-filter-grid">
				<div class="wm-filter-section" data-wm-filter-section>
					<button class="wm-filter-section__head" type="button" data-wm-filter-toggle aria-expanded="true" aria-controls="wm-filter-cat">
						<span><?php esc_html_e( 'Category', 'woodmak-b2b-core' ); ?></span>
						<span class="wm-filter-section__chevron" aria-hidden="true"></span>
					</button>
					<div id="wm-filter-cat" class="wm-filter-section__body" data-wm-filter-body>
						<div class="wm-filter-link-list">
							<?php foreach ( $terms as $term ) : ?>
								<?php
								$term_slug  = sanitize_title( $term->slug );
								$term_link  = get_term_link( $term );
								$is_active  = in_array( $term_slug, $selected_categories, true );
								if ( is_wp_error( $term_link ) ) {
									continue;
								}
								$link_url = self::build_filter_url( $term_link, $current_link_params );
								?>
								<a class="wm-filter-link<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $link_url ); ?>" data-wm-category-link data-wm-category-base-url="<?php echo esc_url( $term_link ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
									<?php echo esc_html( $term->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="wm-filter-section" data-wm-filter-section>
					<button class="wm-filter-section__head" type="button" data-wm-filter-toggle aria-expanded="true" aria-controls="wm-filter-color">
						<span><?php esc_html_e( 'Color', 'woodmak-b2b-core' ); ?></span>
						<span class="wm-filter-section__chevron" aria-hidden="true"></span>
					</button>
					<div id="wm-filter-color" class="wm-filter-section__body" data-wm-filter-body>
						<div class="wm-filter-checkbox-list">
							<?php foreach ( $colors as $color ) : ?>
								<?php $color_slug = sanitize_title( $color->slug ); ?>
								<label class="wm-filter-check">
									<input type="checkbox" name="wm_color[]" value="<?php echo esc_attr( $color_slug ); ?>" <?php checked( in_array( $color_slug, $selected_colors, true ) ); ?> />
									<span><?php echo esc_html( $color->name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="wm-filter-section" data-wm-filter-section>
					<button class="wm-filter-section__head" type="button" data-wm-filter-toggle aria-expanded="true" aria-controls="wm-filter-price">
						<span><?php esc_html_e( 'Price', 'woodmak-b2b-core' ); ?></span>
						<span class="wm-filter-section__chevron" aria-hidden="true"></span>
					</button>
					<div id="wm-filter-price" class="wm-filter-section__body" data-wm-filter-body>
						<div class="wm-filter-range">
							<input type="number" step="0.01" min="0" name="wm_price_min" placeholder="<?php echo esc_attr_x( 'Min', 'price range minimum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_price_min' ) ); ?>" />
							<input type="number" step="0.01" min="0" name="wm_price_max" placeholder="<?php echo esc_attr_x( 'Max', 'price range maximum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_price_max' ) ); ?>" />
						</div>
					</div>
				</div>

				<div class="wm-filter-section is-collapsed" data-wm-filter-section>
					<button class="wm-filter-section__head" type="button" data-wm-filter-toggle aria-expanded="false" aria-controls="wm-filter-dimensions">
						<span><?php esc_html_e( 'Dimensions', 'woodmak-b2b-core' ); ?></span>
						<span class="wm-filter-section__chevron" aria-hidden="true"></span>
					</button>
					<div id="wm-filter-dimensions" class="wm-filter-section__body" data-wm-filter-body hidden>
						<div>
							<label for="wm_width_min"><?php esc_html_e( 'Width', 'woodmak-b2b-core' ); ?></label>
							<div class="wm-filter-range">
								<input id="wm_width_min" type="number" step="0.01" min="0" name="wm_width_min" placeholder="<?php echo esc_attr_x( 'Min', 'width minimum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_width_min' ) ); ?>" />
								<input type="number" step="0.01" min="0" name="wm_width_max" placeholder="<?php echo esc_attr_x( 'Max', 'width maximum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_width_max' ) ); ?>" />
							</div>
						</div>
						<div>
							<label for="wm_height_min"><?php esc_html_e( 'Height', 'woodmak-b2b-core' ); ?></label>
							<div class="wm-filter-range">
								<input id="wm_height_min" type="number" step="0.01" min="0" name="wm_height_min" placeholder="<?php echo esc_attr_x( 'Min', 'height minimum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_height_min' ) ); ?>" />
								<input type="number" step="0.01" min="0" name="wm_height_max" placeholder="<?php echo esc_attr_x( 'Max', 'height maximum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_height_max' ) ); ?>" />
							</div>
						</div>
						<div>
							<label for="wm_depth_min"><?php esc_html_e( 'Depth', 'woodmak-b2b-core' ); ?></label>
							<div class="wm-filter-range">
								<input id="wm_depth_min" type="number" step="0.01" min="0" name="wm_depth_min" placeholder="<?php echo esc_attr_x( 'Min', 'depth minimum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_depth_min' ) ); ?>" />
								<input type="number" step="0.01" min="0" name="wm_depth_max" placeholder="<?php echo esc_attr_x( 'Max', 'depth maximum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_depth_max' ) ); ?>" />
							</div>
						</div>
					</div>
				</div>

				<div class="wm-filter-section is-collapsed" data-wm-filter-section>
					<button class="wm-filter-section__head" type="button" data-wm-filter-toggle aria-expanded="false" aria-controls="wm-filter-weight">
						<span><?php esc_html_e( 'Weight', 'woodmak-b2b-core' ); ?></span>
						<span class="wm-filter-section__chevron" aria-hidden="true"></span>
					</button>
					<div id="wm-filter-weight" class="wm-filter-section__body" data-wm-filter-body hidden>
						<div class="wm-filter-range">
							<input type="number" step="0.01" min="0" name="wm_weight_min" placeholder="<?php echo esc_attr_x( 'Min', 'weight minimum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_weight_min' ) ); ?>" />
							<input type="number" step="0.01" min="0" name="wm_weight_max" placeholder="<?php echo esc_attr_x( 'Max', 'weight maximum', 'woodmak-b2b-core' ); ?>" value="<?php echo esc_attr( self::get_scalar_param( $current, 'wm_weight_max' ) ); ?>" />
						</div>
					</div>
				</div>

				<div class="wm-filter-actions">
					<button type="submit" class="button"><?php esc_html_e( 'Apply', 'woodmak-b2b-core' ); ?></button>
					<a href="<?php echo esc_url( $reset_url ); ?>" class="button button-secondary"><?php esc_html_e( 'Reset', 'woodmak-b2b-core' ); ?></a>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Append numeric range clause.
	 *
	 * @param array  $meta_query Meta query.
	 * @param string $key Meta key.
	 * @param float  $min Minimum.
	 * @param float  $max Maximum.
	 * @return void
	 */
	private static function append_range( &$meta_query, $key, $min, $max ) {
		if ( null !== $min ) {
			$meta_query[] = array(
				'key'     => $key,
				'value'   => $min,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		}
		if ( null !== $max ) {
			$meta_query[] = array(
				'key'     => $key,
				'value'   => $max,
				'compare' => '<=',
				'type'    => 'NUMERIC',
			);
		}
	}

	/**
	 * Build a query-preserving filter URL.
	 *
	 * @param string $base_url Base URL.
	 * @param array  $params Query params.
	 * @return string
	 */
	private static function build_filter_url( $base_url, $params = array() ) {
		$base_url = esc_url_raw( (string) $base_url );
		if ( '' === $base_url || empty( $params ) || ! is_array( $params ) ) {
			return $base_url;
		}

		return add_query_arg( $params, $base_url );
	}

	/**
	 * Keep current filter params while excluding specific keys.
	 *
	 * @param array $params Current params.
	 * @param array $exclude_keys Keys to exclude.
	 * @return array
	 */
	private static function get_preserved_filter_params( $params, $exclude_keys = array() ) {
		$params       = is_array( $params ) ? $params : array();
		$exclude_keys = array_map( 'strval', (array) $exclude_keys );
		$preserved    = array();

		foreach ( $params as $key => $value ) {
			$key = (string) $key;
			if ( in_array( $key, $exclude_keys, true ) || 0 === strpos( $key, 'wm_cat' ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$items = array();
				foreach ( $value as $item ) {
					if ( ! is_scalar( $item ) ) {
						continue;
					}

					$item = sanitize_text_field( (string) wp_unslash( $item ) );
					if ( '' !== $item ) {
						$items[] = $item;
					}
				}

				if ( ! empty( $items ) ) {
					$preserved[ $key ] = array_values( $items );
				}

				continue;
			}

			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$value = sanitize_text_field( (string) wp_unslash( $value ) );
			if ( '' !== $value ) {
				$preserved[ $key ] = $value;
			}
		}

		return $preserved;
	}

	/**
	 * Get scalar param.
	 *
	 * @param array  $params Params.
	 * @param string $key Key.
	 * @return string
	 */
	private static function get_scalar_param( $params, $key ) {
		if ( ! isset( $params[ $key ] ) ) {
			return '';
		}
		$value = $params[ $key ];
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		return sanitize_text_field( (string) wp_unslash( $value ) );
	}

	/**
	 * Get CSV param values.
	 *
	 * @param array  $params Params.
	 * @param string $key Key.
	 * @return array
	 */
	private static function get_csv_param( $params, $key ) {
		$raw = '';
		if ( isset( $params[ $key ] ) ) {
			$raw = $params[ $key ];
		} elseif ( isset( $params[ $key . '[]' ] ) ) {
			$raw = $params[ $key . '[]' ];
		}
		if ( is_array( $raw ) ) {
			$items = array();
			foreach ( $raw as $value ) {
				if ( is_scalar( $value ) ) {
					$sanitized = sanitize_title( (string) wp_unslash( $value ) );
					if ( '' !== $sanitized ) {
						$items[] = $sanitized;
					}
				}
			}
			return array_values( array_unique( $items ) );
		}
		$raw = self::get_scalar_param( $params, $key );
		if ( '' === $raw ) {
			return array();
		}
		$items = array_map( 'sanitize_title', explode( ',', $raw ) );
		return array_values( array_filter( $items ) );
	}

	/**
	 * Get float param.
	 *
	 * @param array  $params Params.
	 * @param string $key Key.
	 * @return float|null
	 */
	private static function get_float_param( $params, $key ) {
		$value = self::get_scalar_param( $params, $key );
		if ( '' === $value ) {
			return null;
		}
		if ( ! is_numeric( $value ) ) {
			return null;
		}
		$float_value = (float) $value;
		if ( $float_value < 0 ) {
			return null;
		}
		return $float_value;
	}

	/**
	 * Normalize min/max pair so min is always lower or equal.
	 *
	 * @param float|null $min Min value.
	 * @param float|null $max Max value.
	 * @return array
	 */
	private static function normalize_range_pair( $min, $max ) {
		if ( null !== $min && null !== $max && $min > $max ) {
			return array( $max, $min );
		}
		return array( $min, $max );
	}
}
