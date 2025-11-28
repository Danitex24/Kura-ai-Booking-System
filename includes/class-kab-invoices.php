<?php
/**
 * Kura-ai Booking System - Invoices
 *
 * Handles invoice generation, management, and email functionality.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Invoices {

	/**
	 * Initialize invoice hooks
	 */
	public function __construct() {
		add_action( 'kab_booking_completed', array( $this, 'handle_booking_completed' ), 10, 2 );
	}

	/**
	 * Handle booking completion - generate invoice
	 *
	 * @param int $booking_id Booking ID
	 * @param array $booking_data Booking data
	 */
	public function handle_booking_completed( $booking_id, $booking_data ) {
		self::generate_invoice( $booking_id );
	}

	/**
	 * Generate invoice for a booking
	 *
	 * @param int $booking_id Booking ID
	 * @return int|false Invoice ID on success, false on failure
	 */
	public static function generate_invoice( $booking_id ) {
		global $wpdb;

		// Get booking details
		$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d", $booking_id ), ARRAY_A );
		
		if ( ! $booking ) {
			return false;
		}

		// Get customer details
		$customer_name  = '';
		$customer_email = '';
		
		if ( $booking['user_id'] ) {
			$user = get_user_by( 'id', $booking['user_id'] );
			if ( $user ) {
				$customer_name  = $user->display_name;
				$customer_email = $user->user_email;
			}
		}

		// Get item details based on booking type
		$item_name = '';
		$price     = 0.00;

		if ( 'service' === $booking['booking_type'] && $booking['service_id'] ) {
			$service = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_services WHERE id = %d", $booking['service_id'] ), ARRAY_A );
			if ( $service ) {
				$item_name = $service['name'];
				$price     = floatval( $service['price'] );
			}
		} elseif ( 'event' === $booking['booking_type'] && $booking['event_id'] ) {
			$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_events WHERE id = %d", $booking['event_id'] ), ARRAY_A );
			if ( $event ) {
				$item_name = $event['name'];
				$price     = floatval( $event['price'] );
			}
		}

		// Generate invoice number
		$invoice_number = self::generate_invoice_number();

		// Calculate amounts
		$subtotal     = $price;
		$tax_amount   = 0.00; // No tax in free version
		$total_amount = $subtotal + $tax_amount;

		// Create invoice record
		$wpdb->insert(
			$wpdb->prefix . 'kab_invoices',
			array(
				'invoice_number' => $invoice_number,
				'booking_id'     => $booking_id,
				'user_id'        => $booking['user_id'],
				'customer_name'  => $customer_name,
				'customer_email' => $customer_email,
				'item_name'      => $item_name,
				'subtotal'       => $subtotal,
				'tax_amount'     => $tax_amount,
				'total_amount'   => $total_amount,
				'payment_status' => 'pending',
				'created_at'     => current_time( 'mysql' ),
			),
			array(
				'%s', '%d', '%d', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s'
			)
		);

		$invoice_id = $wpdb->insert_id;

		if ( $invoice_id ) {
			// Generate PDF
			require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoice-pdf.php';
			$pdf_path = KAB_Invoice_PDF::create_pdf( $invoice_id );

			// Update invoice with PDF path
			if ( $pdf_path ) {
				$wpdb->update(
					$wpdb->prefix . 'kab_invoices',
					array( 'pdf_path' => $pdf_path ),
					array( 'id' => $invoice_id ),
					array( '%s' ),
					array( '%d' )
				);
			}

			// Send email
			self::email_invoice( $invoice_id );

			return $invoice_id;
		}

		return false;
	}

	/**
	 * Generate sequential invoice number
	 *
	 * @return string Invoice number
	 */
	private static function generate_invoice_number() {
		global $wpdb;

		// Get the last invoice number
		$last_invoice = $wpdb->get_var( "SELECT invoice_number FROM {$wpdb->prefix}kab_invoices ORDER BY id DESC LIMIT 1" );

		if ( $last_invoice ) {
			// Extract the numeric part and increment
			$number = intval( substr( $last_invoice, 1 ) ) + 1;
			return '#' . str_pad( $number, 4, '0', STR_PAD_LEFT );
		}

		// First invoice
		return '#0001';
	}

	/**
	 * Email invoice to customer
	 *
	 * @param int $invoice_id Invoice ID
	 * @return bool True on success, false on failure
	 */
	public static function email_invoice( $invoice_id ) {
		global $wpdb;

		// Get invoice details
		$invoice = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_invoices WHERE id = %d", $invoice_id ), ARRAY_A );

		if ( ! $invoice || empty( $invoice['customer_email'] ) || empty( $invoice['pdf_path'] ) ) {
			return false;
		}

		// Get company settings
		$company_name  = get_option( 'kab_company_name', 'Kura-ai Booking' );
		$company_email = get_option( 'kab_support_email', get_option( 'admin_email' ) );

		// Email subject
		$subject = sprintf( __( 'Your Invoice for %s - %s', 'kura-ai-booking-free' ), $invoice['item_name'], $company_name );

		// Email body
		$message = self::get_invoice_email_template( $invoice );

		// Email headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $company_name . ' <' . $company_email . '>',
		);

		// Attach PDF
		$attachments = array( ABSPATH . wp_parse_url( $invoice['pdf_path'], PHP_URL_PATH ) );

		// Send email
		return wp_mail( $invoice['customer_email'], $subject, $message, $headers, $attachments );
	}

	/**
	 * Get invoice email template
	 *
	 * @param array $invoice Invoice data
	 * @return string Email HTML content
	 */
	private static function get_invoice_email_template( $invoice ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/email-invoice-template.php';
		return ob_get_clean();
	}

	/**
	 * Get invoice by ID
	 *
	 * @param int $invoice_id Invoice ID
	 * @return array|false Invoice data or false if not found
	 */
	public static function get_invoice( $invoice_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_invoices WHERE id = %d", $invoice_id ), ARRAY_A );
	}

	/**
	 * Get all invoices with optional filters
	 *
	 * @param array $filters Query filters
	 * @return array Invoice records
	 */
	public static function get_invoices( $filters = array() ) {
		global $wpdb;

		$where_clauses = array();
		$query_params  = array();

		// Date range filter
		if ( ! empty( $filters['date_from'] ) && ! empty( $filters['date_to'] ) ) {
			$where_clauses[] = 'invoice_date BETWEEN %s AND %s';
			$query_params[]  = $filters['date_from'];
			$query_params[]  = $filters['date_to'];
		}

		// Payment status filter
		if ( ! empty( $filters['payment_status'] ) ) {
			$where_clauses[] = 'payment_status = %s';
			$query_params[]  = $filters['payment_status'];
		}

		// Search filter
		if ( ! empty( $filters['search'] ) ) {
			$where_clauses[] = '(invoice_number LIKE %s OR customer_name LIKE %s)';
			$search_term     = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$query_params[]  = $search_term;
			$query_params[]  = $search_term;
		}

		$where_sql = '';
		if ( ! empty( $where_clauses ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
		}

		$query = "SELECT * FROM {$wpdb->prefix}kab_invoices {$where_sql} ORDER BY created_at DESC";

		if ( ! empty( $query_params ) ) {
			$query = $wpdb->prepare( $query, $query_params );
		}

		return $wpdb->get_results( $query, ARRAY_A );
	}
}