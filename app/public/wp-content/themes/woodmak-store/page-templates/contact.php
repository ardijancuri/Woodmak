<?php
/*
Template Name: Contact
*/

get_header();

$contact_details = function_exists( 'ws_get_contact_details' ) ? ws_get_contact_details() : array();
$brand_title     = isset( $contact_details['brand_title'] ) ? $contact_details['brand_title'] : get_bloginfo( 'name' );
$page_heading    = isset( $contact_details['page_heading'] ) ? $contact_details['page_heading'] : ( function_exists( 'ws_unicode_string' ) ? ws_unicode_string( '\u041a\u043e\u043d\u0442\u0430\u043a\u0442\u0438\u0440\u0430\u0458\u0442\u0435 \u043d\u0435' ) : 'Contact us' );
$address_label   = isset( $contact_details['address_label'] ) ? $contact_details['address_label'] : ( function_exists( 'ws_unicode_string' ) ? ws_unicode_string( '\u0410\u0414\u0420\u0415\u0421\u0410' ) : 'Address' );
$address_text    = isset( $contact_details['address_text'] ) ? $contact_details['address_text'] : '';
$phone_label     = isset( $contact_details['phone_label'] ) ? $contact_details['phone_label'] : ( function_exists( 'ws_unicode_string' ) ? ws_unicode_string( '\u0422\u0415\u041b\u0415\u0424\u041e\u041d' ) : 'Phone' );
$phone_text      = isset( $contact_details['phone_text'] ) ? $contact_details['phone_text'] : '';
$phone_href      = isset( $contact_details['phone_href'] ) ? $contact_details['phone_href'] : '';
$email_label     = isset( $contact_details['email_label'] ) ? $contact_details['email_label'] : ( function_exists( 'ws_unicode_string' ) ? ws_unicode_string( '\u0415\u041c\u0410\u0418\u041b' ) : 'Email' );
$email_text      = isset( $contact_details['email_text'] ) ? $contact_details['email_text'] : '';
$email_href      = isset( $contact_details['email_href'] ) ? $contact_details['email_href'] : '';
$social_label    = isset( $contact_details['social_label'] ) ? $contact_details['social_label'] : ( function_exists( 'ws_unicode_string' ) ? ws_unicode_string( '\u0421\u041b\u0415\u0414\u0418 \u041d\u0415' ) : 'Follow us' );
$facebook_url    = isset( $contact_details['facebook_url'] ) ? $contact_details['facebook_url'] : '';
$instagram_url   = isset( $contact_details['instagram_url'] ) ? $contact_details['instagram_url'] : '';
?>
<section class="ws-contact-page">
	<div class="ws-container">
		<div class="ws-contact-page__intro">
			<p class="ws-contact-page__brand"><?php echo esc_html( $brand_title ); ?></p>
			<h1 class="ws-contact-page__title"><?php echo esc_html( $page_heading ); ?></h1>
		</div>

		<div class="ws-contact-grid">
			<section class="ws-contact-grid__item">
				<h2 class="ws-contact-grid__label"><?php echo esc_html( $address_label ); ?></h2>
				<div class="ws-contact-grid__value">
					<p><?php echo nl2br( esc_html( $address_text ) ); ?></p>
				</div>
			</section>

			<section class="ws-contact-grid__item">
				<h2 class="ws-contact-grid__label"><?php echo esc_html( $phone_label ); ?></h2>
				<div class="ws-contact-grid__value">
					<?php if ( '' !== $phone_text ) : ?>
						<p>
							<?php if ( '' !== $phone_href ) : ?>
								<a href="<?php echo esc_url( $phone_href ); ?>"><?php echo esc_html( $phone_text ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $phone_text ); ?>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</div>
			</section>

			<section class="ws-contact-grid__item">
				<h2 class="ws-contact-grid__label"><?php echo esc_html( $email_label ); ?></h2>
				<div class="ws-contact-grid__value">
					<?php if ( '' !== $email_text ) : ?>
						<p>
							<?php if ( '' !== $email_href ) : ?>
								<a href="<?php echo esc_url( $email_href ); ?>"><?php echo esc_html( $email_text ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $email_text ); ?>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</div>
			</section>

			<section class="ws-contact-grid__item">
				<h2 class="ws-contact-grid__label"><?php echo esc_html( $social_label ); ?></h2>
				<div class="ws-contact-grid__value">
					<p class="ws-contact-grid__social">
						<?php if ( '' !== $facebook_url ) : ?>
							<a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook', 'woodmak-store' ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $facebook_url && '' !== $instagram_url ) : ?>
							<span aria-hidden="true">|</span>
						<?php endif; ?>
						<?php if ( '' !== $instagram_url ) : ?>
							<a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Instagram', 'woodmak-store' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			</section>
		</div>
	</div>
</section>
<?php
get_footer();
