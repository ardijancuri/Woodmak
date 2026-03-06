<?php
/**
 * Off-canvas cart sidebar with suggestions.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Cart_Sidebar {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 90 );
		add_action( 'wp_footer', array( __CLASS__, 'render_sidebar_shell' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'add_fragments' ) );
	}

	/**
	 * Enqueue assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$css_path = WM_B2B_CORE_PATH . 'assets/css/cart-sidebar.css';
		$js_path  = WM_B2B_CORE_PATH . 'assets/js/cart-sidebar.js';
		$css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : WM_B2B_CORE_VERSION;
		$js_ver   = file_exists( $js_path ) ? (string) filemtime( $js_path ) : WM_B2B_CORE_VERSION;
		$lang     = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';

		if ( ! in_array( $lang, array( 'mk', 'en' ), true ) ) {
			$locale = strtolower( (string) determine_locale() );
			$lang   = 0 === strpos( $locale, 'mk_' ) ? 'mk' : 'en';
		}

		wp_enqueue_style( 'wm-cart-sidebar', WM_B2B_CORE_URL . 'assets/css/cart-sidebar.css', array(), $css_ver );
		wp_enqueue_script( 'wm-cart-sidebar', WM_B2B_CORE_URL . 'assets/js/cart-sidebar.js', array( 'jquery', 'wc-add-to-cart' ), $js_ver, true );
		wp_localize_script(
			'wm-cart-sidebar',
			'wmCartSidebar',
			array(
				'restUrl' => esc_url_raw( rest_url( 'woodmak/v1/cart-sidebar' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'lang'    => $lang,
			)
		);
	}

	/**
	 * Render sidebar shell.
	 *
	 * @return void
	 */
	public static function render_sidebar_shell() {
		if ( is_admin() ) {
			return;
		}
		?>
		<aside id="wm-cart-sidebar" class="wm-cart-sidebar" aria-hidden="true">
			<button class="wm-cart-sidebar__close" type="button" data-wm-cart-close aria-label="<?php esc_attr_e( 'Close cart sidebar', 'woodmak-b2b-core' ); ?>">
				<span class="wm-cart-sidebar__close-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
						<path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
						<path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
					</svg>
				</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Close cart sidebar', 'woodmak-b2b-core' ); ?></span>
			</button>
			<?php self::render_sidebar_inner_wrapper(); ?>
		</aside>
		<div id="wm-cart-sidebar-overlay" class="wm-cart-sidebar__overlay" data-wm-cart-close></div>
		<?php
	}

	/**
	 * Add fragment for cart updates.
	 *
	 * @param array $fragments Fragments.
	 * @return array
	 */
	public static function add_fragments( $fragments ) {
		ob_start();
		self::render_sidebar_inner_wrapper();
		$fragments['#wm-cart-sidebar-inner'] = ob_get_clean();
		return $fragments;
	}

	/**
	 * Get payload for REST refresh.
	 *
	 * @return array
	 */
	public static function get_sidebar_payload() {
		ob_start();
		self::render_sidebar_inner_wrapper();
		return array(
			'html' => ob_get_clean(),
		);
	}

	/**
	 * Render sidebar inner wrapper.
	 *
	 * @return void
	 */
	private static function render_sidebar_inner_wrapper() {
		echo '<div id="wm-cart-sidebar-inner">';
		self::render_sidebar_inner();
		echo '</div>';
	}

	/**
	 * Render sidebar contents.
	 *
	 * @return void
	 */
	public static function render_sidebar_inner() {
		if ( ! WC()->cart ) {
			return;
		}

		$item_count  = (int) WC()->cart->get_cart_contents_count();
		$count_label = sprintf(
			/* translators: %d cart item count. */
			_n( '%d item', '%d items', $item_count, 'woodmak-b2b-core' ),
			$item_count
		);

		echo '<div class="wm-cart-sidebar__panel">';
		echo '<div class="wm-cart-sidebar__heading">';
		echo '<h3>' . esc_html__( 'Your cart', 'woodmak-b2b-core' ) . '</h3>';
		echo '<span class="wm-cart-sidebar__count">' . esc_html( $count_label ) . '</span>';
		echo '</div>';

		if ( WC()->cart->is_empty() ) {
			$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

			echo '<div class="wm-cart-sidebar__empty">';
			echo '<p>' . esc_html__( 'Your cart is currently empty.', 'woodmak-b2b-core' ) . '</p>';
			echo '<p><a class="button alt wm-cart-sidebar__continue" href="' . esc_url( $shop_url ) . '">' . esc_html__( 'Continue shopping', 'woodmak-b2b-core' ) . '</a></p>';
			echo '</div>';
			echo '</div>';
			return;
		}

		echo '<ul class="wm-cart-sidebar__items">';
		foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
			if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
				continue;
			}

			$product    = $item['data'];
			$quantity   = max( 1, absint( $item['quantity'] ) );
			$line_total = WC()->cart->get_product_subtotal( $product, $quantity );
			$permalink  = $product->is_visible() ? $product->get_permalink( $item ) : '';
			$thumbnail  = $product->get_image( 'woocommerce_thumbnail' );
			$remove_url = wc_get_cart_remove_url( $cart_item_key );
			$remove_label = sprintf(
				/* translators: %s: product name. */
				__( 'Remove %s from cart', 'woodmak-b2b-core' ),
				$product->get_name()
			);

			echo '<li class="wm-cart-sidebar__item">';
			if ( $permalink ) {
				echo '<a class="wm-cart-sidebar__item-image" href="' . esc_url( $permalink ) . '">' . wp_kses_post( $thumbnail ) . '</a>';
			} else {
				echo '<span class="wm-cart-sidebar__item-image">' . wp_kses_post( $thumbnail ) . '</span>';
			}

			echo '<div class="wm-cart-sidebar__item-content">';
			echo '<div class="wm-cart-sidebar__item-head">';
				if ( $permalink ) {
					echo '<a class="wm-cart-sidebar__item-name" href="' . esc_url( $permalink ) . '">' . esc_html( $product->get_name() ) . '</a>';
				} else {
					echo '<span class="wm-cart-sidebar__item-name">' . esc_html( $product->get_name() ) . '</span>';
				}
				echo '<a role="button" href="' . esc_url( $remove_url ) . '" class="wm-cart-sidebar__item-remove remove_from_cart_button" aria-label="' . esc_attr( $remove_label ) . '" data-product_id="' . esc_attr( $product->get_id() ) . '" data-cart_item_key="' . esc_attr( $cart_item_key ) . '" data-product_sku="' . esc_attr( $product->get_sku() ) . '"><span class="wm-cart-sidebar__item-remove-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg></span></a>';
			echo '</div>';
			echo '<span class="wm-cart-sidebar__item-meta">' . esc_html( sprintf( __( 'Qty: %d', 'woodmak-b2b-core' ), $quantity ) ) . '</span>';
			echo '<span class="wm-cart-sidebar__item-price">' . wp_kses_post( $line_total ) . '</span>';
			echo '</div>';
			echo '</li>';
		}
		echo '</ul>';

		$discount_total = (float) WC()->cart->get_discount_total() + (float) WC()->cart->get_discount_tax();
		echo '<div class="wm-cart-sidebar__totals">';
		echo '<dl>';
		echo '<div><dt>' . esc_html__( 'Subtotal', 'woodmak-b2b-core' ) . '</dt><dd>' . wp_kses_post( WC()->cart->get_cart_subtotal() ) . '</dd></div>';
		if ( $discount_total > 0 ) {
			echo '<div><dt>' . esc_html__( 'Discount', 'woodmak-b2b-core' ) . '</dt><dd>-' . wp_kses_post( wc_price( $discount_total ) ) . '</dd></div>';
		}
		echo '<div class="wm-cart-sidebar__totals-final"><dt>' . esc_html__( 'Total', 'woodmak-b2b-core' ) . '</dt><dd>' . wp_kses_post( WC()->cart->get_total() ) . '</dd></div>';
		echo '</dl>';
		echo '</div>';
		echo '<div class="wm-cart-sidebar__actions">';
		echo '<a class="button wm-cart-sidebar__cart-button" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'View cart', 'woodmak-b2b-core' ) . '</a>';
		echo '<a class="button alt wm-cart-sidebar__checkout-button" href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Checkout', 'woodmak-b2b-core' ) . '</a>';
		echo '</div>';

		self::render_suggestions();
		echo '</div>';
	}

	/**
	 * Render similar product suggestions.
	 *
	 * @return void
	 */
	private static function render_suggestions() {
		$cart_product_ids = array();
		$cat_ids          = array();
		$color_ids        = array();

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( empty( $item['product_id'] ) ) {
				continue;
			}
			$product_id       = absint( $item['product_id'] );
			$cart_product_ids[] = $product_id;
			$cat_ids          = array_merge( $cat_ids, wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) ) );
			if ( taxonomy_exists( 'pa_color' ) ) {
				$color_ids = array_merge( $color_ids, wp_get_post_terms( $product_id, 'pa_color', array( 'fields' => 'ids' ) ) );
			}
		}

		$cat_ids   = array_values( array_unique( array_map( 'absint', $cat_ids ) ) );
		$color_ids = array_values( array_unique( array_map( 'absint', $color_ids ) ) );

		if ( empty( $cat_ids ) ) {
			return;
		}

		$base_args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'post__not_in'   => $cart_product_ids,
		);

		if ( ! WM_Utils::current_user_can_view_b2b_only() ) {
			$base_args['meta_query'] = array(
				array(
					'relation' => 'OR',
					array(
						'key'     => '_b2b_only',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_b2b_only',
						'value'   => 'yes',
						'compare' => '!=',
					),
				),
			);
		}

		$tax_query = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $cat_ids,
			),
		);
		if ( ! empty( $color_ids ) ) {
			$tax_query['relation'] = 'AND';
			$tax_query[]           = array(
				'taxonomy' => 'pa_color',
				'field'    => 'term_id',
				'terms'    => $color_ids,
				'operator' => 'IN',
			);
		}

		$query = new WP_Query(
			array_merge(
				$base_args,
				array(
					'tax_query' => $tax_query,
				)
			)
		);
		if ( ! $query->have_posts() && ! empty( $color_ids ) ) {
			wp_reset_postdata();
			$query = new WP_Query(
				array_merge(
					$base_args,
					array(
						'tax_query' => array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $cat_ids,
							),
						),
					)
				)
			);
		}

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		$slides_count = (int) $query->post_count;

		echo '<div class="wm-cart-sidebar__suggestions">';
		echo '<div class="wm-cart-sidebar__suggestions-head">';
		echo '<h4>' . esc_html__( 'You may also like', 'woodmak-b2b-core' ) . '</h4>';
		if ( $slides_count > 2 ) {
			echo '<div class="wm-cart-sidebar__suggestion-controls">';
			echo '<button type="button" class="wm-cart-sidebar__suggestion-nav" data-wm-suggest-prev aria-label="' . esc_attr__( 'Previous products', 'woodmak-b2b-core' ) . '">&#8249;</button>';
			echo '<button type="button" class="wm-cart-sidebar__suggestion-nav" data-wm-suggest-next aria-label="' . esc_attr__( 'Next products', 'woodmak-b2b-core' ) . '">&#8250;</button>';
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="wm-cart-sidebar__suggestion-track" data-wm-suggest-track>';
		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( ! $product ) {
				continue;
			}
			$permalink = get_permalink();
			echo '<article class="wm-cart-sidebar__suggestion-card">';
			echo '<a class="wm-cart-sidebar__suggestion-image" href="' . esc_url( $permalink ) . '">' . wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ) . '</a>';
			echo '<div class="wm-cart-sidebar__suggestion-body">';
			echo '<a class="wm-cart-sidebar__suggestion-title" href="' . esc_url( $permalink ) . '">' . esc_html( get_the_title() ) . '</a>';
			echo '<span class="wm-cart-sidebar__suggestion-price">' . wp_kses_post( $product->get_price_html() ) . '</span>';
			echo '</div>';
			echo '</article>';
		}
		echo '</div>';
		echo '</div>';

		wp_reset_postdata();
	}
}
