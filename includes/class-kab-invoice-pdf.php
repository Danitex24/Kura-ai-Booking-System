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
		if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['basedir'] ) || empty( $upload_dir['baseurl'] ) ) {
			return false;
		}
		$invoice_dir = trailingslashit( $upload_dir['basedir'] ) . 'kuraai/invoices/';

		if ( ! file_exists( $invoice_dir ) ) {
			wp_mkdir_p( $invoice_dir );
		}

        $safe_number  = preg_replace( '/[^A-Za-z0-9_.-]/', '', str_replace( '#', '', (string) $invoice['invoice_number'] ) );
        $pdf_filename = 'invoice-' . $safe_number . '.pdf';
        $pdf_path      = $invoice_dir . $pdf_filename;
        $pdf_url       = trailingslashit( $upload_dir['baseurl'] ) . 'kuraai/invoices/' . $pdf_filename;

        // Prefer mPDF if available, then TCPDF, else fallback
        if ( self::use_mpdf() ) {
            $result = self::generate_with_mpdf( $invoice, $booking, $pdf_path );
        } elseif ( self::use_tcpdf() ) {
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
     * Check if mPDF is available (try loading local or global autoloaders)
     */
    private static function use_mpdf() {
        if ( class_exists( '\Mpdf\Mpdf' ) ) {
            return true;
        }
        $paths = array(
            KAB_FREE_PLUGIN_DIR . 'vendor/autoload.php',
            ABSPATH . 'vendor/autoload.php',
        );
        foreach ( $paths as $p ) {
            if ( file_exists( $p ) ) {
                require_once $p;
                if ( class_exists( '\Mpdf\Mpdf' ) ) {
                    return true;
                }
            }
        }
        return false;
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
        $lines = array(
            'Invoice',
            'Number: ' . (string) $invoice['invoice_number'],
            'Date: ' . date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ),
            'Customer: ' . (string) $invoice['customer_name'],
            'Email: ' . (string) $invoice['customer_email'],
            'Item: ' . (string) $invoice['item_name'],
            'Subtotal: ' . number_format( (float) $invoice['subtotal'], 2 ),
            'Tax: ' . number_format( (float) $invoice['tax_amount'], 2 ),
            'Total: ' . number_format( (float) $invoice['total_amount'], 2 ),
            'Status: ' . ucfirst( (string) $invoice['payment_status'] ),
        );

        $content = "BT\n/F1 12 Tf\n14 TL\n72 760 Td\n";
        foreach ( $lines as $idx => $text ) {
            $safe = str_replace( array('\\', '(', ')'), array('\\\\', '\\(', '\\)'), $text );
            if ( $idx === 0 ) {
                $content .= "(" . $safe . ") Tj\n";
            } else {
                $content .= "T*\n(" . $safe . ") Tj\n";
            }
        }
        $content .= "ET\n";

        $pdf = "%PDF-1.4\n";
        $offsets = array();
        $obj = function( $num, $body ) use ( &$pdf, &$offsets ) {
            $offsets[$num] = strlen( $pdf );
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        };

        $obj( 1, "<< /Type /Catalog /Pages 2 0 R >>" );
        $obj( 2, "<< /Type /Pages /Kids [3 0 R] /Count 1 >>" );
        $obj( 4, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>" );
        $len = strlen( $content );
        $obj( 5, "<< /Length " . $len . " >>\nstream\n" . $content . "endstream" );
        $obj( 3, "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>" );

        $xref_offset = strlen( $pdf );
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ( $i = 1; $i <= 5; $i++ ) {
            $pdf .= sprintf( "%010d 00000 n \n", $offsets[$i] );
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xref_offset . "\n%%EOF";

        file_put_contents( $pdf_path, $pdf );
        return true;
    }

    /**
     * Generate PDF using mPDF
     */
    private static function generate_with_mpdf( $invoice, $booking, $pdf_path ) {
        try {
            $mpdf = new \Mpdf\Mpdf([ 'tempDir' => wp_upload_dir()['basedir'] . '/kuraai/tmp' ]);
            $html = self::get_invoice_html_content( $invoice, $booking );
            $mpdf->WriteHTML( $html );
            $mpdf->Output( $pdf_path, 'F' );
            return true;
        } catch ( \Exception $e ) {
            error_log( 'mPDF Error: ' . $e->getMessage() );
            return false;
        }
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

        $upload_dir = wp_upload_dir();
		// If mPDF is available, regenerate to ensure styled layout
		if ( self::use_mpdf() ) {
			self::create_pdf( $invoice_id );
		}

		$file_path = '';
        if ( ! empty( $upload_dir['baseurl'] ) && ! empty( $upload_dir['basedir'] ) && is_string( $invoice['pdf_path'] ) ) {
            if ( strpos( $invoice['pdf_path'], $upload_dir['baseurl'] ) === 0 ) {
                $rel = substr( $invoice['pdf_path'], strlen( $upload_dir['baseurl'] ) );
                $file_path = trailingslashit( $upload_dir['basedir'] ) . ltrim( $rel, '/' );
            }
        }
        if ( empty( $file_path ) ) {
            $file_path = ABSPATH . wp_parse_url( $invoice['pdf_path'], PHP_URL_PATH );
        }
        if ( ! file_exists( $file_path ) || ( is_file( $file_path ) && filesize( $file_path ) < 200 ) ) {
            if ( ! empty( $upload_dir['basedir'] ) && is_string( $invoice['invoice_number'] ) ) {
                $safe_number = preg_replace( '/[^A-Za-z0-9_.-]/', '', str_replace( '#', '', (string) $invoice['invoice_number'] ) );
                $alt_path    = trailingslashit( $upload_dir['basedir'] ) . 'kuraai/invoices/invoice-' . $safe_number . '.pdf';
                if ( file_exists( $alt_path ) ) {
                    $file_path = $alt_path;
                }
            }
        }
        if ( ! file_exists( $file_path ) || ( is_file( $file_path ) && filesize( $file_path ) < 200 ) ) {
            $regen = self::create_pdf( $invoice_id );
            if ( $regen && strpos( $regen, $upload_dir['baseurl'] ) === 0 ) {
                $rel = substr( $regen, strlen( $upload_dir['baseurl'] ) );
                $file_path = trailingslashit( $upload_dir['basedir'] ) . ltrim( $rel, '/' );
            }
        }
        if ( ! file_exists( $file_path ) ) {
            wp_die( 'PDF file not found' );
        }

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
