<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = array(
	array(
		'icon'  => '+',
		'label' => __( 'Secure Payment', 'woodmak-store' ),
	),
	array(
		'icon'  => '+',
		'label' => __( 'Fast Dispatch', 'woodmak-store' ),
	),
	array(
		'icon'  => '+',
		'label' => __( 'Contract Furniture', 'woodmak-store' ),
	),
	array(
		'icon'  => '+',
		'label' => __( 'Wholesale Support', 'woodmak-store' ),
	),
	array(
		'icon'  => '+',
		'label' => __( 'Warranty Included', 'woodmak-store' ),
	),
	array(
		'icon'  => '+',
		'label' => __( 'Local Service', 'woodmak-store' ),
	),
);
?>
<section class="ws-usp-strip">
	<div class="ws-container">
		<ul class="ws-usp-strip__list">
			<?php foreach ( $items as $item ) : ?>
				<li class="ws-usp-strip__item">
					<span class="ws-usp-strip__icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
					<span><?php echo esc_html( $item['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
