<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'ws_theme_setup' );

/**
 * Theme setup.
 *
 * @return void
 */
function ws_theme_setup() {
	load_theme_textdomain( 'woodmak-store', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'script', 'style' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'woodmak-store' ),
			'utility' => __( 'Utility Menu', 'woodmak-store' ),
			'category' => __( 'Category Menu', 'woodmak-store' ),
			'footer'  => __( 'Footer Menu', 'woodmak-store' ),
		)
	);
}

add_action( 'wp_enqueue_scripts', 'ws_enqueue_assets' );

/**
 * Enqueue theme assets.
 *
 * @return void
 */
function ws_enqueue_assets() {
	$version = wp_get_theme()->get( 'Version' );
	$base_css_path = get_template_directory() . '/assets/css/base.css';
	$shop_css_path = get_template_directory() . '/assets/css/shop.css';
	$checkout_css_path = get_template_directory() . '/assets/css/checkout.css';
	$account_css_path = get_template_directory() . '/assets/css/account.css';
	$theme_js_path = get_template_directory() . '/assets/js/theme.js';
	$base_css_ver = file_exists( $base_css_path ) ? (string) filemtime( $base_css_path ) : $version;
	$shop_css_ver = file_exists( $shop_css_path ) ? (string) filemtime( $shop_css_path ) : $version;
	$checkout_css_ver = file_exists( $checkout_css_path ) ? (string) filemtime( $checkout_css_path ) : $version;
	$account_css_ver = file_exists( $account_css_path ) ? (string) filemtime( $account_css_path ) : $version;
	$theme_js_ver = file_exists( $theme_js_path ) ? (string) filemtime( $theme_js_path ) : $version;
	wp_enqueue_style( 'woodmak-store-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'woodmak-store-base', get_template_directory_uri() . '/assets/css/base.css', array( 'woodmak-store-fonts' ), $base_css_ver );
	wp_enqueue_style( 'woodmak-store-shop', get_template_directory_uri() . '/assets/css/shop.css', array( 'woodmak-store-base' ), $shop_css_ver );
	wp_enqueue_style( 'woodmak-store-checkout', get_template_directory_uri() . '/assets/css/checkout.css', array( 'woodmak-store-base' ), $checkout_css_ver );
	if ( ws_is_account_context() ) {
		wp_enqueue_style( 'woodmak-store-account', get_template_directory_uri() . '/assets/css/account.css', array( 'woodmak-store-base' ), $account_css_ver );
	}
	wp_enqueue_script( 'woodmak-store-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), $theme_js_ver, true );

	wp_add_inline_style( 'woodmak-store-base', ws_get_dynamic_theme_css() );
}

/**
 * Check whether the current request is in account context.
 *
 * @return bool
 */
function ws_is_account_context() {
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return true;
	}

	return function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url();
}

add_filter( 'body_class', 'ws_add_body_classes' );

/**
 * Add theme-specific body classes.
 *
 * @param array $classes Existing classes.
 * @return array
 */
function ws_add_body_classes( $classes ) {
	$classes[] = 'ws-halmar-ui';
	$classes[] = 'ws-sharp-corners';
	return $classes;
}

add_action( 'customize_register', 'ws_register_customizer_settings' );

/**
 * Register customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function ws_register_customizer_settings( $wp_customize ) {
	$wp_customize->add_section(
		'ws_brand_colors',
		array(
			'title'       => __( 'Woodmak Brand Colors', 'woodmak-store' ),
			'priority'    => 35,
			'description' => __( 'Set the primary brand color used across buttons, links, and call-to-actions.', 'woodmak-store' ),
		)
	);

	$wp_customize->add_setting(
		'ws_brand_primary',
		array(
			'default'           => '#a37746',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'ws_brand_primary_control',
			array(
				'label'    => __( 'Primary Brand Color', 'woodmak-store' ),
				'section'  => 'ws_brand_colors',
				'settings' => 'ws_brand_primary',
			)
		)
	);

	$wp_customize->add_section(
		'ws_homepage_visuals',
		array(
			'title'       => __( 'Homepage Visuals', 'woodmak-store' ),
			'priority'    => 36,
			'description' => __( 'Manage hero and visual blocks used on the homepage.', 'woodmak-store' ),
		)
	);

	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_hero_image_desktop',
		__( 'Hero Desktop Image', 'woodmak-store' ),
		'image',
		5
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_hero_image_mobile',
		__( 'Hero Mobile Image', 'woodmak-store' ),
		'image',
		6
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_hero_title',
		__( 'Hero Title', 'woodmak-store' ),
		'text',
		7,
		__( 'New Collection 2026', 'woodmak-store' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_hero_subtitle',
		__( 'Hero Subtitle', 'woodmak-store' ),
		'textarea',
		8,
		__( 'Premium furniture solutions for retail and wholesale buyers.', 'woodmak-store' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_hero_cta_label',
		__( 'Hero CTA Label', 'woodmak-store' ),
		'text',
		9,
		__( 'Shop Now', 'woodmak-store' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_hero_cta_url',
		__( 'Hero CTA URL', 'woodmak-store' ),
		'url',
		10,
		home_url( '/shop/' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_promo_1_image',
		__( 'Promo Banner 1 Image', 'woodmak-store' ),
		'image',
		11
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_promo_1_title',
		__( 'Promo Banner 1 Title', 'woodmak-store' ),
		'text',
		12,
		__( 'Living Room Collection', 'woodmak-store' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_promo_1_url',
		__( 'Promo Banner 1 URL', 'woodmak-store' ),
		'url',
		13,
		home_url( '/shop/' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_promo_2_image',
		__( 'Promo Banner 2 Image', 'woodmak-store' ),
		'image',
		14
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_promo_2_title',
		__( 'Promo Banner 2 Title', 'woodmak-store' ),
		'text',
		15,
		__( 'Dining and Kitchen Picks', 'woodmak-store' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_promo_2_url',
		__( 'Promo Banner 2 URL', 'woodmak-store' ),
		'url',
		16,
		home_url( '/shop/' )
	);
	ws_register_homepage_visual_setting(
		$wp_customize,
		'ws_home_newsletter_bg_image',
		__( 'Newsletter Background Image', 'woodmak-store' ),
		'image',
		17
	);

	$wp_customize->add_section(
		'ws_footer_branding',
		array(
			'title'       => __( 'Footer Branding', 'woodmak-store' ),
			'priority'    => 37,
			'description' => __( 'Configure footer-specific brand assets.', 'woodmak-store' ),
		)
	);

	$wp_customize->add_setting(
		'ws_footer_logo',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'ws_footer_logo_control',
			array(
				'label'     => __( 'Footer Logo', 'woodmak-store' ),
				'section'   => 'ws_footer_branding',
				'settings'  => 'ws_footer_logo',
				'mime_type' => 'image',
				'priority'  => 5,
			)
		)
	);

	$wp_customize->add_section(
		'ws_contact_details',
		array(
			'title'       => __( 'Contact Details', 'woodmak-store' ),
			'priority'    => 38,
			'description' => __( 'Manage shared contact details used in the contact page and footer.', 'woodmak-store' ),
		)
	);

	ws_register_contact_setting( $wp_customize, 'ws_contact_brand_title', __( 'Brand Title', 'woodmak-store' ), 'text', 5, 'Woodmak' );
	ws_register_contact_setting( $wp_customize, 'ws_contact_page_heading', __( 'Page Heading', 'woodmak-store' ), 'text', 6, ws_unicode_string( '\u041a\u043e\u043d\u0442\u0430\u043a\u0442\u0438\u0440\u0430\u0458\u0442\u0435 \u043d\u0435' ) );
	ws_register_contact_setting( $wp_customize, 'ws_contact_address_label', __( 'Address Label', 'woodmak-store' ), 'text', 7, ws_unicode_string( '\u0410\u0414\u0420\u0415\u0421\u0410' ) );
	ws_register_contact_setting( $wp_customize, 'ws_contact_address_text', __( 'Address Text', 'woodmak-store' ), 'textarea', 8, 'Kachanadki Pat bb, Skopje, Macedonia' );
	ws_register_contact_setting( $wp_customize, 'ws_contact_phone_label', __( 'Phone Label', 'woodmak-store' ), 'text', 9, ws_unicode_string( '\u0422\u0415\u041b\u0415\u0424\u041e\u041d' ) );
	ws_register_contact_setting( $wp_customize, 'ws_contact_phone_text', __( 'Phone Number', 'woodmak-store' ), 'text', 10, '+389 75 317 372' );
	ws_register_contact_setting( $wp_customize, 'ws_contact_email_label', __( 'Email Label', 'woodmak-store' ), 'text', 11, ws_unicode_string( '\u0415\u041c\u0410\u0418\u041b' ) );
	ws_register_contact_setting( $wp_customize, 'ws_contact_email_text', __( 'Email Address', 'woodmak-store' ), 'email', 12, 'info@woodmak.mk' );
	ws_register_contact_setting( $wp_customize, 'ws_contact_social_label', __( 'Social Label', 'woodmak-store' ), 'text', 13, ws_unicode_string( '\u0421\u041b\u0415\u0414\u0418 \u041d\u0415' ) );
	ws_register_contact_setting( $wp_customize, 'ws_contact_facebook_url', __( 'Facebook URL', 'woodmak-store' ), 'url', 14, 'https://www.facebook.com/' );
	ws_register_contact_setting( $wp_customize, 'ws_contact_instagram_url', __( 'Instagram URL', 'woodmak-store' ), 'url', 15, 'https://www.instagram.com/' );
}

/**
 * Register a homepage visual setting and control.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @param string               $key Setting key.
 * @param string               $label Field label.
 * @param string               $type Field type.
 * @param int                  $priority Priority.
 * @param string|int           $default Default value.
 * @return void
 */
function ws_register_homepage_visual_setting( $wp_customize, $key, $label, $type, $priority, $default = '' ) {
	$sanitize_callback = 'sanitize_text_field';
	if ( 'url' === $type ) {
		$sanitize_callback = 'esc_url_raw';
	}
	if ( 'textarea' === $type ) {
		$sanitize_callback = 'sanitize_textarea_field';
	}
	if ( 'image' === $type ) {
		$sanitize_callback = 'absint';
	}

	$wp_customize->add_setting(
		$key,
		array(
			'default'           => $default,
			'sanitize_callback' => $sanitize_callback,
			'transport'         => 'refresh',
		)
	);

	if ( 'image' === $type ) {
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				$key . '_control',
				array(
					'label'    => $label,
					'section'  => 'ws_homepage_visuals',
					'settings' => $key,
					'mime_type' => 'image',
					'priority' => $priority,
				)
			)
		);
		return;
	}

	$wp_customize->add_control(
		$key . '_control',
		array(
			'label'    => $label,
			'section'  => 'ws_homepage_visuals',
			'settings' => $key,
			'type'     => $type,
			'priority' => $priority,
		)
	);
}

