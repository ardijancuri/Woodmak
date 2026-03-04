<?php
/**
 * B2B pricing and discounts.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_B2B_Pricing {
	/**
	 * Internal coupon code used for automatic B2B global discounts.
	 *
	 * @var string
	 */
	private const AUTO_B2B_COUPON_CODE = 'wm-b2b-discount';

	/**
	 * Re-entrancy guard for coupon sync.
	 *
	 * @var bool
	 */
	private static $is_syncing_coupon = false;

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		$price_filters = array(
			'woocommerce_product_get_price',
			'woocommerce_product_get_regular_price',
			'woocommerce_product_get_sale_price',
			'woocommerce_product_variation_get_price',
			'woocommerce_product_variation_get_regular_price',
			'woocommerce_product_variation_get_sale_price',
			'woocommerce_variation_prices_price',
			'woocommerce_variation_prices_regular_price',
			'woocommerce_variation_prices_sale_price',
		);

		foreach ( $price_filters as $hook ) {
			add_filter( $hook, array( __CLASS__, 'filter_product_price' ), 20, 2 );
		}

		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_user_discount_to_cart' ), 20 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'sync_discount_coupon' ), 30 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'restore_cart_item_session_data' ), 20, 3 );
		add_filter( 'woocommerce_get_shop_coupon_data', array( __CLASS__, 'register_dynamic_discount_coupon' ), 10, 3 );
		add_filter( 'woocommerce_cart_totals_coupon_label', array( __CLASS__, 'filter_coupon_label' ), 20, 2 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'store_order_discount_meta' ), 20, 2 );
	}

	/**
	 * Filter product price for approved B2B users.
	 *
	 * @param string|float $price Original price.
	 * @param WC_Product   $product Product.
	 * @return string|float
	 */
	public static function filter_product_price( $price, $product ) {
		if ( ! WM_Utils::is_approved_b2b() ) {
			return $price;
		}

		if ( is_admin() && ! wp_doing_ajax() ) {
			return $price;
		}

		if ( ! $product instanceof WC_Product ) {
			return $price;
		}

		// During cart/checkout calculations, cart item runtime prices must not be overwritten.
		if ( self::is_cart_checkout_price_context() ) {
			return $price;
		}

		$b2b_price = self::get_b2b_price_for_product( $product );
		if ( null === $b2b_price ) {
			return $price;
		}

		return $b2b_price;
	}

	/**
	 * Apply product-level B2B base pricing to cart items.
	 *
	 * @param WC_Cart $cart Cart.
	 * @return void
	 */
	public static function apply_user_discount_to_cart( $cart ) {
		if ( ! $cart instanceof WC_Cart || is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$is_approved_b2b = WM_Utils::is_approved_b2b();

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( ! isset( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
				continue;
			}

			$base_price = max( 0, (float) $cart_item['data']->get_price( 'edit' ) );
			if ( $is_approved_b2b ) {
				$b2b_price = self::get_b2b_price_for_product( $cart_item['data'] );
				if ( null !== $b2b_price ) {
					$base_price = $b2b_price;
				}
			}

			$cart->cart_contents[ $cart_item_key ]['wm_effective_base_price'] = (float) $base_price;
			$cart_item['data']->set_price( (float) wc_format_decimal( $base_price ) );
		}
	}

	/**
	 * Keep cart custom values persisted between requests.
	 *
	 * @param array $cart_item Cart item.
	 * @param array $session_values Session values.
	 * @param string $cart_item_key Cart key.
	 * @return array
	 */
	public static function restore_cart_item_session_data( $cart_item, $session_values, $cart_item_key ) {
		if ( isset( $session_values['wm_effective_base_price'] ) ) {
			$cart_item['wm_effective_base_price'] = (float) $session_values['wm_effective_base_price'];
		}

		return $cart_item;
	}

	/**
	 * Provide runtime shop-coupon data for auto B2B discount coupon.
	 *
	 * @param array|false $coupon Coupon data.
	 * @param string      $coupon_code Requested coupon code.
	 * @param WC_Coupon   $coupon Coupon object.
	 * @return array|false
	 */
	public static function register_dynamic_discount_coupon( $coupon, $coupon_code, $coupon_object ) {
		if ( ! self::is_auto_b2b_coupon_code( $coupon_code ) ) {
			return $coupon;
		}

		$discount = self::get_active_discount_percent();
		if ( $discount <= 0 ) {
			return false;
		}

		return array(
			'id'                         => 0,
			'code'                       => self::get_auto_b2b_coupon_code(),
			'amount'                     => (string) $discount,
			'discount_type'              => 'percent',
			'individual_use'             => true,
			'product_ids'                => array(),
			'exclude_product_ids'        => array(),
			'usage_limit'                => 0,
			'usage_limit_per_user'       => 0,
			'limit_usage_to_x_items'     => null,
			'free_shipping'              => false,
			'product_categories'         => array(),
			'exclude_product_categories' => array(),
			'exclude_sale_items'         => false,
			'minimum_amount'             => '',
			'maximum_amount'             => '',
			'email_restrictions'         => array(),
			'virtual'                    => true,
		);
	}

	/**
	 * Replace coupon label in cart/checkout totals.
	 *
	 * @param string    $label Coupon label.
	 * @param WC_Coupon $coupon Coupon object.
	 * @return string
	 */
	public static function filter_coupon_label( $label, $coupon ) {
		if ( ! $coupon instanceof WC_Coupon || ! self::is_auto_b2b_coupon_code( $coupon->get_code() ) ) {
			return $label;
		}

		$discount = (int) $coupon->get_amount();
		if ( $discount <= 0 ) {
			$discount = self::get_active_discount_percent();
		}

		return sprintf( __( 'B2B discount (%d%%)', 'woodmak-b2b-core' ), max( 0, $discount ) );
	}

	/**
	 * Synchronize auto B2B coupon in the cart and enforce non-stacking behavior.
	 *
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public static function sync_discount_coupon( $cart ) {
		if ( self::$is_syncing_coupon || ! $cart instanceof WC_Cart || is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$discount    = self::get_active_discount_percent();
		$coupon_code = self::get_auto_b2b_coupon_code();
		self::$is_syncing_coupon = true;

		try {
			$has_auto_coupon = $cart->has_discount( $coupon_code );

			if ( $discount <= 0 ) {
				if ( $has_auto_coupon ) {
					$cart->remove_coupon( $coupon_code );
				}
				return;
			}

			self::remove_non_auto_coupons( $cart );

			if ( ! $has_auto_coupon ) {
				$cart->apply_coupon( $coupon_code );
			}
		} finally {
			self::$is_syncing_coupon = false;
		}
	}

	/**
	 * Remove all non-auto coupons so B2B discount cannot stack with promo coupons.
	 *
	 * @param WC_Cart $cart Cart.
	 * @return void
	 */
	private static function remove_non_auto_coupons( $cart ) {
		foreach ( $cart->get_applied_coupons() as $applied_coupon_code ) {
			if ( self::is_auto_b2b_coupon_code( $applied_coupon_code ) ) {
				continue;
			}
			$cart->remove_coupon( $applied_coupon_code );
		}
	}

	/**
	 * Store discount metadata on order.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data Checkout data.
	 * @return void
	 */
	public static function store_order_discount_meta( $order, $data ) {
		if ( ! $order instanceof WC_Order || ! WC()->cart ) {
			return;
		}

		$user_id = (int) $order->get_user_id();
		$is_b2b  = $user_id && WM_Utils::is_approved_b2b( $user_id );
		$order->update_meta_data( '_wm_is_b2b_order', $is_b2b ? 'yes' : 'no' );

		if ( $is_b2b ) {
			$company_name = isset( $data['billing_company'] ) ? sanitize_text_field( $data['billing_company'] ) : '';
			$company_vat  = isset( $data['billing_vat'] ) ? sanitize_text_field( $data['billing_vat'] ) : '';
			if ( '' === $company_name ) {
				$company_name = (string) get_user_meta( $user_id, '_company_name', true );
			}
			if ( '' === $company_vat ) {
				$company_vat = (string) get_user_meta( $user_id, '_company_vat', true );
			}
			$order->update_meta_data( '_wm_b2b_company_name', $company_name );
			$order->update_meta_data( '_wm_b2b_company_vat', $company_vat );
		}

		$discount_percent = $is_b2b ? WM_Utils::get_user_discount_percent( $user_id ) : 0;
		$discount_amount  = self::calculate_discount_amount_for_order( $order );

		$order->update_meta_data( '_wm_b2b_discount_percent', max( 0, $discount_percent ) );
		$order->update_meta_data( '_wm_b2b_discount_amount', wc_format_decimal( max( 0, $discount_amount ) ) );
	}

	/**
	 * Calculate discount amount from order totals.
	 *
	 * @param WC_Order $order Order object.
	 * @return float
	 */
	private static function calculate_discount_amount_for_order( $order ) {
		$discount_total = (float) $order->get_discount_total();
		$discount_tax   = (float) $order->get_discount_tax();

		return (float) wc_format_decimal( max( 0, $discount_total + $discount_tax ) );
	}

	/**
	 * Get active B2B user discount percent.
	 *
	 * @return int
	 */
	private static function get_active_discount_percent() {
		if ( ! WM_Utils::is_approved_b2b() ) {
			return 0;
		}

		return max( 0, (int) WM_Utils::get_user_discount_percent() );
	}

	/**
	 * Get normalized coupon code used for the auto B2B discount.
	 *
	 * @return string
	 */
	private static function get_auto_b2b_coupon_code() {
		return wc_format_coupon_code( self::AUTO_B2B_COUPON_CODE );
	}

	/**
	 * Check whether coupon code belongs to the automatic B2B discount.
	 *
	 * @param string $coupon_code Coupon code.
	 * @return bool
	 */
	private static function is_auto_b2b_coupon_code( $coupon_code ) {
		return self::get_auto_b2b_coupon_code() === wc_format_coupon_code( (string) $coupon_code );
	}

	/**
	 * Check if current request is cart/checkout price calculation context.
	 *
	 * @return bool
	 */
	private static function is_cart_checkout_price_context() {
		$before_calculate = did_action( 'woocommerce_before_calculate_totals' );
		$after_calculate  = did_action( 'woocommerce_after_calculate_totals' );
		if ( $before_calculate > $after_calculate ) {
			return true;
		}

		if ( ! wp_doing_ajax() || ! isset( $_REQUEST['wc-ajax'] ) ) {
			return false;
		}

		$ajax_action = sanitize_key( wp_unslash( $_REQUEST['wc-ajax'] ) );
		$cart_ajax_actions = array(
			'update_order_review',
			'get_refreshed_fragments',
			'apply_coupon',
			'remove_coupon',
			'update_shipping_method',
		);

		return in_array( $ajax_action, $cart_ajax_actions, true );
	}

	/**
	 * Resolve B2B price for simple/variation product.
	 *
	 * @param WC_Product $product Product.
	 * @return float|null
	 */
	private static function get_b2b_price_for_product( $product ) {
		$b2b_price = $product->get_meta( '_b2b_price', true );
		if ( '' === $b2b_price && $product->is_type( 'variation' ) ) {
			$parent = wc_get_product( $product->get_parent_id() );
			if ( $parent instanceof WC_Product ) {
				$b2b_price = $parent->get_meta( '_b2b_price', true );
			}
		}

		if ( '' === $b2b_price || ! is_numeric( $b2b_price ) ) {
			return null;
		}

		return (float) wc_format_decimal( max( 0, (float) $b2b_price ) );
	}
}
