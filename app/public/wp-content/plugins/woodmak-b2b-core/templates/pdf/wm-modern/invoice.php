<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
$wm_context = class_exists( 'WM_Invoices' ) ? WM_Invoices::get_invoice_context( $this->order ) : array();
$is_b2b     = ! empty( $wm_context['is_b2b'] );

$invoice_type_label = isset( $wm_context['invoice_type_label'] ) ? (string) $wm_context['invoice_type_label'] : '';
$company_name       = isset( $wm_context['company_name'] ) ? (string) $wm_context['company_name'] : '';
$customer_vat       = isset( $wm_context['customer_vat'] ) ? (string) $wm_context['customer_vat'] : '';
$discount_percent   = isset( $wm_context['discount_percent'] ) ? (int) $wm_context['discount_percent'] : 0;
$discount_html      = isset( $wm_context['discount_amount_html'] ) ? (string) $wm_context['discount_amount_html'] : '';
$shop_vat           = method_exists( $this, 'get_shop_vat_number' ) ? (string) $this->get_shop_vat_number() : '';

$wm_country_name_from_code = static function ( $country_code ) {
	if ( ! function_exists( 'WC' ) || ! WC()->countries ) {
		return (string) $country_code;
	}

	$countries = WC()->countries->get_countries();
	return isset( $countries[ $country_code ] ) ? (string) $countries[ $country_code ] : (string) $country_code;
};

$wm_render_address_lines = static function ( $lines ) {
	foreach ( $lines as $line ) {
		$line_text = '';
		$line_class = '';

		if ( is_array( $line ) ) {
			$line_text  = isset( $line['text'] ) ? (string) $line['text'] : '';
			$line_class = isset( $line['class'] ) ? (string) $line['class'] : '';
		} else {
			$line_text = (string) $line;
		}

		if ( '' === $line_text ) {
			continue;
		}

		$classes = 'wm-address-line';
		if ( '' !== $line_class ) {
			$classes .= ' ' . sanitize_html_class( $line_class );
		}

		echo '<div class="' . esc_attr( $classes ) . '">' . esc_html( $line_text ) . '</div>';
	}
};

$shop_postcode   = trim( (string) $this->get_shop_address_postcode() );
$shop_city       = trim( (string) $this->get_shop_address_city() );
$shop_line_1     = trim( (string) $this->get_shop_address_line_1() );
$shop_line_2     = trim( (string) $this->get_shop_address_line_2() );
$shop_country    = trim( (string) $this->get_shop_address_country() );
$shop_additional = trim( (string) $this->get_shop_address_additional() );

$woo_store_city      = trim( (string) get_option( 'woocommerce_store_city', '' ) );
$woo_store_postcode  = trim( (string) get_option( 'woocommerce_store_postcode', '' ) );
$woo_store_address_1 = trim( (string) get_option( 'woocommerce_store_address', '' ) );
$woo_store_address_2 = trim( (string) get_option( 'woocommerce_store_address_2', '' ) );
$woo_store_country   = trim( (string) get_option( 'woocommerce_default_country', '' ) );
$woo_country_parts   = explode( ':', $woo_store_country );
$woo_country_code    = trim( (string) $woo_country_parts[0] );
$woo_country_name    = '' !== $woo_country_code ? $wm_country_name_from_code( $woo_country_code ) : '';

if ( '' === $shop_city ) {
	$shop_city = $woo_store_city;
}
if ( '' === $shop_postcode ) {
	$shop_postcode = $woo_store_postcode;
}
if ( '' === $shop_line_1 ) {
	$shop_line_1 = $woo_store_address_1;
}
if ( '' === $shop_line_2 ) {
	$shop_line_2 = $woo_store_address_2;
}
if ( '' === $shop_country ) {
	$shop_country = $woo_country_name;
}

