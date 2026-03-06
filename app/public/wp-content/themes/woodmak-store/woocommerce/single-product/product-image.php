<?php
/**
 * Theme-controlled single product gallery.
 *
 * @package woodmak-store
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$image_ids = array();
$main_id   = absint( $product->get_image_id() );

if ( $main_id ) {
	$image_ids[] = $main_id;
}

foreach ( (array) $product->get_gallery_image_ids() as $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( $attachment_id && ! in_array( $attachment_id, $image_ids, true ) ) {
		$image_ids[] = $attachment_id;
	}
}
?>
<div class="ws-product-gallery images" data-ws-product-gallery>
	<div class="ws-product-gallery__stage">
		<?php if ( ! empty( $image_ids ) ) : ?>
			<?php foreach ( $image_ids as $index => $attachment_id ) : ?>
				<figure class="ws-product-gallery__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>>
					<?php
					echo wp_get_attachment_image(
						$attachment_id,
						'woocommerce_single',
						false,
						array(
							'class' => 'ws-product-gallery__image',
							'alt'   => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
						)
					);
					?>
				</figure>
			<?php endforeach; ?>
		<?php else : ?>
			<figure class="ws-product-gallery__slide is-active">
				<img class="ws-product-gallery__image" src="<?php echo esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ); ?>" alt="<?php esc_attr_e( 'Awaiting product image', 'woocommerce' ); ?>" />
			</figure>
		<?php endif; ?>
	</div>

	<?php if ( count( $image_ids ) > 1 ) : ?>
		<ol class="ws-product-gallery__thumbs">
			<?php foreach ( $image_ids as $index => $attachment_id ) : ?>
				<li class="ws-product-gallery__thumb-item">
					<button type="button" class="ws-product-gallery__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" data-ws-gallery-thumb="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show image %d', 'woodmak-store' ), $index + 1 ) ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>">
						<?php
						echo wp_get_attachment_image(
							$attachment_id,
							'woocommerce_gallery_thumbnail',
							false,
							array(
								'class' => 'ws-product-gallery__thumb-image',
								'alt'   => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
							)
						);
						?>
					</button>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>
