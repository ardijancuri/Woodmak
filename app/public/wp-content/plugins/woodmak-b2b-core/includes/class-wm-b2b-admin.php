<?php
/**
 * Admin controls for B2B users and product meta.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_B2B_Admin {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'show_user_profile', array( __CLASS__, 'render_user_fields' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'render_user_fields' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_user_fields' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_fields' ) );

		add_filter( 'manage_users_columns', array( __CLASS__, 'add_users_columns' ) );
		add_filter( 'manage_users_custom_column', array( __CLASS__, 'render_users_columns' ), 10, 3 );
		add_filter( 'user_row_actions', array( __CLASS__, 'add_user_row_actions' ), 10, 2 );
		add_action( 'admin_action_wm_b2b_approve', array( __CLASS__, 'handle_approve_action' ) );
		add_action( 'admin_action_wm_b2b_reject', array( __CLASS__, 'handle_reject_action' ) );

		add_action( 'woocommerce_product_options_pricing', array( __CLASS__, 'render_simple_product_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_simple_product_fields' ) );
		add_action( 'woocommerce_variation_options_pricing', array( __CLASS__, 'render_variation_fields' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation_fields' ), 10, 2 );
	}

	/**
	 * Render user profile fields.
	 *
	 * @param WP_User $user User object.
	 * @return void
	 */
	public static function render_user_fields( $user ) {
		if ( ! current_user_can( 'list_users' ) ) {
			return;
		}
		$status   = get_user_meta( $user->ID, '_b2b_status', true );
		$discount = (int) get_user_meta( $user->ID, '_b2b_discount_percent', true );
		wp_nonce_field( 'wm_b2b_user_profile_update', 'wm_b2b_user_profile_nonce' );
		?>
		<h2><?php esc_html_e( 'B2B Account Settings', 'woodmak-b2b-core' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="wm_b2b_status"><?php esc_html_e( 'B2B Status', 'woodmak-b2b-core' ); ?></label></th>
				<td>
					<select id="wm_b2b_status" name="wm_b2b_status">
						<option value=""><?php esc_html_e( 'Not set', 'woodmak-b2b-core' ); ?></option>
						<option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'woodmak-b2b-core' ); ?></option>
						<option value="approved" <?php selected( $status, 'approved' ); ?>><?php esc_html_e( 'Approved', 'woodmak-b2b-core' ); ?></option>
						<option value="rejected" <?php selected( $status, 'rejected' ); ?>><?php esc_html_e( 'Rejected', 'woodmak-b2b-core' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="wm_b2b_discount_percent"><?php esc_html_e( 'B2B Discount Percent', 'woodmak-b2b-core' ); ?></label></th>
				<td>
					<select id="wm_b2b_discount_percent" name="wm_b2b_discount_percent">
						<?php foreach ( WM_Utils::discount_options() as $option ) : ?>
							<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $discount, $option ); ?>><?php echo esc_html( $option ); ?>%</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="wm_company_name"><?php esc_html_e( 'Company Name', 'woodmak-b2b-core' ); ?></label></th>
				<td><input id="wm_company_name" name="wm_company_name" class="regular-text" type="text" value="<?php echo esc_attr( (string) get_user_meta( $user->ID, '_company_name', true ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="wm_company_vat"><?php esc_html_e( 'Company VAT', 'woodmak-b2b-core' ); ?></label></th>
				<td><input id="wm_company_vat" name="wm_company_vat" class="regular-text" type="text" value="<?php echo esc_attr( (string) get_user_meta( $user->ID, '_company_vat', true ) ); ?>" /></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save user profile fields.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function save_user_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wm_b2b_user_profile_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wm_b2b_user_profile_nonce'] ) ), 'wm_b2b_user_profile_update' ) ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user instanceof WP_User || self::is_restricted_user( $user ) || get_current_user_id() === (int) $user_id ) {
			return;
		}

		$previous_status = (string) get_user_meta( $user_id, '_b2b_status', true );
		$status = isset( $_POST['wm_b2b_status'] ) ? sanitize_key( wp_unslash( $_POST['wm_b2b_status'] ) ) : '';
		if ( in_array( $status, array( 'pending', 'approved', 'rejected' ), true ) ) {
			update_user_meta( $user_id, '_b2b_status', $status );
		} elseif ( '' === $status ) {
			delete_user_meta( $user_id, '_b2b_status' );
		}

		$discount = isset( $_POST['wm_b2b_discount_percent'] ) ? (int) wp_unslash( $_POST['wm_b2b_discount_percent'] ) : 0;
		if ( ! in_array( $discount, WM_Utils::discount_options(), true ) ) {
			$discount = 0;
		}
		update_user_meta( $user_id, '_b2b_discount_percent', $discount );

		$company_name = isset( $_POST['wm_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wm_company_name'] ) ) : '';
		$company_vat  = isset( $_POST['wm_company_vat'] ) ? sanitize_text_field( wp_unslash( $_POST['wm_company_vat'] ) ) : '';
		update_user_meta( $user_id, '_company_name', $company_name );
		update_user_meta( $user_id, '_company_vat', $company_vat );

		if ( 'approved' === $status ) {
			$user->set_role( 'b2b_wholesale' );
			if ( '' === get_user_meta( $user_id, '_b2b_discount_percent', true ) ) {
				update_user_meta( $user_id, '_b2b_discount_percent', 0 );
			}
		}
		if ( 'pending' === $status ) {
			$user->set_role( 'b2b_pending' );
		}
		if ( 'rejected' === $status ) {
			$user->set_role( 'customer' );
			update_user_meta( $user_id, '_b2b_discount_percent', 0 );
		}

		if ( $previous_status !== $status && in_array( $status, array( 'approved', 'rejected' ), true ) ) {
			WM_Mailer::notify_user_status_change( $user_id, $status );
		}
	}

	/**
	 * Add columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function add_users_columns( $columns ) {
		$columns['wm_b2b_status'] = __( 'B2B Status', 'woodmak-b2b-core' );
		$columns['wm_b2b_discount'] = __( 'B2B Discount', 'woodmak-b2b-core' );
		return $columns;
	}

	/**
	 * Render custom columns.
	 *
	 * @param string $output Existing output.
	 * @param string $column Column key.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public static function render_users_columns( $output, $column, $user_id ) {
		if ( 'wm_b2b_status' === $column ) {
			$status = (string) get_user_meta( $user_id, '_b2b_status', true );
			return $status ? esc_html( ucfirst( $status ) ) : '-';
		}
		if ( 'wm_b2b_discount' === $column ) {
			$discount = (int) get_user_meta( $user_id, '_b2b_discount_percent', true );
			return esc_html( $discount . '%' );
		}
		return $output;
	}

	/**
	 * Add row actions.
	 *
	 * @param array   $actions Existing actions.
	 * @param WP_User $user User.
	 * @return array
	 */
	public static function add_user_row_actions( $actions, $user ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			return $actions;
		}
		if ( self::is_restricted_user( $user ) || get_current_user_id() === (int) $user->ID ) {
			return $actions;
		}

		$status = (string) get_user_meta( $user->ID, '_b2b_status', true );
		if ( '' === $status && ! in_array( 'b2b_pending', (array) $user->roles, true ) && ! in_array( 'b2b_wholesale', (array) $user->roles, true ) ) {
			return $actions;
		}

		$approve_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'  => 'wm_b2b_approve',
					'user_id' => $user->ID,
				),
				admin_url( 'users.php' )
			),
			'wm_b2b_approve_' . $user->ID
		);
		$reject_url  = wp_nonce_url(
			add_query_arg(
				array(
					'action'  => 'wm_b2b_reject',
					'user_id' => $user->ID,
				),
				admin_url( 'users.php' )
			),
			'wm_b2b_reject_' . $user->ID
		);

		if ( 'approved' !== $status ) {
			$actions['wm_b2b_approve'] = '<a href="' . esc_url( $approve_url ) . '">' . esc_html__( 'Approve B2B', 'woodmak-b2b-core' ) . '</a>';
		}
		if ( 'rejected' !== $status ) {
			$actions['wm_b2b_reject'] = '<a href="' . esc_url( $reject_url ) . '">' . esc_html__( 'Reject B2B', 'woodmak-b2b-core' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Handle approval action.
	 *
	 * @return void
	 */
	public static function handle_approve_action() {
		self::process_action( 'approved', 'b2b_wholesale', 'wm_b2b_approve_' );
	}

	/**
	 * Handle rejection action.
	 *
	 * @return void
	 */
	public static function handle_reject_action() {
		self::process_action( 'rejected', 'customer', 'wm_b2b_reject_' );
	}

	/**
	 * Process user action.
	 *
	 * @param string $status Status.
	 * @param string $role Role.
	 * @param string $nonce_prefix Nonce prefix.
	 * @return void
	 */
	private static function process_action( $status, $role, $nonce_prefix ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'woodmak-b2b-core' ) );
		}

		$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_safe_redirect( admin_url( 'users.php' ) );
			exit;
		}

		check_admin_referer( $nonce_prefix . $user_id );

		$user = get_user_by( 'id', $user_id );
		if ( $user instanceof WP_User ) {
			if ( self::is_restricted_user( $user ) || get_current_user_id() === (int) $user_id ) {
				wp_safe_redirect( admin_url( 'users.php' ) );
				exit;
			}
			$user->set_role( $role );
			update_user_meta( $user_id, '_b2b_status', $status );
			if ( 'approved' === $status && '' === get_user_meta( $user_id, '_b2b_discount_percent', true ) ) {
				update_user_meta( $user_id, '_b2b_discount_percent', 0 );
			}
			WM_Mailer::notify_user_status_change( $user_id, $status );
		}

		wp_safe_redirect( admin_url( 'users.php' ) );
		exit;
	}

	/**
	 * Check if account should never be reassigned by B2B actions.
	 *
	 * @param WP_User $user User.
	 * @return bool
	 */
	private static function is_restricted_user( $user ) {
		return user_can( $user, 'manage_options' ) || user_can( $user, 'manage_woocommerce' );
	}

	/**
	 * Render simple product fields.
	 *
	 * @return void
	 */
	public static function render_simple_product_fields() {
		echo '<div class="options_group">';

		woocommerce_wp_text_input(
			array(
				'id'                => '_b2b_price',
				'label'             => __( 'B2B price', 'woodmak-b2b-core' ),
				'desc_tip'          => true,
				'description'       => __( 'Wholesale unit price for approved B2B users.', 'woodmak-b2b-core' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
				),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => '_b2b_only',
				'label'       => __( 'B2B only product', 'woodmak-b2b-core' ),
				'description' => __( 'Visible only to approved B2B users.', 'woodmak-b2b-core' ),
			)
		);

		echo '</div>';
	}

	/**
	 * Save simple product fields.
	 *
	 * @param WC_Product $product Product.
	 * @return void
	 */
	public static function save_simple_product_fields( $product ) {
		if ( ! $product instanceof WC_Product || ! current_user_can( 'edit_post', $product->get_id() ) ) {
			return;
		}

		$b2b_price = isset( $_POST['_b2b_price'] ) ? wc_format_decimal( wp_unslash( $_POST['_b2b_price'] ) ) : '';
		if ( '' !== $b2b_price && $b2b_price >= 0 ) {
			$product->update_meta_data( '_b2b_price', $b2b_price );
		} else {
			$product->delete_meta_data( '_b2b_price' );
		}

		$b2b_only = isset( $_POST['_b2b_only'] ) ? 'yes' : 'no';
		$product->update_meta_data( '_b2b_only', $b2b_only );
	}

	/**
	 * Render variation fields.
	 *
	 * @param int     $loop Loop index.
	 * @param array   $data Variation data.
	 * @param WP_Post $variation Variation post.
	 * @return void
	 */
	public static function render_variation_fields( $loop, $data, $variation ) {
		$value = get_post_meta( $variation->ID, '_b2b_price', true );
		?>
		<p class="form-row form-row-first">
			<label><?php esc_html_e( 'B2B price', 'woodmak-b2b-core' ); ?></label>
			<input type="number" min="0" step="0.01" name="wm_variation_b2b_price[<?php echo esc_attr( $variation->ID ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
		</p>
		<?php
	}

	/**
	 * Save variation fields.
	 *
	 * @param int $variation_id Variation ID.
	 * @param int $index Index.
	 * @return void
	 */
	public static function save_variation_fields( $variation_id, $index ) {
		if ( ! current_user_can( 'edit_post', $variation_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wm_variation_b2b_price'][ $variation_id ] ) ) {
			delete_post_meta( $variation_id, '_b2b_price' );
			return;
		}
		$value = wc_format_decimal( wp_unslash( $_POST['wm_variation_b2b_price'][ $variation_id ] ) );
		if ( '' !== $value && $value >= 0 ) {
			update_post_meta( $variation_id, '_b2b_price', $value );
		} else {
			delete_post_meta( $variation_id, '_b2b_price' );
		}
	}
}