// Fallback for older/legacy shop address setups where city is stored in another line.
if ( '' === $shop_city && '' !== $shop_postcode ) {
	if ( '' !== $shop_line_1 ) {
		$line_1_parts    = preg_split( '/\r\n|\r|\n/', $shop_line_1 );
		$candidate_city  = is_array( $line_1_parts ) && isset( $line_1_parts[0] ) ? trim( (string) $line_1_parts[0] ) : '';
		$remaining_parts = is_array( $line_1_parts ) ? array_slice( $line_1_parts, 1 ) : array();

		if ( '' !== $candidate_city && ! preg_match( '/\d/u', $candidate_city ) ) {
			$shop_city   = $candidate_city;
			$shop_line_1 = trim( implode( "\n", $remaining_parts ) );
		}
	}

	if ( '' === $shop_city && '' !== $shop_line_2 && ! preg_match( '/\d/u', $shop_line_2 ) ) {
		$shop_city   = $shop_line_2;
		$shop_line_2 = '';
	} elseif ( '' === $shop_city && '' !== $shop_additional ) {
		$additional_lines = preg_split( '/\r\n|\r|\n/', $shop_additional );
		$candidate_city   = is_array( $additional_lines ) && isset( $additional_lines[0] ) ? trim( (string) $additional_lines[0] ) : '';
		if ( '' !== $candidate_city && ! preg_match( '/\d/u', $candidate_city ) ) {
			$shop_city = $candidate_city;
			array_shift( $additional_lines );
			$shop_additional = trim( implode( "\n", $additional_lines ) );
		}
	}
}

$shop_address_lines = array_values(
	array_filter(
		array(
			$shop_line_1,
			$shop_line_2,
			$shop_country,
			$shop_additional,
		),
		static function ( $line ) use ( $shop_city, $shop_postcode ) {
			$line = trim( (string) $line );
			if ( '' === $line ) {
				return false;
			}
			if ( '' !== $shop_city && 0 === strcasecmp( $line, $shop_city ) ) {
				return false;
			}
			if ( '' !== $shop_postcode && 0 === strcasecmp( $line, $shop_postcode ) ) {
				return false;
			}
			return true;
		}
	)
);

$billing_city_postcode_line = trim(
	implode(
		' ',
		array_filter(
			array(
				(string) $this->order->get_billing_postcode(),
				(string) $this->order->get_billing_city(),
			)
		)
	)
);
$billing_address_lines      = array_filter(
	array(
		(string) $this->order->get_formatted_billing_full_name(),
		(string) $this->order->get_billing_company(),
		(string) $this->order->get_billing_address_1(),
		(string) $this->order->get_billing_address_2(),
		$billing_city_postcode_line,
		$wm_country_name_from_code( (string) $this->order->get_billing_country() ),
	)
);

$shipping_city_postcode_line = trim(
	implode(
		' ',
		array_filter(
			array(
				(string) $this->order->get_shipping_postcode(),
				(string) $this->order->get_shipping_city(),
			)
		)
	)
);
$shipping_address_lines      = array_filter(
	array(
		(string) $this->order->get_formatted_shipping_full_name(),
		(string) $this->order->get_shipping_company(),
		(string) $this->order->get_shipping_address_1(),
		(string) $this->order->get_shipping_address_2(),
		$shipping_city_postcode_line,
		$wm_country_name_from_code( (string) $this->order->get_shipping_country() ),
	)
);
?>

<?php do_action( 'wpo_wcpdf_before_document', $this->get_type(), $this->order ); ?>