/**
 * Register a contact detail setting and control.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @param string               $key Setting key.
 * @param string               $label Field label.
 * @param string               $type Field type.
 * @param int                  $priority Priority.
 * @param string               $default Default value.
 * @return void
 */
function ws_register_contact_setting( $wp_customize, $key, $label, $type, $priority, $default = '' ) {
	$sanitize_callback = 'sanitize_text_field';
	if ( 'textarea' === $type ) {
		$sanitize_callback = 'sanitize_textarea_field';
	}
	if ( 'url' === $type ) {
		$sanitize_callback = 'esc_url_raw';
	}
	if ( 'email' === $type ) {
		$sanitize_callback = 'sanitize_email';
	}

	$wp_customize->add_setting(
		$key,
		array(
			'default'           => $default,
			'sanitize_callback' => $sanitize_callback,
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		$key . '_control',
		array(
			'label'    => $label,
			'section'  => 'ws_contact_details',
			'settings' => $key,
			'type'     => 'email' === $type ? 'email' : $type,
			'priority' => $priority,
		)
	);
}

/**
 * Decode ASCII-safe unicode escape strings.
 *
 * @param string $escaped Escaped unicode string.
 * @return string
 */
function ws_unicode_string( $escaped ) {
	$decoded = json_decode( '"' . $escaped . '"' );
	return is_string( $decoded ) ? $decoded : (string) $escaped;
}

/**
 * Build dynamic CSS from theme mods.
 *
 * @return string
 */
function ws_get_dynamic_theme_css() {
	$primary = get_theme_mod( 'ws_brand_primary', '#a37746' );
	$primary = sanitize_hex_color( $primary );
	if ( empty( $primary ) ) {
		$primary = '#a37746';
	}

	$hover = ws_adjust_hex_color( $primary, -22 );
	if ( ! $hover ) {
		$hover = '#8e6334';
	}

	$on_primary = ws_get_contrast_color( $primary );

	$css  = ':root {';
	$css .= '--wm-primary:' . $primary . ';';
	$css .= '--wm-primary-strong:' . $hover . ';';
	$css .= '--wm-on-primary:' . $on_primary . ';';
	$css .= '}';

	return $css;
}

/**
 * Return sanitized text theme mod.
 *
 * @param string $key Mod key.
 * @param string $default Default value.
 * @return string
 */
function ws_get_theme_mod_text( $key, $default = '' ) {
	$value = sanitize_text_field( (string) get_theme_mod( $key, $default ) );
	return ws_translate_dynamic_text( $key, $value );
}

/**
 * Register/translate dynamic text through Polylang.
 *
 * @param string $key   Stable key.
 * @param string $value Text value.
 * @return string
 */
function ws_translate_dynamic_text( $key, $value ) {
	$value = (string) $value;
	if ( '' === $value ) {
		return $value;
	}

	if ( function_exists( 'pll_register_string' ) ) {
		pll_register_string( 'ws_theme_mod_' . sanitize_key( $key ), $value, 'woodmak-store' );
	}

	if ( function_exists( 'pll__' ) ) {
		$translated = (string) pll__( $value );
		if ( '' !== $translated ) {
			return $translated;
		}
	}

	$is_mk = function_exists( 'pll_current_language' ) && 'mk' === (string) pll_current_language( 'slug' );
	if ( $is_mk ) {
		$normalized = trim( $value );
		$fallback_map = array(
			'New Collection 2026' => 'Нова колекција 2026',
			'Premium furniture solutions for retail and wholesale buyers.' => 'Премиум мебел решенија за малопродажни и големопродажни купувачи.',
			'Shop Now' => 'Купи сега',
			'Living Room Collection' => 'Колекција за дневна соба',
			'Dining and Kitchen Picks' => 'Избор за трпезарија и кујна',
		);

		if ( isset( $fallback_map[ $normalized ] ) ) {
			return $fallback_map[ $normalized ];
		}
	}

	return $value;
}

/**
 * Return sanitized URL theme mod.
 *
 * @param string $key Mod key.
 * @param string $default Default value.
 * @return string
 */
function ws_get_theme_mod_url( $key, $default = '' ) {
	$value = get_theme_mod( $key, $default );
	return esc_url_raw( (string) $value );
}

/**
 * Return image URL from image-id theme mod.
 *
 * @param string $key Mod key.
 * @return string
 */
function ws_get_theme_mod_image_url( $key ) {
	$raw_value = get_theme_mod( $key, 0 );
	$image_id  = absint( $raw_value );

	if ( $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( $image_url ) {
			return esc_url( $image_url );
		}
	}

	if ( is_string( $raw_value ) && filter_var( $raw_value, FILTER_VALIDATE_URL ) ) {
		return esc_url( $raw_value );
	}

	return '';
}

/**
 * Return shared contact details.
 *
 * @return array
 */
function ws_get_contact_details() {
	$phone_text   = ws_get_theme_mod_text( 'ws_contact_phone_text', '+389 75 317 372' );
	$phone_href   = preg_replace( '/[^0-9+]/', '', $phone_text );
	$email_text   = sanitize_email( ws_get_theme_mod_text( 'ws_contact_email_text', 'info@woodmak.mk' ) );
	$address_text = sanitize_textarea_field( (string) get_theme_mod( 'ws_contact_address_text', 'Kachanadki Pat bb, Skopje, Macedonia' ) );

	return array(
		'brand_title'   => ws_get_theme_mod_text( 'ws_contact_brand_title', 'Woodmak' ),
		'page_heading'  => ws_get_theme_mod_text( 'ws_contact_page_heading', ws_unicode_string( '\u041a\u043e\u043d\u0442\u0430\u043a\u0442\u0438\u0440\u0430\u0458\u0442\u0435 \u043d\u0435' ) ),
		'address_label' => ws_get_theme_mod_text( 'ws_contact_address_label', ws_unicode_string( '\u0410\u0414\u0420\u0415\u0421\u0410' ) ),
		'address_text'  => ws_translate_dynamic_text( 'ws_contact_address_text', $address_text ),
		'phone_label'   => ws_get_theme_mod_text( 'ws_contact_phone_label', ws_unicode_string( '\u0422\u0415\u041b\u0415\u0424\u041e\u041d' ) ),
		'phone_text'    => $phone_text,
		'phone_href'    => '' !== $phone_href ? 'tel:' . $phone_href : '',
		'email_label'   => ws_get_theme_mod_text( 'ws_contact_email_label', ws_unicode_string( '\u0415\u041c\u0410\u0418\u041b' ) ),
		'email_text'    => $email_text,
		'email_href'    => '' !== $email_text ? 'mailto:' . $email_text : '',
		'social_label'  => ws_get_theme_mod_text( 'ws_contact_social_label', ws_unicode_string( '\u0421\u041b\u0415\u0414\u0418 \u041d\u0415' ) ),
		'facebook_url'  => ws_get_theme_mod_url( 'ws_contact_facebook_url', 'https://www.facebook.com/' ),
		'instagram_url' => ws_get_theme_mod_url( 'ws_contact_instagram_url', 'https://www.instagram.com/' ),
	);
}

/**
 * Get homepage products for a given tab key.
 *
 * @param string $tab_key Tab key.
 * @param int    $limit Products count.
 * @param array  $exclude_ids Product IDs to exclude.
 * @return array
 */
function ws_get_home_tab_products( $tab_key, $limit = 8, $exclude_ids = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$limit       = max( 1, absint( $limit ) );
	$exclude_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $exclude_ids ) ) ) );
	$args        = array(
		'status' => 'publish',
		'limit'  => $limit,
		'return' => 'objects',
	);

	if ( 'recommended' === $tab_key ) {
		return ws_get_random_home_products( $limit, $exclude_ids );
	}

	if ( 'bestsellers' === $tab_key ) {
		$bestseller_ids = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => max( $limit * 6, 24 ),
				'return'  => 'ids',
				'exclude' => $exclude_ids,
				'orderby' => 'total_sales',
				'order'   => 'DESC',
			)
		);

		$bestseller_ids = array_values(
			array_filter(
				array_unique( array_map( 'absint', (array) $bestseller_ids ) ),
				static function ( $product_id ) {
					return (int) get_post_meta( absint( $product_id ), 'total_sales', true ) > 0;
				}
			)
		);

		if ( ! empty( $bestseller_ids ) ) {
			shuffle( $bestseller_ids );
		}

		$selected_ids = array_slice( $bestseller_ids, 0, $limit );
		if ( count( $selected_ids ) < $limit ) {
			$fallback_ids = ws_get_random_home_product_ids( $limit - count( $selected_ids ), array_merge( $exclude_ids, $selected_ids ) );
			$selected_ids = array_values( array_unique( array_merge( $selected_ids, $fallback_ids ) ) );
		}

		return ws_get_home_products_by_ids( $selected_ids );
	}

	if ( ! empty( $exclude_ids ) ) {
		$args['exclude'] = $exclude_ids;
	}

	$args['orderby'] = 'date';
	$args['order']   = 'DESC';
	return wc_get_products( $args );
}

