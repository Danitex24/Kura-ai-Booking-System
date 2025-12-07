<?php
/**
 * Email Ticket Template
 *
 * HTML template for email ticket notifications.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

// Get plugin settings from setup wizard
$settings = get_option( 'kab_settings', array() );
$company_name = isset( $settings['company_name'] ) ? $settings['company_name'] : get_bloginfo( 'name' );
$company_logo = isset( $settings['company_logo'] ) ? $settings['company_logo'] : '';
$support_email = isset( $settings['support_email'] ) ? $settings['support_email'] : get_option( 'admin_email' );
$primary_color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#E67E22';
$secondary_color = isset( $settings['secondary_color'] ) ? $settings['secondary_color'] : '#628141';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo esc_html__( 'Booking Confirmation', 'kura-ai-booking-free' ); ?></title>
	<style>
		body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; }
		.header { background-color: <?php echo esc_attr( $primary_color ); ?>; color: white; padding: 20px; text-align: center; }
		.header img { max-width: 150px; margin-bottom: 10px; }
		.content { padding: 20px; border: 1px solid #ddd; }
		.ticket-info { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid <?php echo esc_attr( $secondary_color ); ?>; }
		.footer { margin-top: 20px; padding: 15px; background-color: #f5f5f5; text-align: center; font-size: 12px; color: #666; }
		.qr-code { text-align: center; margin: 20px 0; }
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<?php if ( $company_logo ) : ?>
				<img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" />
			<?php endif; ?>
			<h1><?php echo esc_html( $company_name ); ?></h1>
			<p><?php echo esc_html__( 'Booking Confirmation', 'kura-ai-booking-free' ); ?></p>
		</div>
		
		<div class="content">
			<p><?php echo esc_html__( 'Thank you for your booking! Here are your booking details:', 'kura-ai-booking-free' ); ?></p>
			
			<div class="ticket-info">
				<h3><?php echo esc_html__( 'Booking Details', 'kura-ai-booking-free' ); ?></h3>
				<p><strong><?php echo esc_html__( 'Booking ID:', 'kura-ai-booking-free' ); ?></strong> #<?php echo esc_html( $booking_id ); ?></p>
				<p><strong><?php echo esc_html__( 'Customer:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $customer_name ); ?></p>
				<p><strong><?php echo esc_html__( 'Service/Event:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $item_name ); ?></p>
				<p><strong><?php echo esc_html__( 'Type:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( ucfirst( $booking_type ) ); ?></p>
				<p><strong><?php echo esc_html__( 'Date:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $booking_date ); ?></p>
				<p><strong><?php echo esc_html__( 'Time:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $booking_time ); ?></p>
				<p><strong><?php echo esc_html__( 'Ticket ID:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $ticket_id ); ?></p>
			</div>

			<?php if ( $qr_code_path ) : ?>
			<div class="qr-code">
				<h4><?php echo esc_html__( 'Your QR Code Ticket', 'kura-ai-booking-free' ); ?></h4>
				<img src="<?php echo esc_url( $qr_code_path ); ?>" alt="QR Code" style="max-width: 180px;" />
				<p><small><?php echo esc_html__( 'Present this QR code at check-in', 'kura-ai-booking-free' ); ?></small></p>
			</div>
			<?php endif; ?>

			<div class="instructions">
				<h4><?php echo esc_html__( 'Important Information', 'kura-ai-booking-free' ); ?></h4>
				<ul>
					<li><?php echo esc_html__( 'Please arrive 15 minutes before your scheduled time', 'kura-ai-booking-free' ); ?></li>
					<li><?php echo esc_html__( 'Bring a valid ID for verification', 'kura-ai-booking-free' ); ?></li>
					<li><?php echo esc_html__( 'Present your QR code or booking confirmation at check-in', 'kura-ai-booking-free' ); ?></li>
					<li><?php echo esc_html__( 'Cancellations must be made at least 24 hours in advance', 'kura-ai-booking-free' ); ?></li>
				</ul>
			</div>
		</div>
		
		<div class="footer">
			<p><?php echo esc_html__( 'If you have any questions, please contact our support team at', 'kura-ai-booking-free' ); ?> <?php echo esc_html( $support_email ); ?></p>
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $company_name ); ?>. <?php echo esc_html__( 'All rights reserved.', 'kura-ai-booking-free' ); ?></p>
		</div>
	</div>
</body>
</html>