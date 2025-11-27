<?php
/**
 * Kura-ai Booking System Tickets
 *
 * Handles PDF ticket creation, email sending, and validation.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Tickets {
	public function __construct() {}

	public static function generate_and_send_ticket( $booking_id, $ticket_id, $data ) {
		global $wpdb;
		// Generate QR code (placeholder: base64 PNG)
		$qr_code = base64_encode( $ticket_id );
		$qr_code_path = '';
		if ( function_exists( 'imagepng' ) ) {
			// Use GD to generate QR PNG (placeholder)
			$qr_code_path = self::generate_qr_code_png( $ticket_id );
		}
		// Save ticket to DB
		$wpdb->insert(
			$wpdb->prefix . 'kab_tickets',
			array(
				'booking_id'   => intval( $booking_id ),
				'ticket_id'    => sanitize_text_field( $ticket_id ),
				'qr_code_path' => esc_url_raw( $qr_code_path ),
				'pdf_path'     => '',
				'status'       => 'valid',
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		// Send email (placeholder)
		self::send_ticket_email( $data['customer_email'], $ticket_id, $qr_code_path );
	}

	public static function generate_qr_code_png( $ticket_id ) {
		// Placeholder: returns empty string, implement QR PNG generation here
		return '';
	}

	public static function send_ticket_email( $email, $ticket_id, $qr_code_path ) {
		$subject = __( 'Your Kura-ai Booking Ticket', 'kura-ai-booking-free' );
		$message = __( 'Thank you for your booking. Your ticket ID is: ', 'kura-ai-booking-free' ) . esc_html( $ticket_id );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		wp_mail( sanitize_email( $email ), $subject, $message, $headers );
	}

	public function get_ticket_by_id( $ticket_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_tickets WHERE ticket_id = %s", $ticket_id ), ARRAY_A );
	}
}
