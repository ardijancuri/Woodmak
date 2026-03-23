<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ranked_categories = ws_get_ranked_product_categories();
if ( empty( $ranked_categories ) ) {
	return;
}

$section_a_categories = ws_select_product_categories( $ranked_categories, 10, 5 );
$section_a_ids        = wp_list_pluck( $section_a_categories, 'term_id' );
$section_b_categories = ws_select_product_categories( $ranked_categories, 15, 5, $section_a_ids );

$sections = array(
	array(
		'id'         => 'ws-home-category-tabs-a',
		'title'      => ws_get_theme_mod_text( 'ws_home_category_tabs_section_a_title', __( 'Category Highlights', 'woodmak-store' ) ),
		'categories' => $section_a_categories,
	),
	array(
		'id'         => 'ws-home-category-tabs-b',
		'title'      => ws_get_theme_mod_text( 'ws_home_category_tabs_section_b_title', __( 'More Category Picks', 'woodmak-store' ) ),
		'categories' => $section_b_categories,
	),
);

$faq_items = array(
	array(
		'number'   => '1',
		'question' => 'Што е самосклоплив мебел?',
		'answer'   => 'Самосклопливиот мебел е дизајниран да го составите сами дома, без потреба од специјални алати или сложено искуство. Секој производ доаѓа со упатство за лесно склопување.',
	),
	array(
		'number'   => '2',
		'question' => 'Кои алати ми се потребни за склопување?',
		'answer'   => 'Во повеќето случаи, потребни Ви се само основни алати како штрафцигер. Сите производи доаѓаат со алатки вклучени во пакетот со производот.',
	),
	array(
		'number'   => '3',
		'question' => 'Колку време обично е потребно за склопување на мебелот?',
		'answer'   => 'Времето зависи од типот и големината на производот. На пример, полица може да се склопи за 10-15 минути, а плакар за 2-3 часа.',
	),
	array(
		'number'   => '4',
		'question' => 'Дали мебелот е издржлив?',
		'answer'   => 'Да. Нашиот мебел е произведен од квалитетни материјали и е тестиран за издржливост и стабилност.',
	),
	array(
		'number'   => '5',
		'question' => 'Може ли да го вратам или заменам производот?',
		'answer'   => 'Да, нудиме поврат или замена во случај на производствен дефект.',
	),
	array(
		'number'   => '6',
		'question' => 'Дали имате упатство за склопување?',
		'answer'   => 'Секој производ доаѓа со јасно и детално упатство за склопување. Исто така, нудиме видео инструкции на нашата веб-страница за дополнителна помош.',
	),
	array(
		'number'   => '8',
		'question' => 'Дали мебелот е безбеден за деца и домашни миленици?',
		'answer'   => 'Да, користиме нетоксични материјали и безбедни завршни обработки.',
	),
	array(
		'number'   => '9',
		'question' => 'Каде можам да го купам вашиот мебел?',
		'answer'   => 'Мебелот може да се купи директно преку нашата веб-страница.',
	),
	array(
		'number'   => '10',
		'question' => 'Како да знам дали мебелот ќе се вклопи во мојот простор?',
		'answer'   => 'Секој производ има точни димензии наведени на веб-страницата, за да можете лесно да проверите дали се вклопува.',
	),
	array(
		'number'   => '11',
		'question' => 'Дали има ограничувања за тежината што мебелот може да ја издржи?',
		'answer'   => 'Да, сите производи имаат наведена максимална тежина. Проверете ја спецификацијата на секој производ.',
	),
	array(
		'number'   => '12',
		'question' => 'Што ако ми недостасува дел од пакетот?',
		'answer'   => 'Контактирајте не веднаш и ќе ви испратиме недостасувачки дел.',
	),
	array(
		'number'   => '13',
		'question' => 'Дали мебелот е отпорен на вода и влажност?',
		'answer'   => 'Мебелот е отпорен на нормални услови на користење, но не е погоден за директен контакт со вода.',
	),
	array(
		'number'   => '14',
		'question' => 'Колку време е потребно да пристигне мојата нарачка?',
		'answer'   => 'Времето за испорака зависи од локацијата и типот на производот. За производ кој го имаме на залиха, времето на испорака е три до пет работни дена, а за производ кој го немаме на залиха времето на испорака е од 10 до 15 дена.',
	),
	array(
		'number'   => '15',
		'question' => 'Дали нудите достава до врата?',
		'answer'   => 'Да, достава до врата е достапна за сите производи на испорака. Испораката ја вршиме преку курирската служба Еко Логистик.',
	),
	array(
		'number'   => '16',
		'question' => 'Како да го чистам и одржувам мебелот?',
		'answer'   => 'Препорачуваме да се користи мека крпа и нежни средства за чистење. Детални упатства се во пакетот.',
	),
	array(
		'number'   => '17',
		'question' => 'Дали мебелот доаѓа веќе собран?',
		'answer'   => 'Не, сите производи се самосклопливи за полесна испорака и транспорт.',
	),
	array(
		'number'   => '18',
		'question' => 'Дали мебелот е пријателски за околината?',
		'answer'   => 'Да, користиме еколошки материјали и процеси кои се одржливи.',
	),
	array(
		'number'   => '19',
		'question' => 'Што ако забележам оштетување по испорака?',
		'answer'   => 'Веднаш контактирајте го нашиот сервис за поддршка и ние ќе обезбедиме замена или поправка.',
	),
	array(
		'number'   => '20',
		'question' => 'Може ли да нарачам различни бои за ист производ?',
		'answer'   => 'Да, нудиме избор на бои и завршни обработки за повеќето производи.',
	),
	array(
		'number'   => '21',
		'question' => 'Може ли мебелот да се користи на отворено или на тераса?',
		'answer'   => 'Нашиот мебел е дизајниран за внатрешна употреба.',
	),
);

