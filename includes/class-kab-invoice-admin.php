<?php
/**
 * Kura-ai Booking System - Invoice Admin
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KAB_Invoice_Admin extends KAB_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'admin_post_kab_create_invoice', array( $this, 'handle_create_invoice_action' ) );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'kab-dashboard',
            __( 'Invoices', 'kura-ai-booking-free' ),
            __( 'Invoices', 'kura-ai-booking-free' ),
            'manage_options',
            'kab-invoices',
            array( $this, 'render_invoices_page' )
        );

        add_submenu_page(
            'kab-dashboard',
            __( 'Create Invoice', 'kura-ai-booking-free' ),
            __( 'Create Invoice', 'kura-ai-booking-free' ),
            'manage_options',
            'kab-create-invoice',
            array( $this, 'render_create_invoice_page' )
        );

        add_submenu_page(
            'kab-dashboard',
            __( 'Invoice Details', 'kura-ai-booking-free' ),
            __( 'Invoice Details', 'kura-ai-booking-free' ),
            'read',
            'kab-invoice-details',
            array( $this, 'render_invoice_details' )
        );
    }

    public function enqueue_admin_scripts( $hook ) {
        if ( ! is_string( $hook ) ) {
            return;
        }
        if ( strpos( $hook, 'kab-invoices' ) !== false || strpos( $hook, 'kab-invoice-details' ) !== false ) {
            wp_enqueue_style( 'kab-admin-invoices', KAB_FREE_PLUGIN_URL . 'assets/css/admin-invoices.css', array(), KAB_VERSION );
            wp_enqueue_script( 'kab-admin-invoices', KAB_FREE_PLUGIN_URL . 'assets/js/admin-invoices.js', array( 'jquery' ), KAB_VERSION, true );
        }
    }

    public function render_invoices_page() {
        $this->handle_invoice_actions();
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
        require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
		$invoices = KAB_Invoices::get_invoices( $filters );
		// Pagination
		$per_page   = isset( $_GET['per_page'] ) ? max( 1, intval( $_GET['per_page'] ) ) : 10;
		$paged      = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$total_count = KAB_Invoices::get_invoices_count( $filters );
		$total_pages = max( 1, (int) ceil( $total_count / $per_page ) );
		$filters['limit']  = $per_page;
		$filters['offset'] = ( $paged - 1 ) * $per_page;
		$invoices = KAB_Invoices::get_invoices( $filters );
        $paid_count = 0;
        $pending_count = 0;
        $total_revenue = 0.0;
        foreach ( $invoices as $inv ) {
            if ( isset( $inv['payment_status'] ) ) {
                if ( $inv['payment_status'] === 'paid' ) {
                    $paid_count++;
                }
                if ( $inv['payment_status'] === 'pending' ) {
                    $pending_count++;
                }
            }
            $total_revenue += floatval( $inv['total_amount'] );
        }
        $this->render_static_header( 'invoices' );
        echo '<div class="wrap">';
        echo '<div class="kab-admin-content">';
        echo '<div class="kab-card">';
        echo '<div class="kab-card-header" style="display:flex;justify-content:space-between;align-items:center;">';
        echo '<h1 class="kab-card-title"><span class="dashicons dashicons-media-spreadsheet"></span> ' . esc_html__( 'Invoices', 'kura-ai-booking-free' ) . '</h1>';
        $export_url = wp_nonce_url( add_query_arg( $_GET, admin_url( 'admin-post.php?action=kab_export_invoices' ) ), 'kab_export_invoices' );
        echo '<a href="#" class="kab-btn kab-btn-secondary" id="kab-export-pdf-btn" data-has-invoices="' . ( $total_count > 0 ? '1' : '0' ) . '">' . esc_html__( 'Export PDF', 'kura-ai-booking-free' ) . '</a>';
        echo ' <a href="' . esc_url( admin_url( 'admin.php?page=kab-create-invoice' ) ) . '" class="kab-btn kab-btn-primary">' . esc_html__( 'Create Invoice', 'kura-ai-booking-free' ) . '</a>';
        echo '<script type="text/javascript">document.addEventListener("DOMContentLoaded",function(){var b=document.getElementById("kab-export-pdf-btn");if(!b)return;b.addEventListener("click",function(e){var has=b.getAttribute("data-has-invoices")==="1";if(!has){e.preventDefault();if(window.Swal){Swal.fire({icon:"info",title:"' . esc_js( __( 'No invoices', 'kura-ai-booking-free' ) ) . '",text:"' . esc_js( __( 'There are no invoices to export.', 'kura-ai-booking-free' ) ) . '"});}}else{window.location.href="' . esc_url( $export_url ) . '";}});});</script>';
        echo '</div>';
        echo '<div class="kab-invoice-filters">';
        $this->render_invoice_filters( $filters );
        echo '</div>';
        echo '<div class="kab-detail-grid" style="margin-top:10px;">';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Total Invoices', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( $total_count ) . '</div></div>';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Paid', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( $paid_count ) . '</div></div>';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Pending', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( $pending_count ) . '</div></div>';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Total Revenue', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( number_format( $total_revenue, 2 ) ) . '</div></div>';
        echo '</div>';
		if ( $invoices ) {
            echo '<table class="wp-list-table widefat fixed striped kab-invoices-table">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__( 'Invoice', 'kura-ai-booking-free' ) . '</th>';
            echo '<th>' . esc_html__( 'Customer', 'kura-ai-booking-free' ) . '</th>';
            echo '<th>' . esc_html__( 'Item', 'kura-ai-booking-free' ) . '</th>';
            echo '<th>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</th>';
            echo '<th>' . esc_html__( 'Amount', 'kura-ai-booking-free' ) . '</th>';
            echo '<th>' . esc_html__( 'Status', 'kura-ai-booking-free' ) . '</th>';
            echo '<th>' . esc_html__( 'Actions', 'kura-ai-booking-free' ) . '</th>';
            echo '</tr></thead>';
            echo '<tbody>';
			foreach ( $invoices as $invoice ) {
                $status_class = 'kab-status-' . sanitize_key( $invoice['payment_status'] );
                echo '<tr>';
                echo '<td>' . esc_html( $invoice['invoice_number'] ) . '</td>';
                echo '<td>' . esc_html( $invoice['customer_name'] ) . '<br><small>' . esc_html( $invoice['customer_email'] ) . '</small></td>';
                echo '<td>' . esc_html( $invoice['item_name'] ) . '</td>';
                echo '<td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ) ) . '</td>';
                echo '<td>' . esc_html( number_format( $invoice['total_amount'], 2 ) ) . '</td>';
                echo '<td><span class="kab-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( $invoice['payment_status'] ) ) . '</span></td>';
                echo '<td class="kab-invoice-actions">';
                echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice['id'] ) ) . '" class="kab-btn kab-btn-primary">' . esc_html__( 'View', 'kura-ai-booking-free' ) . '</a>';
                echo '</td>';
                echo '</tr>';
			}
			echo '</tbody></table>';
			// Pagination UI
			echo '<div class="kab-pagination" style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-top:12px;">';
			echo '<span>Page ' . esc_html( $paged ) . ' of ' . esc_html( $total_pages ) . '</span>';
			$base_url = admin_url( 'admin.php?page=kab-invoices' );
			$query   = $_GET;
			$query['per_page'] = $per_page;
			if ( $paged > 1 ) {
				$query['paged'] = $paged - 1;
				echo '<a class="kab-btn" href="' . esc_url( add_query_arg( $query, $base_url ) ) . '">&larr; Prev</a>';
			}
			if ( $paged < $total_pages ) {
				$query['paged'] = $paged + 1;
				echo '<a class="kab-btn" href="' . esc_url( add_query_arg( $query, $base_url ) ) . '">Next &rarr;</a>';
			}
			echo '</div>';
		} else {
            echo '<div class="kab-card" style="margin-top:10px;">';
            echo '<div class="kab-card-body">';
            echo '<p>' . esc_html__( 'No invoices found.', 'kura-ai-booking-free' ) . '</p>';
            echo '</div></div>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_invoice_filters( $filters ) {
        echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '">';
        echo '<input type="hidden" name="page" value="kab-invoices">';
        echo '<div class="kab-filter-row">';
        echo '<div class="kab-filter-group">';
        echo '<label class="kab-filter-label">' . esc_html__( 'Date From', 'kura-ai-booking-free' ) . '</label>';
        echo '<input type="date" name="date_from" class="kab-filter-input" value="' . esc_attr( $filters['date_from'] ?? '' ) . '">';
        echo '</div>';
        echo '<div class="kab-filter-group">';
        echo '<label class="kab-filter-label">' . esc_html__( 'Date To', 'kura-ai-booking-free' ) . '</label>';
        echo '<input type="date" name="date_to" class="kab-filter-input" value="' . esc_attr( $filters['date_to'] ?? '' ) . '">';
        echo '</div>';
        echo '<div class="kab-filter-group">';
        echo '<label class="kab-filter-label">' . esc_html__( 'Payment Status', 'kura-ai-booking-free' ) . '</label>';
        echo '<select name="payment_status" class="kab-filter-select">';
        echo '<option value="">' . esc_html__( 'All Statuses', 'kura-ai-booking-free' ) . '</option>';
        echo '<option value="pending" ' . selected( $filters['payment_status'] ?? '', 'pending', false ) . '>' . esc_html__( 'Pending', 'kura-ai-booking-free' ) . '</option>';
        echo '<option value="paid" ' . selected( $filters['payment_status'] ?? '', 'paid', false ) . '>' . esc_html__( 'Paid', 'kura-ai-booking-free' ) . '</option>';
        echo '<option value="partial" ' . selected( $filters['payment_status'] ?? '', 'partial', false ) . '>' . esc_html__( 'Partial', 'kura-ai-booking-free' ) . '</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="kab-filter-group">';
        echo '<label class="kab-filter-label">' . esc_html__( 'Search', 'kura-ai-booking-free' ) . '</label>';
        echo '<input type="text" name="search" class="kab-filter-input" value="' . esc_attr( $filters['search'] ?? '' ) . '" placeholder="' . esc_attr__( 'Invoice number or customer name', 'kura-ai-booking-free' ) . '">';
        echo '</div>';
        echo '</div>';
        echo '<div style="margin-top:15px;display:flex;gap:10px;">';
        echo '<input type="submit" class="kab-btn kab-btn-primary" value="' . esc_attr__( 'Filter', 'kura-ai-booking-free' ) . '">';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-invoices' ) ) . '" class="kab-btn">' . esc_html__( 'Reset', 'kura-ai-booking-free' ) . '</a>';
        echo '</div>';
        echo '</form>';
    }

    public function render_create_invoice_page() {
        $users_dropdown = wp_dropdown_users( array(
            'echo' => false,
            'name' => 'user_id',
            'show_option_none' => __( 'Select a user', 'kura-ai-booking-free' ),
        ) );
        echo '<div class="wrap">';
        echo '<div class="kab-admin-content">';
        $this->render_static_header( 'invoices' );
        echo '<div class="kab-card">';
        echo '<div class="kab-card-header"><h2>' . esc_html__( 'Create Invoice', 'kura-ai-booking-free' ) . '</h2></div>';
        echo '<div class="kab-card-body">';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="kab_create_invoice">';
        wp_nonce_field( 'kab_create_invoice', 'kab_create_invoice_nonce' );
        echo '<div class="kab-form-group">';
        echo '<label class="kab-form-label">' . esc_html__( 'User', 'kura-ai-booking-free' ) . '</label>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $users_dropdown;
        echo '</div>';
        echo '<div class="kab-form-group">';
        echo '<label class="kab-form-label">' . esc_html__( 'Item Name', 'kura-ai-booking-free' ) . '</label>';
        echo '<input type="text" name="item_name" class="kab-form-control" required>';
        echo '</div>';
        echo '<div class="kab-form-group">';
        echo '<label class="kab-form-label">' . esc_html__( 'Amount', 'kura-ai-booking-free' ) . '</label>';
        echo '<input type="number" step="0.01" name="amount" class="kab-form-control" required>';
        echo '</div>';
        echo '<div class="kab-form-group">';
        echo '<button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Create Invoice', 'kura-ai-booking-free' ) . '</button>';
        echo ' <a href="' . esc_url( admin_url( 'admin.php?page=kab-invoices' ) ) . '" class="kab-btn">' . esc_html__( 'Cancel', 'kura-ai-booking-free' ) . '</a>';
        echo '</div>';
        echo '</form>';
        echo '</div></div></div>';
    }

    public function handle_create_invoice_action() {
        if ( ! isset( $_POST['kab_create_invoice_nonce'] ) || ! wp_verify_nonce( $_POST['kab_create_invoice_nonce'], 'kab_create_invoice' ) ) {
            wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) );
        }
        $user_id   = intval( $_POST['user_id'] ?? 0 );
        $item_name = sanitize_text_field( $_POST['item_name'] ?? '' );
        $amount    = floatval( $_POST['amount'] ?? 0 );
        if ( ! $user_id || ! $item_name || $amount <= 0 ) {
            wp_die( __( 'Please provide all required fields.', 'kura-ai-booking-free' ) );
        }
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            wp_die( __( 'User not found.', 'kura-ai-booking-free' ) );
        }
        require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
        $invoice_id = KAB_Invoices::create_manual_invoice( $user_id, $user->display_name, $user->user_email, $item_name, $amount );
        if ( ! $invoice_id ) {
            wp_die( __( 'Failed to create invoice.', 'kura-ai-booking-free' ) );
        }
        wp_redirect( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . intval( $invoice_id ) ) );
        exit;
    }

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
        global $wpdb;
        $booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d", $invoice['booking_id'] ), ARRAY_A );
        $this->render_static_header( 'invoices' );
        echo '<div class="wrap"><div class="kab-admin-content"><div class="kab-card">';
        echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=kab-invoices' ) ) . '" class="button">&larr; ' . esc_html__( 'Back to Invoices', 'kura-ai-booking-free' ) . '</a></p>';
        echo '<h1>' . esc_html__( 'Invoice Details', 'kura-ai-booking-free' ) . ' - ' . esc_html( $invoice['invoice_number'] ) . '</h1>';
        echo '<div class="kab-invoice-details">';
        echo '<table class="kab-invoice-meta-table">';
        echo '<tr><td>' . esc_html__( 'Invoice #', 'kura-ai-booking-free' ) . '</td><td>' . esc_html( $invoice['invoice_number'] ) . '</td><td>' . esc_html__( 'Issue Date', 'kura-ai-booking-free' ) . '</td><td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ) ) . '</td></tr>';
        echo '<tr><td>' . esc_html__( 'Status', 'kura-ai-booking-free' ) . '</td><td><span class="kab-status-badge kab-status-' . esc_attr( $invoice['payment_status'] ) . '">' . esc_html( ucfirst( $invoice['payment_status'] ) ) . '</span></td><td>' . esc_html__( 'Payment Method', 'kura-ai-booking-free' ) . '</td><td>' . esc_html( $invoice['payment_method'] ?? 'N/A' ) . '</td></tr>';
        echo '<tr><td>' . esc_html__( 'Customer', 'kura-ai-booking-free' ) . '</td><td>' . esc_html( $invoice['customer_name'] ) . '</td><td>' . esc_html__( 'Email', 'kura-ai-booking-free' ) . '</td><td>' . esc_html( $invoice['customer_email'] ) . '</td></tr>';
        echo '</table>';
        echo '<table class="kab-invoice-items">';
        echo '<thead><tr><th>' . esc_html__( 'Description', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Qty', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Unit Price', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Amount', 'kura-ai-booking-free' ) . '</th></tr></thead><tbody>';
        echo '<tr><td>' . esc_html( $invoice['item_name'] ) . '</td><td>1</td><td>' . esc_html( number_format( $invoice['subtotal'], 2 ) ) . '</td><td>' . esc_html( number_format( $invoice['subtotal'], 2 ) ) . '</td></tr>';
        echo '</tbody></table>';
        echo '<div class="kab-invoice-summary"><table>';
        echo '<tr><td>' . esc_html__( 'Subtotal', 'kura-ai-booking-free' ) . '</td><td class="text-right">' . esc_html( number_format( $invoice['subtotal'], 2 ) ) . '</td></tr>';
        echo '<tr><td>' . esc_html__( 'Tax', 'kura-ai-booking-free' ) . '</td><td class="text-right">' . esc_html( number_format( $invoice['tax_amount'], 2 ) ) . '</td></tr>';
        echo '<tr class="kab-invoice-total"><td>' . esc_html__( 'Total', 'kura-ai-booking-free' ) . '</td><td class="text-right"><strong>' . esc_html( number_format( $invoice['total_amount'], 2 ) ) . '</strong></td></tr>';
        echo '</table></div>';
        echo '</div>'; // end details
        echo '<div class="kab-invoice-actions">';
        echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_download_invoice&invoice_id=' . $invoice_id ), 'kab_download_invoice_' . $invoice_id ) ) . '" class="button kab-btn-primary">' . esc_html__( 'Download PDF', 'kura-ai-booking-free' ) . '</a>';
        echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_preview_invoice&invoice_id=' . $invoice_id ), 'kab_preview_invoice_' . $invoice_id ) ) . '" class="button" target="_blank">' . esc_html__( 'Preview PDF', 'kura-ai-booking-free' ) . '</a>';
        echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_resend_invoice&invoice_id=' . $invoice_id ), 'kab_resend_invoice_' . $invoice_id ) ) . '" class="button">' . esc_html__( 'Re-send Email', 'kura-ai-booking-free' ) . '</a>';
        echo '</div>';
        echo '</div></div></div>';
    }

    private function handle_invoice_actions() {
        if ( ! empty( $_GET['action'] ) && ! empty( $_GET['invoice_id'] ) && ! empty( $_GET['_wpnonce'] ) ) {
            $action     = sanitize_text_field( $_GET['action'] );
            $invoice_id = intval( $_GET['invoice_id'] );
            $nonce      = sanitize_text_field( $_GET['_wpnonce'] );
            if ( 'kab_download_invoice' === $action && wp_verify_nonce( $nonce, 'kab_download_invoice_' . $invoice_id ) ) {
                require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoice-pdf.php';
                KAB_Invoice_PDF::serve_pdf( $invoice_id, 'attachment' );
                exit;
            }
            if ( 'kab_preview_invoice' === $action && wp_verify_nonce( $nonce, 'kab_preview_invoice_' . $invoice_id ) ) {
                require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoice-pdf.php';
                KAB_Invoice_PDF::serve_pdf( $invoice_id, 'inline' );
                exit;
            }
            if ( 'kab_resend_invoice' === $action && wp_verify_nonce( $nonce, 'kab_resend_invoice_' . $invoice_id ) ) {
                require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
                KAB_Invoices::email_invoice( $invoice_id );
            }
        }
        if ( ! empty( $_GET['action'] ) && $_GET['action'] === 'kab_export_invoices' && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'kab_export_invoices' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
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
            $rows = KAB_Invoices::get_invoices( $filters );
            $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . __( 'Invoices Export', 'kura-ai-booking-free' ) . '</title>';
            $html .= '<style>body{font-family:Arial,sans-serif;margin:20px;background:#f8f9fa}h1{color:#0073aa}table{width:100%;border-collapse:collapse;background:#fff}th,td{padding:10px;border:1px solid #e2e4e7;text-align:left}th{background:#f1f5f9}tfoot td{font-weight:bold} .meta{margin-bottom:15px;color:#555}</style>';
            $html .= '</head><body>';
            $html .= '<h1>' . __( 'Invoices Export', 'kura-ai-booking-free' ) . '</h1>';
            $html .= '<div class="meta">' . __( 'Generated:', 'kura-ai-booking-free' ) . ' ' . esc_html( current_time( 'mysql' ) ) . '</div>';
            $html .= '<table><thead><tr>';
            $html .= '<th>' . __( 'Invoice', 'kura-ai-booking-free' ) . '</th><th>' . __( 'Customer', 'kura-ai-booking-free' ) . '</th><th>' . __( 'Email', 'kura-ai-booking-free' ) . '</th><th>' . __( 'Item', 'kura-ai-booking-free' ) . '</th><th>' . __( 'Date', 'kura-ai-booking-free' ) . '</th><th>' . __( 'Amount', 'kura-ai-booking-free' ) . '</th><th>' . __( 'Status', 'kura-ai-booking-free' ) . '</th>';
            $html .= '</tr></thead><tbody>';
            $total = 0.0;
            foreach ( $rows as $r ) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html( $r['invoice_number'] ) . '</td>';
                $html .= '<td>' . esc_html( $r['customer_name'] ) . '</td>';
                $html .= '<td>' . esc_html( $r['customer_email'] ) . '</td>';
                $html .= '<td>' . esc_html( $r['item_name'] ) . '</td>';
                $html .= '<td>' . esc_html( $r['invoice_date'] ) . '</td>';
                $html .= '<td>' . esc_html( number_format( (float) $r['total_amount'], 2 ) ) . '</td>';
                $html .= '<td>' . esc_html( ucfirst( $r['payment_status'] ) ) . '</td>';
                $html .= '</tr>';
                $total += (float) $r['total_amount'];
            }
            $html .= '</tbody><tfoot><tr><td colspan="5">' . __( 'Total', 'kura-ai-booking-free' ) . '</td><td colspan="2">' . esc_html( number_format( $total, 2 ) ) . '</td></tr></tfoot></table>';
            $html .= '</body></html>';
            $upload_dir = wp_upload_dir();
            if ( empty( $upload_dir['error'] ) && ! empty( $upload_dir['basedir'] ) ) {
                $report_dir = trailingslashit( $upload_dir['basedir'] ) . 'kuraai/invoices/';
                if ( ! file_exists( $report_dir ) ) {
                    wp_mkdir_p( $report_dir );
                }
                $filename = 'invoices-export-' . date( 'Ymd-His' ) . '.pdf';
                $file_path = $report_dir . $filename;
                file_put_contents( $file_path, $html );
                header( 'Content-Type: application/pdf' );
                header( 'Content-Disposition: attachment; filename=' . $filename );
                header( 'Content-Length: ' . filesize( $file_path ) );
                readfile( $file_path );
                exit;
            } else {
                header( 'Content-Type: text/html; charset=UTF-8' );
                echo $html;
                exit;
            }
        }
    }
}
