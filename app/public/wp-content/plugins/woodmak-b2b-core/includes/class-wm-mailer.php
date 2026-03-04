<?php
/**
 * Mail notifications.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Mailer {
	/**
	 * Notify admin of B2B request.
	 *
	 * @param array $payload Form payload.
	 * @return void
	 */
	public static function notify_admin_b2b_request( array $payload ) {
		$admin_email = get_option( 'admin_email' );
		if ( ! is_email( $admin_email ) ) {
			return;
		}

		$subject     = __( '[Woodmak] New B2B request', 'woodmak-b2b-core' );
		$first_name  = isset( $payload['first_name'] ) ? (string) $payload['first_name'] : '';
		$last_name   = isset( $payload['last_name'] ) ? (string) $payload['last_name'] : '';
		$email       = isset( $payload['email'] ) ? (string) $payload['email'] : '';
		$phone       = isset( $payload['phone'] ) ? (string) $payload['phone'] : '';
		$company     = isset( $payload['company_name'] ) ? (string) $payload['company_name'] : '';
		$vat         = isset( $payload['company_vat'] ) ? (string) $payload['company_vat'] : '';
		$country     = isset( $payload['country_label'] ) ? (string) $payload['country_label'] : ( isset( $payload['country'] ) ? (string) $payload['country'] : '' );
		$city        = isset( $payload['city'] ) ? (string) $payload['city'] : '';
		$address     = isset( $payload['address'] ) ? (string) $payload['address'] : '';
		$message     = isset( $payload['message'] ) ? (string) $payload['message'] : '';
		$lines       = array(
			__( 'A new B2B request was submitted:', 'woodmak-b2b-core' ),
			'',
			sprintf( __( 'Name: %s %s', 'woodmak-b2b-core' ), $first_name, $last_name ),
			sprintf( __( 'Email: %s', 'woodmak-b2b-core' ), $email ),
			sprintf( __( 'Phone: %s', 'woodmak-b2b-core' ), $phone ),
			sprintf( __( 'Company: %s', 'woodmak-b2b-core' ), $company ),
			sprintf( __( 'VAT: %s', 'woodmak-b2b-core' ), $vat ),
			sprintf( __( 'Country: %s', 'woodmak-b2b-core' ), $country ),
			sprintf( __( 'City: %s', 'woodmak-b2b-core' ), $city ),
			sprintf( __( 'Address: %s', 'woodmak-b2b-core' ), $address ),
			sprintf( __( 'Message: %s', 'woodmak-b2b-core' ), $message ),
		);

		wp_mail( $admin_email, $subject, implode( "\n", $lines ) );
	}

	/**
	 * Notify user request received.
	 *
	 * @param string $email User email.
	 * @return void
	 */
	public static function notify_user_pending( $email ) {
		if ( ! is_email( $email ) ) {
			return;
		}

		$subject = __( 'Your B2B request is pending review', 'woodmak-b2b-core' );
		$body    = __( 'Thanks for your request. We will review your B2B account and notify you shortly.', 'woodmak-b2b-core' );

		wp_mail( sanitize_email( $email ), $subject, $body );
	}

	/**
	 * Notify user approval or rejection.
	 *
	 * @param int    $user_id User ID.
	 * @param string $status  approved|rejected.
	 * @return void
	 */
	public static function notify_user_status_change( $user_id, $status ) {
		$user = get_user_by( 'id', absint( $user_id ) );
		if ( ! $user instanceof WP_User ) {
			return;
		}
		if ( ! is_email( $user->user_email ) ) {
			return;
		}

		if ( 'approved' === $status ) {
			$subject = __( 'Your B2B account was approved', 'woodmak-b2b-core' );
			$body    = __( 'Your account now has B2B wholesale access.', 'woodmak-b2b-core' );
		} else {
			$subject = __( 'Your B2B request was rejected', 'woodmak-b2b-core' );
			$body    = __( 'Your B2B request was not approved. You can continue shopping as a B2C customer.', 'woodmak-b2b-core' );
		}

		wp_mail( $user->user_email, $subject, $body );
	}
}
