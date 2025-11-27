<?php
/**
 * Kura-ai Booking System PDF Generator
 *
 * Handles PDF generation using TCPDF library (fallback to HTML if not available).
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF Generator for Kura-ai Booking System.
 *
 * Handles PDF ticket generation with TCPDF support and HTML fallback.
 *
 * @since 1.0.0
 */
class KAB_PDF_Generator {

	/**
	 * Generate ticket PDF.
	 *
	 * @since 1.0.0
	 * @param int    $booking_id Booking ID.
	 * @param string $ticket_id Ticket ID.
	 * @param array  $data Booking data.
	 * @param string $qr_code_path QR code image path.
	 * @return string|bool PDF file URL on success, false on failure.
	 */
	public static function generate_ticket_pdf( $booking_id, $ticket_id, $data, $qr_code_path ) {
		$upload_dir = wp_upload_dir();
		$pdf_dir    = trailingslashit( $upload_dir['basedir'] ) . 'kab_ticket_pdfs/';

		if ( ! file_exists( $pdf_dir ) ) {
			wp_mkdir_p( $pdf_dir );
		}

		$pdf_file = $pdf_dir . $ticket_id . '.pdf';

		// Try to use TCPDF if available, otherwise fallback to HTML.
		if ( class_exists( 'TCPDF' ) ) {
			return self::generate_with_tcpdf( $booking_id, $ticket_id, $data, $qr_code_path, $pdf_file );
		} else {
			return self::generate_with_html_fallback( $booking_id, $ticket_id, $data, $qr_code_path, $pdf_file );
		}
	}

