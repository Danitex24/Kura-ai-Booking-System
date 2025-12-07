<?php
/**
 * Kura-ai Booking System - Email Reminders
 *
 * Handles automated email reminders for upcoming bookings.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Reminders {

	/**
	 * Initialize reminder system
	 */
	public static function init() {
		// Schedule daily cron job for reminders
		if ( ! wp_next_scheduled( 'kab_send_reminders' ) ) {
			wp_schedule_event( time(), 'hourly', 'kab_send_reminders' );
		}

		add_action( 'kab_send_reminders', array( __CLASS__, 'process_reminders' ) );
	}

	/**
	 * Process and send reminders
	 */
	public static function process_reminders() {
		global $wpdb;

		try {
			// Get reminders scheduled for the next 24-48 hours that haven't been sent
			$now = current_time( 'mysql' );
			$reminder_window_start = date( 'Y-m-d H:i:s', strtotime( '+23 hours', current_time( 'timestamp' ) ) );
			$reminder_window_end = date( 'Y-m-d H:i:s', strtotime( '+25 hours', current_time( 'timestamp' ) ) );

			// Get bookings that need reminders
			$bookings = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT b.*,
					CONCAT(b.booking_date, ' ', b.booking_time) as booking_datetime
					FROM {$wpdb->prefix}kab_bookings b
					LEFT JOIN {$wpdb->prefix}kab_reminders r ON b.id = r.booking_id AND r.reminder_type = '24h_before'
					WHERE b.status IN ('confirmed', 'pending')
					AND CONCAT(b.booking_date, ' ', b.booking_time) BETWEEN %s AND %s
					AND r.id IS NULL",
					$reminder_window_start,
					$reminder_window_end
				),
				ARRAY_A
			);

			$sent_count = 0;

			foreach ( $bookings as $booking ) {
				if ( self::send_reminder_email( $booking ) ) {
					// Record that reminder was sent
					$wpdb->insert(
						$wpdb->prefix . 'kab_reminders',
						array(
							'booking_id'    => $booking['id'],
							'reminder_type' => '24h_before',
							'sent_at'       => current_time( 'mysql' ),
							'status'        => 'sent',
						),
						array( '%d', '%s', '%s', '%s' )
					);

					$sent_count++;
				}
			}

			error_log( "KAB Reminders: Sent {$sent_count} reminder emails" );

		} catch ( Exception $e ) {
			error_log( 'KAB Reminders Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Send reminder email to customer
	 *
	 * @param array $booking Booking data
	 * @return bool Success status
	 */
	private static function send_reminder_email( $booking ) {
		// Get customer details
		global $wpdb;

		$customer_email = $booking['customer_email'];
		$customer_name = $booking['customer_name'];

		if ( empty( $customer_email ) ) {
			return false;
		}

		// Get event/service details
		$item_name = '';
		$item_details = '';

		if ( $booking['booking_type'] === 'event' && ! empty( $booking['event_id'] ) ) {
			$event = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}kab_events WHERE id = %d",
					$booking['event_id']
				),
				ARRAY_A
			);

			if ( $event ) {
				$item_name = $event['name'];
				$item_details = "Location: {$event['location']}";
			}
		} elseif ( $booking['booking_type'] === 'service' && ! empty( $booking['service_id'] ) ) {
			$service = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}kab_services WHERE id = %d",
					$booking['service_id']
				),
				ARRAY_A
			);

			if ( $service ) {
				$item_name = $service['name'];
				$item_details = "Duration: {$service['duration']} minutes";
			}
		}

		// Get settings
		$settings = get_option( 'kab_settings', array() );
		$company_name = isset( $settings['company_name'] ) ? $settings['company_name'] : get_bloginfo( 'name' );
		$primary_color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#E67E22';

		// Prepare email
		$subject = sprintf( __( 'Reminder: Your booking tomorrow at %s', 'kura-ai-booking-free' ), $company_name );

		$booking_datetime = date_i18n(
			get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			strtotime( $booking['booking_date'] . ' ' . $booking['booking_time'] )
		);

		// Build email HTML
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $subject ); ?></title>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
				.container { max-width: 600px; margin: 20px auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; }
				.header { background-color: <?php echo esc_attr( $primary_color ); ?>; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
				.content { padding: 30px; }
				.booking-details { background-color: #f9f9f9; padding: 20px; border-left: 4px solid <?php echo esc_attr( $primary_color ); ?>; margin: 20px 0; }
				.booking-details h3 { margin-top: 0; color: <?php echo esc_attr( $primary_color ); ?>; }
				.footer { padding: 20px; text-align: center; color: #777; font-size: 12px; background: #f5f5f5; border-radius: 0 0 8px 8px; }
				.btn { display: inline-block; padding: 12px 24px; background-color: <?php echo esc_attr( $primary_color ); ?>; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php echo esc_html__( 'Booking Reminder', 'kura-ai-booking-free' ); ?></h1>
				</div>

				<div class="content">
					<p><?php echo esc_html( sprintf( __( 'Hi %s,', 'kura-ai-booking-free' ), $customer_name ) ); ?></p>

					<p><?php echo esc_html__( 'This is a friendly reminder that you have an upcoming booking tomorrow:', 'kura-ai-booking-free' ); ?></p>

					<div class="booking-details">
						<h3><?php echo esc_html( $item_name ); ?></h3>
						<p><strong><?php echo esc_html__( 'When:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $booking_datetime ); ?></p>
						<?php if ( $item_details ) : ?>
							<p><?php echo esc_html( $item_details ); ?></p>
						<?php endif; ?>
						<p><strong><?php echo esc_html__( 'Booking ID:', 'kura-ai-booking-free' ); ?></strong> #<?php echo esc_html( $booking['id'] ); ?></p>
					</div>

					<p><?php echo esc_html__( 'Please arrive 10-15 minutes early. If you need to cancel or reschedule, please contact us as soon as possible.', 'kura-ai-booking-free' ); ?></p>

					<p><?php echo esc_html__( 'We look forward to seeing you!', 'kura-ai-booking-free' ); ?></p>
				</div>

				<div class="footer">
					<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $company_name ); ?></p>
					<p><?php echo esc_html__( 'This is an automated reminder. Please do not reply to this email.', 'kura-ai-booking-free' ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		$message = ob_get_clean();

		// Send email
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $customer_email, $subject, $message, $headers );
	}

	/**
	 * Schedule reminder for a specific booking
	 *
	 * @param int $booking_id Booking ID
	 * @return bool Success status
	 */
	public static function schedule_reminder( $booking_id ) {
		global $wpdb;

		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d",
				$booking_id
			),
			ARRAY_A
		);

		if ( ! $booking ) {
			return false;
		}

		// Calculate reminder time (24 hours before booking)
		$booking_datetime = strtotime( $booking['booking_date'] . ' ' . $booking['booking_time'] );
		$reminder_time = $booking_datetime - ( 24 * 60 * 60 ); // 24 hours before

		// Only schedule if booking is in the future
		if ( $reminder_time > current_time( 'timestamp' ) ) {
			// Record reminder schedule in database
			$wpdb->insert(
				$wpdb->prefix . 'kab_reminders',
				array(
					'booking_id'     => $booking_id,
					'reminder_type'  => '24h_before',
					'scheduled_time' => date( 'Y-m-d H:i:s', $reminder_time ),
					'status'         => 'scheduled',
				),
				array( '%d', '%s', '%s', '%s' )
			);

			return true;
		}

		return false;
	}

	/**
	 * Cancel reminder for a booking
	 *
	 * @param int $booking_id Booking ID
	 * @return bool Success status
	 */
	public static function cancel_reminder( $booking_id ) {
		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . 'kab_reminders',
			array( 'status' => 'cancelled' ),
			array( 'booking_id' => intval( $booking_id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Send manual reminder
	 *
	 * @param int $booking_id Booking ID
	 * @return bool Success status
	 */
	public static function send_manual_reminder( $booking_id ) {
		global $wpdb;

		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT b.*, u.display_name as customer_name, u.user_email as customer_email
				FROM {$wpdb->prefix}kab_bookings b
				LEFT JOIN {$wpdb->prefix}users u ON b.user_id = u.ID
				WHERE b.id = %d",
				$booking_id
			),
			ARRAY_A
		);

		if ( ! $booking ) {
			return false;
		}

		if ( self::send_reminder_email( $booking ) ) {
			// Record that manual reminder was sent
			$wpdb->insert(
				$wpdb->prefix . 'kab_reminders',
				array(
					'booking_id'    => $booking_id,
					'reminder_type' => 'manual',
					'sent_at'       => current_time( 'mysql' ),
					'status'        => 'sent',
				),
				array( '%d', '%s', '%s', '%s' )
			);

			return true;
		}

		return false;
	}
}

// Initialize reminders system
KAB_Reminders::init();
