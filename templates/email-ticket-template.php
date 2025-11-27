<?php
/**
 * Kura-ai Booking System - Email Ticket Template
 *
 * HTML email template for booking confirmation and ticket delivery
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Template variables
$company_name = get_option( 'kab_company_name', 'Kura-ai Booking System' );
$company_logo  = get_option( 'kab_company_logo', '' );
$support_email = get_option( 'kab_support_email', get_option( 'admin_email' ) );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $subject ); ?></title>
	<style>
		body {
			font-family: Arial, sans-serif;
			line-height: 1.6;
			color: #333;
			margin: 0;
			padding: 0;
			background-color: #f8f9fa;
		}
		.email-container {
			max-width: 600px;
			margin: 0 auto;
			background: #ffffff;
			border: 1px solid #ddd;
			border-radius: 8px;
			overflow: hidden;
		}
		.email-header {
			background: linear-gradient(135deg, #0073aa, #00a0d2);
			color: white;
			padding: 30px 20px;
			text-align: center;
		}
		.email-header h1 {
			margin: 0;
			font-size: 24px;
			font-weight: bold;
		}
		.company-logo {
			max-width: 120px;
			height: auto;
			margin-bottom: 15px;
		}
		.email-body {
			padding: 30px 20px;
		}
		.ticket-info {
			background: #f8f9fa;
			border: 1px solid #e9ecef;
			border-radius: 6px;
			padding: 20px;
			margin: 20px 0;
		}
		.info-row {
			display: flex;
			justify-content: space-between;
			margin-bottom: 12px;
			border-bottom: 1px solid #eee;
			padding-bottom: 12px;
		}
		.info-row:last-child {
			border-bottom: none;
			margin-bottom: 0;
			padding-bottom: 0;
		}
		.info-label {
			font-weight: bold;
			color: #555;
			min-width: 120px;
		}
		.info-value {
			color: #333;
			text-align: right;
		}
		.qr-section {
			text-align: center;
			margin: 25px 0;
			padding: 20px;
			background: #f8f9fa;
			border-radius: 6px;
		}
		.qr-code {
			max-width: 180px;
			height: auto;
			border: 2px solid #0073aa;
			border-radius: 8px;
			padding: 10px;
			background: white;
		}
		.email-footer {
			background: #f8f9fa;
			padding: 20px;
			text-align: center;
			color: #666;
			font-size: 12px;
			border-top: 1px solid #eee;
		}
		.cta-button {
			display: inline-block;
			background: #0073aa;
			color: white !important;
			text-decoration: none;
			padding: 12px 24px;
			border-radius: 4px;
			font-weight: bold;
			margin: 15px 0;
		}
		@media (max-width: 480px) {
			.email-body {
				padding: 20px 15px;
			}
			.info-row {
				flex-direction: column;
				text-align: center;
			}
			.info-label, .info-value {
				text-align: center;
				min-width: auto;
			}
		}
	</style>
</head>
<body>
	<div class="email-container">
		<div class="email-header">
			<?php if ( ! empty( $company_logo ) ) : ?>
				<img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="company-logo">
			<?php endif; ?>
			<h1><?php echo esc_html( $subject ); ?></h1>
		</div>
		
		<div class="email-body">
			<p><?php _e( 'Thank you for choosing', 'kura-ai-booking-free' ); ?> <strong><?php echo esc_html( $company_name ); ?></strong>!</p>
			<p><?php _e( 'Your booking has been confirmed. Below are the details of your reservation:', 'kura-ai-booking-free' ); ?></p>
			
			<div class="ticket-info">
				<div class="info-row">
					<span class="info-label"><?php _e( 'Event/Service:', 'kura-ai-booking-free' ); ?></span>
					<span class="info-value"><?php echo esc_html( $booking_item_name ); ?></span>
				</div>
				<div class="info-row">
					<span class="info-label"><?php _e( 'Customer Name:', 'kura-ai-booking-free' ); ?></span>
					<span class="info-value"><?php echo esc_html( $customer_name ); ?></span>
				</div>
				<div class="info-row">
					<span class="info-label"><?php _e( 'Booking ID:', 'kura-ai-booking-free' ); ?></span>
					<span class="info-value">#<?php echo esc_html( $booking_id ); ?></span>
				</div>
				<div class="info-row">
					<span class="info-label"><?php _e( 'Ticket ID:', 'kura-ai-booking-free' ); ?></span>
					<span class="info-value"><?php echo esc_html( $ticket_id ); ?></span>
				</div>
				<div class="info-row">
					<span class="info-label"><?php _e( 'Date:', 'kura-ai-booking-free' ); ?></span>
					<span class="info-value"><?php echo esc_html( $booking_date ); ?></span>
				</div>
				<div class="info-row">
					<span class="info-label"><?php _e( 'Time:', 'kura-ai-booking-free' ); ?></span>
					<span class="info-value"><?php echo esc_html( $booking_time ); ?></span>
				</div>
			</div>

			<?php if ( ! empty( $qr_code_path ) ) : ?>
			<div class="qr-section">
				<h3><?php _e( 'Scan this QR Code for Validation', 'kura-ai-booking-free' ); ?></h3>
				<img src="<?php echo esc_url( $qr_code_path ); ?>" alt="QR Code" class="qr-code">
				<p><?php _e( 'Present this QR code at the venue for quick check-in.', 'kura-ai-booking-free' ); ?></p>
			</div>
			<?php endif; ?>

			<p><?php _e( 'Your ticket PDF is attached to this email. Please keep this email for your records.', 'kura-ai-booking-free' ); ?></p>
			
			<p><?php _e( 'If you need to make any changes to your booking, please contact us at:', 'kura-ai-booking-free' ); ?></p>
			<p><a href="mailto:<?php echo esc_attr( $support_email ); ?>" style="color: #0073aa;"><?php echo esc_html( $support_email ); ?></a></p>
		</div>
		
		<div class="email-footer">
			<p>&copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( $company_name ); ?>. <?php _e( 'All rights reserved.', 'kura-ai-booking-free' ); ?></p>
			<p><?php _e( 'This is an automated message, please do not reply to this email.', 'kura-ai-booking-free' ); ?></p>
		</div>
	</div>
</body>
</html>