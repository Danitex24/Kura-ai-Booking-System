<?php
/**
 * Kura-ai Booking System - Cancellations & Refunds
 *
 * Handles booking cancellations and refund workflows.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Cancellations {

	/**
	 * Request booking cancellation
	 *
	 * @param int   $booking_id Booking ID
	 * @param array $data Cancellation data
	 * @return array Result with success status and message
	 */
	public static function request_cancellation( $booking_id, $data = array() ) {
		global $wpdb;

		try {
			// Get booking details
			$booking = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d",
					$booking_id
				),
				ARRAY_A
			);

			if ( ! $booking ) {
				return array(
					'success' => false,
					'message' => __( 'Booking not found', 'kura-ai-booking-free' ),
				);
			}

			// Check if already cancelled
			if ( $booking['status'] === 'cancelled' ) {
				return array(
					'success' => false,
					'message' => __( 'Booking is already cancelled', 'kura-ai-booking-free' ),
				);
			}

			// Check cancellation policy
			$policy_check = self::check_cancellation_policy( $booking );

			if ( ! $policy_check['allowed'] ) {
				return array(
					'success' => false,
					'message' => $policy_check['message'],
					'refund_amount' => 0,
				);
			}

			// Calculate refund amount
			$refund_amount = self::calculate_refund( $booking, $policy_check['hours_before'] );

			// Create cancellation record
			$wpdb->insert(
				$wpdb->prefix . 'kab_cancellations',
				array(
					'booking_id'      => $booking_id,
					'reason'          => isset( $data['reason'] ) ? sanitize_textarea_field( $data['reason'] ) : null,
					'cancelled_by'    => isset( $data['cancelled_by'] ) ? sanitize_text_field( $data['cancelled_by'] ) : 'customer',
					'refund_amount'   => $refund_amount,
					'refund_status'   => $refund_amount > 0 ? 'pending' : 'not_applicable',
					'cancellation_fee'=> $policy_check['fee'],
				),
				array( '%d', '%s', '%s', '%f', '%s', '%f' )
			);

			$cancellation_id = $wpdb->insert_id;

			// Update booking status
			$wpdb->update(
				$wpdb->prefix . 'kab_bookings',
				array( 'status' => 'cancelled' ),
				array( 'id' => $booking_id ),
				array( '%s' ),
				array( '%d' )
			);

			// Send cancellation confirmation email
			self::send_cancellation_email( $booking, $cancellation_id, $refund_amount );

			// Notify waitlist if event/service has one
			if ( $booking['booking_type'] === 'event' && ! empty( $booking['event_id'] ) ) {
				require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-waitlist.php';
				KAB_Waitlist::notify_next_in_line( 'event', $booking['event_id'], $booking['booking_date'] );
			} elseif ( $booking['booking_type'] === 'service' && ! empty( $booking['service_id'] ) ) {
				require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-waitlist.php';
				KAB_Waitlist::notify_next_in_line( 'service', $booking['service_id'] );
			}

			return array(
				'success' => true,
				'message' => __( 'Booking cancelled successfully', 'kura-ai-booking-free' ),
				'cancellation_id' => $cancellation_id,
				'refund_amount' => $refund_amount,
			);

		} catch ( Exception $e ) {
			error_log( 'KAB Cancellation Error: ' . $e->getMessage() );
			return array(
				'success' => false,
				'message' => __( 'Error processing cancellation', 'kura-ai-booking-free' ),
			);
		}
	}

	/**
	 * Check if cancellation is allowed based on policy
	 *
	 * @param array $booking Booking data
	 * @return array Policy check result
	 */
	private static function check_cancellation_policy( $booking ) {
		// Get cancellation policy settings
		$settings = get_option( 'kab_settings', array() );
		$min_hours_before = isset( $settings['cancellation_min_hours'] ) ? intval( $settings['cancellation_min_hours'] ) : 24;
		$cancellation_fee_percent = isset( $settings['cancellation_fee_percent'] ) ? floatval( $settings['cancellation_fee_percent'] ) : 0;

		// Calculate hours until booking
		$booking_datetime = strtotime( $booking['booking_date'] . ' ' . $booking['booking_time'] );
		$current_time = current_time( 'timestamp' );
		$hours_before = ( $booking_datetime - $current_time ) / 3600;

		if ( $hours_before < $min_hours_before ) {
			return array(
				'allowed' => false,
				'message' => sprintf(
					__( 'Cancellations must be made at least %d hours before the booking time', 'kura-ai-booking-free' ),
					$min_hours_before
				),
				'hours_before' => $hours_before,
				'fee' => 0,
			);
		}

		return array(
			'allowed' => true,
			'hours_before' => $hours_before,
			'fee' => $cancellation_fee_percent,
		);
	}

	/**
	 * Calculate refund amount based on cancellation policy
	 *
	 * @param array $booking Booking data
	 * @param float $hours_before Hours before booking
	 * @return float Refund amount
	 */
	private static function calculate_refund( $booking, $hours_before ) {
		// Get invoice for this booking
		global $wpdb;

		$invoice = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_invoices WHERE booking_id = %d",
				$booking['id']
			),
			ARRAY_A
		);

		if ( ! $invoice || $invoice['payment_status'] !== 'paid' ) {
			return 0; // No refund if not paid
		}

		$total_amount = floatval( $invoice['total_amount'] );

		// Get refund policy based on timing
		$settings = get_option( 'kab_settings', array() );

		// Example policy: 100% refund if >48h, 50% if 24-48h, 0% if <24h
		if ( $hours_before >= 48 ) {
			$refund_percent = 100;
		} elseif ( $hours_before >= 24 ) {
			$refund_percent = isset( $settings['refund_percent_24h'] ) ? floatval( $settings['refund_percent_24h'] ) : 50;
		} else {
			$refund_percent = 0;
		}

		return round( ( $total_amount * $refund_percent ) / 100, 2 );
	}

	/**
	 * Send cancellation confirmation email
	 *
	 * @param array $booking Booking data
	 * @param int   $cancellation_id Cancellation ID
	 * @param float $refund_amount Refund amount
	 * @return bool Success status
	 */
	private static function send_cancellation_email( $booking, $cancellation_id, $refund_amount ) {
		$settings = get_option( 'kab_settings', array() );
		$company_name = isset( $settings['company_name'] ) ? $settings['company_name'] : get_bloginfo( 'name' );
		$primary_color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#E67E22';

		$subject = sprintf( __( 'Booking Cancellation Confirmation - %s', 'kura-ai-booking-free' ), $company_name );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 20px auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; }
				.header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
				.content { padding: 30px; }
				.info-box { background-color: #f9f9f9; padding: 20px; border-left: 4px solid <?php echo esc_attr( $primary_color ); ?>; margin: 20px 0; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php echo esc_html__( 'Booking Cancelled', 'kura-ai-booking-free' ); ?></h1>
				</div>
				<div class="content">
					<p><?php echo esc_html( sprintf( __( 'Hi %s,', 'kura-ai-booking-free' ), $booking['customer_name'] ) ); ?></p>
					<p><?php echo esc_html__( 'Your booking has been successfully cancelled.', 'kura-ai-booking-free' ); ?></p>

					<div class="info-box">
						<p><strong><?php echo esc_html__( 'Booking ID:', 'kura-ai-booking-free' ); ?></strong> #<?php echo esc_html( $booking['id'] ); ?></p>
						<p><strong><?php echo esc_html__( 'Cancellation ID:', 'kura-ai-booking-free' ); ?></strong> #<?php echo esc_html( $cancellation_id ); ?></p>
						<?php if ( $refund_amount > 0 ) : ?>
							<p><strong><?php echo esc_html__( 'Refund Amount:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( kab_format_currency( $refund_amount ) ); ?></p>
							<p><?php echo esc_html__( 'Your refund will be processed within 5-7 business days.', 'kura-ai-booking-free' ); ?></p>
						<?php endif; ?>
					</div>

					<p><?php echo esc_html__( 'We hope to see you again soon!', 'kura-ai-booking-free' ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		$message = ob_get_clean();

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		return wp_mail( $booking['customer_email'], $subject, $message, $headers );
	}

	/**
	 * Process refund for a cancellation
	 *
	 * @param int $cancellation_id Cancellation ID
	 * @return bool Success status
	 */
	public static function process_refund( $cancellation_id ) {
		global $wpdb;

		$cancellation = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_cancellations WHERE id = %d",
				$cancellation_id
			),
			ARRAY_A
		);

		if ( ! $cancellation || $cancellation['refund_status'] !== 'pending' ) {
			return false;
		}

		// Here you would integrate with payment gateway to process refund
		// For now, we'll just mark as processed
		// TODO: Integrate with Stripe, PayPal, etc.

		$wpdb->update(
			$wpdb->prefix . 'kab_cancellations',
			array(
				'refund_status' => 'processed',
				'refund_processed_at' => current_time( 'mysql' ),
			),
			array( 'id' => $cancellation_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return true;
	}

	/**
	 * Get cancellation details
	 *
	 * @param int $cancellation_id Cancellation ID
	 * @return array|null Cancellation data
	 */
	public static function get_cancellation( $cancellation_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_cancellations WHERE id = %d",
				$cancellation_id
			),
			ARRAY_A
		);
	}
}
