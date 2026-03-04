<?php
/*
Template Name: Full Width
*/

get_header();
?>
<div class="ws-container ws-container--wide">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; ?>
</div>
<?php
get_footer();
