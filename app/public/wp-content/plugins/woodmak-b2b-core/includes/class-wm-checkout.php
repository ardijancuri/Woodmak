<?php
/**
 * Checkout UX and role-aware behavior.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Checkout {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'body_class', array( __CLASS__, 'add_role_body_classes' ) );
		add_action( 'wp', array( __CLASS__, 'handle_frontend_notices' ) );
		add_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'customize_default_address_fields' ) );
		add_filter( 'woocommerce_billing_fields', array( __CLASS__, 'customize_billing_fields' ) );
		add_filter( 'woocommerce_shipping_fields', array( __CLASS__, 'customize_shipping_fields' ) );
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'customize_checkout_fields' ) );
		add_filter( 'woocommerce_form_field_args', array( __CLASS__, 'force_hidden_state_fields' ), 20, 3 );
		add_filter( 'woocommerce_checkout_get_value', array( __CLASS__, 'prefill_checkout_values' ), 10, 2 );
		add_action( 'woocommerce_checkout_process', array( __CLASS__, 'validate_b2b_checkout_fields' ) );
		add_action( 'woocommerce_checkout_update_user_meta', array( __CLASS__, 'persist_b2b_profile_fields' ), 20, 2 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_vat_to_order' ), 30, 2 );
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'render_role_checkout_message' ), 5 );
	}

	/**
	 * Remove state from generic WooCommerce address defaults.
	 *
	 * @param array $fields Default address fields.
	 * @return array
	 */
	public static function customize_default_address_fields( $fields ) {
		unset( $fields['state'] );
		return $fields;
	}

	/**
	 * Remove state from billing fields.
	 *
	 * @param array $fields Billing fields.
	 * @return array
	 */
	public static function customize_billing_fields( $fields ) {
		unset( $fields['billing_state'] );
		return $fields;
	}

	/**
	 * Remove state from shipping fields.
	 *
	 * @param array $fields Shipping fields.
	 * @return array
	 */
	public static function customize_shipping_fields( $fields ) {
		unset( $fields['shipping_state'] );
		return $fields;
	}

	/**
	 * Add role-specific body classes.
	 *
	 * @param string[] $classes Existing classes.
	 * @return string[]
	 */
	public static function add_role_body_classes( $classes ) {
		if ( WM_Utils::is_approved_b2b() ) {
			$classes[] = 'role-b2b-wholesale';
			return $classes;
		}
		if ( WM_Utils::is_pending_b2b() ) {
			$classes[] = 'role-b2b-pending';
			return $classes;
		}
		$classes[] = 'role-b2c';
		return $classes;
	}

	/**
	 * Render role-aware checkout message.
	 *
	 * @return void
	 */
	public static function render_role_checkout_message() {
		if ( ! is_checkout() ) {
			return;
		}

		if ( WM_Utils::is_approved_b2b() ) {
			echo '<div class="woocommerce-info wm-checkout-note">' . esc_html__( 'B2B checkout: company and VAT details are required. Your wholesale pricing and user discount are active.', 'woodmak-b2b-core' ) . '</div>';
			return;
		}

		echo '<div class="woocommerce-info wm-checkout-note">' . esc_html__( 'B2C checkout: complete your billing details to finalize the order.', 'woodmak-b2b-core' ) . '</div>';
	}

	/**
	 * Show notices from redirected URL params.
	 *
	 * @return void
	 */
	public static function handle_frontend_notices() {
		if ( ! function_exists( 'wc_add_notice' ) ) {
			return;
		}
		$notice = isset( $_GET['wm_notice'] ) ? sanitize_key( wp_unslash( $_GET['wm_notice'] ) ) : '';
		if ( 'b2b_login_required' === $notice ) {
			wc_add_notice( __( 'Please log in to access this product.', 'woodmak-b2b-core' ), 'notice' );
		}
		if ( 'b2b_approval_required' === $notice ) {
			wc_add_notice( __( 'This product is available only for approved B2B accounts. Submit your request to continue.', 'woodmak-b2b-core' ), 'notice' );
		}
	}

	/**
	 * Customize checkout fields.
	 *
	 * @param array $fields Checkout fields.
	 * @return array
	 */
	public static function customize_checkout_fields( $fields ) {
		unset( $fields['billing']['billing_country'] );
		unset( $fields['billing']['billing_state'] );
		unset( $fields['shipping']['shipping_country'] );
		unset( $fields['shipping']['shipping_state'] );

		if ( isset( $fields['billing']['billing_postcode'] ) ) {
			$fields['billing']['billing_postcode']['class']    = array( 'form-row-first' );
			$fields['billing']['billing_postcode']['priority'] = 62;
		}

		if ( isset( $fields['billing']['billing_city'] ) ) {
			$fields['billing']['billing_city']['class']    = array( 'form-row-last' );
			$fields['billing']['billing_city']['clear']    = true;
			$fields['billing']['billing_city']['priority'] = 63;
		}

		$fields['billing']['billing_vat'] = array(
			'type'     => 'text',
			'label'    => __( 'VAT / Tax Number', 'woodmak-b2b-core' ),
			'required' => WM_Utils::is_approved_b2b(),
			'class'    => array( 'form-row-wide' ),
			'clear'    => true,
			'priority' => 72,
			'custom_attributes' => array(
				'maxlength' => 40,
			),
		);

		if ( WM_Utils::is_approved_b2b() ) {
			$fields['billing']['billing_company']['required'] = true;
		}

		return $fields;
	}

	/**
	 * Force-hide leaked state fields if WooCommerce rebuilds them later.
	 *
	 * @param array  $args Form field args.
	 * @param string $key Field key.
	 * @param mixed  $value Field value.
	 * @return array
	 */
	public static function force_hidden_state_fields( $args, $key, $value ) {
		unset( $value );

		if ( ! in_array( (string) $key, array( 'billing_state', 'shipping_state' ), true ) ) {
			return $args;
		}

		$args['type']     = 'hidden';
		$args['required'] = false;
		$args['class']    = array( 'wm-force-hidden' );
		$args['label']    = '';

		return $args;
	}

	/**
	 * Prefill checkout values from B2B profile.
	 *
	 * @param mixed  $value Field value.
	 * @param string $input Input name.
	 * @return mixed
	 */
	public static function prefill_checkout_values( $value, $input ) {
		if ( ! is_user_logged_in() ) {
			return $value;
		}

		$user_id = get_current_user_id();
		if ( 'billing_company' === $input ) {
			$company = get_user_meta( $user_id, '_company_name', true );
			return $company ? $company : $value;
		}
		if ( 'billing_vat' === $input ) {
			$vat = get_user_meta( $user_id, '_company_vat', true );
			return $vat ? $vat : $value;
		}
		return $value;
	}

	/**
	 * Save VAT value to order.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data Data.
	 * @return void
	 */
	public static function save_vat_to_order( $order, $data ) {
		if ( isset( $data['billing_vat'] ) ) {
			$order->update_meta_data( '_billing_vat', sanitize_text_field( $data['billing_vat'] ) );
		}
	}

	/**
	 * Validate B2B-specific checkout fields.
	 *
	 * @return void
	 */
	public static function validate_b2b_checkout_fields() {
		if ( ! WM_Utils::is_approved_b2b() ) {
			return;
		}

		$vat = isset( $_POST['billing_vat'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['billing_vat'] ) ) ) : '';
		if ( '' === $vat ) {
			wc_add_notice( __( 'VAT / Tax Number is required for B2B checkout.', 'woodmak-b2b-core' ), 'error' );
			return;
		}

		if ( ! preg_match( '/^[A-Z0-9][A-Z0-9\\-\\/\\s.]*$/', $vat ) || strlen( $vat ) > 40 ) {
			wc_add_notice( __( 'Please enter a valid VAT / Tax Number.', 'woodmak-b2b-core' ), 'error' );
		}
	}

	/**
	 * Persist B2B profile values from checkout.
	 *
	 * @param int   $user_id User ID.
	 * @param array $posted Posted checkout data.
	 * @return void
	 */
	public static function persist_b2b_profile_fields( $user_id, $posted ) {
		if ( ! $user_id || ! WM_Utils::is_approved_b2b( $user_id ) ) {
			return;
		}

		if ( isset( $posted['billing_company'] ) ) {
			update_user_meta( $user_id, '_company_name', sanitize_text_field( $posted['billing_company'] ) );
		}
		if ( isset( $posted['billing_vat'] ) ) {
			update_user_meta( $user_id, '_company_vat', strtoupper( sanitize_text_field( $posted['billing_vat'] ) ) );
		}
	}
}
