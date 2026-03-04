<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$background = ws_get_theme_mod_image_url( 'ws_home_newsletter_bg_image' );
$style_attr = '';
if ( $background ) {
	$style_attr = ' style="background-image:url(' . esc_url( $background ) . ');"';
}
?>
<section class="ws-home-section ws-home-section--newsletter">
	<div class="ws-container ws-newsletter"<?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="ws-newsletter__copy">
			<p class="ws-kicker"><?php esc_html_e( 'Newsletter', 'woodmak-store' ); ?></p>
			<h2><?php esc_html_e( 'You must accept the terms and conditions.', 'woodmak-store' ); ?></h2>
		</div>
		<form class="ws-newsletter__form" action="#" method="post">
			<label for="ws_newsletter_email" class="screen-reader-text"><?php esc_html_e( 'Email address', 'woodmak-store' ); ?></label>
			<input id="ws_newsletter_email" type="email" placeholder="<?php echo esc_attr__( 'Enter your email', 'woodmak-store' ); ?>" />
			<button type="submit" class="button alt"><?php esc_html_e( 'Sign up', 'woodmak-store' ); ?></button>
		</form>
	</div>
</section>
