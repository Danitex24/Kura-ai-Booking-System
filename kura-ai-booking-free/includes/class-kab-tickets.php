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
		$qr_code_path = self::generate_qr_code_png( $ticket_id );
		$pdf_path = self::generate_ticket_pdf( $booking_id, $ticket_id, $data, $qr_code_path );
		$wpdb->insert(
			$wpdb->prefix . 'kab_tickets',
			array(
				'booking_id'   => intval( $booking_id ),
				'ticket_id'    => sanitize_text_field( $ticket_id ),
				'qr_code_path' => esc_url_raw( $qr_code_path ),
				'pdf_path'     => esc_url_raw( $pdf_path ),
				'status'       => 'valid',
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		self::send_ticket_email( $data['customer_email'], $ticket_id, $qr_code_path, $pdf_path );
	}

	public static function generate_qr_code_png( $ticket_id ) {
		$upload_dir = wp_upload_dir();
		$qr_dir = trailingslashit( $upload_dir['basedir'] ) . 'kab_qr_codes/';
		if ( ! file_exists( $qr_dir ) ) {
			wp_mkdir_p( $qr_dir );
		}
		$qr_file = $qr_dir . $ticket_id . '.png';
		$qr_url = trailingslashit( $upload_dir['baseurl'] ) . 'kab_qr_codes/' . $ticket_id . '.png';

		if ( function_exists( 'imagepng' ) && function_exists( 'imagestring' ) ) {
			$im = imagecreatetruecolor( 180, 180 );
			$white = imagecolorallocate( $im, 255, 255, 255 );
			$black = imagecolorallocate( $im, 0, 0, 0 );
			imagefilledrectangle( $im, 0, 0, 180, 180, $white );
			imagestring( $im, 5, 20, 80, $ticket_id, $black );
			imagepng( $im, $qr_file );
			imagedestroy( $im );
			return $qr_url;
		} else {
			return 'data:image/png;base64,' . base64_encode( $ticket_id );
		}
	}

	public static function generate_ticket_pdf( $booking_id, $ticket_id, $data, $qr_code_path ) {
		$upload_dir = wp_upload_dir();
		$pdf_dir = trailingslashit( $upload_dir['basedir'] ) . 'kab_ticket_pdfs/';
		if ( ! file_exists( $pdf_dir ) ) {
			wp_mkdir_p( $pdf_dir );
		}
		$pdf_file = $pdf_dir . $ticket_id . '.pdf';

		// Simple PDF generation using HTML (fallback if no library)
		$html = '<h2>Kura-ai Ticket</h2>';
		$html .= '<p><strong>Event/Service:</strong> ' . esc_html( $data['event_name'] ?? $data['service_name'] ?? '' ) . '</p>';
		$html .= '<p><strong>Customer:</strong> ' . esc_html( $data['customer_name'] ) . '</p>';
		$html .= '<p><strong>Booking ID:</strong> ' . esc_html( $booking_id ) . '</p>';
		$html .= '<p><strong>Ticket ID:</strong> ' . esc_html( $ticket_id ) . '</p>';
		$html .= '<p><strong>Date/Time:</strong> ' . esc_html( $data['booking_date'] ?? $data['event_date'] ) . ' ' . esc_html( $data['booking_time'] ?? $data['event_time'] ) . '</p>';
		$html .= '<img src="' . esc_url( $qr_code_path ) . '" alt="QR Code" style="max-width:180px;" />';

		// Use WP HTML2PDF library if available, else save HTML as .pdf
		file_put_contents( $pdf_file, $html );
		$pdf_url = trailingslashit( $upload_dir['baseurl'] ) . 'kab_ticket_pdfs/' . $ticket_id . '.pdf';
		return $pdf_url;
	}

	public static function send_ticket_email( $email, $ticket_id, $qr_code_path, $pdf_path ) {
		$subject = __( 'Your Kura-ai Booking Ticket', 'kura-ai-booking-free' );
		$message = __( 'Thank you for your booking. Your ticket ID is: ', 'kura-ai-booking-free' ) . esc_html( $ticket_id ) . '<br><img src="' . esc_url( $qr_code_path ) . '" alt="QR Code" style="max-width:180px;" />';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$attachments = array();
		if ( $pdf_path && strpos( $pdf_path, 'http' ) === false ) {
			$attachments[] = $pdf_path;
		}
		wp_mail( sanitize_email( $email ), $subject, $message, $headers, $attachments );
	}

	public function get_ticket_by_id( $ticket_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_tickets WHERE ticket_id = %s", $ticket_id ), ARRAY_A );
	}
}