/**
 * Load products by ID while preserving order.
 *
 * @param array $product_ids Product IDs.
 * @return array
 */
function ws_get_home_products_by_ids( $product_ids ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$product_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $product_ids ) ) ) );
	if ( empty( $product_ids ) ) {
		return array();
	}

	return wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => count( $product_ids ),
			'return'  => 'objects',
			'include' => $product_ids,
			'orderby' => 'include',
		)
	);
}

/**
 * Get random product IDs for homepage tabs.
 *
 * @param int   $limit Product count.
 * @param array $exclude_ids Product IDs to exclude.
 * @return array
 */
function ws_get_random_home_product_ids( $limit, $exclude_ids = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$limit       = max( 1, absint( $limit ) );
	$exclude_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $exclude_ids ) ) ) );

	$product_ids = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => max( $limit * 6, 24 ),
			'return'  => 'ids',
			'exclude' => $exclude_ids,
			'orderby' => 'rand',
		)
	);

	return array_slice( array_values( array_unique( array_filter( array_map( 'absint', (array) $product_ids ) ) ) ), 0, $limit );
}

/**
 * Get random products for homepage tabs.
 *
 * @param int   $limit Product count.
 * @param array $exclude_ids Product IDs to exclude.
 * @return array
 */
function ws_get_random_home_products( $limit, $exclude_ids = array() ) {
	return ws_get_home_products_by_ids( ws_get_random_home_product_ids( $limit, $exclude_ids ) );
}

