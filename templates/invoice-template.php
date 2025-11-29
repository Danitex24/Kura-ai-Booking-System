<?php
/**
 * Kura-ai Booking System - Invoice Template
 *
 * HTML template for invoice PDF generation
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get plugin settings
$company_name = get_option( 'kab_company_name', get_bloginfo( 'name' ) );
$company_logo = get_option( 'kab_company_logo', '' );
$support_email = get_option( 'kab_support_email', get_option( 'admin_email' ) );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Invoice <?php echo esc_html( $invoice['invoice_number'] ); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; line-height: 1.4; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        h2 { font-size: 16px; margin: 0 0 8px; }
        h3 { font-size: 13px; margin: 12px 0 6px; }
        .invoice-header { border-bottom: 2px solid #3a3a3a; padding-bottom: 12px; margin-bottom: 20px; }
        .invoice-logo { max-width: 120px; margin-bottom: 12px; }
        .invoice-details { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .invoice-details th { background-color: #f8f9fa; text-align: left; padding: 8px; border: 1px solid #dee2e6; font-weight: 600; }
        .invoice-details td { padding: 8px; border: 1px solid #dee2e6; }
        .invoice-summary-table { width: 100%; margin-top: 12px; border-collapse: collapse; }
        .invoice-summary-table td { vertical-align: top; }
        .invoice-totals { width: 200px; margin-top: 0; }
        .invoice-totals table { width: 200px; }
        .invoice-totals td { padding: 3px 5px; }
        .invoice-totals .total-row { border-top: 2px solid #3a3a3a; font-weight: bold; }
        .invoice-footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .brand-color { color: #3a3a3a; }
    </style>
</head>
<body>
	<div class="invoice-header">
		<?php if ( $company_logo ) : ?>
			<img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="invoice-logo">
		<?php endif; ?>
		
		<h1 class="brand-color"><?php echo esc_html( $company_name ); ?></h1>
		<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
	</div>

    <div class="invoice-body">
        <h2>INVOICE</h2>
		<p><strong>Invoice Number:</strong> <?php echo esc_html( $invoice['invoice_number'] ); ?></p>
		<p><strong>Issue Date:</strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ) ); ?></p>
		<p><strong>Due Date:</strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] . ' +30 days' ) ) ); ?></p>
		<p><strong>Payment Status:</strong> <?php echo esc_html( ucfirst( $invoice['payment_status'] ) ); ?></p>

        <div style="margin: 20px 0;">
            <div style="width: 46%; float: left;">
                <h3>Bill To:</h3>
                <p>
                    <strong><?php echo esc_html( $invoice['customer_name'] ); ?></strong><br>
                    <?php echo esc_html( $invoice['customer_email'] ); ?>
                </p>
            </div>

            <div style="width: 46%; float: right;">
                <h3>From:</h3>
                <p>
                    <strong><?php echo esc_html( $company_name ); ?></strong><br>
                    <?php echo esc_html( get_bloginfo( 'admin_email' ) ); ?><br>
                    <?php echo esc_html( get_site_url() ); ?>
                </p>
            </div>
            <div style="clear: both;"></div>
        </div>

		<table class="invoice-details">
			<thead>
				<tr>
					<th>Description</th>
					<th>Booking Date</th>
					<th>Quantity</th>
					<th>Unit Price</th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>
                <?php
                $rendered = false;
                $items = array();
                if ( ! empty( $invoice['item_name'] ) ) {
                    $decoded = json_decode( $invoice['item_name'], true );
                    if ( is_array( $decoded ) ) {
                        $items = $decoded;
                    }
                }
                if ( ! empty( $items ) ) {
                    foreach ( $items as $li ) {
                        $n = isset( $li['name'] ) ? $li['name'] : '';
                        $a = isset( $li['amount'] ) ? floatval( $li['amount'] ) : 0.0;
                        ?>
                        <tr>
                            <td><?php echo esc_html( $n ); ?></td>
                            <td>
                                <?php if ( $booking ) : ?>
                                    <?php echo esc_html( $booking['booking_date'] ); ?> <?php echo esc_html( $booking['booking_time'] ); ?>
                                <?php else : ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>1</td>
                            <td><?php echo esc_html( number_format( $a, 2 ) ); ?></td>
                            <td><?php echo esc_html( number_format( $a, 2 ) ); ?></td>
                        </tr>
                        <?php
                        $rendered = true;
                    }
                }
                if ( ! $rendered ) : ?>
                    <tr>
                        <td><?php echo esc_html( $invoice['item_name'] ); ?></td>
                        <td>
                            <?php if ( $booking ) : ?>
                                <?php echo esc_html( $booking['booking_date'] ); ?> <?php echo esc_html( $booking['booking_time'] ); ?>
                            <?php else : ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>1</td>
                        <td><?php echo esc_html( number_format( $invoice['subtotal'], 2 ) ); ?></td>
                        <td><?php echo esc_html( number_format( $invoice['subtotal'], 2 ) ); ?></td>
                    </tr>
                <?php endif; ?>
			</tbody>
		</table>

        <table class="invoice-summary-table">
            <tr>
                <td style="width:110px;">
                    <?php if ( isset( $qr_url ) ) : ?>
                        <img src="<?php echo esc_url( $qr_url ); ?>" alt="QR" style="width:90px;height:90px;border:1px solid #dee2e6;border-radius:6px;">
                        <div style="font-size:10px;color:#6c757d;margin-top:4px;">Scan to view invoice</div>
                    <?php endif; ?>
                </td>
                <td style="width:200px;">
                    <div class="invoice-totals">
                        <table style="width: 100%;">
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-right"><?php echo esc_html( number_format( $invoice['subtotal'], 2 ) ); ?></td>
                            </tr>
                            <tr>
                                <td>Tax:</td>
                                <td class="text-right"><?php echo esc_html( number_format( $invoice['tax_amount'], 2 ) ); ?></td>
                            </tr>
                            <tr class="total-row">
                                <td><strong>Total:</strong></td>
                                <td class="text-right"><strong><?php echo esc_html( number_format( $invoice['total_amount'], 2 ) ); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

		<div style="margin-top: 40px;">
			<h3>Payment Instructions:</h3>
			<p>Please make payment within 30 days of invoice date. For any questions about this invoice, contact <?php echo esc_html( $support_email ); ?>.</p>
			
			<p><strong>Payment Methods:</strong><br>
			- Bank Transfer<br>
			- Credit Card (via website)<br>
			- PayPal</p>
		</div>
	</div>

	<div class="invoice-footer">
		<p class="text-center">
			Thank you for your business!<br>
			<?php echo esc_html( $company_name ); ?> | <?php echo esc_html( get_site_url() ); ?>
		</p>
		<p class="text-center">
			This is an automated invoice generated by Kura-ai Booking System
		</p>
	</div>
</body>
</html>
