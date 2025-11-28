<?php
/**
 * Kura-ai Booking System Bookings
 *
 * Handles booking logic, validation, and email triggers.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Bookings {
	public function __construct() {}

	public static function create_booking( $data ) {
		global $wpdb;
		$status       = 'pending';
		$user_id      = get_current_user_id();
		$booking_type = sanitize_text_field( $data['booking_type'] );
		$booking_date = sanitize_text_field( $data['booking_date'] );
		$booking_time = sanitize_text_field( $data['booking_time'] );
		$service_id   = isset( $data['service_id'] ) ? intval( $data['service_id'] ) : null;
		$event_id     = isset( $data['event_id'] ) ? intval( $data['event_id'] ) : null;

		// Prevent double booking for same slot
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings WHERE user_id = %d AND booking_type = %s AND booking_date = %s AND booking_time = %s AND status != 'cancelled'",
				$user_id,
				$booking_type,
				$booking_date,
				$booking_time
			)
		);
		if ( $existing > 0 ) {
			return false; // Already booked
		}

		// Check service availability for service bookings
		if ( $booking_type === 'service' && $service_id ) {
			if ( ! self::is_service_available( $service_id, $booking_date, $booking_time ) ) {
				return false; // Service not available at requested time
			}
		}

		// Check event capacity
		if ( $booking_type === 'event' && $event_id ) {
			$event  = $wpdb->get_row( $wpdb->prepare( "SELECT capacity FROM {$wpdb->prefix}kab_events WHERE id = %d", $event_id ), ARRAY_A );
			$booked = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings WHERE event_id = %d AND booking_type = 'event' AND status != 'cancelled'", $event_id ) );
			if ( $event && $booked >= intval( $event['capacity'] ) ) {
				return false; // Event full
			}
		}

		$ticket_id = uniqid( 'kab_' );
		$wpdb->insert(
			$wpdb->prefix . 'kab_bookings',
			array(
				'user_id'      => $user_id,
				'service_id'   => $service_id,
				'event_id'     => $event_id,
				'booking_type' => $booking_type,
				'booking_date' => $booking_date,
				'booking_time' => $booking_time,
				'status'       => $status,
				'ticket_id'    => $ticket_id,
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		$booking_id = $wpdb->insert_id;
		if ( $booking_id ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-kab-tickets.php';
			KAB_Tickets::generate_and_send_ticket( $booking_id, $ticket_id, $data );
		}
		return $booking_id;
	}

	public static function cancel_booking( $booking_id, $user_id ) {
		global $wpdb;
		$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d AND user_id = %d", $booking_id, $user_id ), ARRAY_A );
		if ( $booking && $booking['status'] === 'pending' ) {
			$wpdb->update(
				$wpdb->prefix . 'kab_bookings',
				array( 'status' => 'cancelled' ),
				array( 'id' => $booking_id ),
				array( '%s' ),
				array( '%d' )
			);
			return true;
		}
		return false;
	}

	/**
	 * Check if a service is available at the requested date and time
	 *
	 * @param int    $service_id Service ID
	 * @param string $booking_date Booking date (YYYY-MM-DD)
	 * @param string $booking_time Booking time (HH:MM)
	 * @return bool True if available, false otherwise
	 */
	public static function is_service_available( $service_id, $booking_date, $booking_time ) {
		global $wpdb;

		// Check if the service exists and is active
		$service = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_services WHERE id = %d AND status = %s",
				$service_id,
				'active'
			),
			ARRAY_A
		);

		if ( ! $service ) {
			return false; // Service not found or inactive
		}

		// Check if the requested date is in the past
		if ( strtotime( $booking_date . ' ' . $booking_time ) < current_time( 'timestamp' ) ) {
			return false; // Cannot book in the past
		}

		// Check for overlapping bookings for the same service at the same time
		$overlapping_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings 
				WHERE service_id = %d 
				AND booking_date = %s 
				AND booking_time = %s 
				AND status NOT IN ('cancelled', 'completed')",
				$service_id,
				$booking_date,
				$booking_time
			)
		);

		// For services, we typically allow only one booking per time slot
		if ( $overlapping_bookings > 0 ) {
			return false; // Time slot already booked
		}

		// Additional validation: check business hours and day of week
		return self::validate_business_hours( $booking_date, $booking_time ) &&
				self::validate_day_of_week( $booking_date );
	}

	/**
	 * Validate booking against business hours
	 *
	 * @param string $booking_date Booking date (YYYY-MM-DD)
	 * @param string $booking_time Booking time (HH:MM)
	 * @return bool True if within business hours, false otherwise
	 */
	private static function validate_business_hours( $booking_date, $booking_time ) {
		// Default business hours: 9 AM to 5 PM
		$business_hours_start = '09:00';
		$business_hours_end   = '17:00';

		// Check if booking time is within business hours
		$booking_timestamp = strtotime( $booking_date . ' ' . $booking_time );
		$start_timestamp   = strtotime( $booking_date . ' ' . $business_hours_start );
		$end_timestamp     = strtotime( $booking_date . ' ' . $business_hours_end );

		return $booking_timestamp >= $start_timestamp && $booking_timestamp <= $end_timestamp;
	}

	/**
	 * Validate booking day of week
	 *
	 * @param string $booking_date Booking date (YYYY-MM-DD)
	 * @return bool True if valid day of week, false otherwise
	 */
	private static function validate_day_of_week( $booking_date ) {
		// Default: allow bookings Monday to Friday only
		$day_of_week = date( 'N', strtotime( $booking_date ) );

		// 1 = Monday, 7 = Sunday
		return $day_of_week >= 1 && $day_of_week <= 5; // Monday to Friday
	}

	/**
	 * Get upcoming appointments
	 *
	 * @param int $limit Number of appointments to retrieve.
	 * @return array List of upcoming appointments.
	 */
	public static function get_upcoming_appointments( $limit = 5 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'kab_bookings';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE booking_date >= CURDATE() AND status = 'pending' ORDER BY booking_date, booking_time ASC LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get recent bookings
	 *
	 * @param int $limit Number of bookings to retrieve.
	 * @return array List of recent bookings.
	 */
	public static function get_recent_bookings( $limit = 5 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'kab_bookings';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results;
	}
}
