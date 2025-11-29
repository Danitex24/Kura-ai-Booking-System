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
        $revenue_currency = 'USD';
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
            if ( isset( $inv['currency'] ) && $inv['currency'] ) { $revenue_currency = strtoupper( $inv['currency'] ); }
        }
        $this->render_static_header( 'invoices' );
        echo '<div class="wrap">';
        echo '<div class="kab-admin-content">';
        echo '<div class="kab-card">';
        echo '<div class="kab-card-header" style="display:flex;justify-content:space-between;align-items:center;">';
        echo '<h1 class="kab-card-title"><span class="dashicons dashicons-media-spreadsheet"></span> ' . esc_html__( 'Invoices', 'kura-ai-booking-free' ) . '</h1>';
        $export_url = wp_nonce_url( add_query_arg( $_GET, admin_url( 'admin-post.php?action=kab_export_invoices' ) ), 'kab_export_invoices' );
        echo '<div class="kab-header-actions">';
        echo '<a href="#" class="kab-btn kab-btn-secondary" id="kab-export-pdf-btn" data-has-invoices="' . ( $total_count > 0 ? '1' : '0' ) . '">' . esc_html__( 'Export PDF', 'kura-ai-booking-free' ) . '</a>';
        echo ' <a href="' . esc_url( admin_url( 'admin.php?page=kab-create-invoice' ) ) . '" class="kab-btn kab-btn-primary">' . esc_html__( 'Create Invoice', 'kura-ai-booking-free' ) . '</a>';
        echo '</div>';
        echo '<script type="text/javascript">document.addEventListener("DOMContentLoaded",function(){var b=document.getElementById("kab-export-pdf-btn");if(!b)return;b.addEventListener("click",function(e){var has=b.getAttribute("data-has-invoices")==="1";if(!has){e.preventDefault();if(window.Swal){Swal.fire({icon:"info",title:"' . esc_js( __( 'No invoices', 'kura-ai-booking-free' ) ) . '",text:"' . esc_js( __( 'There are no invoices to export.', 'kura-ai-booking-free' ) ) . '"});}}else{window.location.href="' . esc_url( $export_url ) . '";}});});</script>';
        echo '</div>';
        echo '<div class="kab-detail-grid" style="margin-top:10px;">';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Total Invoices', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( $total_count ) . '</div></div>';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Paid', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( $paid_count ) . '</div></div>';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Pending', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( $pending_count ) . '</div></div>';
        echo '<div class="kab-detail-item"><div class="kab-detail-label">' . esc_html__( 'Total Revenue', 'kura-ai-booking-free' ) . '</div><div class="kab-detail-value">' . esc_html( kab_format_currency( $total_revenue, kab_currency_symbol( $revenue_currency ) ) ) . '</div></div>';
        echo '</div>';
        echo '<div class="kab-invoice-filters">';
        $this->render_invoice_filters( $filters );
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
                $item_cell = '';
                if ( ! empty( $invoice['item_name'] ) ) {
                    $decoded = json_decode( $invoice['item_name'], true );
                    if ( is_array( $decoded ) ) {
                        $lines = array();
                        foreach ( $decoded as $li ) {
                            $lines[] = esc_html( isset( $li['name'] ) ? $li['name'] : '' );
                        }
                        $item_cell = implode( '<br>', $lines );
                    } else {
                        $item_cell = esc_html( $invoice['item_name'] );
                    }
                }
                echo '<td>' . $item_cell . '</td>';
                echo '<td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice['invoice_date'] ) ) ) . '</td>';
                $sym = kab_currency_symbol( isset( $invoice['currency'] ) ? $invoice['currency'] : 'USD' );
                echo '<td>' . esc_html( kab_format_currency( $invoice['total_amount'], $sym ) ) . '</td>';
                echo '<td><span class="kab-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( $invoice['payment_status'] ) ) . '</span></td>';
                echo '<td class="kab-invoice-actions">';
                echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice['id'] ) ) . '" class="kab-btn kab-btn-primary kab-btn-sm">' . esc_html__( 'View', 'kura-ai-booking-free' ) . '</a> ';
                echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice['id'] . '&edit=1' ) ) . '" class="kab-btn kab-btn-secondary kab-btn-sm">' . esc_html__( 'Edit', 'kura-ai-booking-free' ) . '</a> ';
                $del_url = wp_nonce_url( admin_url( 'admin-post.php?action=kab_delete_invoice&invoice_id=' . intval( $invoice['id'] ) ), 'kab_delete_invoice_' . intval( $invoice['id'] ) );
                echo '<a href="' . esc_url( $del_url ) . '" class="kab-btn kab-btn-danger kab-btn-sm kab-delete-invoice" data-invoice="' . esc_attr( $invoice['invoice_number'] ) . '">' . esc_html__( 'Delete', 'kura-ai-booking-free' ) . '</a>';
                echo '</td>';
                echo '</tr>';
			}
			echo '</tbody></table>';
			// Pagination UI
			echo '<div class="kab-pagination">';
			echo '<span class="kab-page-info">Page ' . esc_html( $paged ) . ' of ' . esc_html( $total_pages ) . '</span>';
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
        echo '<script>(function(){function waitSwal(cb){if(window.Swal){cb(window.Swal);return;}var i=setInterval(function(){if(window.Swal){clearInterval(i);cb(window.Swal);}},50);}document.querySelectorAll(".kab-delete-invoice").forEach(function(btn){btn.addEventListener("click",function(e){e.preventDefault();var href=btn.getAttribute("href"),label=btn.getAttribute("data-invoice");waitSwal(function(Swal){Swal.fire({title:"' . esc_js( __( 'Delete invoice?', 'kura-ai-booking-free' ) ) . '",text:label,icon:"warning",showCancelButton:true,confirmButtonText:"' . esc_js( __( 'Delete', 'kura-ai-booking-free' ) ) . '"}).then(function(r){if(r.isConfirmed){window.location.href=href;}});});});});var params=new URLSearchParams(window.location.search);if(params.has("deleted")){waitSwal(function(Swal){Swal.fire({title: "' . esc_js( __( 'Invoice deleted', 'kura-ai-booking-free' ) ) . '", icon: "success"});});}})();</script>';
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
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" id="kab-create-invoice-form">';
        echo '<input type="hidden" name="action" value="kab_create_invoice">';
        wp_nonce_field( 'kab_create_invoice', 'kab_create_invoice_nonce' );
        echo '<div class="kab-form-group">';
        echo '<label class="kab-form-label">' . esc_html__( 'Currency', 'kura-ai-booking-free' ) . '</label>';
        echo '<select name="currency" id="kab-currency" class="kab-form-control" required>';
        echo '<option value="USD">USD (&#36;)</option>';
        echo '<option value="EUR">EUR (&euro;)</option>';
        echo '<option value="GBP">GBP (&pound;)</option>';
        echo '<option value="JPY">JPY (&yen;)</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="kab-form-group">';
        echo '<label class="kab-form-label">' . esc_html__( 'User', 'kura-ai-booking-free' ) . '</label>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $users_dropdown;
        echo '</div>';
        echo '<div class="kab-form-group">';
        echo '<label class="kab-form-label">' . esc_html__( 'Invoice Items', 'kura-ai-booking-free' ) . '</label>';
        echo '<table class="kab-items-builder" style="width:100%;border-collapse:collapse;">';
        echo '<thead><tr><th style="text-align:left;padding:6px;border:1px solid #ddd;">' . esc_html__( 'Description', 'kura-ai-booking-free' ) . '</th><th style="text-align:right;padding:6px;border:1px solid #ddd;">' . esc_html__( 'Amount', 'kura-ai-booking-free' ) . '</th><th style="width:40px;padding:6px;border:1px solid #ddd;">&nbsp;</th></tr></thead>';
        echo '<tbody id="kab-items-rows">';
        echo '<tr>';
        echo '<td style="padding:6px;border:1px solid #ddd;"><input type="text" name="items[name][]" class="kab-form-control" placeholder="' . esc_attr__( 'Item description', 'kura-ai-booking-free' ) . '" required></td>';
        echo '<td style="padding:6px;border:1px solid #ddd;text-align:right;"><input type="number" step="0.01" min="0" name="items[amount][]" class="kab-form-control kab-item-amount" placeholder="0.00" required></td>';
        echo '<td style="padding:6px;border:1px solid #ddd;text-align:center;"><button type="button" class="kab-btn" id="kab-add-row">+</button></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '<tfoot><tr><td style="padding:6px;border:1px solid #ddd;font-weight:600;">' . esc_html__( 'Total', 'kura-ai-booking-free' ) . '</td><td style="padding:6px;border:1px solid #ddd;text-align:right;"><span id="kab-items-total">0.00</span></td><td style="padding:6px;border:1px solid #ddd;">&nbsp;</td></tr></tfoot>';
        echo '</table>';
        echo '</div>';
        echo '<script>(function(){const rows=document.getElementById("kab-items-rows");const add=document.getElementById("kab-add-row");const totalEl=document.getElementById("kab-items-total");const curSel=document.getElementById("kab-currency");function sym(){var c=(curSel&&curSel.value)||"USD";return {USD:"$",EUR:"€",GBP:"£",JPY:"¥"}[c]||"$";}function recalc(){let t=0;rows.querySelectorAll(".kab-item-amount").forEach(function(inp){const v=parseFloat(inp.value);if(!isNaN(v))t+=v;});totalEl.textContent=sym()+t.toFixed(2);}function makeRow(){const tr=document.createElement("tr");tr.innerHTML=`<td style=\"padding:6px;border:1px solid #ddd;\"><input type=\"text\" name=\"items[name][]\" class=\"kab-form-control\" placeholder=\"Item description\" required></td><td style=\"padding:6px;border:1px solid #ddd;text-align:right;\"><input type=\"number\" step=\"0.01\" min=\"0\" name=\"items[amount][]\" class=\"kab-form-control kab-item-amount\" placeholder=\"0.00\" required></td><td style=\"padding:6px;border:1px solid #ddd;text-align:center;\"><button type=\"button\" class=\"kab-btn kab-remove-row\">−</button></td>`;rows.appendChild(tr);bind(tr);}function bind(scope){scope.querySelectorAll(".kab-item-amount").forEach(function(inp){inp.addEventListener("input",recalc);});scope.querySelectorAll(".kab-remove-row").forEach(function(btn){btn.addEventListener("click",function(){btn.closest("tr").remove();recalc();});});}add.addEventListener("click",function(){makeRow();});if(curSel){curSel.addEventListener("change",recalc);}bind(document);recalc();})();</script>';
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
        $items     = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? $_POST['items'] : array();
        $currency  = strtoupper( sanitize_text_field( $_POST['currency'] ?? 'USD' ) );
        $names     = isset( $items['name'] ) && is_array( $items['name'] ) ? $items['name'] : array();
        $amounts   = isset( $items['amount'] ) && is_array( $items['amount'] ) ? $items['amount'] : array();
        $clean     = array();
        $subtotal  = 0.0;
        for ( $i = 0; $i < count( $names ); $i++ ) {
            $n = sanitize_text_field( $names[$i] ?? '' );
            $a = floatval( $amounts[$i] ?? 0 );
            if ( $n && $a > 0 ) {
                $clean[] = $n;
                $subtotal += $a;
            }
        }
        if ( ! $user_id || empty( $clean ) || $subtotal <= 0 ) {
            wp_die( __( 'Please provide all required fields.', 'kura-ai-booking-free' ) );
        }
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            wp_die( __( 'User not found.', 'kura-ai-booking-free' ) );
        }
        require_once plugin_dir_path( __FILE__ ) . 'class-kab-invoices.php';
        $items_payload = array();
        for ( $i = 0; $i < count( $clean ); $i++ ) {
            $items_payload[] = array(
                'name'   => $clean[$i],
                'amount' => floatval( $amounts[$i] ?? 0 ),
            );
        }
        $item_blob = wp_json_encode( $items_payload );
        $invoice_id = KAB_Invoices::create_manual_invoice( $user_id, $user->display_name, $user->user_email, $item_blob, $subtotal, $currency );
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
        $rendered = false;
        $items = array();
        if ( ! empty( $invoice['item_name'] ) ) {
            $decoded = json_decode( $invoice['item_name'], true );
            if ( is_array( $decoded ) ) {
                $items = $decoded;
            }
        }
        if ( ! empty( $items ) ) {
            foreach ( $items as $li ) {
                $n = isset( $li['name'] ) ? $li['name'] : '';
                $a = isset( $li['amount'] ) ? floatval( $li['amount'] ) : 0.0;
                $sym = kab_currency_symbol( isset( $invoice['currency'] ) ? $invoice['currency'] : 'USD' );
                echo '<tr><td>' . esc_html( $n ) . '</td><td>1</td><td>' . esc_html( kab_format_currency( $a, $sym ) ) . '</td><td>' . esc_html( kab_format_currency( $a, $sym ) ) . '</td></tr>';
                $rendered = true;
            }
        }
        if ( ! $rendered ) {
            $sym = kab_currency_symbol( isset( $invoice['currency'] ) ? $invoice['currency'] : 'USD' );
            echo '<tr><td>' . esc_html( $invoice['item_name'] ) . '</td><td>1</td><td>' . esc_html( kab_format_currency( $invoice['subtotal'], $sym ) ) . '</td><td>' . esc_html( kab_format_currency( $invoice['subtotal'], $sym ) ) . '</td></tr>';
        }
        echo '</tbody></table>';
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-tickets.php';
        $invoice_view = admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . intval( $invoice_id ) );
        $qr_url = KAB_Tickets::generate_qr_code_png( 'inv-' . preg_replace( '/[^A-Za-z0-9_.-]/', '', str_replace( '#', '', (string) $invoice['invoice_number'] ) ), $invoice_view );
        echo '<div style="display:flex;justify-content:flex-end;align-items:flex-start;gap:12px;margin-top:8px;">';
        echo '<img src="' . esc_url( $qr_url ) . '" alt="QR" style="width:80px;height:80px;border:1px solid #e2e4e7;border-radius:6px;">';
        echo '<div class="kab-invoice-summary"><table>';
        $sym = kab_currency_symbol( isset( $invoice['currency'] ) ? $invoice['currency'] : 'USD' );
        echo '<tr><td>' . esc_html__( 'Subtotal', 'kura-ai-booking-free' ) . '</td><td class="text-right">' . esc_html( kab_format_currency( $invoice['subtotal'], $sym ) ) . '</td></tr>';
        echo '<tr><td>' . esc_html__( 'Tax', 'kura-ai-booking-free' ) . '</td><td class="text-right">' . esc_html( kab_format_currency( $invoice['tax_amount'], $sym ) ) . '</td></tr>';
        echo '<tr class="kab-invoice-total"><td>' . esc_html__( 'Total', 'kura-ai-booking-free' ) . '</td><td class="text-right"><strong>' . esc_html( kab_format_currency( $invoice['total_amount'], $sym ) ) . '</strong></td></tr>';
        echo '</table></div>';
        echo '</div>'; // end summary row
        echo '</div>'; // end details
        echo '<div class="kab-invoice-actions">';
        echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_download_invoice&invoice_id=' . $invoice_id ), 'kab_download_invoice_' . $invoice_id ) ) . '" class="button kab-btn-primary">' . esc_html__( 'Download PDF', 'kura-ai-booking-free' ) . '</a>';
        echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_preview_invoice&invoice_id=' . $invoice_id ), 'kab_preview_invoice_' . $invoice_id ) ) . '" class="button" target="_blank">' . esc_html__( 'Preview PDF', 'kura-ai-booking-free' ) . '</a>';
        $resend_url = wp_nonce_url( admin_url( 'admin-post.php?action=kab_resend_invoice&invoice_id=' . $invoice_id ), 'kab_resend_invoice_' . $invoice_id );
        echo '<a href="#" id="kab-resend-email-btn" data-href="' . esc_url( $resend_url ) . '" class="button">' . esc_html__( 'Re-send Email', 'kura-ai-booking-free' ) . '</a>';
        $payset = kab_get_payment_settings();
        // Per-service payment restrictions
        $allowed = array();
        if ( $booking && ! empty( $booking['service_id'] ) ) {
            global $wpdb; $srv = $wpdb->get_row( $wpdb->prepare( "SELECT payment_methods FROM {$wpdb->prefix}kab_services WHERE id=%d", intval( $booking['service_id'] ) ), ARRAY_A );
            if ( $srv && ! empty( $srv['payment_methods'] ) ) { $allowed = (array) json_decode( $srv['payment_methods'], true ); }
        }
        $allow = function( $k ) use ( $allowed ) { return empty( $allowed ) ? true : in_array( $k, $allowed, true ); };
        if ( $invoice['payment_status'] !== 'paid' && ! empty( $payset['paypal_enabled'] ) && ! empty( $payset['paypal_merchant'] ) && $allow('paypal') ) {
            $paypal_endpoint = ! empty( $payset['paypal_sandbox'] ) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
            echo '<form id="kab-paypal-form" method="post" action="' . esc_url( $paypal_endpoint ) . '" style="display:none;">';
            echo '<input type="hidden" name="cmd" value="_xclick">';
            echo '<input type="hidden" name="business" value="' . esc_attr( $payset['paypal_merchant'] ) . '">';
            echo '<input type="hidden" name="item_name" value="' . esc_attr( 'Invoice ' . (string) $invoice['invoice_number'] ) . '">';
            echo '<input type="hidden" name="amount" value="' . esc_attr( number_format( (float) $invoice['total_amount'], 2, '.', '' ) ) . '">';
            echo '<input type="hidden" name="currency_code" value="' . esc_attr( isset( $invoice['currency'] ) ? strtoupper( $invoice['currency'] ) : 'USD' ) . '">';
            echo '<input type="hidden" name="notify_url" value="' . esc_url( admin_url( 'admin-post.php?action=kab_paypal_ipn' ) ) . '">';
            echo '<input type="hidden" name="return" value="' . esc_url( admin_url( 'admin-post.php?action=kab_paypal_return&invoice_id=' . $invoice_id ) ) . '">';
            echo '<input type="hidden" name="cancel_return" value="' . esc_url( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id ) ) . '">';
            echo '<input type="hidden" name="custom" value="' . esc_attr( $invoice_id ) . '">';
            echo '</form>';
            echo '<a href="#" class="button kab-btn-success" id="kab-pay-now">' . esc_html__( 'Pay with PayPal', 'kura-ai-booking-free' ) . '</a>';
        }
        if ( $invoice['payment_status'] !== 'paid' ) {
            if ( ! empty( $payset['stripe_enabled'] ) && $allow('stripe') ) { echo ' <a href="' . esc_url( admin_url( 'admin-post.php?action=kab_pay_invoice&gateway=stripe&invoice_id=' . $invoice_id ) ) . '" class="button kab-btn-success">' . esc_html__( 'Pay with Stripe', 'kura-ai-booking-free' ) . '</a>'; }
            if ( ! empty( $payset['mollie_enabled'] ) && $allow('mollie') ) { echo ' <a href="' . esc_url( admin_url( 'admin-post.php?action=kab_pay_invoice&gateway=mollie&invoice_id=' . $invoice_id ) ) . '" class="button kab-btn-success">' . esc_html__( 'Pay with Mollie', 'kura-ai-booking-free' ) . '</a>'; }
            if ( ! empty( $payset['razor_enabled'] ) && $allow('razorpay') ) { echo ' <a href="' . esc_url( admin_url( 'admin-post.php?action=kab_pay_invoice&gateway=razorpay&invoice_id=' . $invoice_id ) ) . '" class="button kab-btn-success">' . esc_html__( 'Pay with Razorpay', 'kura-ai-booking-free' ) . '</a>'; }
            if ( ! empty( $payset['paystack_enabled'] ) && $allow('paystack') ) { echo ' <a href="' . esc_url( admin_url( 'admin-post.php?action=kab_pay_invoice&gateway=paystack&invoice_id=' . $invoice_id ) ) . '" class="button kab-btn-success">' . esc_html__( 'Pay with Paystack', 'kura-ai-booking-free' ) . '</a>'; }
            if ( ! empty( $payset['flutter_enabled'] ) && $allow('flutterwave') ) { echo ' <a href="' . esc_url( admin_url( 'admin-post.php?action=kab_pay_invoice&gateway=flutterwave&invoice_id=' . $invoice_id ) ) . '" class="button kab-btn-success">' . esc_html__( 'Pay with Flutterwave', 'kura-ai-booking-free' ) . '</a>'; }
        }
        echo '</div>';
        // Edit form
        $is_edit = isset( $_GET['edit'] ) && $_GET['edit'] === '1';
        if ( $is_edit ) {
            echo '<div class="kab-card" style="margin-top:15px;"><div class="kab-card-header"><h2>' . esc_html__( 'Edit Invoice', 'kura-ai-booking-free' ) . '</h2></div><div class="kab-card-body">';
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
            echo '<input type="hidden" name="action" value="kab_update_invoice">';
            echo '<input type="hidden" name="invoice_id" value="' . esc_attr( $invoice_id ) . '">';
            wp_nonce_field( 'kab_update_invoice_' . $invoice_id );
            echo '<div class="kab-form-group">';
            echo '<label class="kab-form-label">' . esc_html__( 'Payment Status', 'kura-ai-booking-free' ) . '</label>';
            echo '<select name="payment_status" class="kab-form-control">';
            foreach ( array( 'pending', 'paid', 'partial' ) as $st ) {
                echo '<option value="' . esc_attr( $st ) . '" ' . selected( $invoice['payment_status'], $st, false ) . '>' . esc_html( ucfirst( $st ) ) . '</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div class="kab-form-group">';
            echo '<label class="kab-form-label">' . esc_html__( 'Payment Method', 'kura-ai-booking-free' ) . '</label>';
            echo '<input type="text" name="payment_method" class="kab-form-control" value="' . esc_attr( $invoice['payment_method'] ?? '' ) . '" placeholder="' . esc_attr__( 'e.g. Bank transfer, Cash', 'kura-ai-booking-free' ) . '">';
            echo '</div>';
            echo '<div class="kab-form-group">';
            echo '<label class="kab-form-label">' . esc_html__( 'Currency', 'kura-ai-booking-free' ) . '</label>';
            $curr = isset( $invoice['currency'] ) ? strtoupper( $invoice['currency'] ) : 'USD';
            echo '<select name="currency" class="kab-form-control">';
            echo '<option value="USD" ' . selected( $curr, 'USD', false ) . '>USD (&#36;)</option>';
            echo '<option value="EUR" ' . selected( $curr, 'EUR', false ) . '>EUR (&euro;)</option>';
            echo '<option value="GBP" ' . selected( $curr, 'GBP', false ) . '>GBP (&pound;)</option>';
            echo '<option value="JPY" ' . selected( $curr, 'JPY', false ) . '>JPY (&yen;)</option>';
            echo '</select>';
            echo '</div>';
            echo '<div class="kab-form-group">';
            echo '<button type="submit" class="kab-btn kab-btn-primary">' . esc_html__( 'Update Invoice', 'kura-ai-booking-free' ) . '</button> ';
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id ) ) . '" class="kab-btn">' . esc_html__( 'Cancel', 'kura-ai-booking-free' ) . '</a>';
            echo '</div>';
            echo '</form>';
            echo '</div></div>';
        }
        echo '<script>(function(){function waitSwal(cb){if(window.Swal){cb(window.Swal);return;}var i=setInterval(function(){if(window.Swal){clearInterval(i);cb(window.Swal);}},50);}var btn=document.getElementById("kab-resend-email-btn");if(btn){btn.addEventListener("click",function(e){e.preventDefault();var href=btn.getAttribute("data-href");waitSwal(function(Swal){Swal.fire({title:"' . esc_js( __( 'Re-send invoice email?', 'kura-ai-booking-free' ) ) . '",icon:"question",showCancelButton:true,confirmButtonText:"' . esc_js( __( 'Send', 'kura-ai-booking-free' ) ) . '",cancelButtonText:"' . esc_js( __( 'Cancel', 'kura-ai-booking-free' ) ) . '"}).then(function(r){if(r.isConfirmed){window.location.href=href;}});});});}var params=new URLSearchParams(window.location.search);if(params.has("sent")){var ok=params.get("sent")==="1";waitSwal(function(Swal){Swal.fire({title: ok?"' . esc_js( __( 'Email sent', 'kura-ai-booking-free' ) ) . '":"' . esc_js( __( 'Failed to send', 'kura-ai-booking-free' ) ) . '",icon: ok?"success":"error"});});}if(params.has("updated")){waitSwal(function(Swal){Swal.fire({title: "' . esc_js( __( 'Invoice updated', 'kura-ai-booking-free' ) ) . '", icon: "success"});});}var payBtn=document.getElementById("kab-pay-now");if(payBtn){payBtn.addEventListener("click",function(e){e.preventDefault();waitSwal(function(Swal){Swal.fire({title:"' . esc_js( __( 'Proceed to PayPal?', 'kura-ai-booking-free' ) ) . '",icon:"info",showCancelButton:true,confirmButtonText:"' . esc_js( __( 'Pay Now', 'kura-ai-booking-free' ) ) . '"}).then(function(r){if(r.isConfirmed){document.getElementById("kab-paypal-form").submit();}});});});}if(params.has("paid")){waitSwal(function(Swal){Swal.fire({title:"' . esc_js( __( 'Payment complete', 'kura-ai-booking-free' ) ) . '",icon:"success"});});}})();</script>';
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
            if ( 'kab_delete_invoice' === $action && wp_verify_nonce( $nonce, 'kab_delete_invoice_' . $invoice_id ) ) {
                global $wpdb;
                // Attempt to delete PDF file
                $inv = $wpdb->get_row( $wpdb->prepare( "SELECT pdf_path FROM {$wpdb->prefix}kab_invoices WHERE id = %d", $invoice_id ), ARRAY_A );
                if ( $inv && ! empty( $inv['pdf_path'] ) ) {
                    $upload_dir = wp_upload_dir();
                    $file_path = '';
                    if ( strpos( $inv['pdf_path'], $upload_dir['baseurl'] ) === 0 ) {
                        $rel = substr( $inv['pdf_path'], strlen( $upload_dir['baseurl'] ) );
                        $file_path = trailingslashit( $upload_dir['basedir'] ) . ltrim( $rel, '/' );
                    } else {
                        $file_path = ABSPATH . wp_parse_url( $inv['pdf_path'], PHP_URL_PATH );
                    }
                    if ( $file_path && file_exists( $file_path ) ) @unlink( $file_path );
                }
                // Delete invoice record
                $wpdb->delete( $wpdb->prefix . 'kab_invoices', array( 'id' => $invoice_id ), array( '%d' ) );
                // Clean service->invoice map
                $map = get_option( 'kab_service_invoice_map', array() );
                if ( is_array( $map ) ) {
                    foreach ( $map as $sid => $iid ) { if ( intval( $iid ) === $invoice_id ) unset( $map[ $sid ] ); }
                    update_option( 'kab_service_invoice_map', $map );
                }
                wp_redirect( add_query_arg( array( 'page' => 'kab-invoices', 'deleted' => '1' ), admin_url( 'admin.php' ) ) );
                exit;
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
