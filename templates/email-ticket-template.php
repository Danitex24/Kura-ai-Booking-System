<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo esc_html__( 'Booking Confirmation', 'kura-ai-booking-free' ); ?></title>
	<style>
		body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; }
		.header { background-color: #E67E22; color: white; padding: 20px; text-align: center; }
		.content { padding: 20px; border: 1px solid #ddd; }
		.ticket-info { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #628141; }
		.footer { margin-top: 20px; padding: 15px; background-color: #f5f5f5; text-align: center; font-size: 12px; color: #666; }
		.qr-code { text-align: center; margin: 20px 0; }
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php echo esc_html__( 'Booking Confirmation', 'kura-ai-booking-free' ); ?></h1>
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
			<p><?php echo esc_html__( 'If you have any questions, please contact our support team at', 'kura-ai-booking-free' ); ?> <?php echo esc_html( get_option( 'admin_email' ) ); ?></p>
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php echo esc_html__( 'All rights reserved.', 'kura-ai-booking-free' ); ?></p>
		</div>
	</div>
</body>
</html>