	/**
	 * Generate PDF using TCPDF.
	 *
	 * @since 1.0.0
	 * @param int    $booking_id Booking ID.
	 * @param string $ticket_id Ticket ID.
	 * @param array  $data Booking data.
	 * @param string $qr_code_path QR code image path.
	 * @param string $pdf_file Output file path.
	 * @return string|bool PDF file URL on success, false on failure.
	 */
	private static function generate_with_tcpdf( $booking_id, $ticket_id, $data, $qr_code_path, $pdf_file ) {
		try {
			$pdf = new TCPDF( 'P', 'mm', 'A4', true, 'UTF-8', false );

			// Set document information.
			$pdf->SetCreator( 'Kura-ai Booking System' );
			$pdf->SetAuthor( 'Kura-ai' );
			$pdf->SetTitle( 'Booking Ticket - ' . $ticket_id );
			$pdf->SetSubject( 'Booking Confirmation' );

			// Add a page.
			$pdf->AddPage();

			// Set font.
			$pdf->SetFont( 'helvetica', 'B', 16 );

			// Title.
			$pdf->Cell( 0, 10, __( 'Kura-ai Booking Ticket', 'kura-ai-booking-free' ), 0, 1, 'C' );
			$pdf->Ln( 10 );

			// Set font for content.
			$pdf->SetFont( 'helvetica', '', 12 );

			// Ticket information.
			$event_name = $data['event_name'] ?? $data['service_name'] ?? '';
			$date_time  = ( $data['booking_date'] ?? $data['event_date'] ) . ' ' . ( $data['booking_time'] ?? $data['event_time'] );

			$pdf->Cell( 40, 10, __( 'Event/Service:', 'kura-ai-booking-free' ), 0, 0, 'L' );
			$pdf->Cell( 0, 10, $event_name, 0, 1, 'L' );
			$pdf->Ln( 5 );

			$pdf->Cell( 40, 10, __( 'Customer:', 'kura-ai-booking-free' ), 0, 0, 'L' );
			$pdf->Cell( 0, 10, $data['customer_name'], 0, 1, 'L' );
			$pdf->Ln( 5 );

			$pdf->Cell( 40, 10, __( 'Booking ID:', 'kura-ai-booking-free' ), 0, 0, 'L' );
			$pdf->Cell( 0, 10, $booking_id, 0, 1, 'L' );
			$pdf->Ln( 5 );

			$pdf->Cell( 40, 10, __( 'Ticket ID:', 'kura-ai-booking-free' ), 0, 0, 'L' );
			$pdf->Cell( 0, 10, $ticket_id, 0, 1, 'L' );
			$pdf->Ln( 5 );

			$pdf->Cell( 40, 10, __( 'Date/Time:', 'kura-ai-booking-free' ), 0, 0, 'L' );
			$pdf->Cell( 0, 10, $date_time, 0, 1, 'L' );
			$pdf->Ln( 15 );

			// QR Code.
			if ( ! empty( $qr_code_path ) ) {
				$pdf->SetFont( 'helvetica', 'B', 14 );
				$pdf->Cell( 0, 10, __( 'Scan this QR Code for Validation', 'kura-ai-booking-free' ), 0, 1, 'C' );
				$pdf->SetFont( 'helvetica', '', 12 );

				// Add QR code image.
				if ( strpos( $qr_code_path, 'http' ) !== false ) {
					$pdf->Image( $qr_code_path, 85, null, 40, 40, 'PNG', '', 'C', true, 300, '', false, false, 0, false, false, false );
				}
			}

			$pdf->Ln( 20 );

			// Footer.
			$pdf->SetFont( 'helvetica', 'I', 10 );
			$pdf->Cell( 0, 10, __( 'Thank you for choosing Kura-ai Booking System', 'kura-ai-booking-free' ), 0, 1, 'C' );
			$pdf->Cell( 0, 10, __( 'Present this ticket for entry', 'kura-ai-booking-free' ), 0, 1, 'C' );

			// Save PDF.
			$pdf->Output( $pdf_file, 'F' );

			$upload_dir = wp_upload_dir();
			return trailingslashit( $upload_dir['baseurl'] ) . 'kab_ticket_pdfs/' . $ticket_id . '.pdf';

		} catch ( Exception $e ) {
			error_log( 'TCPDF Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Generate HTML fallback PDF.
	 *
	 * @since 1.0.0
	 * @param int    $booking_id Booking ID.
	 * @param string $ticket_id Ticket ID.
	 * @param array  $data Booking data.
	 * @param string $qr_code_path QR code image path.
	 * @param string $pdf_file Output file path.
	 * @return string|bool PDF file URL on success, false on failure.
	 */
	private static function generate_with_html_fallback( $booking_id, $ticket_id, $data, $qr_code_path, $pdf_file ) {
		$event_name = $data['event_name'] ?? $data['service_name'] ?? '';
		$date_time  = ( $data['booking_date'] ?? $data['event_date'] ) . ' ' . ( $data['booking_time'] ?? $data['event_time'] );

		$html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . __( 'Kura-ai Booking Ticket', 'kura-ai-booking-free' ) . '</title>';
		$html .= '<style>body{font-family:Arial,sans-serif;margin:20px;background:#f8f9fa}.ticket{max-width:600px;margin:0 auto;background:white;border:2px solid #0073aa;border-radius:10px;padding:30px}.header{text-align:center;margin-bottom:30px;border-bottom:2px solid #0073aa;padding-bottom:20px}.header h1{color:#0073aa;margin:0}.info-row{display:flex;justify-content:space-between;margin-bottom:15px;border-bottom:1px solid #eee;padding-bottom:10px}.info-label{font-weight:bold;color:#555;min-width:120px}.info-value{color:#333}.qr-section{text-align:center;margin-top:30px;padding-top:20px;border-top:2px solid #0073aa}.qr-code{max-width:180px;height:auto}.footer{text-align:center;margin-top:30px;color:#666;font-size:12px}</style>';
		$html .= '</head><body>';
		$html .= '<div class="ticket">';
		$html .= '<div class="header"><h1>' . __( 'Kura-ai Booking Ticket', 'kura-ai-booking-free' ) . '</h1></div>';
		$html .= '<div class="info-row"><span class="info-label">' . __( 'Event/Service:', 'kura-ai-booking-free' ) . '</span><span class="info-value">' . esc_html( $event_name ) . '</span></div>';
		$html .= '<div class="info-row"><span class="info-label">' . __( 'Customer:', 'kura-ai-booking-free' ) . '</span><span class="info-value">' . esc_html( $data['customer_name'] ) . '</span></div>';
		$html .= '<div class="info-row"><span class="info-label">' . __( 'Booking ID:', 'kura-ai-booking-free' ) . '</span><span class="info-value">' . esc_html( $booking_id ) . '</span></div>';
		$html .= '<div class="info-row"><span class="info-label">' . __( 'Ticket ID:', 'kura-ai-booking-free' ) . '</span><span class="info-value">' . esc_html( $ticket_id ) . '</span></div>';
		$html .= '<div class="info-row"><span class="info-label">' . __( 'Date/Time:', 'kura-ai-booking-free' ) . '</span><span class="info-value">' . esc_html( $date_time ) . '</span></div>';

		if ( ! empty( $qr_code_path ) ) {
			$html .= '<div class="qr-section"><h3>' . __( 'Scan this QR Code for Validation', 'kura-ai-booking-free' ) . '</h3>';
			$html .= '<img src="' . esc_url( $qr_code_path ) . '" alt="QR Code" class="qr-code" /></div>';
		}

		$html .= '<div class="footer"><p>' . __( 'Thank you for choosing Kura-ai Booking System', 'kura-ai-booking-free' ) . '</p>';
		$html .= '<p>' . __( 'Present this ticket for entry', 'kura-ai-booking-free' ) . '</p></div>';
		$html .= '</div></body></html>';

		// Save HTML as PDF file (simple fallback).
		file_put_contents( $pdf_file, $html );

		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir['baseurl'] ) . 'kab_ticket_pdfs/' . $ticket_id . '.pdf';
	}
}