$faq_columns = array();
if ( ! empty( $faq_items ) ) {
	$faq_columns = array_chunk( $faq_items, (int) ceil( count( $faq_items ) / 2 ) );
}

foreach ( $sections as $section ) :
	$categories = $section['categories'];
	if ( empty( $categories ) ) {
		continue;
	}

	$products_by_tab = array();
	$has_products    = false;

	foreach ( $categories as $category ) {
		if ( ! $category instanceof WP_Term ) {
			continue;
		}

		$products = ws_get_home_category_products( $category, 5 );
		if ( ! empty( $products ) ) {
			$has_products = true;
		}
		$products_by_tab[ absint( $category->term_id ) ] = $products;
	}

	if ( ! $has_products ) {
		continue;
	}
	?>
	<section class="ws-home-section ws-home-section--product-tabs ws-home-section--category-tabs">
		<div class="ws-container">
			<div class="ws-section-heading">
				<h2><?php echo esc_html( $section['title'] ); ?></h2>
			</div>
			<div class="ws-product-tabs" data-ws-product-tabs>
				<div class="ws-product-tabs__head" role="tablist" aria-label="<?php esc_attr_e( 'Homepage category tabs', 'woodmak-store' ); ?>">
					<?php $is_first = true; ?>
					<?php foreach ( $categories as $category ) : ?>
						<?php if ( ! $category instanceof WP_Term ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php
						$tab_id = $section['id'] . '-tab-' . absint( $category->term_id );
						?>
						<button
							type="button"
							class="ws-product-tabs__tab<?php echo $is_first ? ' is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr( $tab_id ); ?>"
							data-ws-tab-trigger="<?php echo esc_attr( $tab_id ); ?>"
						>
							<?php echo esc_html( $category->name ); ?>
						</button>
						<?php $is_first = false; ?>
					<?php endforeach; ?>
				</div>

				<?php $is_first = true; ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php if ( ! $category instanceof WP_Term ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php
					$tab_id = $section['id'] . '-tab-' . absint( $category->term_id );
					?>
					<div
						id="<?php echo esc_attr( $tab_id ); ?>"
						class="ws-product-tabs__panel<?php echo $is_first ? ' is-active' : ''; ?>"
						role="tabpanel"
						<?php if ( ! $is_first ) : ?>hidden<?php endif; ?>
						data-ws-tab-panel="<?php echo esc_attr( $tab_id ); ?>"
					>
						<?php ws_render_home_products( $products_by_tab[ absint( $category->term_id ) ], 'ws-home-products--tabs' ); ?>
					</div>
					<?php $is_first = false; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php if ( 'ws-home-category-tabs-b' === $section['id'] && ! empty( $faq_items ) ) : ?>
		<section class="ws-home-section ws-home-section--faq">
			<div class="ws-container">
				<div class="ws-home-faq">
					<div class="ws-home-faq__intro">
						<p class="ws-home-faq__eyebrow">FAQ</p>
						<div class="ws-section-heading">
							<h2>Често поставувани прашања (FAQ)</h2>
							<p>Одговори на најчестите прашања за склопување, испорака, одржување и користење на мебелот.</p>
						</div>
					</div>
					<div class="ws-home-faq__list" data-ws-faq>
						<?php $faq_opened = false; ?>
						<?php foreach ( $faq_columns as $faq_column ) : ?>
							<div class="ws-home-faq__column">
								<?php foreach ( $faq_column as $faq_item ) : ?>
									<details class="ws-home-faq__item"<?php echo ! $faq_opened ? ' open' : ''; ?>>
										<summary class="ws-home-faq__question">
											<span class="ws-home-faq__index"><?php echo esc_html( $faq_item['number'] ); ?></span>
											<span class="ws-home-faq__question-text"><?php echo esc_html( $faq_item['question'] ); ?></span>
										</summary>
										<div class="ws-home-faq__answer">
											<p><?php echo esc_html( $faq_item['answer'] ); ?></p>
										</div>
									</details>
									<?php $faq_opened = true; ?>
								<?php endforeach; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>
<?php endforeach; ?>
