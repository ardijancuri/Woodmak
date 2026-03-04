<?php
/**
 * REST endpoints for AJAX catalog and cart sidebar.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_REST {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register API routes.
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			'woodmak/v1',
			'/catalog',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'catalog_response' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'woodmak/v1',
			'/cart-sidebar',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'cart_sidebar_response' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Catalog endpoint response.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function catalog_response( WP_REST_Request $request ) {
		$paged = min( 100, max( 1, absint( $request->get_param( 'page' ) ) ) );

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'paged'          => $paged,
			'posts_per_page' => (int) get_option( 'posts_per_page', 12 ),
			'meta_query'     => array(),
			'tax_query'      => array(),
		);

		$parts = WM_Catalog_Filters::build_query_parts_from_params( $request->get_params() );
		if ( ! empty( $parts['meta_query'] ) ) {
			$args['meta_query'] = WM_Utils::merge_query_clauses( $args['meta_query'], $parts['meta_query'] );
		}
		if ( ! empty( $parts['tax_query'] ) ) {
			$args['tax_query'] = WM_Utils::merge_query_clauses( $args['tax_query'], $parts['tax_query'] );
		}

		if ( ! WM_Utils::current_user_can_view_b2b_only() ) {
			$args['meta_query'] = WM_Utils::merge_query_clauses(
				$args['meta_query'],
				array(
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
				)
			);
		}

		$query = new WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) {
			woocommerce_product_loop_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			woocommerce_product_loop_end();
		} else {
			do_action( 'woocommerce_no_products_found' );
		}
		$products_html = ob_get_clean();

		$pagination = paginate_links(
			array(
				'current'   => $paged,
				'total'     => max( 1, (int) $query->max_num_pages ),
				'type'      => 'list',
				'prev_text' => __( 'Previous', 'woodmak-b2b-core' ),
				'next_text' => __( 'Next', 'woodmak-b2b-core' ),
			)
		);

		$result_count = sprintf(
			/* translators: %d: number of products. */
			_n( '%d product found', '%d products found', (int) $query->found_posts, 'woodmak-b2b-core' ),
			(int) $query->found_posts
		);

		wp_reset_postdata();

		return rest_ensure_response(
			array(
				'products_html'   => $products_html,
				'pagination_html' => $pagination,
				'result_count'    => $result_count,
			)
		);
	}

	/**
	 * Cart sidebar endpoint.
	 *
	 * @return WP_REST_Response
	 */
	public static function cart_sidebar_response() {
		self::ensure_woocommerce_cart_loaded();
		return rest_ensure_response( WM_Cart_Sidebar::get_sidebar_payload() );
	}

	/**
	 * Ensure WooCommerce cart/session context is available in REST requests.
	 *
	 * @return void
	 */
	private static function ensure_woocommerce_cart_loaded() {
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return;
		}

		if ( null === WC()->session && method_exists( WC(), 'initialize_session' ) ) {
			WC()->initialize_session();
		}

		if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}
	}
}
