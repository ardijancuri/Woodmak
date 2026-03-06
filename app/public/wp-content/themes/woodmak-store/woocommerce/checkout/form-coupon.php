<?php
/**
 * Checkout coupon form override.
 *
 * @package woodmak-store
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) {
	return;
}
?>
<div class="woocommerce-form-coupon-toggle">
	<?php
	wc_print_notice(
		apply_filters(
			'woocommerce_checkout_coupon_message',
			esc_html__( 'Имате купон?', 'woodmak-store' ) . ' <a href="#" role="button" aria-label="' . esc_attr__( 'Внесете го вашиот код за купон', 'woodmak-store' ) . '" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false" class="showcoupon ws-checkout-coupon-link">' . esc_html__( 'Кликни овде за да го внесиш твојот код', 'woodmak-store' ) . '</a>'
		),
		'notice'
	);
	?>
</div>

<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none" id="woocommerce-checkout-form-coupon">

	<p class="form-row form-row-first">
		<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Купон:', 'woodmak-store' ); ?></label>
		<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Код за купон', 'woodmak-store' ); ?>" id="coupon_code" value="" />
	</p>

	<p class="form-row form-row-last">
		<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Примени купон', 'woodmak-store' ); ?>"><?php esc_html_e( 'Примени купон', 'woodmak-store' ); ?></button>
	</p>

	<div class="clear"></div>
</form>
