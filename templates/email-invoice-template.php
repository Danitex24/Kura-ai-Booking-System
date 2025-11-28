<?php
/**
 * Email Invoice Template
 * 
 * @package Kura-ai-Booking-System
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company_name  = get_option( 'kab_company_name', 'Kura-ai Booking' );
$company_email = get_option( 'kab_support_email', get_option( 'admin_email' ) );
$company_phone = get_option( 'kab_company_phone', '' );
$company_address = get_option( 'kab_company_address', '' );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html__( 'Invoice', 'kura-ai-booking-free' ); ?></title>
	<style>
		body {
			font-family: Arial, sans-serif;
			line-height: 1.6;
			color: #333;
			margin: 0;
			padding: 20px;
			background-color: #f9f9f9;
		}
		.email-container {
			max-width: 600px;
			margin: 0 auto;
			background-color: #ffffff;
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 30px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
		.header {
			text-align: center;
			margin-bottom: 30px;
			padding-bottom: 20px;
			border-bottom: 2px solid #e63946;
		}
		.brand-color {
			color: #e63946;
		}
		.invoice-details {
			margin-bottom: 30px;
		}
		.detail-row {
			display: flex;
			justify-content: space-between;
			margin-bottom: 10px;
		}
		.detail-label {
			font-weight: bold;
			color: #555;
		}
		.amount-row {
			background-color: #f8f9fa;
			padding: 15px;
			border-radius: 6px;
			margin-top: 20px;
			border-left: 4px solid #e63946;
		}
		.total-amount {
			font-size: 18px;
			font-weight: bold;
			color: #e63946;
		}
		.footer {
			text-align: center;
			margin-top: 40px;
			padding-top: 20px;
			border-top: 1px solid #ddd;
			color: #777;
			font-size: 14px;
		}
		.attachment-note {
			background-color: #e7f3ff;
			padding: 15px;
			border-radius: 6px;
			margin-top: 20px;
			border-left: 4px solid #2196f3;
		}
		@media (max-width: 480px) {
			.email-container {
				padding: 20px;
			}
			.detail-row {
				flex-direction: column;
			}
		}
	</style>
</head>
<body>
	<div class="email-container">
		<div class="header">
			<h1 class="brand-color"><?php echo esc_html( $company_name ); ?></h1>
			<?php if ( $company_address ) : ?>
				<p><?php echo esc_html( $company_address ); ?></p>
			<?php endif; ?>
			<?php if ( $company_phone ) : ?>
				<p><?php echo esc_html( $company_phone ); ?></p>
			<?php endif; ?>
			<p><?php echo esc_html( $company_email ); ?></p>
		</div>

		<h2><?php echo esc_html__( 'Invoice Details', 'kura-ai-booking-free' ); ?></h2>
		
		<div class="invoice-details">
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Invoice Number:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( $invoice['invoice_number'] ); ?></span>
			</div>
			
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Customer:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( $invoice['customer_name'] ); ?></span>
			</div>
			
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Email:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( $invoice['customer_email'] ); ?></span>
			</div>
			
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Service/Event:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( $invoice['item_name'] ); ?></span>
			</div>
			
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Invoice Date:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ) ); ?></span>
			</div>
			
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Payment Status:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( ucfirst( $invoice['payment_status'] ) ); ?></span>
			</div>
		</div>

		<div class="amount-row">
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Subtotal:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( kab_format_currency( $invoice['subtotal'] ) ); ?></span>
			</div>
			
			<?php if ( ! empty( $invoice['tax_amount'] ) && $invoice['tax_amount'] > 0 ) : ?>
			<div class="detail-row">
				<span class="detail-label"><?php echo esc_html__( 'Tax:', 'kura-ai-booking-free' ); ?></span>
				<span><?php echo esc_html( kab_format_currency( $invoice['tax_amount'] ) ); ?></span>
			</div>
			<?php endif; ?>
			
			<div class="detail-row">
				<span class="detail-label total-amount"><?php echo esc_html__( 'Total Amount:', 'kura-ai-booking-free' ); ?></span>
				<span class="total-amount"><?php echo esc_html( kab_format_currency( $invoice['total_amount'] ) ); ?></span>
			</div>
		</div>

		<div class="attachment-note">
			<p><strong><?php echo esc_html__( 'Important:', 'kura-ai-booking-free' ); ?></strong></p>
			<p><?php echo esc_html__( 'Your detailed invoice is attached to this email as a PDF document. Please keep this invoice for your records.', 'kura-ai-booking-free' ); ?></p>
		</div>

		<div class="footer">
			<p><?php echo esc_html__( 'Thank you for your business!', 'kura-ai-booking-free' ); ?></p>
			<p><?php echo esc_html__( 'If you have any questions about this invoice, please contact us at:', 'kura-ai-booking-free' ); ?></p>
			<p><a href="mailto:<?php echo esc_attr( $company_email ); ?>" style="color: #e63946;"><?php echo esc_html( $company_email ); ?></a></p>
			
			<p style="margin-top: 20px; font-size: 12px; color: #999;">
				<?php echo esc_html__( 'This is an automated message, please do not reply to this email.', 'kura-ai-booking-free' ); ?>
			</p>
		</div>
	</div>
</body>
</html>