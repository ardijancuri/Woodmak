<?php
/**
 * Plugin bootstrap.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Bootstrap {
	/**
	 * Dependency warnings.
	 *
	 * @var string[]
	 */
	private static $dependency_warnings = array();

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'woocommerce_missing_notice' ) );
			return;
		}

		self::check_dependency_versions();
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, WM_B2B_CORE_MIN_WC, '<' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'unsupported_woocommerce_notice' ) );
			return;
		}

		if ( ! empty( self::$dependency_warnings ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'dependency_warning_notice' ) );
		}

		WM_Roles::ensure_roles();
		WM_B2B_Request_Form::init();
		WM_B2B_Admin::init();
		WM_B2B_Pricing::init();
		WM_B2B_Visibility::init();
		WM_Catalog_Filters::init();
		WM_REST::init();
		WM_Cart_Sidebar::init();
		WM_Checkout::init();
		if ( defined( 'WPO_WCPDF_VERSION' ) ) {
			WM_Invoices::init();
		}
	}

	/**
	 * Admin notice for missing dependency.
	 *
	 * @return void
	 */
	public static function woocommerce_missing_notice() {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Woodmak B2B Core requires WooCommerce to be installed and active.', 'woodmak-b2b-core' ) . '</p></div>';
	}

	/**
	 * Admin notice for unsupported WooCommerce versions.
	 *
	 * @return void
	 */
	public static function unsupported_woocommerce_notice() {
		echo '<div class="notice notice-error"><p>' . esc_html( sprintf( __( 'Woodmak B2B Core requires WooCommerce %1$s or newer. Current version: %2$s.', 'woodmak-b2b-core' ), WM_B2B_CORE_MIN_WC, WC_VERSION ) ) . '</p></div>';
	}

	/**
	 * Declare WooCommerce feature compatibility.
	 *
	 * @return void
	 */
	public static function declare_woocommerce_compatibility() {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WM_B2B_CORE_FILE, true );
	}

	/**
	 * Validate active dependency versions.
	 *
	 * @return void
	 */
	private static function check_dependency_versions() {
		if ( defined( 'WC_VERSION' ) ) {
			if ( version_compare( WC_VERSION, WM_B2B_CORE_MIN_WC, '<' ) ) {
				self::$dependency_warnings[] = sprintf(
					/* translators: 1: detected version, 2: minimum version. */
					__( 'WooCommerce %1$s detected. Minimum supported version is %2$s.', 'woodmak-b2b-core' ),
					WC_VERSION,
					WM_B2B_CORE_MIN_WC
				);
			} elseif ( version_compare( WC_VERSION, WM_B2B_CORE_TESTED_WC, '!=' ) ) {
				self::$dependency_warnings[] = sprintf(
					/* translators: 1: detected version, 2: tested version. */
					__( 'WooCommerce %1$s detected. This plugin is tested against %2$s.', 'woodmak-b2b-core' ),
					WC_VERSION,
					WM_B2B_CORE_TESTED_WC
				);
			}
		}

		if ( defined( 'POLYLANG_VERSION' ) && version_compare( POLYLANG_VERSION, WM_B2B_CORE_TESTED_POLYLANG, '!=' ) ) {
			self::$dependency_warnings[] = sprintf(
				/* translators: 1: detected version, 2: tested version. */
				__( 'Polylang %1$s detected. Language switcher was tested against %2$s.', 'woodmak-b2b-core' ),
				POLYLANG_VERSION,
				WM_B2B_CORE_TESTED_POLYLANG
			);
		}

		if ( defined( 'WPO_WCPDF_VERSION' ) && version_compare( WPO_WCPDF_VERSION, WM_B2B_CORE_TESTED_WPO_WCPDF, '!=' ) ) {
			self::$dependency_warnings[] = sprintf(
				/* translators: 1: detected version, 2: tested version. */
				__( 'PDF Invoices plugin %1$s detected. Invoice customization was tested against %2$s.', 'woodmak-b2b-core' ),
				WPO_WCPDF_VERSION,
				WM_B2B_CORE_TESTED_WPO_WCPDF
			);
		}
	}

	/**
	 * Render dependency warning notice.
	 *
	 * @return void
	 */
	public static function dependency_warning_notice() {
		if ( empty( self::$dependency_warnings ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Woodmak B2B Core compatibility check:', 'woodmak-b2b-core' ) . '</strong></p><ul>';
		foreach ( self::$dependency_warnings as $warning ) {
			echo '<li>' . esc_html( $warning ) . '</li>';
		}
		echo '</ul></div>';
	}
}
