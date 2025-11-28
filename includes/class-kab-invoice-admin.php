<?php
/**
 * Kura-ai Booking System - Invoice Admin
 *
 * Handles admin interface for invoice management.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Invoice_Admin extends KAB_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu items
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'kab-dashboard',
			__( 'Invoices', 'kura-ai-booking-free' ),
			__( 'Invoices', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-invoices',
			array( $this, 'render_invoices_page' )
		);

		// Hidden page for invoice details
		add_submenu_page(
			null,
			__( 'Invoice Details', 'kura-ai-booking-free' ),
			__( 'Invoice Details', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-invoice-details',
			array( $this, 'render_invoice_details' )
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, 'kab-invoices' ) !== false || strpos( $hook, 'kab-invoice-details' ) !== false ) {
			wp_enqueue_style( 'kab-admin-invoices', plugins_url( '../assets/css/admin-invoices.css', __FILE__ ), array(), KAB_VERSION );
			wp_enqueue_script( 'kab-admin-invoices', plugins_url( '../assets/js/admin-invoices.js', __FILE__ ), array( 'jquery' ), KAB_VERSION, true );
		}
	}

	/**
	 * Render invoices page
	 */
	public function render_invoices_page() {
		// Handle actions
		$this->handle_invoice_actions();

		// Get filters
		$filters = array();
		if ( ! empty( $_GET['date_from'] ) && ! empty( $_GET['date_to'] ) ) {
			$filters['date_from'] = sanitize_text_field( $_GET['date_from'] );
			$filters['date_to']   = sanitize_text_field( $_GET['date_to'] );
		}
		if ( ! empty( $_GET['payment_status'] ) ) {
			$filters['payment_status'] = sanitize_text_field( $_GET['payment_status'] );
		}
		if ( ! empty( $_GET['search'] ) ) {
			$filters['search'] = sanitize_text_field( $_GET['search'] );
		}

		// Get invoices
		require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
		$invoices = KAB_Invoices::get_invoices( $filters );

		// Render static header
		$this->render_static_header( 'invoices' );

		// Render page content
		echo '<div class="wrap">';
		echo '<div class="kab-admin-content">';
		echo '<div class="kab-card">';
		echo '<h1>' . esc_html__( 'Invoices', 'kura-ai-booking-free' ) . '</h1>';

		// Filters
		$this->render_invoice_filters( $filters );

		// Invoices table
		if ( $invoices ) {
			echo '<table class="wp-list-table widefat fixed striped kab-invoices-table">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Invoice Number', 'kura-ai-booking-free' ) . '</th>';
			echo '<th>' . esc_html__( 'Customer', 'kura-ai-booking-free' ) . '</th>';
			echo '<th>' . esc_html__( 'Booking Type', 'kura-ai-booking-free' ) . '</th>';
			echo '<th>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</th>';
			echo '<th>' . esc_html__( 'Amount', 'kura-ai-booking-free' ) . '</th>';
			echo '<th>' . esc_html__( 'Payment Status', 'kura-ai-booking-free' ) . '</th>';
			echo '<th>' . esc_html__( 'Actions', 'kura-ai-booking-free' ) . '</th>';
			echo '</tr></thead>';
			echo '<tbody>';

			foreach ( $invoices as $invoice ) {
				echo '<tr>';
				echo '<td>' . esc_html( $invoice['invoice_number'] ) . '</td>';
				echo '<td>' . esc_html( $invoice['customer_name'] ) . '<br><small>' . esc_html( $invoice['customer_email'] ) . '</small></td>';
				echo '<td>' . esc_html( ucfirst( $invoice['item_name'] ) ) . '</td>';
				echo '<td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ) ) . '</td>';
				echo '<td>' . esc_html( number_format( $invoice['total_amount'], 2 ) ) . '</td>';
				echo '<td><span class="kab-status kab-status-' . esc_attr( $invoice['payment_status'] ) . '">' . esc_html( ucfirst( $invoice['payment_status'] ) ) . '</span></td>';
				echo '<td>';
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice['id'] ) ) . '" class="button kab-btn-primary">' . esc_html__( 'View', 'kura-ai-booking-free' ) . '</a>';
				echo '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
		} else {
			echo '<p>' . esc_html__( 'No invoices found.', 'kura-ai-booking-free' ) . '</p>';
		}

		echo '</div>'; // .kab-card
		echo '</div>'; // .kab-admin-content
		echo '</div>'; // .wrap
	}

	/**
	 * Render invoice filters
	 *
	 * @param array $filters Current filters
	 */
	private function render_invoice_filters( $filters ) {
		echo '<div class="kab-invoice-filters">';
		echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '">';
		echo '<input type="hidden" name="page" value="kab-invoices">';

		// Date range
		echo '<div class="kab-form-group">';
		echo '<label>' . esc_html__( 'Date From:', 'kura-ai-booking-free' ) . '</label>';
		echo '<input type="date" name="date_from" value="' . esc_attr( $filters['date_from'] ?? '' ) . '">';
		echo '</div>';

		echo '<div class="kab-form-group">';
		echo '<label>' . esc_html__( 'Date To:', 'kura-ai-booking-free' ) . '</label>';
		echo '<input type="date" name="date_to" value="' . esc_attr( $filters['date_to'] ?? '' ) . '">';
		echo '</div>';

		// Payment status
		echo '<div class="kab-form-group">';
		echo '<label>' . esc_html__( 'Payment Status:', 'kura-ai-booking-free' ) . '</label>';
		echo '<select name="payment_status">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'kura-ai-booking-free' ) . '</option>';
		echo '<option value="pending" ' . selected( $filters['payment_status'] ?? '', 'pending', false ) . '>' . esc_html__( 'Pending', 'kura-ai-booking-free' ) . '</option>';
		echo '<option value="paid" ' . selected( $filters['payment_status'] ?? '', 'paid', false ) . '>' . esc_html__( 'Paid', 'kura-ai-booking-free' ) . '</option>';
		echo '<option value="partial" ' . selected( $filters['payment_status'] ?? '', 'partial', false ) . '>' . esc_html__( 'Partial', 'kura-ai-booking-free' ) . '</option>';
		echo '</select>';
		echo '</div>';

		// Search
		echo '<div class="kab-form-group">';
		echo '<label>' . esc_html__( 'Search:', 'kura-ai-booking-free' ) . '</label>';
		echo '<input type="text" name="search" value="' . esc_attr( $filters['search'] ?? '' ) . '" placeholder="' . esc_attr__( 'Invoice number or customer name', 'kura-ai-booking-free' ) . '">';
		echo '</div>';

		echo '<div class="kab-form-group">';
		echo '<input type="submit" class="button kab-btn-primary" value="' . esc_attr__( 'Filter', 'kura-ai-booking-free' ) . '">';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-invoices' ) ) . '" class="button">' . esc_html__( 'Reset', 'kura-ai-booking-free' ) . '</a>';
		echo '</div>';

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render invoice details page
	 */
	public function render_invoice_details() {
		if ( empty( $_GET['invoice_id'] ) ) {
			wp_die( 'Invoice ID is required' );
		}

		$invoice_id = intval( $_GET['invoice_id'] );

		require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
		$invoice = KAB_Invoices::get_invoice( $invoice_id );

		if ( ! $invoice ) {
			wp_die( 'Invoice not found' );
		}

		// Get booking details
		global $wpdb;
		$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d", $invoice['booking_id'] ), ARRAY_A );

		// Render static header
		$this->render_static_header( 'invoices' );

		// Render page content
		echo '<div class="wrap">';
		echo '<div class="kab-admin-content">';
		echo '<div class="kab-card">';

		// Back button
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=kab-invoices' ) ) . '" class="button">&larr; ' . esc_html__( 'Back to Invoices', 'kura-ai-booking-free' ) . '</a></p>';

		echo '<h1>' . esc_html__( 'Invoice Details', 'kura-ai-booking-free' ) . ' - ' . esc_html( $invoice['invoice_number'] ) . '</h1>';

		// Invoice details
		echo '<div class="kab-invoice-details">';
		echo '<div class="kab-detail-row">';
		echo '<div class="kab-detail-col">';
		echo '<h3>' . esc_html__( 'Invoice Information', 'kura-ai-booking-free' ) . '</h3>';
		echo '<p><strong>' . esc_html__( 'Invoice Number:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $invoice['invoice_number'] ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Issue Date:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ) ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Payment Status:', 'kura-ai-booking-free' ) . '</strong> <span class="kab-status kab-status-' . esc_attr( $invoice['payment_status'] ) . '">' . esc_html( ucfirst( $invoice['payment_status'] ) ) . '</span></p>';
		echo '</div>';

		echo '<div class="kab-detail-col">';
		echo '<h3>' . esc_html__( 'Customer Information', 'kura-ai-booking-free' ) . '</h3>';
		echo '<p><strong>' . esc_html__( 'Name:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $invoice['customer_name'] ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Email:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $invoice['customer_email'] ) . '</p>';
		echo '</div>';
		echo '</div>'; // .kab-detail-row

		// Booking information
		echo '<div class="kab-detail-row">';
		echo '<div class="kab-detail-col">';
		echo '<h3>' . esc_html__( 'Booking Information', 'kura-ai-booking-free' ) . '</h3>';
		echo '<p><strong>' . esc_html__( 'Item:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $invoice['item_name'] ) . '</p>';
		if ( $booking ) {
			echo '<p><strong>' . esc_html__( 'Booking Date:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $booking['booking_date'] ) . '</p>';
			echo '<p><strong>' . esc_html__( 'Booking Time:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $booking['booking_time'] ) . '</p>';
		}
		echo '</div>';

		echo '<div class="kab-detail-col">';
		echo '<h3>' . esc_html__( 'Payment Details', 'kura-ai-booking-free' ) . '</h3>';
		echo '<p><strong>' . esc_html__( 'Subtotal:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( number_format( $invoice['subtotal'], 2 ) ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Tax:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( number_format( $invoice['tax_amount'], 2 ) ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Total:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( number_format( $invoice['total_amount'], 2 ) ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Payment Method:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $invoice['payment_method'] ?? 'N/A' ) . '</p>';
		echo '</div>';
		echo '</div>'; // .kab-detail-row

		echo '</div>'; // .kab-invoice-details

		// Action buttons
		echo '<div class="kab-invoice-actions">';
		echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_download_invoice&invoice_id=' . $invoice_id ), 'kab_download_invoice_' . $invoice_id ) ) . '" class="button kab-btn-primary">' . esc_html__( 'Download PDF', 'kura-ai-booking-free' ) . '</a>';
		echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_preview_invoice&invoice_id=' . $invoice_id ), 'kab_preview_invoice_' . $invoice_id ) ) . '" class="button" target="_blank">' . esc_html__( 'Preview PDF', 'kura-ai-booking-free' ) . '</a>';
		echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_resend_invoice&invoice_id=' . $invoice_id ), 'kab_resend_invoice_' . $invoice_id ) ) . '" class="button">' . esc_html__( 'Re-send Email', 'kura-ai-booking-free' ) . '</a>';
		echo '</div>';

		echo '</div>'; // .kab-card
		echo '</div>'; // .kab-admin-content
		echo '</div>'; // .wrap
	}

	/**
	 * Handle invoice actions
	 */
	private function handle_invoice_actions() {
		// Handle actions via admin-post.php
		if ( ! empty( $_GET['action'] ) && ! empty( $_GET['invoice_id'] ) && ! empty( $_GET['_wpnonce'] ) ) {
			$action     = sanitize_text_field( $_GET['action'] );
			$invoice_id = intval( $_GET['invoice_id'] );
			$nonce      = sanitize_text_field( $_GET['_wpnonce'] );

			if ( 'kab_download_invoice' === $action ) {
				if ( wp_verify_nonce( $nonce, 'kab_download_invoice_' . $invoice_id ) ) {
					require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoice-pdf.php';
					KAB_Invoice_PDF::serve_pdf( $invoice_id, 'attachment' );
					exit;
				}
			} elseif ( 'kab_preview_invoice' === $action ) {
				if ( wp_verify_nonce( $nonce, 'kab_preview_invoice_' . $invoice_id ) ) {
					require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoice-pdf.php';
					KAB_Invoice_PDF::serve_pdf( $invoice_id, 'inline' );
					exit;
				}
			} elseif ( 'kab_resend_invoice' === $action ) {
				if ( wp_verify_nonce( $nonce, 'kab_resend_invoice_' . $invoice_id ) ) {
					require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
					if ( KAB_Invoices::email_invoice( $invoice_id ) ) {
						add_action( 'admin_notices', function() {
							echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Invoice email sent successfully.', 'kura-ai-booking-free' ) . '</p></div>';
						} );
					} else {
						add_action( 'admin_notices', function() {
							echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to send invoice email.', 'kura-ai-booking-free' ) . '</p></div>';
						} );
					}
				}
			}
		}
	}
}