<table class="wm-head container">
	<tr>
		<td class="wm-head__logo">
			<?php if ( $this->has_header_logo() ) : ?>
				<div class="wm-logo-wrap">
					<?php do_action( 'wpo_wcpdf_before_shop_logo', $this->get_type(), $this->order ); ?>
					<?php $this->header_logo(); ?>
					<?php do_action( 'wpo_wcpdf_after_shop_logo', $this->get_type(), $this->order ); ?>
				</div>
			<?php else : ?>
				<h1 class="wm-doc-title"><?php $this->title(); ?></h1>
			<?php endif; ?>
			<?php if ( ! empty( $this->get_shop_phone_number() ) ) : ?>
				<div class="shop-phone-number"><?php $this->shop_phone_number(); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $this->get_shop_email_address() ) ) : ?>
				<div class="shop-email-address"><?php $this->shop_email_address(); ?></div>
			<?php endif; ?>
		</td>
		<td class="wm-head__shop">
			<div class="shop-name"><strong><?php $this->shop_name(); ?></strong></div>
			<?php if ( '' !== $shop_vat ) : ?>
				<div class="shop-vat"><strong><?php esc_html_e( 'VAT:', 'woodmak-b2b-core' ); ?></strong> <?php echo esc_html( $shop_vat ); ?></div>
			<?php endif; ?>
			<div class="shop-address">
				<?php if ( '' !== $shop_postcode || '' !== $shop_city ) : ?>
					<div class="wm-address-line wm-address-line--city-postcode">
						<?php
						if ( '' !== $shop_postcode && '' !== $shop_city ) {
							echo esc_html( $shop_postcode ) . '&nbsp;' . esc_html( $shop_city ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} elseif ( '' !== $shop_postcode ) {
							echo esc_html( $shop_postcode );
						} else {
							echo esc_html( $shop_city );
						}
						?>
					</div>
				<?php endif; ?>
				<?php $wm_render_address_lines( $shop_address_lines ); ?>
			</div>
			<div class="shop-extra-fields">
				<div class="shop-extra-field"><span class="shop-extra-label"><?php echo esc_html( 'Даночен број:' ); ?></span> <span class="shop-extra-value"><?php echo esc_html( '4044020518113' ); ?></span></div>
				<div class="shop-extra-field"><span class="shop-extra-label"><?php echo esc_html( 'Банка Депонент:' ); ?></span> <span class="shop-extra-value"><?php echo esc_html( 'Халк банка270074746360185' ); ?></span></div>
				<div class="shop-extra-field"><span class="shop-extra-label"><?php echo esc_html( 'Банка Депонент:' ); ?></span> <span class="shop-extra-value"><?php echo esc_html( 'Комерцијална банка300000004573198' ); ?></span></div>
			</div>
		</td>
	</tr>
</table>

<h1 class="document-type-label"><?php $this->title(); ?></h1>

<table class="wm-summary container">
	<tr>
		<td class="wm-summary__customer">
			<h3><?php esc_html_e( 'Bill to', 'woodmak-b2b-core' ); ?></h3>
			<?php do_action( 'wpo_wcpdf_before_billing_address', $this->get_type(), $this->order ); ?>
			<div class="wm-address-block">
				<?php $wm_render_address_lines( $billing_address_lines ); ?>
			</div>
			<?php do_action( 'wpo_wcpdf_after_billing_address', $this->get_type(), $this->order ); ?>
			<?php if ( isset( $this->settings['display_email'] ) ) : ?>
				<div class="billing-email"><?php $this->billing_email(); ?></div>
			<?php endif; ?>
			<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
				<div class="billing-phone"><?php $this->billing_phone(); ?></div>
			<?php endif; ?>
		</td>
		<td class="wm-summary__meta">
			<table class="wm-meta-card">
				<?php do_action( 'wpo_wcpdf_before_order_data', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_number'] ) ) : ?>
					<tr class="invoice-number">
						<th><?php $this->number_title(); ?></th>
						<td><?php $this->number( $this->get_type() ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( isset( $this->settings['display_date'] ) ) : ?>
					<tr class="invoice-date">
						<th><?php $this->date_title(); ?></th>
						<td><?php $this->date( $this->get_type() ); ?></td>
					</tr>
				<?php endif; ?>
				<tr class="order-number">
					<th><?php $this->order_number_title(); ?></th>
					<td><?php $this->order_number(); ?></td>
				</tr>
				<tr class="order-date">
					<th><?php $this->order_date_title(); ?></th>
					<td><?php $this->order_date(); ?></td>
				</tr>
				<?php if ( '' !== $invoice_type_label ) : ?>
					<tr class="invoice-type">
						<th><?php esc_html_e( 'Invoice type', 'woodmak-b2b-core' ); ?></th>
						<td><?php echo esc_html( $invoice_type_label ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( '' !== $company_name ) : ?>
					<tr class="company-name">
						<th><?php esc_html_e( 'Company', 'woodmak-b2b-core' ); ?></th>
						<td><?php echo esc_html( $company_name ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( '' !== $customer_vat ) : ?>
					<tr class="company-vat">
						<th><?php esc_html_e( 'VAT', 'woodmak-b2b-core' ); ?></th>
						<td><?php echo esc_html( $customer_vat ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( $is_b2b && $discount_percent > 0 ) : ?>
					<tr class="b2b-discount">
						<th><?php esc_html_e( 'B2B discount', 'woodmak-b2b-core' ); ?></th>
						<td>
							<?php echo esc_html( $discount_percent ); ?>%
							<?php if ( '' !== $discount_html ) : ?>
								<span class="discount-amount">(-<?php echo wp_kses_post( $discount_html ); ?>)</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_order_data', $this->get_type(), $this->order ); ?>
			</table>
		</td>
	</tr>
</table>

<?php if ( $this->show_shipping_address() ) : ?>
	<table class="wm-shipping container">
		<tr>
			<td class="wm-shipping__cell">
				<h3><?php $this->shipping_address_title(); ?></h3>
				<?php do_action( 'wpo_wcpdf_before_shipping_address', $this->get_type(), $this->order ); ?>
				<div class="wm-address-block">
					<?php
					if ( ! empty( $shipping_address_lines ) ) {
						$wm_render_address_lines( $shipping_address_lines );
					} else {
						echo '<div class="wm-address-line">' . esc_html__( 'N/A', 'woocommerce-pdf-invoices-packing-slips' ) . '</div>';
					}
					?>
				</div>
				<?php do_action( 'wpo_wcpdf_after_shipping_address', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
					<div class="shipping-phone"><?php $this->shipping_phone(); ?></div>
				<?php endif; ?>
			</td>
		</tr>
	</table>
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->get_type(), $this->order ); ?>

<table class="order-details">
	<thead>
		<tr>
			<th class="line-number"><?php esc_html_e( 'No.', 'woodmak-b2b-core' ); ?></th>
			<th class="product"><?php esc_html_e( 'Product', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="quantity"><?php esc_html_e( 'Quantity', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="price"><?php esc_html_e( 'Price', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $wm_item_index = 1; ?>
		<?php foreach ( $this->get_order_items() as $item_id => $item ) : ?>
			<tr class="<?php echo esc_html( $item['row_class'] ); ?>">
				<td class="line-number"><?php echo esc_html( $wm_item_index ); ?></td>
				<td class="product">
					<p class="item-name"><?php echo esc_html( $item['name'] ); ?></p>
					<?php do_action( 'wpo_wcpdf_before_item_meta', $this->get_type(), $item, $this->order ); ?>
					<div class="item-meta">
						<?php if ( ! empty( $item['sku'] ) ) : ?>
							<p class="sku"><span class="label"><?php $this->sku_title(); ?></span> <?php echo esc_attr( $item['sku'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $item['weight'] ) ) : ?>
							<p class="weight"><span class="label"><?php $this->weight_title(); ?></span> <?php echo esc_attr( $item['weight'] ); ?><?php echo esc_attr( get_option( 'woocommerce_weight_unit' ) ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $item['meta'] ) ) : ?>
							<?php echo wp_kses_post( $item['meta'] ); ?>
						<?php endif; ?>
					</div>
					<?php do_action( 'wpo_wcpdf_after_item_meta', $this->get_type(), $item, $this->order ); ?>
				</td>
				<td class="quantity"><?php echo esc_html( $item['quantity'] ); ?></td>
				<td class="price"><?php echo esc_html( $item['order_price'] ); ?></td>
			</tr>
			<?php $wm_item_index++; ?>
		<?php endforeach; ?>
	</tbody>
</table>

<table class="notes-totals">
	<tbody>
		<tr class="no-borders">
			<td class="no-borders notes-cell">
				<?php do_action( 'wpo_wcpdf_before_document_notes', $this->get_type(), $this->order ); ?>
				<?php if ( $this->get_document_notes() ) : ?>
					<div class="document-notes">
						<h3><?php $this->notes_title(); ?></h3>
						<?php $this->document_notes(); ?>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_document_notes', $this->get_type(), $this->order ); ?>
				<?php do_action( 'wpo_wcpdf_before_customer_notes', $this->get_type(), $this->order ); ?>
				<?php if ( $this->get_shipping_notes() ) : ?>
					<div class="customer-notes">
						<h3><?php $this->customer_notes_title(); ?></h3>
						<?php $this->shipping_notes(); ?>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->get_type(), $this->order ); ?>
			</td>
			<td class="no-borders totals-cell">
				<table class="totals">
					<tfoot>
						<?php foreach ( $this->get_woocommerce_totals() as $key => $total ) : ?>
							<tr class="<?php echo esc_attr( $key ); ?>">
								<th class="description"><?php echo esc_html( $total['label'] ); ?></th>
								<td class="price"><span class="totals-price"><?php echo esc_html( $total['value'] ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tfoot>
				</table>
			</td>
		</tr>
	</tbody>
</table>

<?php do_action( 'wpo_wcpdf_after_order_details', $this->get_type(), $this->order ); ?>

<div class="bottom-spacer"></div>

<?php if ( $this->get_footer() ) : ?>
	<htmlpagefooter name="docFooter"><!-- required for mPDF engine -->
		<div id="footer">
			<?php $this->footer(); ?>
		</div>
	</htmlpagefooter><!-- required for mPDF engine -->
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_document', $this->get_type(), $this->order ); ?>
