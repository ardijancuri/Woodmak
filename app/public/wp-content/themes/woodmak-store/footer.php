<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$footer_logo_url = ws_get_theme_mod_image_url( 'ws_footer_logo' );
$contact_details = function_exists( 'ws_get_contact_details' ) ? ws_get_contact_details() : array();
$phone_text      = isset( $contact_details['phone_text'] ) ? $contact_details['phone_text'] : '+389 75 317 372';
$phone_href      = isset( $contact_details['phone_href'] ) ? $contact_details['phone_href'] : 'tel:+38975317372';
$email_text      = isset( $contact_details['email_text'] ) ? $contact_details['email_text'] : 'info@woodmak.mk';
$email_href      = isset( $contact_details['email_href'] ) ? $contact_details['email_href'] : 'mailto:info@woodmak.mk';
$facebook_url    = isset( $contact_details['facebook_url'] ) ? $contact_details['facebook_url'] : '';
$instagram_url   = isset( $contact_details['instagram_url'] ) ? $contact_details['instagram_url'] : '';
if ( empty( $footer_logo_url ) ) {
	$footer_logo_url = ws_get_theme_mod_image_url( 'custom_logo' );
}
?>
</main>
<footer class="ws-footer">
	<div class="ws-container ws-footer__top">
		<section class="ws-footer__brand" aria-label="<?php esc_attr_e( 'Footer brand', 'woodmak-store' ); ?>">
			<div class="ws-footer__brand-logo">
				<?php if ( ! empty( $footer_logo_url ) ) : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img class="custom-logo" src="<?php echo esc_url( $footer_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
				<?php endif; ?>
			</div>
			<p class="ws-footer__brand-copy"><?php esc_html_e( 'Furniture for retail, interior projects, and wholesale partners.', 'woodmak-store' ); ?></p>
			<?php if ( ! empty( $instagram_url ) || ! empty( $facebook_url ) ) : ?>
				<div class="ws-footer__social" aria-label="<?php esc_attr_e( 'Social links', 'woodmak-store' ); ?>">
					<?php if ( ! empty( $instagram_url ) ) : ?>
						<a class="ws-footer__social-link" href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Instagram', 'woodmak-store' ); ?>">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<rect x="3.75" y="3.75" width="16.5" height="16.5" rx="4.2" stroke="currentColor" stroke-width="1.8" fill="none"></rect>
								<circle cx="12" cy="12" r="4.1" stroke="currentColor" stroke-width="1.8" fill="none"></circle>
								<circle cx="17.3" cy="6.7" r="1.2" fill="currentColor"></circle>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $facebook_url ) ) : ?>
						<a class="ws-footer__social-link" href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Facebook', 'woodmak-store' ); ?>">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<path d="M13.2 20V12.9H15.9L16.3 9.9H13.2V8C13.2 7.12 13.46 6.5 14.75 6.5H16.4V3.82C16.12 3.78 15.15 3.7 14.02 3.7C11.67 3.7 10.1 5.06 10.1 7.73V9.9H7.5V12.9H10.1V20H13.2Z" fill="currentColor"></path>
							</svg>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</section>

		<div class="ws-footer__links">
			<section class="ws-footer__column">
				<h4><?php esc_html_e( 'Information', 'woodmak-store' ); ?></h4>
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'fallback_cb'    => false,
						)
					);
					?>
				<?php else : ?>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></li>
						<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Catalog', 'woodmak-store' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>"><?php esc_html_e( 'My Account', 'woodmak-store' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</section>

			<section class="ws-footer__column">
				<h4><?php esc_html_e( 'For Partners', 'woodmak-store' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/b2b-request/' ) ); ?>"><?php esc_html_e( 'B2B Request', 'woodmak-store' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>"><?php esc_html_e( 'Account', 'woodmak-store' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/my-account/orders/' ) ); ?>"><?php esc_html_e( 'Order Tracking', 'woodmak-store' ); ?></a></li>
				</ul>
			</section>

			<section class="ws-footer__column">
				<h4><?php esc_html_e( 'Contact', 'woodmak-store' ); ?></h4>
				<ul>
					<li>
						<?php if ( ! empty( $phone_href ) ) : ?>
							<a href="<?php echo esc_url( $phone_href ); ?>"><?php echo esc_html( $phone_text ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $phone_text ); ?>
						<?php endif; ?>
					</li>
					<li>
						<?php if ( ! empty( $email_href ) ) : ?>
							<a href="<?php echo esc_url( $email_href ); ?>"><?php echo esc_html( $email_text ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $email_text ); ?>
						<?php endif; ?>
					</li>
					<li><?php esc_html_e( 'Mon-Fri: 08:00 - 16:00', 'woodmak-store' ); ?></li>
				</ul>
			</section>
		</div>
	</div>
	<div class="ws-container ws-footer__bottom">
		<p>
			<?php echo esc_html( date_i18n( 'Y' ) ); ?>
			<?php bloginfo( 'name' ); ?>.
			<?php esc_html_e( 'Powered by', 'woodmak-store' ); ?>
			<a href="https://oninova.net" target="_blank" rel="noopener noreferrer">Oninova</a>
		</p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
