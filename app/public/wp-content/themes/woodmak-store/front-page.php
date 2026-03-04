<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ws-homepage">
	<?php get_template_part( 'template-parts/home/hero' ); ?>
	<?php get_template_part( 'template-parts/home/product-tabs' ); ?>
	<?php get_template_part( 'template-parts/home/stats' ); ?>
	<?php get_template_part( 'template-parts/home/category-tabs-sections' ); ?>
	<?php get_template_part( 'template-parts/home/newsletter' ); ?>
</div>
<?php
get_footer();
