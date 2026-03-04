<?php
/**
 * B2B request form handling.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_B2B_Request_Form {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'wm_b2b_request_form', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'init', array( __CLASS__, 'handle_submission' ) );
	}

	/**
	 * Render form shortcode.
	 *
	 * @return string
	 */
	public static function render_shortcode() {
		$notice = isset( $_GET['wm_b2b_notice'] ) ? sanitize_key( wp_unslash( $_GET['wm_b2b_notice'] ) ) : '';
		ob_start();
		?>
		<div class="wm-b2b-request-wrap">
			<?php self::render_notice( $notice ); ?>
			<form method="post" class="wm-b2b-request-form">
				<?php wp_nonce_field( 'wm_b2b_request_submit', 'wm_b2b_request_nonce' ); ?>
				<input type="hidden" name="wm_b2b_action" value="submit_request" />
				<p class="wm-hp-field" aria-hidden="true">
					<label for="wm_website"><?php esc_html_e( 'Website', 'woodmak-b2b-core' ); ?></label>
					<input id="wm_website" name="website" type="text" tabindex="-1" autocomplete="off" />
				</p>
				<p>
					<label for="wm_first_name"><?php esc_html_e( 'First name', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_first_name" name="first_name" type="text" maxlength="80" required />
				</p>
				<p>
					<label for="wm_last_name"><?php esc_html_e( 'Last name', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_last_name" name="last_name" type="text" maxlength="80" required />
				</p>
				<p>
					<label for="wm_email"><?php esc_html_e( 'Email', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_email" name="email" type="email" required />
				</p>
				<p>
					<label for="wm_phone"><?php esc_html_e( 'Phone', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_phone" name="phone" type="text" maxlength="30" required />
				</p>
				<p>
					<label for="wm_company_name"><?php esc_html_e( 'Company name', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_company_name" name="company_name" type="text" maxlength="120" required />
				</p>
				<p>
					<label for="wm_company_vat"><?php esc_html_e( 'VAT / Tax number', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_company_vat" name="company_vat" type="text" maxlength="40" required />
				</p>
				<?php self::render_country_field(); ?>
				<p>
					<label for="wm_city"><?php esc_html_e( 'City', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_city" name="city" type="text" maxlength="80" required />
				</p>
				<p>
					<label for="wm_address"><?php esc_html_e( 'Address', 'woodmak-b2b-core' ); ?>*</label>
					<input id="wm_address" name="address" type="text" maxlength="180" required />
				</p>
				<p>
					<label for="wm_message"><?php esc_html_e( 'Message', 'woodmak-b2b-core' ); ?></label>
					<textarea id="wm_message" name="message" rows="4" maxlength="2000"></textarea>
				</p>
				<p>
					<button type="submit" class="button alt"><?php esc_html_e( 'Send B2B request', 'woodmak-b2b-core' ); ?></button>
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render request notice.
	 *
	 * @param string $notice Notice key.
	 * @return void
	 */
	private static function render_notice( $notice ) {
		$messages = array(
			'success'       => __( 'Your B2B request was submitted successfully. We will review it shortly.', 'woodmak-b2b-core' ),
			'invalid_nonce' => __( 'Security check failed. Please try again.', 'woodmak-b2b-core' ),
			'invalid_email' => __( 'Please use a valid email address.', 'woodmak-b2b-core' ),
			'missing_fields' => __( 'Please fill in all required fields.', 'woodmak-b2b-core' ),
			'invalid_fields' => __( 'Some fields are invalid. Please review your form data.', 'woodmak-b2b-core' ),
			'rate_limited'  => __( 'Too many submissions. Please wait one minute and try again.', 'woodmak-b2b-core' ),
			'forbidden_role' => __( 'This account cannot be converted to B2B pending automatically. Contact support.', 'woodmak-b2b-core' ),
			'already_b2b'   => __( 'This account is already approved for B2B.', 'woodmak-b2b-core' ),
			'error'         => __( 'Could not submit your request. Please try again.', 'woodmak-b2b-core' ),
		);

		if ( ! isset( $messages[ $notice ] ) ) {
			return;
		}

		$class = 'success' === $notice ? 'woocommerce-message' : 'woocommerce-error';
		echo '<div class="' . esc_attr( $class ) . '">' . esc_html( $messages[ $notice ] ) . '</div>';
	}

	/**
	 * Render country field with WooCommerce countries when available.
	 *
	 * @return void
	 */
	private static function render_country_field() {
		$countries = self::get_country_options();
		if ( empty( $countries ) ) {
			?>
			<p>
				<label for="wm_country"><?php esc_html_e( 'Country', 'woodmak-b2b-core' ); ?>*</label>
				<input id="wm_country" name="country" type="text" maxlength="80" required />
			</p>
			<?php
			return;
		}

		?>
		<p>
			<label for="wm_country"><?php esc_html_e( 'Country', 'woodmak-b2b-core' ); ?>*</label>
			<select id="wm_country" name="country" required>
				<option value=""><?php esc_html_e( 'Select country', 'woodmak-b2b-core' ); ?></option>
				<?php foreach ( $countries as $country_code => $country_name ) : ?>
					<option value="<?php echo esc_attr( $country_code ); ?>"><?php echo esc_html( $country_name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Handle posted request.
	 *
	 * @return void
	 */
	public static function handle_submission() {
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		if ( ! isset( $_POST['wm_b2b_action'] ) || 'submit_request' !== sanitize_key( wp_unslash( $_POST['wm_b2b_action'] ) ) ) {
			return;
		}

		$redirect_url = self::get_redirect_url();
		if ( ! isset( $_POST['wm_b2b_request_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wm_b2b_request_nonce'] ) ), 'wm_b2b_request_submit' ) ) {
			wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'invalid_nonce', $redirect_url ) );
			exit;
		}

		$payload = self::normalize_payload( self::get_payload() );
		if ( ! empty( $payload['website'] ) ) {
			wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'success', $redirect_url ) );
			exit;
		}

		if ( self::is_rate_limited( $payload['email'] ) ) {
			wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'rate_limited', $redirect_url ) );
			exit;
		}

		if ( ! is_email( $payload['email'] ) ) {
			wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'invalid_email', $redirect_url ) );
			exit;
		}

		$required = array( 'first_name', 'last_name', 'email', 'phone', 'company_name', 'company_vat', 'country', 'city', 'address' );
		foreach ( $required as $key ) {
			if ( empty( $payload[ $key ] ) ) {
				wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'missing_fields', $redirect_url ) );
				exit;
			}
		}

		if ( ! self::validate_payload( $payload ) ) {
			wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'invalid_fields', $redirect_url ) );
			exit;
		}

		$user_id = email_exists( $payload['email'] );
		if ( $user_id ) {
			$user = get_user_by( 'id', (int) $user_id );
			if ( $user instanceof WP_User && in_array( 'b2b_wholesale', (array) $user->roles, true ) ) {
				wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'already_b2b', $redirect_url ) );
				exit;
			}
			if ( $user instanceof WP_User && self::user_has_restricted_role( $user ) ) {
				wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'forbidden_role', $redirect_url ) );
				exit;
			}
		} else {
			$password = wp_generate_password( 20, true, true );
			$user_id  = wp_insert_user(
				array(
					'user_login' => self::generate_login_from_email( $payload['email'] ),
					'user_pass'  => $password,
					'user_email' => $payload['email'],
					'role'       => 'b2b_pending',
				)
			);
			if ( is_wp_error( $user_id ) ) {
				wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'error', $redirect_url ) );
				exit;
			}

			wp_update_user(
				array(
					'ID'         => $user_id,
					'first_name' => $payload['first_name'],
					'last_name'  => $payload['last_name'],
				)
			);

			wp_new_user_notification( $user_id, null, 'both' );
		}

		$user = get_user_by( 'id', (int) $user_id );
		if ( ! $user instanceof WP_User ) {
			wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'error', $redirect_url ) );
			exit;
		}

		if ( ! in_array( 'b2b_pending', (array) $user->roles, true ) && ! in_array( 'b2b_wholesale', (array) $user->roles, true ) ) {
			$user->set_role( 'b2b_pending' );
		}

		wp_update_user(
			array(
				'ID'         => $user_id,
				'first_name' => $payload['first_name'],
				'last_name'  => $payload['last_name'],
			)
		);

		update_user_meta( $user_id, '_b2b_status', 'pending' );
		update_user_meta( $user_id, '_company_name', $payload['company_name'] );
		update_user_meta( $user_id, '_company_vat', $payload['company_vat'] );
		update_user_meta( $user_id, '_company_phone', $payload['phone'] );
		update_user_meta( $user_id, '_company_country', $payload['country'] );
		update_user_meta( $user_id, '_company_country_name', $payload['country_label'] );
		update_user_meta( $user_id, '_company_city', $payload['city'] );
		update_user_meta( $user_id, '_company_address', $payload['address'] );
		update_user_meta( $user_id, '_b2b_request_message', $payload['message'] );
		update_user_meta( $user_id, '_b2b_discount_percent', 0 );

		WM_Mailer::notify_admin_b2b_request( $payload );
		WM_Mailer::notify_user_pending( $payload['email'] );
		self::set_rate_limit( $payload['email'] );

		wp_safe_redirect( add_query_arg( 'wm_b2b_notice', 'success', $redirect_url ) );
		exit;
	}

	/**
	 * Get sanitized payload.
	 *
	 * @return array
	 */
	private static function get_payload() {
		return array(
			'first_name'   => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
			'last_name'    => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
			'email'        => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone'        => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'company_name' => isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) : '',
			'company_vat'  => isset( $_POST['company_vat'] ) ? sanitize_text_field( wp_unslash( $_POST['company_vat'] ) ) : '',
			'country'      => isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '',
			'city'         => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
			'address'      => isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '',
			'message'      => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
			'website'      => isset( $_POST['website'] ) ? sanitize_text_field( wp_unslash( $_POST['website'] ) ) : '',
		);
	}

	/**
	 * Normalize payload values for consistent validation/storage.
	 *
	 * @param array $payload Raw payload.
	 * @return array
	 */
	private static function normalize_payload( $payload ) {
		$payload = is_array( $payload ) ? $payload : array();

		$payload['first_name']   = trim( (string) ( $payload['first_name'] ?? '' ) );
		$payload['last_name']    = trim( (string) ( $payload['last_name'] ?? '' ) );
		$payload['email']        = trim( strtolower( (string) ( $payload['email'] ?? '' ) ) );
		$payload['phone']        = trim( (string) ( $payload['phone'] ?? '' ) );
		$payload['company_name'] = trim( (string) ( $payload['company_name'] ?? '' ) );
		$payload['company_vat']  = strtoupper( trim( (string) ( $payload['company_vat'] ?? '' ) ) );
		$payload['country']      = strtoupper( trim( (string) ( $payload['country'] ?? '' ) ) );
		$payload['city']         = trim( (string) ( $payload['city'] ?? '' ) );
		$payload['address']      = trim( (string) ( $payload['address'] ?? '' ) );
		$payload['message']      = trim( (string) ( $payload['message'] ?? '' ) );
		$payload['website']      = trim( (string) ( $payload['website'] ?? '' ) );

		$payload['country_label'] = self::country_label_from_code( $payload['country'] );

		return $payload;
	}

	/**
	 * Validate form payload values.
	 *
	 * @param array $payload Form payload.
	 * @return bool
	 */
	private static function validate_payload( $payload ) {
		$first_name_len = self::safe_length( $payload['first_name'] );
		$last_name_len  = self::safe_length( $payload['last_name'] );
		if ( $first_name_len < 1 || $first_name_len > 80 || $last_name_len < 1 || $last_name_len > 80 ) {
			return false;
		}

		if ( ! preg_match( "/^[\\p{L}\\p{M}\\s'.-]+$/u", $payload['first_name'] ) ) {
			return false;
		}
		if ( ! preg_match( "/^[\\p{L}\\p{M}\\s'.-]+$/u", $payload['last_name'] ) ) {
			return false;
		}

		if ( ! preg_match( '/^[0-9+()\\-\\s.]{6,30}$/', $payload['phone'] ) ) {
			return false;
		}

		if ( self::safe_length( $payload['company_name'] ) < 2 || self::safe_length( $payload['company_name'] ) > 120 ) {
			return false;
		}
		if ( ! preg_match( "/^[\\p{L}\\p{M}0-9&.,()'\\/\\s-]+$/u", $payload['company_name'] ) ) {
			return false;
		}

		if ( '' === $payload['country'] || ! self::is_valid_country( $payload['country'] ) ) {
			return false;
		}

		if ( self::safe_length( $payload['city'] ) < 1 || self::safe_length( $payload['city'] ) > 80 ) {
			return false;
		}

		if ( self::safe_length( $payload['address'] ) < 3 || self::safe_length( $payload['address'] ) > 180 ) {
			return false;
		}
		if ( self::safe_length( $payload['company_vat'] ) < 2 || self::safe_length( $payload['company_vat'] ) > 40 ) {
			return false;
		}
		if ( self::safe_length( $payload['message'] ) > 2000 ) {
			return false;
		}

		if ( ! preg_match( '/^[A-Z0-9][A-Z0-9\\-\\/\\s.]*$/', $payload['company_vat'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate unique user login from email.
	 *
	 * @param string $email Email.
	 * @return string
	 */
	private static function generate_login_from_email( $email ) {
		$email_parts = explode( '@', $email );
		$base        = sanitize_user( (string) $email_parts[0], true );
		if ( '' === $base ) {
			$base = 'b2buser';
		}

		$login   = $base;
		$counter = 1;
		while ( username_exists( $login ) ) {
			$login = $base . $counter;
			++$counter;
		}

		return $login;
	}

	/**
	 * Check whether a user has a role that should never be auto-changed.
	 *
	 * @param WP_User $user User.
	 * @return bool
	 */
	private static function user_has_restricted_role( $user ) {
		return user_can( $user, 'manage_options' ) || user_can( $user, 'manage_woocommerce' );
	}

	/**
	 * Check submit rate limit.
	 *
	 * @param string $email Email.
	 * @return bool
	 */
	private static function is_rate_limited( $email ) {
		$key = self::rate_limit_key( $email );
		return (bool) get_transient( $key );
	}

	/**
	 * Set submit rate limit.
	 *
	 * @param string $email Email.
	 * @return void
	 */
	private static function set_rate_limit( $email ) {
		set_transient( self::rate_limit_key( $email ), '1', MINUTE_IN_SECONDS );
	}

	/**
	 * Generate rate-limit transient key.
	 *
	 * @param string $email Email.
	 * @return string
	 */
	private static function rate_limit_key( $email ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$email = sanitize_email( strtolower( (string) $email ) );
		if ( '' === $email ) {
			$email = 'unknown';
		}
		return 'wm_b2b_req_' . md5( $email . '|' . $ip );
	}

	/**
	 * Get redirect URL.
	 *
	 * @return string
	 */
	private static function get_redirect_url() {
		$referer = wp_get_referer();
		if ( ! $referer ) {
			$referer = home_url( '/b2b-request/' );
		}
		return remove_query_arg( 'wm_b2b_notice', wp_validate_redirect( $referer, home_url( '/b2b-request/' ) ) );
	}

	/**
	 * Get countries from WooCommerce.
	 *
	 * @return array
	 */
	private static function get_country_options() {
		$countries = array();
		if ( function_exists( 'WC' ) && WC()->countries ) {
			$countries = WC()->countries->get_countries();
		}
		return is_array( $countries ) ? $countries : array();
	}

	/**
	 * Resolve country code to display label.
	 *
	 * @param string $country Country code.
	 * @return string
	 */
	private static function country_label_from_code( $country ) {
		$countries = self::get_country_options();
		if ( isset( $countries[ $country ] ) ) {
			return (string) $countries[ $country ];
		}
		return $country;
	}

	/**
	 * Validate posted country.
	 *
	 * @param string $country Country code.
	 * @return bool
	 */
	private static function is_valid_country( $country ) {
		$countries = self::get_country_options();
		if ( empty( $countries ) ) {
			return self::safe_length( $country ) <= 80;
		}
		return isset( $countries[ $country ] );
	}

	/**
	 * Multibyte-safe string length.
	 *
	 * @param string $value Value.
	 * @return int
	 */
	private static function safe_length( $value ) {
		$value = (string) $value;
		if ( function_exists( 'mb_strlen' ) ) {
			return (int) mb_strlen( $value );
		}
		return strlen( $value );
	}
}