/**
 * Get ranked WooCommerce product categories by product count.
 *
 * @param int $limit Max categories to return.
 * @param int $offset Categories offset.
 * @return array
 */
function ws_get_ranked_product_categories( $limit = 0, $offset = 0 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$categories = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		if ( 'uncategorized' === sanitize_title( $term->slug ) ) {
			continue;
		}
		$categories[] = $term;
	}

	usort(
		$categories,
		static function ( $left, $right ) {
			if ( (int) $left->count === (int) $right->count ) {
				return strcasecmp( $left->name, $right->name );
			}
			return ( (int) $left->count > (int) $right->count ) ? -1 : 1;
		}
	);

	$offset = max( 0, absint( $offset ) );
	$limit  = max( 0, absint( $limit ) );

	if ( $offset > 0 || $limit > 0 ) {
		return array_slice( $categories, $offset, $limit ? $limit : null );
	}

	return $categories;
}

/**
 * Get all non-empty WooCommerce categories for the header megamenu.
 *
 * @return array
 */
function ws_get_megamenu_product_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$categories = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		if ( 'uncategorized' === sanitize_title( $term->slug ) ) {
			continue;
		}

		$categories[] = $term;
	}

	usort(
		$categories,
		static function ( $left, $right ) {
			return strcasecmp( $left->name, $right->name );
		}
	);

	return $categories;
}

