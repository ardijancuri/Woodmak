<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
	)
);
if ( empty( $posts ) ) {
	return;
}

$news_url = get_permalink( get_option( 'page_for_posts' ) );
if ( ! $news_url ) {
	$news_url = home_url( '/' );
}
?>
<section class="ws-home-section ws-home-section--news">
	<div class="ws-container">
		<div class="ws-section-heading">
			<h2><?php esc_html_e( 'What\'s New', 'woodmak-store' ); ?></h2>
			<a href="<?php echo esc_url( $news_url ); ?>"><?php esc_html_e( 'More', 'woodmak-store' ); ?></a>
		</div>
		<div class="ws-news-grid">
			<?php foreach ( $posts as $post ) : ?>
				<article class="ws-news-card">
					<?php if ( has_post_thumbnail( $post ) ) : ?>
						<a class="ws-news-card__image" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
							<?php echo get_the_post_thumbnail( $post, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
					<div class="ws-news-card__body">
						<p class="ws-news-card__meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></p>
						<h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 14 ) ); ?></p>
						<a class="button" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'See all', 'woodmak-store' ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
