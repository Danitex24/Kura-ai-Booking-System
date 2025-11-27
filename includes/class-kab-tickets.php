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
		$pdf_path     = self::generate_ticket_pdf( $booking_id, $ticket_id, $data, $qr_code_path );
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
		self::send_ticket_email( $data['customer_email'], $ticket_id, $qr_code_path, $pdf_path, $data );
	}

	public static function generate_qr_code_png( $ticket_id ) {
		$upload_dir = wp_upload_dir();
		$qr_dir     = trailingslashit( $upload_dir['basedir'] ) . 'kab_qr_codes/';
		if ( ! file_exists( $qr_dir ) ) {
			wp_mkdir_p( $qr_dir );
		}
		$qr_file = $qr_dir . $ticket_id . '.png';
		$qr_url  = trailingslashit( $upload_dir['baseurl'] ) . 'kab_qr_codes/' . $ticket_id . '.png';

		if ( function_exists( 'imagepng' ) && function_exists( 'imagestring' ) ) {
			$im    = imagecreatetruecolor( 180, 180 );
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
		// Use the new PDF generator class
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-pdf-generator.php';
		return KAB_PDF_Generator::generate_ticket_pdf( $booking_id, $ticket_id, $data, $qr_code_path );
	}

	public static function send_ticket_email( $email, $ticket_id, $qr_code_path, $pdf_path, $booking_data = array() ) {
		$subject = __( 'Your Kura-ai Booking Ticket', 'kura-ai-booking-free' );
		
		// Use the email template
		ob_start();
		
		// Extract variables for the template
		$customer_name = isset( $booking_data['customer_name'] ) ? $booking_data['customer_name'] : '';
		$customer_email = isset( $booking_data['customer_email'] ) ? $booking_data['customer_email'] : '';
		$booking_date = isset( $booking_data['booking_date'] ) ? $booking_data['booking_date'] : '';
		$booking_time = isset( $booking_data['booking_time'] ) ? $booking_data['booking_time'] : '';
		$booking_id = isset( $booking_data['booking_id'] ) ? $booking_data['booking_id'] : '';
		
		// Determine event/service name
		$event_name = '';
		$service_name = '';
		if ( isset( $booking_data['booking_type'] ) ) {
			if ( $booking_data['booking_type'] === 'event' && isset( $booking_data['event_id'] ) ) {
				// Get event name from database
				global $wpdb;
				$event = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}kab_events WHERE id = %d", $booking_data['event_id'] ) );
				if ( $event ) {
					$event_name = $event->name;
				}
			} elseif ( $booking_data['booking_type'] === 'service' && isset( $booking_data['service_id'] ) ) {
				// Get service name from database
				global $wpdb;
				$service = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}kab_services WHERE id = %d", $booking_data['service_id'] ) );
				if ( $service ) {
					$service_name = $service->name;
				}
			}
		}
		$booking_item_name = ! empty( $event_name ) ? $event_name : $service_name;
		
		include KAB_FREE_PLUGIN_DIR . 'templates/email-ticket-template.php';
		$message = ob_get_clean();
		
		$headers     = array( 'Content-Type: text/html; charset=UTF-8' );
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
