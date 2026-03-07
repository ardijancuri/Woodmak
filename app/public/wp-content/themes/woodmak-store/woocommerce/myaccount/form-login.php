<?php
/**
 * Logged-out My Account form override.
 *
 * Keeps WooCommerce form behavior intact while providing a custom Woodmak
 * login-first layout for the storefront account page.
 *
 * @package Woodmak_Store
 */

defined( 'ABSPATH' ) || exit;

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="ws-account-auth<?php echo $registration_enabled ? '' : ' ws-account-auth--login-only'; ?>" id="customer_login">
	<section class="ws-account-auth__card ws-account-auth__card--login u-column1 col-1">
			<div class="ws-account-auth__head">
				<h2><?php esc_html_e( 'Login', 'woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Access your saved details and recent orders.', 'woodmak-store' ); ?></p>
			</div>

			<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
				</p>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<div class="ws-account-auth__actions">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme ws-account-auth__remember">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox ws-account-auth__remember-input" name="rememberme" type="checkbox" id="rememberme" value="forever" />
						<span class="ws-account-auth__remember-text"><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
					</label>
					<a class="ws-account-auth__lost" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
				</div>

				<p class="form-row ws-account-auth__submit-row">
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
				</p>

				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>
	</section>

	<aside class="ws-account-auth__intro" aria-label="<?php esc_attr_e( 'Account benefits', 'woodmak-store' ); ?>">
		<p class="ws-kicker"><?php esc_html_e( 'My Account', 'woodmak-store' ); ?></p>
		<h2><?php esc_html_e( 'Welcome back', 'woodmak-store' ); ?></h2>
		<p class="ws-account-auth__lede"><?php esc_html_e( 'Sign in to manage orders, saved addresses, and faster checkout.', 'woodmak-store' ); ?></p>
		<div class="ws-account-auth__benefits">
			<div class="ws-account-auth__benefit">
				<span class="ws-account-auth__benefit-icon" aria-hidden="true">01</span>
				<div>
					<h3><?php esc_html_e( 'Track current and past orders', 'woodmak-store' ); ?></h3>
					<p><?php esc_html_e( 'Keep order details, invoices, and account activity in one place.', 'woodmak-store' ); ?></p>
				</div>
			</div>
			<div class="ws-account-auth__benefit">
				<span class="ws-account-auth__benefit-icon" aria-hidden="true">02</span>
				<div>
					<h3><?php esc_html_e( 'Save billing and shipping details', 'woodmak-store' ); ?></h3>
					<p><?php esc_html_e( 'Reuse your saved information without re-entering it every time.', 'woodmak-store' ); ?></p>
				</div>
			</div>
			<div class="ws-account-auth__benefit">
				<span class="ws-account-auth__benefit-icon" aria-hidden="true">03</span>
				<div>
					<h3><?php esc_html_e( 'Checkout faster on your next purchase', 'woodmak-store' ); ?></h3>
					<p><?php esc_html_e( 'Move from product selection to payment with fewer steps.', 'woodmak-store' ); ?></p>
				</div>
			</div>
		</div>
	</aside>

	<?php if ( $registration_enabled ) : ?>
		<section class="ws-account-auth__card ws-account-auth__card--register u-column2 col-2">
				<div class="ws-account-auth__head">
					<p class="ws-account-auth__eyebrow"><?php esc_html_e( 'New here?', 'woodmak-store' ); ?></p>
					<h2><?php esc_html_e( 'Register', 'woocommerce' ); ?></h2>
					<p><?php esc_html_e( 'Create an account for faster ordering and order history access.', 'woodmak-store' ); ?></p>
				</div>

				<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
						</p>
					<?php endif; ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) && is_string( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
					</p>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
						</p>
					<?php else : ?>
						<p class="ws-account-auth__hint"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<p class="woocommerce-form-row form-row ws-account-auth__submit-row">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>
				</form>
		</section>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
