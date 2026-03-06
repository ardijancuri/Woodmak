<?php
/**
 * Frontend localization helpers.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Localization {
	/**
	 * Guard against recursive locale filter calls.
	 *
	 * @var bool
	 */
	private static $in_locale_filter = false;

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'locale', array( __CLASS__, 'filter_locale' ), 20 );
		add_filter( 'plugin_locale', array( __CLASS__, 'filter_plugin_locale' ), 20, 2 );
		add_filter( 'gettext', array( __CLASS__, 'filter_gettext' ), 20, 3 );
		add_filter( 'ngettext', array( __CLASS__, 'filter_ngettext' ), 20, 5 );
		add_filter( 'load_script_translations', array( __CLASS__, 'filter_script_translations' ), 20, 4 );
		add_filter( 'the_title', array( __CLASS__, 'filter_page_titles' ), 20, 2 );
		add_filter( 'nav_menu_item_title', array( __CLASS__, 'filter_menu_titles' ), 20, 4 );
		add_filter( 'the_content', array( __CLASS__, 'filter_content_strings' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'start_output_buffer' ), 0 );
	}

	/**
	 * Determine language slug for current request.
	 *
	 * @return string
	 */
	private static function current_language_slug( $locale_context = '' ) {
		$locale_context = is_string( $locale_context ) ? strtolower( $locale_context ) : '';
		if ( 0 === strpos( $locale_context, 'mk_' ) ) {
			return 'mk';
		}
		if ( 0 === strpos( $locale_context, 'en_' ) ) {
			return 'en';
		}

		$request_lang = '';
		if ( isset( $_GET['lang'] ) ) {
			$request_lang = sanitize_key( wp_unslash( (string) $_GET['lang'] ) );
		} elseif ( isset( $_REQUEST['lang'] ) ) {
			$request_lang = sanitize_key( wp_unslash( (string) $_REQUEST['lang'] ) );
		}
		if ( 'mk' === $request_lang ) {
			return 'mk';
		}
		if ( 'en' === $request_lang ) {
			return 'en';
		}

		$cookie_lang = self::get_cookie_language_slug();
		if ( '' !== $cookie_lang ) {
			return $cookie_lang;
		}

		$referer_lang = self::get_referer_language_slug();
		if ( '' !== $referer_lang ) {
			return $referer_lang;
		}

		if ( function_exists( 'pll_current_language' ) && ! self::$in_locale_filter ) {
			$lang = (string) pll_current_language( 'slug' );
			if ( '' !== $lang ) {
				return $lang;
			}
		}

		$global_locale = '';
		if ( isset( $GLOBALS['locale'] ) && is_string( $GLOBALS['locale'] ) ) {
			$global_locale = strtolower( $GLOBALS['locale'] );
		}
		if ( 0 === strpos( $global_locale, 'mk_' ) ) {
			return 'mk';
		}
		if ( 0 === strpos( $global_locale, 'en_' ) ) {
			return 'en';
		}

		return 'en';
	}

	/**
	 * Resolve Polylang language from cookie.
	 *
	 * @return string
	 */
	private static function get_cookie_language_slug() {
		$cookie_name = defined( 'PLL_COOKIE' ) && PLL_COOKIE ? (string) PLL_COOKIE : 'pll_language';
		if ( empty( $_COOKIE[ $cookie_name ] ) ) {
			return '';
		}

		return self::normalize_language_slug( wp_unslash( (string) $_COOKIE[ $cookie_name ] ) );
	}

	/**
	 * Resolve language from current referer.
	 *
	 * @return string
	 */
	private static function get_referer_language_slug() {
		$referer = wp_get_referer();
		if ( ! $referer && isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referer = wp_unslash( (string) $_SERVER['HTTP_REFERER'] );
		}
		if ( ! is_string( $referer ) || '' === $referer ) {
			return '';
		}

		$query = wp_parse_url( $referer, PHP_URL_QUERY );
		if ( is_string( $query ) && '' !== $query ) {
			parse_str( $query, $query_args );
			if ( ! empty( $query_args['lang'] ) ) {
				$lang = self::normalize_language_slug( (string) $query_args['lang'] );
				if ( '' !== $lang ) {
					return $lang;
				}
			}
		}

		$path = wp_parse_url( $referer, PHP_URL_PATH );
		if ( ! is_string( $path ) || '' === $path ) {
			return '';
		}

		$segments = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
		if ( empty( $segments ) ) {
			return '';
		}

		return self::normalize_language_slug( (string) $segments[0] );
	}

	/**
	 * Normalize raw language slug.
	 *
	 * @param string $lang Raw slug.
	 * @return string
	 */
	private static function normalize_language_slug( $lang ) {
		$lang = sanitize_key( (string) $lang );
		return in_array( $lang, array( 'mk', 'en' ), true ) ? $lang : '';
	}

	/**
	 * Decode ASCII-safe unicode escape strings.
	 *
	 * @param string $escaped Escaped unicode string.
	 * @return string
	 */
	private static function decode_unicode_string( $escaped ) {
		$decoded = json_decode( '"' . $escaped . '"' );
		return is_string( $decoded ) ? $decoded : (string) $escaped;
	}

	/**
	 * Check if request should be Macedonian.
	 *
	 * @return bool
	 */
	private static function is_macedonian_request( $locale_context = '' ) {
		return 'mk' === self::current_language_slug( $locale_context );
	}

	/**
	 * Public storefront language check for other plugin classes.
	 *
	 * @return bool
	 */
	public static function is_macedonian_storefront() {
		return self::should_localize_request() && self::is_macedonian_request();
	}

	/**
	 * Check if request should be localized on frontend.
	 *
	 * @return bool
	 */
	private static function should_localize_request() {
		if ( is_admin() && ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}

		if ( self::is_invoice_generation_request() ) {
			return false;
		}

		return true;
	}

	/**
	 * Detect invoice generation requests where storefront localization should not run.
	 *
	 * @return bool
	 */
	private static function is_invoice_generation_request() {
		if ( ! isset( $_REQUEST['action'] ) ) {
			return false;
		}

		$action = sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) );
		if ( '' === $action ) {
			return false;
		}

		if ( 'generate_wpo_wcpdf' === $action ) {
			return true;
		}

		return ( false !== strpos( $action, 'wpo_wcpdf' ) );
	}

	/**
	 * Set locale by current Polylang language.
	 *
	 * @param string $locale Current locale.
	 * @return string
	 */
	public static function filter_locale( $locale ) {
		if ( ! self::should_localize_request() ) {
			return $locale;
		}

		if ( self::$in_locale_filter ) {
			return $locale;
		}

		self::$in_locale_filter = true;
		try {
			return self::is_macedonian_request( (string) $locale ) ? 'mk_MK' : 'en_US';
		} finally {
			self::$in_locale_filter = false;
		}
	}

	/**
	 * Keep plugin locale aligned with frontend locale.
	 *
	 * @param string $locale Current locale.
	 * @param string $domain Text domain.
	 * @return string
	 */
	public static function filter_plugin_locale( $locale, $domain ) {
		if ( ! self::should_localize_request() ) {
			return $locale;
		}

		$domains = array(
			'woodmak-store',
			'woodmak-b2b-core',
			'woocommerce',
			'woocommerce-pdf-invoices-packing-slips',
		);

		if ( ! in_array( (string) $domain, $domains, true ) ) {
			return $locale;
		}

		if ( self::$in_locale_filter ) {
			return $locale;
		}

		self::$in_locale_filter = true;
		try {
			return self::is_macedonian_request( (string) $locale ) ? 'mk_MK' : 'en_US';
		} finally {
			self::$in_locale_filter = false;
		}
	}

	/**
	 * Translate selected strings on Macedonian storefront.
	 *
	 * @param string $translated Translated text.
	 * @param string $text Source text.
	 * @param string $domain Text domain.
	 * @return string
	 */
	public static function filter_gettext( $translated, $text, $domain ) {
		if ( ! self::should_localize_request() || ! self::is_macedonian_request() ) {
			return $translated;
		}

		$supported_domains = array(
			'woodmak-store',
			'woodmak-b2b-core',
			'woocommerce',
			'woocommerce-pdf-invoices-packing-slips',
			'default',
			'messages',
		);

		if ( ! in_array( (string) $domain, $supported_domains, true ) ) {
			return $translated;
		}

		$map = array(
			'Search products' => 'Пребарај производи',
			'Search products...' => 'Пребарај производи...',
			'Account' => 'Сметка',
			'Cart' => 'Кошничка',
			'Checkout' => 'Наплата',
			'Categories' => 'Категории',
			'Open menu' => 'Отвори мени',
			'Close menu' => self::decode_unicode_string( '\u0417\u0430\u0442\u0432\u043e\u0440\u0438 \u043c\u0435\u043d\u0438' ),
			'Close cart sidebar' => self::decode_unicode_string( '\u0417\u0430\u0442\u0432\u043e\u0440\u0438 \u043a\u043e\u0448\u043d\u0438\u0447\u043a\u0430' ),
			'Category navigation' => 'Навигација по категории',
			'Mobile category navigation' => 'Мобилна навигација по категории',
			'Mobile utility navigation' => 'Мобилна корисничка навигација',
			'Premium Furniture for Retail, Interior Projects, and Wholesale Partners' => 'Премиум мебел за малопродажба, ентериерни проекти и големопродажни партнери',
			'No categories found yet.' => 'Сe уште нема категории.',
			'Information' => 'Информации',
			'Catalog' => 'Каталог',
			'My Account' => 'Мој профил',
			'For Partners' => 'За партнери',
			'B2B Request' => 'Барање за B2B',
			'Order Tracking' => 'Следење на нарачка',
			'Contact' => 'Контакт',
			'Powered by' => 'Поддржано од',
			'Furniture for retail, interior projects, and wholesale partners.' => 'Мебел за малопродажба, ентериерни проекти и големопродажни партнери.',
			'Mon-Fri: 08:00 - 16:00' => 'Пон-Пет: 08:00 - 16:00',
			'B2B Account Request' => 'Барање за B2B профил',
			'Apply for wholesale access by completing the form below.' => 'Аплицирајте за големопродажен пристап со пополнување на формата подолу.',
			'Shop now' => 'Купи сега',
			'Shop Now' => 'Купи сега',
			'New Arrivals' => 'Нови производи',
			'Special Offers' => 'Специјални понуди',
			'New' => 'Ново',
			'Clearance Sale' => 'Распродажба',
			'Recommended' => 'Препорачано',
			'Bestsellers' => 'Најпродавани',
			'See All' => 'Види сe',
			'Browse by Category' => 'Преглед по категории',
			'View all' => 'Види сe',
			'products' => 'производи',
			'Featured Collection' => 'Издвоена колекција',
			'What\'s New' => 'Што е ново',
			'More' => 'Повеќе',
			'See all' => 'Види сe',
			'Newsletter' => 'Билтен',
			'You must accept the terms and conditions.' => 'Мора да ги прифатите условите и правилата.',
			'Email address' => 'Е-пошта',
			'Enter your email' => 'Внесете ја вашата е-пошта',
			'Sign up' => 'Пријави се',
			'For Retailers and Projects' => 'За трговци и проекти',
			'Apply for Woodmak Wholesale Program' => 'Аплицирајте за Woodmak големопродажна програма',
			'Get access to B2B-only products, role-based pricing, and streamlined ordering for your business.' => 'Добијте пристап до B2B производи, цени според улога и поедноставено нарачување за вашиот бизнис.',
			'Start B2B Request' => 'Започни B2B барање',
			'Category' => 'Категорија',
			'Color' => 'Боја',
			'Price' => 'Цена',
			'Min' => 'Мин',
			'Max' => 'Макс',
			'Dimensions' => 'Димензии',
			'Width' => 'Ширина',
			'Height' => 'Висина',
			'Depth' => 'Длабочина',
			'Weight' => 'Тежина',
			'Apply' => 'Примени',
			'Reset' => 'Ресетирај',
			'Your cart' => 'Вашата кошничка',
			'Your cart is currently empty.' => 'Вашата кошничка моментално е празна.',
			'Continue shopping' => self::decode_unicode_string( '\u041f\u0440\u043e\u0434\u043e\u043b\u0436\u0438 \u0441\u043e \u043a\u0443\u043f\u0443\u0432\u0430\u045a\u0435' ),
			'Remove %s from cart' => self::decode_unicode_string( '\u041e\u0442\u0441\u0442\u0440\u0430\u043d\u0438 %s \u043e\u0434 \u043a\u043e\u0448\u043d\u0438\u0447\u043a\u0430' ),
			'Qty: %d' => 'Количина: %d',
			'Subtotal' => 'Меѓузбир',
			'Estimated total' => 'Проценет вкупен износ',
			'Discount' => 'Попуст',
			'Total' => 'Вкупно',
			'View cart' => 'Види кошничка',
			'You may also like' => 'Може да ви се допадне',
			'Previous products' => 'Претходни производи',
			'Next products' => 'Следни производи',
			'B2B checkout: company and VAT details are required. Your wholesale pricing and user discount are active.' => 'B2B наплата: задолжителни се податоци за компанија и ДДВ. Вашите големопродажни цени и кориснички попуст се активни.',
			'B2C checkout: complete your billing details to finalize the order.' => 'B2C наплата: пополнете ги податоците за наплата за да ја завршите нарачката.',
			'This product is available only for approved B2B accounts.' => 'Овој производ е достапен само за одобрени B2B профили.',
			'This product is available only for approved B2B accounts. Submit your request to continue.' => 'Овој производ е достапен само за одобрени B2B профили. Поднесете барање за да продолжите.',
			'VAT / Tax Number is required for B2B checkout.' => 'ЕДБ / Даночен број е задолжителен за B2B наплата.',
			'First name' => 'Име',
			'Last name' => 'Презиме',
			'Email' => 'Е-пошта',
			'Phone' => 'Телефон',
			'Company name' => 'Име на компанија',
			'VAT / Tax number' => 'ЕДБ / Даночен број',
			'City' => 'Град',
			'Address' => 'Адреса',
			'Country' => 'Држава',
			'Select country' => 'Избери држава',
			'Message' => 'Порака',
			'Website' => 'Веб-страница',
			'Send B2B request' => 'Испрати B2B барање',
			'Invoice type' => 'Тип на фактура',
			'Company' => 'Компанија',
			'VAT' => 'ЕДB',
			'B2B discount' => 'B2B попуст',
			'Bill to' => 'Фактурира до',
			'No.' => 'Број',
			'Product' => 'Производ',
			'Quantity' => 'Количина',
			'N/A' => 'Н/Д',
			'Invoice' => 'Фактура',
			'Invoice Number:' => 'Број на фактура:',
			'Invoice Date:' => 'Датум на фактура:',
			'Order Number:' => 'Број на нарачка:',
			'Order Date:' => 'Датум на нарачка:',
			'Payment Method:' => 'Начин на плаќање:',
			'Shipping Method:' => 'Начин на испорака:',
			'Subtotal:' => 'Меѓузбир:',
			'Total:' => 'Вкупно:',
			'Proceed to Checkout' => 'Продолжи кон наплата',
			'Your cart is currently empty!' => 'Вашата кошничка моментално е празна!',
			'New in store' => 'Ново во продавницата',
			'Add to cart' => 'Додај',
			'Add to Cart' => 'Додај',
			'My account' => 'Мој профил',
			'Shop' => 'Продавница',
			'Cart totals' => 'Вкупно во кошничка',
			'Shipping' => 'Испорака',
		);

		return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
	}

	/**
	 * Translate selected plural strings on Macedonian storefront.
	 *
	 * @param string $translated Translated text.
	 * @param string $single Singular text.
	 * @param string $plural Plural text.
	 * @param int    $number Count.
	 * @param string $domain Text domain.
	 * @return string
	 */
	public static function filter_ngettext( $translated, $single, $plural, $number, $domain ) {
		if ( ! self::should_localize_request() || ! self::is_macedonian_request() ) {
			return $translated;
		}

		if ( ! in_array( (string) $domain, array( 'woodmak-b2b-core', 'woocommerce' ), true ) ) {
			return $translated;
		}

		if ( '%d item' === $single && '%d items' === $plural ) {
			return 1 === (int) $number
				? self::decode_unicode_string( '%d \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434' )
				: self::decode_unicode_string( '%d \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438' );
		}

		return $translated;
	}

	/**
	 * Translate key page titles in Macedonian.
	 *
	 * @param string $title Title.
	 * @param int    $post_id Post ID.
	 * @return string
	 */
	public static function filter_page_titles( $title, $post_id ) {
		if ( is_admin() || ! self::is_macedonian_request() ) {
			return $title;
		}

		if ( empty( $post_id ) || 'page' !== get_post_type( $post_id ) ) {
			return $title;
		}

		$slug = (string) get_post_field( 'post_name', $post_id );
		$map  = array(
			'shop'        => 'Продавница',
			'cart'        => 'Кошничка',
			'checkout'    => 'Наплата',
			'my-account'  => 'Мој профил',
			'b2b-request' => 'B2B Барање',
		);

		return isset( $map[ $slug ] ) ? $map[ $slug ] : $title;
	}

	/**
	 * Translate menu labels in Macedonian.
	 *
	 * @param string   $title Menu title.
	 * @param WP_Post  $item Menu item.
	 * @param stdClass $args Menu args.
	 * @param int      $depth Depth.
	 * @return string
	 */
	public static function filter_menu_titles( $title, $item, $args, $depth ) {
		if ( ! self::is_macedonian_request() ) {
			return $title;
		}

		$map = array(
			'Shop' => 'Продавница',
			'Cart' => 'Кошничка',
			'Checkout' => 'Наплата',
			'My account' => 'Мој профил',
			'My Account' => 'Мој профил',
			'B2B Request' => 'B2B Барање',
		);

		return isset( $map[ $title ] ) ? $map[ $title ] : $title;
	}

	/**
	 * Replace static block content strings in MK storefront pages.
	 *
	 * @param string $content Page content HTML.
	 * @return string
	 */
	public static function filter_content_strings( $content ) {
		if ( ! self::is_macedonian_request() || ! is_string( $content ) || '' === $content ) {
			return $content;
		}

		$map = array(
			'Your cart is currently empty!' => 'Вашата кошничка моментално е празна!',
			'New in store'                  => 'Ново во продавницата',
			'Estimated total'               => 'Проценет вкупен износ',
			'Cart'                          => 'Кошничка',
			'Checkout'                      => 'Наплата',
			'Shop'                          => 'Продавница',
			'My account'                    => 'Мој профил',
			'B2B Request'                   => 'B2B Барање',
		);

		return strtr( $content, $map );
	}

	/**
	 * Patch missing Woo Blocks script translations for MK storefront.
	 *
	 * @param string $translations JSON encoded translations.
	 * @param string $file Translation file path.
	 * @param string $handle Script handle.
	 * @param string $domain Text domain.
	 * @return string
	 */
	public static function filter_script_translations( $translations, $file, $handle, $domain ) {
		if ( ! self::should_localize_request() || ! self::is_macedonian_request() ) {
			return $translations;
		}

		if ( ! is_string( $translations ) || '' === $translations ) {
			return $translations;
		}

		if ( ! in_array( (string) $domain, array( 'woocommerce', 'messages' ), true ) ) {
			return $translations;
		}

		$data = json_decode( $translations, true );
		if ( ! is_array( $data ) || empty( $data['locale_data']['messages'] ) || ! is_array( $data['locale_data']['messages'] ) ) {
			return $translations;
		}

		$data['locale_data']['messages']['Estimated total']  = array( 'Проценет вкупен износ' );
		$data['locale_data']['messages']['Estimated total:'] = array( 'Проценет вкупен износ:' );

		$encoded = wp_json_encode( $data );
		return is_string( $encoded ) ? $encoded : $translations;
	}

	/**
	 * Start MK-only output buffer for last-mile text replacements.
	 *
	 * @return void
	 */
	public static function start_output_buffer() {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		ob_start( array( __CLASS__, 'filter_output_buffer' ) );
	}

	/**
	 * Replace remaining English frontend phrases in MK pages.
	 *
	 * @param string $html Full HTML output.
	 * @return string
	 */
	public static function filter_output_buffer( $html ) {
		if ( ! self::is_macedonian_request() || ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		$map = array(
			'New Collection 2026' => 'Нова колекција 2026',
			'Premium furniture solutions for retail and wholesale buyers.' => 'Премиум мебел решенија за малопродажни и големопродажни купувачи.',
			'Living Room Collection' => 'Колекција за дневна соба',
			'Dining and Kitchen Picks' => 'Избор за трпезарија и кујна',
			'B2B Request' => 'B2B Барање',
			'My account' => 'Мој профил',
			'My Account' => 'Мој профил',
		);

		return strtr( $html, $map );
	}
}


