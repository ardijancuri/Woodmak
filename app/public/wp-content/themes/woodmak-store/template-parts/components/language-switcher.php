<?php
if ( function_exists( 'pll_the_languages' ) ) {
	$items = pll_the_languages(
		array(
			'raw'           => 1,
			'hide_if_empty' => 0,
		)
	);
	if ( ! empty( $items ) && is_array( $items ) ) {
		echo '<ul class="ws-lang-switcher">';
		foreach ( $items as $item ) {
			$class = ! empty( $item['current_lang'] ) ? ' class="is-current"' : '';
			echo '<li' . $class . '><a href="' . esc_url( $item['url'] ) . '">' . esc_html( strtoupper( $item['slug'] ) ) . '</a></li>';
		}
		echo '</ul>';
	}
}