/**
 * Select a category subset from a ranked list.
 *
 * @param array $categories Ranked categories.
 * @param int   $start Start index.
 * @param int   $count Category count.
 * @param array $exclude_ids Category IDs to exclude.
 * @return array
 */
function ws_select_product_categories( $categories, $start, $count, $exclude_ids = array() ) {
	$categories = is_array( $categories ) ? $categories : array();
	$count      = max( 0, absint( $count ) );
	$start      = max( 0, absint( $start ) );

	if ( empty( $categories ) || ! $count ) {
		return array();
	}

	$exclude_lookup = array();
	foreach ( $exclude_ids as $exclude_id ) {
		$exclude_lookup[ absint( $exclude_id ) ] = true;
	}

	$selected      = array();
	$selected_ids  = array();
	$total         = count( $categories );
	$passes        = array( array( $start, $total ), array( 0, min( $start, $total ) ) );

	foreach ( $passes as $pass ) {
		$from = (int) $pass[0];
		$to   = (int) $pass[1];

		for ( $index = $from; $index < $to; $index++ ) {
			if ( count( $selected ) >= $count ) {
				break 2;
			}

			$category = $categories[ $index ];
			if ( ! $category instanceof WP_Term ) {
				continue;
			}

			$category_id = absint( $category->term_id );
			if ( isset( $exclude_lookup[ $category_id ] ) || isset( $selected_ids[ $category_id ] ) ) {
				continue;
			}

			$selected[]               = $category;
			$selected_ids[ $category_id ] = true;
		}
	}

	return $selected;
}

