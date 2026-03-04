<?php
/**
 * Invoice integration.
 *
 * @package woodmak-b2b-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WM_Invoices {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wpo_wcpdf_after_order_data', array( __CLASS__, 'render_role_invoice_meta' ), 10, 2 );
		add_action( 'wpo_wcpdf_custom_styles', array( __CLASS__, 'inject_custom_styles' ), 10, 2 );
	}

	/**
	 * Render role-specific invoice details.
	 *
	 * @param string         $document_type Document type.
	 * @param WC_Order|mixed $order Order object.
	 * @return void
	 */
	public static function render_role_invoice_meta( $document_type = '', $order = null ) {
		if ( ! self::is_invoice_document_type( $document_type ) ) {
			return;
		}

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$user_id      = (int) $order->get_user_id();
		$is_b2b_order = 'yes' === (string) $order->get_meta( '_wm_is_b2b_order', true );
		if ( ! $is_b2b_order && $user_id && WM_Utils::is_approved_b2b( $user_id ) ) {
			$is_b2b_order = true;
		}

		echo '<table class="wm-invoice-role"><tbody>';
		if ( $is_b2b_order ) {
			$company_name = (string) $order->get_meta( '_wm_b2b_company_name', true );
			$company_vat  = (string) $order->get_meta( '_wm_b2b_company_vat', true );
			if ( '' === $company_name ) {
				$company_name = (string) $order->get_billing_company();
			}
			if ( '' === $company_vat ) {
				$company_vat = (string) $order->get_meta( '_billing_vat', true );
			}
			if ( '' === $company_name && $user_id ) {
				$company_name = (string) get_user_meta( $user_id, '_company_name', true );
			}
			if ( '' === $company_vat && $user_id ) {
				$company_vat = (string) get_user_meta( $user_id, '_company_vat', true );
			}
			$discount_pct = (int) $order->get_meta( '_wm_b2b_discount_percent', true );
			$discount_amt = (float) $order->get_meta( '_wm_b2b_discount_amount', true );

			echo '<tr><th>' . esc_html__( 'Invoice type', 'woodmak-b2b-core' ) . '</th><td>' . esc_html__( 'B2B', 'woodmak-b2b-core' ) . '</td></tr>';
			echo '<tr><th>' . esc_html__( 'Company', 'woodmak-b2b-core' ) . '</th><td>' . esc_html( (string) $company_name ) . '</td></tr>';
			echo '<tr><th>' . esc_html__( 'VAT', 'woodmak-b2b-core' ) . '</th><td>' . esc_html( (string) $company_vat ) . '</td></tr>';
			if ( $discount_pct > 0 ) {
				echo '<tr><th>' . esc_html__( 'B2B discount', 'woodmak-b2b-core' ) . '</th><td>' . esc_html( $discount_pct . '%' ) . ' (-' . wp_kses_post( wc_price( $discount_amt, array( 'currency' => $order->get_currency() ) ) ) . ')</td></tr>';
			}
		} else {
			echo '<tr><th>' . esc_html__( 'Invoice type', 'woodmak-b2b-core' ) . '</th><td>' . esc_html__( 'B2C', 'woodmak-b2b-core' ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Inject role-aware invoice styles.
	 *
	 * @param string $document_type Document type.
	 * @param mixed  $document Document object.
	 * @return void
	 */
	public static function inject_custom_styles( $document_type, $document = null ) {
		if ( ! self::is_invoice_document_type( $document_type ) ) {
			return;
		}

		echo '.wm-invoice-role{margin-top:12px;width:100%;border-collapse:collapse;}';
		echo '.wm-invoice-role th,.wm-invoice-role td{padding:4px 6px;border:1px solid #d9d9d9;text-align:left;}';
		echo '.wm-invoice-role th{width:180px;background:#f7f7f7;}';
	}

	/**
	 * Check if document type should use invoice customizations.
	 *
	 * @param string $document_type Document type.
	 * @return bool
	 */
	private static function is_invoice_document_type( $document_type ) {
		$document_type = (string) $document_type;
		return in_array( $document_type, array( 'invoice', 'proforma', 'credit-note' ), true );
	}
}
