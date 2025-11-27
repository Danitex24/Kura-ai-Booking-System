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
}
