<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mega_categories = ws_get_megamenu_product_categories();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="ws-header">
	<div class="ws-topbar">
		<div class="ws-container ws-topbar__inner">
			<div class="ws-topbar__left">
				<span><?php esc_html_e( 'Premium Furniture for Retail, Interior Projects, and Wholesale Partners', 'woodmak-store' ); ?></span>
			</div>
			<div class="ws-topbar__right">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'utility',
						'container'      => false,
						'fallback_cb'    => false,
					)
				);
				?>
			</div>
		</div>
	</div>

	<div class="ws-header-main">
		<div class="ws-container ws-header-main__inner">
			<div class="ws-brand">
				<?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
				<?php endif; ?>
			</div>
			<form class="ws-product-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label for="ws_product_search" class="screen-reader-text"><?php esc_html_e( 'Search products', 'woodmak-store' ); ?></label>
				<input id="ws_product_search" type="search" name="s" placeholder="<?php echo esc_attr__( 'Search products...', 'woodmak-store' ); ?>" />
				<input type="hidden" name="post_type" value="product" />
			</form>
			<div class="ws-header__actions">
				<?php get_template_part( 'template-parts/components/language-switcher' ); ?>
				<a class="ws-account-link ws-icon-link" href="<?php echo esc_url( home_url( '/мој-профил/' ) ); ?>">
					<span class="ws-icon-link__icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" focusable="false">
							<path d="M20 21C20 17.6863 16.4183 15 12 15C7.58172 15 4 17.6863 4 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
							<circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"></circle>
						</svg>
					</span>
					<span class="ws-icon-link__label"><?php esc_html_e( 'Account', 'woodmak-store' ); ?></span>
				</a>
				<button class="ws-cart-toggle ws-icon-link" data-wm-cart-open type="button">
					<span class="ws-icon-link__icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" focusable="false">
							<path d="M3 4H5L7.2 14.2C7.36781 14.9681 8.04995 15.5 8.83618 15.5H17.7C18.4862 15.5 19.1684 14.9681 19.3362 14.2L21 7.5H6.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
							<circle cx="9.5" cy="19.5" r="1.5" fill="currentColor"></circle>
							<circle cx="17.5" cy="19.5" r="1.5" fill="currentColor"></circle>
						</svg>
					</span>
					<span class="ws-icon-link__label"><?php esc_html_e( 'Cart', 'woodmak-store' ); ?></span>
				</button>
			</div>
			<button class="ws-nav-toggle" type="button" data-ws-nav-toggle aria-expanded="false" aria-controls="ws-mobile-nav">
				<span class="ws-nav-toggle__icon" aria-hidden="true">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" focusable="false">
						<path d="M4 6H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
						<path d="M4 12H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
						<path d="M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
					</svg>
				</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'woodmak-store' ); ?></span>
			</button>
		</div>
	</div>

	<div class="ws-category-bar">
		<div class="ws-container">
			<div class="ws-category-bar__inner">
				<button class="ws-categories-toggle" type="button" data-ws-categories-toggle aria-expanded="false" aria-controls="ws-category-mega">
					<span class="ws-categories-toggle__icon" aria-hidden="true">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" focusable="false">
							<path d="M4 6H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
							<path d="M4 12H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
							<path d="M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
						</svg>
					</span>
					<span><?php esc_html_e( 'Categories', 'woodmak-store' ); ?></span>
				</button>
				<nav id="ws-main-nav" class="ws-nav" data-ws-nav-links aria-label="<?php esc_attr_e( 'Main navigation', 'woodmak-store' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			</div>
			<div id="ws-category-mega" class="ws-category-mega" data-ws-category-mega hidden>
				<?php if ( ! empty( $mega_categories ) ) : ?>
					<ul class="ws-category-mega__grid">
						<?php foreach ( $mega_categories as $category ) : ?>
							<?php
							$category_url = get_term_link( $category );
							if ( is_wp_error( $category_url ) ) {
								continue;
							}
							?>
							<li class="ws-category-mega__item">
								<a class="ws-category-mega__card" href="<?php echo esc_url( $category_url ); ?>">
									<span><?php echo esc_html( $category->name ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="ws-category-mega__empty"><?php esc_html_e( 'No categories found yet.', 'woodmak-store' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<aside id="ws-mobile-nav" class="ws-mobile-nav" aria-hidden="true">
		<div class="ws-mobile-nav__header">
			<div class="ws-mobile-nav__brand">
				<?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
				<?php endif; ?>
			</div>
			<button class="ws-mobile-nav__close" type="button" data-ws-nav-close aria-label="<?php echo esc_attr__( 'Close menu', 'woodmak-store' ); ?>">
				<span class="ws-mobile-nav__close-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
						<path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
						<path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
					</svg>
				</span>
			</button>
		</div>
		<div class="ws-mobile-nav__categories">
			<button class="ws-mobile-nav__categories-toggle" type="button" data-ws-mobile-categories-toggle aria-expanded="false" aria-controls="ws-mobile-categories-panel">
				<span><?php esc_html_e( 'Categories', 'woodmak-store' ); ?></span>
				<span class="ws-mobile-nav__categories-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
						<path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
					</svg>
				</span>
			</button>
			<div id="ws-mobile-categories-panel" class="ws-mobile-nav__categories-panel" data-ws-mobile-categories-panel hidden>
				<?php if ( ! empty( $mega_categories ) ) : ?>
					<ul class="ws-mobile-nav__categories-list">
						<?php foreach ( $mega_categories as $category ) : ?>
							<?php
							$category_url = get_term_link( $category );
							if ( is_wp_error( $category_url ) ) {
								continue;
							}
							?>
							<li>
								<a href="<?php echo esc_url( $category_url ); ?>"><?php echo esc_html( $category->name ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="ws-mobile-nav__categories-empty"><?php esc_html_e( 'No categories found yet.', 'woodmak-store' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<nav class="ws-mobile-nav__menu" data-ws-nav-links aria-label="<?php esc_attr_e( 'Mobile main navigation', 'woodmak-store' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
		<nav class="ws-mobile-nav__utility" aria-label="<?php esc_attr_e( 'Mobile utility navigation', 'woodmak-store' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'utility',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</aside>
	<div class="ws-mobile-nav__overlay" data-ws-nav-close></div>
</header>
<main class="ws-main">
