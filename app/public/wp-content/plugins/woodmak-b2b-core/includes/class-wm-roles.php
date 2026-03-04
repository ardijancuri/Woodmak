<?php
/**
 * Roles and capabilities.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Roles {
	/**
	 * Activation hook.
	 *
	 * @return void
	 */
	public static function activate() {
		self::ensure_roles();
		self::maybe_create_b2b_request_page();
	}

	/**
	 * Ensure plugin roles exist.
	 *
	 * @return void
	 */
	public static function ensure_roles() {
		$customer_role = get_role( 'customer' );
		$caps          = $customer_role ? $customer_role->capabilities : array( 'read' => true );

		add_role( 'b2b_pending', __( 'B2B Pending', 'woodmak-b2b-core' ), $caps );
		add_role( 'b2b_wholesale', __( 'B2B Wholesale', 'woodmak-b2b-core' ), $caps );
	}

	/**
	 * Ensure B2B request page exists.
	 *
	 * @return void
	 */
	private static function maybe_create_b2b_request_page() {
		$existing = get_page_by_path( 'b2b-request' );
		if ( $existing instanceof WP_Post ) {
			return;
		}

		wp_insert_post(
			array(
				'post_title'   => __( 'B2B Request', 'woodmak-b2b-core' ),
				'post_name'    => 'b2b-request',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '[wm_b2b_request_form]',
			)
		);
	}
}
