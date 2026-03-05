<?php
/**
 * Plugin Name: Woodmak B2B Core
 * Requires Plugins: woocommerce
 * Description: B2B/B2C business logic for WooCommerce on Woodmak.
 * Version: 1.0.0
 * Author: Woodmak
 * Text Domain: woodmak-b2b-core
 * Domain Path: /languages
 * WC requires at least: 8.0.0
 * WC tested up to: 10.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WM_B2B_CORE_VERSION', '1.0.0' );
define( 'WM_B2B_CORE_FILE', __FILE__ );
define( 'WM_B2B_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WM_B2B_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'WM_B2B_CORE_TESTED_WC', '10.5.3' );
define( 'WM_B2B_CORE_TESTED_POLYLANG', '3.7.8' );
define( 'WM_B2B_CORE_TESTED_WPO_WCPDF', '5.8.2' );
define( 'WM_B2B_CORE_MIN_WC', '8.0.0' );

require_once WM_B2B_CORE_PATH . 'includes/class-wm-utils.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-roles.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-mailer.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-b2b-request-form.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-b2b-admin.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-b2b-pricing.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-b2b-visibility.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-catalog-filters.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-rest.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-cart-sidebar.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-checkout.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-invoices.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-localization.php';
require_once WM_B2B_CORE_PATH . 'includes/class-wm-bootstrap.php';

register_activation_hook( __FILE__, array( 'WM_Roles', 'activate' ) );
add_action( 'before_woocommerce_init', array( 'WM_Bootstrap', 'declare_woocommerce_compatibility' ) );

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'woodmak-b2b-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		WM_Bootstrap::init();
	}
);
