<?php
/*
Template Name: B2B Request
*/

get_header();
?>
<section class="ws-hero ws-hero--b2b">
	<div class="ws-container">
		<h1><?php esc_html_e( 'B2B Account Request', 'woodmak-store' ); ?></h1>
		<p><?php esc_html_e( 'Apply for wholesale access by completing the form below.', 'woodmak-store' ); ?></p>
	</div>
</section>
<div class="ws-container ws-b2b-page">
	<?php echo do_shortcode( '[wm_b2b_request_form]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<?php
get_footer();
