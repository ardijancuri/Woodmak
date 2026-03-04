<?php
/**
 * Visibility controls for B2B-only products.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_B2B_Visibility {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'pre_get_posts', array( __CLASS__, 'hide_b2b_only_from_queries' ), 20 );
		add_filter( 'woocommerce_product_query_meta_query', array( __CLASS__, 'filter_product_query_meta_query' ), 20, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'guard_single_product_access' ), 1 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'guard_add_to_cart' ), 20, 6 );
		add_filter( 'woocommerce_related_products', array( __CLASS__, 'filter_product_ids' ), 20 );
		add_filter( 'woocommerce_product_get_upsell_ids', array( __CLASS__, 'filter_product_ids' ), 20 );
		add_filter( 'woocommerce_cart_crosssell_ids', array( __CLASS__, 'filter_product_ids' ), 20 );
	}

	/**
	 * Hide b2b-only products from main loops.
	 *
	 * @param WP_Query $query Query.
	 * @return void
	 */
	public static function hide_b2b_only_from_queries( $query ) {
		if ( WM_Utils::current_user_can_view_b2b_only() ) {
			return;
		}

		if ( ! $query instanceof WP_Query || ! $query->is_main_query() ) {
			return;
		}

		$post_type         = $query->get( 'post_type' );
		$is_product_search = $query->is_search() && ( 'product' === $post_type || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) ) );
		if ( ! ( $query->is_post_type_archive( 'product' ) || $query->is_tax( 'product_cat' ) || $query->is_tax( 'product_tag' ) || $is_product_search ) ) {
			return;
		}

		$meta_query = WM_Utils::merge_query_clauses( (array) $query->get( 'meta_query' ), array( self::b2b_only_exclusion_clause() ) );
		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Apply exclusion to Woo product query.
	 *
	 * @param array            $meta_query Meta query.
	 * @param WC_Product_Query $query Woo query.
	 * @return array
	 */
	public static function filter_product_query_meta_query( $meta_query, $query ) {
		if ( WM_Utils::current_user_can_view_b2b_only() ) {
			return $meta_query;
		}
		return WM_Utils::merge_query_clauses( (array) $meta_query, array( self::b2b_only_exclusion_clause() ) );
	}

	/**
	 * Restrict direct access.
	 *
	 * @return void
	 */
	public static function guard_single_product_access() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		$product_id = get_queried_object_id();
		if ( ! WM_Utils::is_product_b2b_only( $product_id ) || WM_Utils::current_user_can_view_b2b_only() ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			$login_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
			$target = add_query_arg(
				array(
					'wm_notice'   => 'b2b_login_required',
					'redirect_to' => get_permalink( $product_id ),
				),
				$login_url
			);
			wp_safe_redirect( $target );
			exit;
		}

		$target = add_query_arg( 'wm_notice', 'b2b_approval_required', home_url( '/b2b-request/' ) );
		wp_safe_redirect( $target );
		exit;
	}

	/**
	 * Restrict add to cart for unauthorized users.
	 *
	 * @param bool $passed Passed.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity Quantity.
	 * @param int  $variation_id Variation ID.
	 * @param array $variations Variation values.
	 * @param array $cart_item_data Cart item data.
	 * @return bool
	 */
	public static function guard_add_to_cart( $passed, $product_id, $quantity, $variation_id = 0, $variations = array(), $cart_item_data = array() ) {
		$target_product_id     = $variation_id ? $variation_id : $product_id;
		$restricted_product_id = self::resolve_visibility_product_id( $target_product_id );
		if ( WM_Utils::current_user_can_view_b2b_only() || ! WM_Utils::is_product_b2b_only( $restricted_product_id ) ) {
			return $passed;
		}

		wc_add_notice( __( 'This product is available only for approved B2B accounts.', 'woodmak-b2b-core' ), 'error' );
		return false;
	}

	/**
	 * Filter product IDs arrays.
	 *
	 * @param array $ids Product IDs.
	 * @return array
	 */
	public static function filter_product_ids( $ids ) {
		if ( WM_Utils::current_user_can_view_b2b_only() || empty( $ids ) || ! is_array( $ids ) ) {
			return $ids;
		}

		return array_values(
			array_filter(
				array_map( 'absint', $ids ),
				static function ( $id ) {
					return ! WM_Utils::is_product_b2b_only( $id );
				}
			)
		);
	}

	/**
	 * Meta query clause excluding B2B-only products.
	 *
	 * @return array
	 */
	private static function b2b_only_exclusion_clause() {
		return array(
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
		);
	}

	/**
	 * Resolve variation-to-parent ID for visibility checks.
	 *
	 * @param int $product_id Product or variation ID.
	 * @return int
	 */
	private static function resolve_visibility_product_id( $product_id ) {
		$product = wc_get_product( absint( $product_id ) );
		if ( ! $product ) {
			return absint( $product_id );
		}

		if ( $product->is_type( 'variation' ) ) {
			return absint( $product->get_parent_id() );
		}

		return absint( $product->get_id() );
	}
}
