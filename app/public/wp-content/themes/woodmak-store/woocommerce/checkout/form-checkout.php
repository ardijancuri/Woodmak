<?php
/**
 * Checkout form override.
 *
 * @package woodmak-store
 */

defined( 'ABSPATH' ) || exit;

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>
<div class="ws-checkout-wrap">
	<?php do_action( 'woocommerce_before_checkout_form', $checkout ); ?>
	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">
		<?php if ( $checkout->get_checkout_fields() ) : ?>
			<div class="ws-checkout-columns">
				<section class="ws-checkout-col ws-checkout-col--details">
					<h3><?php esc_html_e( 'Billing Details', 'woodmak-store' ); ?></h3>
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</section>
				<section class="ws-checkout-col ws-checkout-col--review">
					<h3 id="order_review_heading"><?php esc_html_e( 'Your Order', 'woodmak-store' ); ?></h3>
					<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
					<div id="order_review" class="woocommerce-checkout-review-order">
						<?php do_action( 'woocommerce_checkout_order_review' ); ?>
					</div>
					<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
				</section>
			</div>
		<?php endif; ?>
	</form>
</div>
