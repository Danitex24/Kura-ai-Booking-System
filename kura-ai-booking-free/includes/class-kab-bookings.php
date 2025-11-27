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
		$status = 'pending';
		$ticket_id = uniqid( 'kab_' );
		$wpdb->insert(
			$wpdb->prefix . 'kab_bookings',
			array(
				'user_id'      => get_current_user_id(),
				'service_id'   => isset( $data['service_id'] ) ? intval( $data['service_id'] ) : null,
				'event_id'     => isset( $data['event_id'] ) ? intval( $data['event_id'] ) : null,
				'booking_type' => sanitize_text_field( $data['booking_type'] ),
				'booking_date' => sanitize_text_field( $data['booking_date'] ),
				'booking_time' => sanitize_text_field( $data['booking_time'] ),
				'status'       => $status,
				'ticket_id'    => $ticket_id,
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		$booking_id = $wpdb->insert_id;
		if ( $booking_id ) {
			// Create ticket
			require_once plugin_dir_path( __FILE__ ) . 'class-kab-tickets.php';
			KAB_Tickets::generate_and_send_ticket( $booking_id, $ticket_id, $data );
		}
		return $booking_id;
	}
}