/**
 * Get product-category image URL.
 *
 * @param WP_Term|int $category Category term or ID.
 * @return string
 */
function ws_get_product_category_image_url( $category ) {
	$category_id = 0;
	if ( $category instanceof WP_Term ) {
		$category_id = absint( $category->term_id );
	} else {
		$category_id = absint( $category );
	}

	if ( ! $category_id ) {
		return '';
	}

	$thumbnail_id = absint( get_term_meta( $category_id, 'thumbnail_id', true ) );
	if ( $thumbnail_id ) {
		$image_url = wp_get_attachment_image_url( $thumbnail_id, 'medium_large' );
		if ( $image_url ) {
			return esc_url_raw( $image_url );
		}
	}

	if ( function_exists( 'wc_placeholder_img_src' ) ) {
		return esc_url_raw( wc_placeholder_img_src( 'woocommerce_thumbnail' ) );
	}

	return '';
}

/**
 * Get homepage products for a specific category.
 *
 * @param WP_Term|int $category Category term or ID.
 * @param int         $limit Product count.
 * @return array
 */
function ws_get_home_category_products( $category, $limit = 5 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$term = null;
	if ( $category instanceof WP_Term ) {
		$term = $category;
	} elseif ( $category ) {
		$term = get_term( absint( $category ), 'product_cat' );
	}

	if ( ! $term instanceof WP_Term || empty( $term->slug ) ) {
		return array();
	}

	return wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => max( 1, absint( $limit ) ),
			'return'   => 'objects',
			'orderby'  => 'date',
			'order'    => 'DESC',
			'category' => array( sanitize_title( $term->slug ) ),
		)
	);
}

