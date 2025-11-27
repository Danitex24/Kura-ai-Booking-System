<?php
/**
 * Email notifications handler for Kura-ai Booking System
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WordPress functions
if ( ! function_exists( 'wp_mail' ) ) {
	require_once ABSPATH . WPINC . '/pluggable.php';
}

class KAB_Emails {

	/**
	 * Send booking confirmation email with ticket
	 *
	 * @param int $booking_id Booking ID
	 * @param string $customer_email Customer email address
	 * @param string $customer_name Customer name
	 * @param string $booking_type Booking type (service/event)
	 * @param string $booking_date Booking date
	 * @param string $booking_time Booking time
	 * @param string $item_name Service/Event name
	 * @param string $ticket_id Ticket ID
	 * @param string $qr_code_path QR code image path
	 * @param string $pdf_path PDF ticket path
	 * @return bool True if email sent successfully, false otherwise
	 */
	public static function send_booking_confirmation( $booking_id, $customer_email, $customer_name, $booking_type, $booking_date, $booking_time, $item_name, $ticket_id, $qr_code_path = null, $pdf_path = null ) {
		$subject = sprintf(
			__( 'Booking Confirmation - %s', 'kura-ai-booking-free' ),
			$item_name
		);

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		$message = self::get_email_template( $booking_id, $customer_name, $booking_type, $booking_date, $booking_time, $item_name, $ticket_id, $qr_code_path );

		$attachments = array();
		if ( $pdf_path && file_exists( $pdf_path ) ) {
			$attachments[] = $pdf_path;
		}

		return wp_mail( $customer_email, $subject, $message, $headers, $attachments );
	}

	/**
	 * Get email HTML template
	 *
	 * @param int $booking_id Booking ID
	 * @param string $customer_name Customer name
	 * @param string $booking_type Booking type
	 * @param string $booking_date Booking date
	 * @param string $booking_time Booking time
	 * @param string $item_name Service/Event name
	 * @param string $ticket_id Ticket ID
	 * @param string $qr_code_path QR code image path
	 * @return string HTML email content
	 */
	private static function get_email_template( $booking_id, $customer_name, $booking_type, $booking_date, $booking_time, $item_name, $ticket_id, $qr_code_path ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/email-ticket-template.php';
		return ob_get_clean();
	}

	/**
	 * Send booking cancellation email
	 *
	 * @param string $customer_email Customer email address
	 * @param string $customer_name Customer name
	 * @param string $booking_type Booking type
	 * @param string $booking_date Booking date
	 * @param string $booking_time Booking time
	 * @param string $item_name Service/Event name
	 * @return bool True if email sent successfully
	 */
	public static function send_cancellation_notification( $customer_email, $customer_name, $booking_type, $booking_date, $booking_time, $item_name ) {
		$subject = sprintf(
			__( 'Booking Cancelled - %s', 'kura-ai-booking-free' ),
			$item_name
		);

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		$message = sprintf(
			'<html><body>' .
			'<h2>%s</h2>' .
			'<p>%s</p>' .
			'<p><strong>%s:</strong> %s</p>' .
			'<p><strong>%s:</strong> %s %s</p>' .
			'<p><strong>%s:</strong> %s</p>' .
			'<p>%s</p>' .
			'</body></html>',
			esc_html__( 'Booking Cancellation Notice', 'kura-ai-booking-free' ),
			esc_html__( 'Your booking has been successfully cancelled.', 'kura-ai-booking-free' ),
			esc_html__( 'Customer', 'kura-ai-booking-free' ),
			esc_html( $customer_name ),
			esc_html__( 'Date/Time', 'kura-ai-booking-free' ),
			esc_html( $booking_date ),
			esc_html( $booking_time ),
			esc_html__( 'Service/Event', 'kura-ai-booking-free' ),
			esc_html( $item_name ),
			esc_html__( 'If you have any questions, please contact our support team.', 'kura-ai-booking-free' )
		);

		return wp_mail( $customer_email, $subject, $message, $headers );
	}

	/**
	 * Send admin notification for new booking
	 *
	 * @param int $booking_id Booking ID
	 * @param string $customer_name Customer name
	 * @param string $customer_email Customer email
	 * @param string $booking_type Booking type
	 * @param string $booking_date Booking date
	 * @param string $booking_time Booking time
	 * @param string $item_name Service/Event name
	 * @return bool True if email sent successfully
	 */
	public static function send_admin_notification( $booking_id, $customer_name, $customer_email, $booking_type, $booking_date, $booking_time, $item_name ) {
		$admin_email = get_option( 'admin_email' );
		$subject = sprintf(
			__( 'New Booking Received - #%d', 'kura-ai-booking-free' ),
			$booking_id
		);

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		$message = sprintf(
			'<html><body>' .
			'<h2>%s</h2>' .
			'<p><strong>%s:</strong> #%d</p>' .
			'<p><strong>%s:</strong> %s</p>' .
			'<p><strong>%s:</strong> %s</p>' .
			'<p><strong>%s:</strong> %s</p>' .
			'<p><strong>%s:</strong> %s %s</p>' .
			'<p><strong>%s:</strong> %s</p>' .
			'</body></html>',
			esc_html__( 'New Booking Notification', 'kura-ai-booking-free' ),
			esc_html__( 'Booking ID', 'kura-ai-booking-free' ),
			$booking_id,
			esc_html__( 'Customer Name', 'kura-ai-booking-free' ),
			esc_html( $customer_name ),
			esc_html__( 'Customer Email', 'kura-ai-booking-free' ),
			esc_html( $customer_email ),
			esc_html__( 'Booking Type', 'kura-ai-booking-free' ),
			esc_html( $booking_type ),
			esc_html__( 'Date/Time', 'kura-ai-booking-free' ),
			esc_html( $booking_date ),
			esc_html( $booking_time ),
			esc_html__( 'Service/Event', 'kura-ai-booking-free' ),
			esc_html( $item_name )
		);

		return wp_mail( $admin_email, $subject, $message, $headers );
	}
}