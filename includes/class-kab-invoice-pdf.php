<?php
/**
 * Kura-ai Booking System - Invoice PDF Generator
 *
 * Handles PDF invoice generation using TCPDF or HTML fallback.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Invoice_PDF {

	/**
	 * Create PDF invoice
	 *
	 * @param int $invoice_id Invoice ID
	 * @return string|false PDF file path on success, false on failure
	 */
	public static function create_pdf( $invoice_id ) {
		global $wpdb;

		// Get invoice details
		$invoice = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_invoices WHERE id = %d", $invoice_id ), ARRAY_A );

		if ( ! $invoice ) {
			return false;
		}

		// Get booking details
		$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d", $invoice['booking_id'] ), ARRAY_A );

		// Prepare upload directory
		$upload_dir = wp_upload_dir();
		$invoice_dir = trailingslashit( $upload_dir['basedir'] ) . 'kuraai/invoices/';

		if ( ! file_exists( $invoice_dir ) ) {
			wp_mkdir_p( $invoice_dir );
		}

		$pdf_filename = 'invoice-' . $invoice['invoice_number'] . '.pdf';
		$pdf_path      = $invoice_dir . $pdf_filename;
		$pdf_url       = trailingslashit( $upload_dir['baseurl'] ) . 'kuraai/invoices/' . $pdf_filename;

		// Try to use TCPDF if available
		if ( self::use_tcpdf() ) {
			$result = self::generate_with_tcpdf( $invoice, $booking, $pdf_path );
		} else {
			// Fallback to HTML to PDF conversion
			$result = self::generate_with_html( $invoice, $booking, $pdf_path );
		}

		if ( $result ) {
			return $pdf_url;
		}

		return false;
	}

	/**
	 * Check if TCPDF is available
	 *
	 * @return bool True if TCPDF is available
	 */
	private static function use_tcpdf() {
		return class_exists( 'TCPDF' );
	}

	/**
	 * Generate PDF using TCPDF
	 *
	 * @param array $invoice Invoice data
	 * @param array $booking Booking data
	 * @param string $pdf_path Output PDF path
	 * @return bool True on success, false on failure
	 */
	private static function generate_with_tcpdf( $invoice, $booking, $pdf_path ) {
		try {
			$pdf = new TCPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

			// Set document information
			$pdf->SetCreator( 'Kura-ai Booking System' );
			$pdf->SetAuthor( 'Kura-ai Booking' );
			$pdf->SetTitle( 'Invoice ' . $invoice['invoice_number'] );
			$pdf->SetSubject( 'Booking Invoice' );

			// Add a page
			$pdf->AddPage();

			// Generate HTML content
			$html = self::get_invoice_html_content( $invoice, $booking );

			// Output HTML content
			$pdf->writeHTML( $html, true, false, true, false, '' );

			// Save PDF
			$pdf->Output( $pdf_path, 'F' );

			return true;
		} catch ( Exception $e ) {
			error_log( 'TCPDF Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Generate PDF using HTML fallback
	 *
	 * @param array $invoice Invoice data
	 * @param array $booking Booking data
	 * @param string $pdf_path Output PDF path
	 * @return bool True on success, false on failure
	 */
	private static function generate_with_html( $invoice, $booking, $pdf_path ) {
		// Generate HTML content
		$html = self::get_invoice_html_content( $invoice, $booking );

		// For free version, we'll just save the HTML as a simple solution
		// In a production environment, you might want to use a proper HTML to PDF converter
		file_put_contents( $pdf_path, $html );

		return true;
	}

	/**
	 * Get invoice HTML content for PDF
	 *
	 * @param array $invoice Invoice data
	 * @param array $booking Booking data
	 * @return string HTML content
	 */
	private static function get_invoice_html_content( $invoice, $booking ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/invoice-template.php';
		return ob_get_clean();
	}

	/**
	 * Serve PDF for download
	 *
	 * @param int $invoice_id Invoice ID
	 * @param string $disposition inline|attachment
	 * @return void
	 */
	public static function serve_pdf( $invoice_id, $disposition = 'attachment' ) {
		global $wpdb;

		// Get invoice details
		$invoice = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_invoices WHERE id = %d", $invoice_id ), ARRAY_A );

		if ( ! $invoice || empty( $invoice['pdf_path'] ) ) {
			wp_die( 'Invoice not found' );
		}

		$file_path = ABSPATH . wp_parse_url( $invoice['pdf_path'], PHP_URL_PATH );

		if ( ! file_exists( $file_path ) ) {
			wp_die( 'PDF file not found' );
		}

		// Set headers
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: ' . $disposition . '; filename="invoice-' . $invoice['invoice_number'] . '.pdf"' );
		header( 'Content-Length: ' . filesize( $file_path ) );

		// Output file
		readfile( $file_path );
		exit;
	}
}