/**
 * Render WooCommerce products list.
 *
 * @param array  $products Product objects.
 * @param string $extra_class Additional class.
 * @return void
 */
function ws_render_home_products( $products, $extra_class = '' ) {
	if ( empty( $products ) || ! is_array( $products ) ) {
		return;
	}

	$class_attr = trim( 'products ws-home-products ' . $extra_class );
	echo '<ul class="' . esc_attr( $class_attr ) . '">';
	foreach ( $products as $product ) {
		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$post_object = get_post( $product->get_id() );
		if ( ! $post_object instanceof WP_Post ) {
			continue;
		}

		$GLOBALS['post'] = $post_object;
		setup_postdata( $GLOBALS['post'] );
		wc_get_template_part( 'content', 'product' );
	}
	wp_reset_postdata();
	echo '</ul>';
}

/**
 * Adjust brightness for a hex color.
 *
 * @param string $hex_color Hex color.
 * @param int    $steps Brightness steps.
 * @return string
 */
function ws_adjust_hex_color( $hex_color, $steps ) {
	$hex = ltrim( (string) $hex_color, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '';
	}

	$steps = max( -255, min( 255, (int) $steps ) );
	$r     = max( 0, min( 255, hexdec( substr( $hex, 0, 2 ) ) + $steps ) );
	$g     = max( 0, min( 255, hexdec( substr( $hex, 2, 2 ) ) + $steps ) );
	$b     = max( 0, min( 255, hexdec( substr( $hex, 4, 2 ) ) + $steps ) );

	return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Compute accessible contrast color for a background hex color.
 *
 * @param string $hex_color Hex color.
 * @return string
 */
function ws_get_contrast_color( $hex_color ) {
	$hex = ltrim( (string) $hex_color, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '#ffffff';
	}

	$r          = hexdec( substr( $hex, 0, 2 ) );
	$g          = hexdec( substr( $hex, 2, 2 ) );
	$b          = hexdec( substr( $hex, 4, 2 ) );
	$luminance  = ( ( 299 * $r ) + ( 587 * $g ) + ( 114 * $b ) ) / 1000;

	return $luminance >= 140 ? '#111111' : '#ffffff';
}

add_action( 'init', 'ws_reorder_single_product_summary_price', 20 );
add_action( 'woocommerce_single_product_summary', 'ws_render_product_specs', 25 );

/**
 * Move product price below product specifications in single-product summary.
 *
 * @return void
 */
function ws_reorder_single_product_summary_price() {
	if ( ! function_exists( 'woocommerce_template_single_price' ) ) {
		return;
	}

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 26 );
}

/**
 * Render dimensions and weight on single product page.
 *
 * @return void
 */
function ws_render_product_specs() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$dimensions = wc_format_dimensions( $product->get_dimensions( false ) );
	$weight     = wc_format_weight( $product->get_weight() );

	echo '<div class="wm-product-specs">';
	echo '<h3>' . esc_html__( 'Product Specifications', 'woodmak-store' ) . '</h3>';
	if ( $dimensions ) {
		echo '<p><strong>' . esc_html__( 'Dimensions:', 'woodmak-store' ) . '</strong> ' . esc_html( $dimensions ) . '</p>';
	}
	if ( $weight ) {
		echo '<p><strong>' . esc_html__( 'Weight:', 'woodmak-store' ) . '</strong> ' . esc_html( $weight ) . '</p>';
	}
	echo '</div>';
}
