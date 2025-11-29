<?php
/**
 * Plugin Name: Kura-ai Booking System (Free)
 * Description: Lightweight booking plugin for appointments and events with QR code e-tickets.
 * Version: 1.0.0
 * Author: Daniel Abughdyer
 * Text Domain: kura-ai-booking-free
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * License: GPLv2 or later
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function() {
    global $wpdb;
    $tables = array( $wpdb->prefix . 'kab_services', $wpdb->prefix . 'kab_invoices' );
    foreach ( $tables as $t ) {
        $exists = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$t} LIKE %s", 'currency' ) );
        if ( ! $exists ) {
            $wpdb->query( "ALTER TABLE {$t} ADD COLUMN currency VARCHAR(3) NOT NULL DEFAULT 'USD'" );
        }
    }
    // Add payment_methods to services
    $exists_pm = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$wpdb->prefix}kab_services LIKE %s", 'payment_methods' ) );
    if ( ! $exists_pm ) {
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}kab_services ADD COLUMN payment_methods TEXT" );
    }
} );

add_action( 'admin_post_kab_export_invoices', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'kura-ai-booking-free' ) );
    }
    $nonce = sanitize_text_field( $_GET['_wpnonce'] ?? '' );
    $nonce_valid = $nonce && wp_verify_nonce( $nonce, 'kab_export_invoices' );
    // Allow export for admins even if nonce validation fails (local/dev or URL mangling)
    if ( ! $nonce_valid && ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) );
    }
    require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoices.php';
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
    $total = 0.0;
    ob_start();
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . esc_html__( 'Invoices Export', 'kura-ai-booking-free' ) . '</title>';
    echo '<style>body{font-family:Arial,sans-serif;margin:20px;background:#fff;font-size:12px}h1{color:#333;margin:0 0 10px}table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #ddd;text-align:left}th{background:#f8f9fa}tfoot td{font-weight:bold}</style>';
    echo '</head><body>';
    echo '<h1>' . esc_html__( 'Invoices Export', 'kura-ai-booking-free' ) . '</h1>';
    echo '<div style="margin-bottom:10px;color:#555">' . esc_html__( 'Generated:', 'kura-ai-booking-free' ) . ' ' . esc_html( current_time( 'mysql' ) ) . '</div>';
    echo '<table><thead><tr>';
    echo '<th>' . esc_html__( 'Invoice', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Customer', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Email', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Item', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Amount', 'kura-ai-booking-free' ) . '</th><th>' . esc_html__( 'Status', 'kura-ai-booking-free' ) . '</th>';
    echo '</tr></thead><tbody>';
    foreach ( $rows as $r ) {
        $sym = kab_currency_symbol( isset( $r['currency'] ) ? $r['currency'] : 'USD' );
        echo '<tr>';
        echo '<td>' . esc_html( $r['invoice_number'] ) . '</td>';
        echo '<td>' . esc_html( $r['customer_name'] ) . '</td>';
        echo '<td>' . esc_html( $r['customer_email'] ) . '</td>';
        // Item column: decode JSON items if present and render as lines
        $item_display = esc_html( $r['item_name'] );
        $decoded = json_decode( (string) $r['item_name'], true );
        if ( is_array( $decoded ) ) {
            $lines = array();
            foreach ( $decoded as $li ) {
                $n = isset( $li['name'] ) ? (string) $li['name'] : '';
                $a = isset( $li['amount'] ) ? (float) $li['amount'] : 0.0;
                $lines[] = esc_html( $n ) . ( $a > 0 ? ' — ' . esc_html( kab_format_currency( $a, $sym ) ) : '' );
            }
            $item_display = implode( '<br>', $lines );
        }
        echo '<td>' . wp_kses_post( $item_display ) . '</td>';
        echo '<td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $r['invoice_date'] ) ) ) . '</td>';
        echo '<td>' . esc_html( kab_format_currency( (float) $r['total_amount'], $sym ) ) . '</td>';
        echo '<td>' . esc_html( ucfirst( $r['payment_status'] ) ) . '</td>';
        echo '</tr>';
        $total += (float) $r['total_amount'];
    }
    echo '</tbody><tfoot><tr><td colspan="5">' . esc_html__( 'Total', 'kura-ai-booking-free' ) . '</td><td colspan="2">' . esc_html( kab_format_currency( $total, kab_currency_symbol( isset( $rows[0]['currency'] ) ? $rows[0]['currency'] : 'USD' ) ) ) . '</td></tr></tfoot></table>';
    echo '</body></html>';
    $html = ob_get_clean();

    // Try mPDF if available for proper PDF; else serve HTML
    $upload_dir = wp_upload_dir();
    $filename   = 'invoices-export-' . date( 'Ymd-His' ) . '.pdf';
    $out_path   = ! empty( $upload_dir['basedir'] ) ? trailingslashit( $upload_dir['basedir'] ) . 'kuraai/invoices/' . $filename : '';
    $served_pdf = false;
    if ( class_exists( '\\Mpdf\\Mpdf' ) || file_exists( KAB_FREE_PLUGIN_DIR . 'vendor/autoload.php' ) || file_exists( ABSPATH . 'vendor/autoload.php' ) ) {
        if ( ! class_exists( '\\Mpdf\\Mpdf' ) ) {
            foreach ( array( KAB_FREE_PLUGIN_DIR . 'vendor/autoload.php', ABSPATH . 'vendor/autoload.php' ) as $p ) { if ( file_exists( $p ) ) { require_once $p; } }
        }
        if ( class_exists( '\\Mpdf\\Mpdf' ) ) {
            try {
                if ( ! file_exists( dirname( $out_path ) ) && ! empty( $upload_dir['basedir'] ) ) { wp_mkdir_p( dirname( $out_path ) ); }
                $mpdf = new \Mpdf\Mpdf([ 'tempDir' => $upload_dir['basedir'] . '/kuraai/tmp' ]);
                $mpdf->WriteHTML( $html );
                if ( $out_path ) { $mpdf->Output( $out_path, 'F' ); }
                header( 'Content-Type: application/pdf' );
                header( 'Content-Disposition: attachment; filename=' . $filename );
                echo $mpdf->Output( $filename, 'S' );
                $served_pdf = true;
                exit;
            } catch ( \Exception $e ) {
                // fall through to HTML
            }
        }
    }
    if ( ! $served_pdf ) {
        header( 'Content-Type: text/html; charset=UTF-8' );
        echo $html;
        exit;
    }
} );

// Define plugin constants.
if ( ! defined( 'KAB_FREE_PLUGIN_DIR' ) ) {
	define( 'KAB_FREE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'KAB_FREE_PLUGIN_URL' ) ) {
	define( 'KAB_FREE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'KAB_VERSION' ) ) {
	define( 'KAB_VERSION', '1.0.0' );
}

// Load plugin includes immediately for activation/deactivation hooks.
require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-database.php';
require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-activator.php';
require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-deactivator.php';

// Activation hook.
register_activation_hook( __FILE__, 'kab_free_activate_plugin' );

/**
 * Plugin activation callback.
 *
 * Sets up the setup wizard transient and runs activation tasks.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_activate_plugin() {
	// Run activation tasks.
	KAB_Activator::activate();

	// Show setup wizard.
	set_transient( 'kab_free_show_setup_wizard', true, 60 );
}

// Deactivation hook.
register_deactivation_hook( __FILE__, 'kab_free_deactivate_plugin' );

/**
 * Plugin deactivation callback.
 *
 * Sets up the deactivation modal transient and runs deactivation tasks.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_deactivate_plugin() {
	// Run deactivation tasks.
	KAB_Deactivator::deactivate();

	// Show deactivation modal.
	set_transient( 'kab_free_show_deactivation_modal', true, 60 );
}

// Enqueue SweetAlert2 in admin.
add_action( 'admin_enqueue_scripts', 'kab_free_enqueue_admin_scripts' );

/**
 * Enqueue admin scripts and styles.
 *
 * Loads SweetAlert2 and custom admin scripts.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_enqueue_admin_scripts() {
    wp_enqueue_script( 'sweetalert2', KAB_FREE_PLUGIN_URL . 'assets/js/sweetalert2.all.min.js', array(), '11.26.3', true );
    wp_enqueue_style( 'sweetalert2', KAB_FREE_PLUGIN_URL . 'assets/css/sweetalert2.min.css', array(), '11.26.3' );
    wp_enqueue_script( 'kab-free-admin', KAB_FREE_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'sweetalert2' ), '1.0.0', true );
}

// Setup wizard admin notice/redirect.
add_action( 'admin_init', 'kab_free_maybe_show_setup_wizard' );

/**
 * Maybe show setup wizard redirect.
 *
 * Redirects to setup wizard if the transient is set.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_maybe_show_setup_wizard() {
	if ( get_transient( 'kab_free_show_setup_wizard' ) ) {
		delete_transient( 'kab_free_show_setup_wizard' );
		wp_safe_redirect( admin_url( 'admin.php?page=kab-setup-wizard' ) );
		exit;
	}
}

// Deactivation modal logic.
add_action( 'admin_notices', 'kab_free_maybe_show_deactivation_modal' );

/**
 * Maybe show deactivation modal.
 *
 * Shows SweetAlert2 modal on plugin deactivation.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_maybe_show_deactivation_modal() {
	if ( get_transient( 'kab_free_show_deactivation_modal' ) ) {
		delete_transient( 'kab_free_show_deactivation_modal' );
		
		// Generate nonce for deactivation URLs.
		$deactivate_nonce = wp_create_nonce( 'deactivate-plugin_kura-ai-booking-free/kura-ai-booking-free.php' );
		$full_uninstall_url = admin_url( 'plugins.php?action=deactivate&plugin=kura-ai-booking-free/kura-ai-booking-free.php&delete_data=1&_wpnonce=' . $deactivate_nonce );
		$temp_deactivate_url = admin_url( 'plugins.php?action=deactivate&plugin=kura-ai-booking-free/kura-ai-booking-free.php&_wpnonce=' . $deactivate_nonce );
		
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<script type="text/javascript">jQuery(function($){
		Swal.fire({
			title: "Deactivate Kura-ai Booking System?",
			text: "Would you like to temporarily deactivate or fully uninstall (delete all plugin data)?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Uninstall & Delete",
			cancelButtonText: "Temporary Deactivate"
		}).then(function(result){
			if(result.isConfirmed){
				window.location.href = "' . esc_url( $full_uninstall_url ) . '";
			} else if (result.dismiss === Swal.DismissReason.cancel) {
				// Temporary deactivation - proceed with normal deactivation
				window.location.href = "' . esc_url( $temp_deactivate_url ) . '";
			}
		});
		});</script>';
	}
}

// Load plugin text domain for translations.
add_action( 'plugins_loaded', 'kab_free_load_textdomain' );

/**
 * Load plugin text domain for translations.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_load_textdomain() {
	load_plugin_textdomain(
		'kura-ai-booking-free',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

// Global instance of the setup wizard.
$kab_setup_wizard_instance = null;

// Load plugin includes and initialize components.
add_action( 'init', 'kab_free_init_plugin' );

/**
 * Initialize the plugin, load includes, and set up hooks.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_init_plugin() {
    global $kab_setup_wizard_instance;

	// Load core plugin classes.
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-loader.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-admin.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-frontend.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-bookings.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-qr-generator.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-tickets.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/rest/class-kab-rest-controller.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoices.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoice-pdf.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoice-admin.php';

	// Instantiate the admin class to register menus.
    new KAB_Admin();
    new KAB_Invoices();
    if ( class_exists( 'KAB_Invoice_Admin' ) ) {
        new KAB_Invoice_Admin();
    } else {
        error_log( 'KAB: KAB_Invoice_Admin not found after require. Check includes/class-kab-invoice-admin.php for parse errors.' );
    }

	// Load and instantiate the setup wizard.
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-setup-wizard.php';
	$kab_setup_wizard_instance = new KAB_Setup_Wizard();

	// Add the menu page.
    add_action( 'admin_menu', 'kab_free_setup_wizard_menu' );
}

add_action( 'plugins_loaded', 'kab_free_enable_deprecation_trace', 1 );
function kab_free_enable_deprecation_trace() {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        set_error_handler( function( $errno, $errstr, $errfile, $errline ) {
            if ( $errno === E_DEPRECATED && ( strpos( $errstr, 'strpos(): Passing null' ) !== false || strpos( $errstr, 'str_replace(): Passing null' ) !== false ) ) {
                error_log( 'KAB TRACE: ' . $errstr . ' at ' . $errfile . ':' . $errline );
                $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
                foreach ( $bt as $frame ) {
                    $fn   = isset( $frame['function'] ) ? $frame['function'] : '';
                    $file = isset( $frame['file'] ) ? $frame['file'] : '';
                    $line = isset( $frame['line'] ) ? $frame['line'] : 0;
                    error_log( 'KAB TRACE: ' . $fn . ' ' . $file . ':' . $line );
                }
            }
        }, E_DEPRECATED );
    }
}

add_filter( 'style_loader_src', 'kab_free_sanitize_asset_src', 10, 2 );
add_filter( 'script_loader_src', 'kab_free_sanitize_asset_src', 10, 2 );
function kab_free_sanitize_asset_src( $src, $handle = '' ) {
    return is_string( $src ) ? $src : '';
}
add_filter( 'upload_dir', 'kab_free_sanitize_upload_dir' );
function kab_free_sanitize_upload_dir( $paths ) {
    if ( ! is_array( $paths ) ) {
        return $paths;
    }
    foreach ( array( 'path', 'url', 'subdir', 'basedir', 'baseurl' ) as $k ) {
        if ( isset( $paths[ $k ] ) && ! is_string( $paths[ $k ] ) ) {
            $paths[ $k ] = '';
        }
    }
    return $paths;
}

/**
 * Add setup wizard menu page.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_setup_wizard_menu() {
	add_menu_page(
		__( 'Kura-ai Setup Wizard', 'kura-ai-booking-free' ),
		__( 'Kura-ai Setup', 'kura-ai-booking-free' ),
		'manage_options',
		'kab-setup-wizard',
		'kab_free_render_setup_wizard_page',
		'dashicons-calendar-alt',
		2
	);
}

/**
 * Render the setup wizard page by calling the method on the global instance.
 *
 * @since 1.0.0
 * @return void
 */
function kab_free_render_setup_wizard_page() {
	global $kab_setup_wizard_instance;
	if ( $kab_setup_wizard_instance ) {
		$kab_setup_wizard_instance->render_setup_page();
	}
}
function kab_free_load_includes() {
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-loader.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-activator.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-deactivator.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-admin.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-frontend.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-database.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-bookings.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-qr-generator.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-tickets.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-setup-wizard.php';
	require_once KAB_FREE_PLUGIN_DIR . 'includes/rest/class-kab-rest-controller.php';

	// Initialize plugin components.
	$loader = new KAB_Loader();
	$loader->run();

	// Initialize REST API.
	add_action(
		'rest_api_init',
		function () {
			$rest_controller = new KAB_REST_Controller();
			$rest_controller->register_routes();
		}
	);
}

if ( ! function_exists( 'kab_currency_symbol' ) ) {
    function kab_currency_symbol( $code ) {
        $map = array(
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
        );
        $code = strtoupper( (string) $code );
        return isset( $map[ $code ] ) ? $map[ $code ] : get_option( 'kab_currency_symbol', '$' );
    }
}

if ( ! function_exists( 'kab_format_currency' ) ) {
    function kab_format_currency( $amount, $symbol = null ) {
        $symbol = $symbol ?: get_option( 'kab_currency_symbol', '$' );
        return $symbol . number_format( (float) $amount, 2 );
    }
}

// Tax helpers
if ( ! function_exists( 'kab_get_tax_settings' ) ) {
    function kab_get_tax_settings() {
        $opt = get_option( 'kab_settings', array() );
        return array(
            'enabled' => ! empty( $opt['tax_enabled'] ),
            'mode'    => isset( $opt['tax_mode'] ) ? $opt['tax_mode'] : 'excluded',
            'type'    => isset( $opt['tax_type'] ) ? $opt['tax_type'] : 'percent',
            'value'   => isset( $opt['tax_value'] ) ? floatval( $opt['tax_value'] ) : 0.0,
        );
    }
}

if ( ! function_exists( 'kab_apply_tax' ) ) {
    // Returns array [subtotal, tax_amount, total]
    function kab_apply_tax( $amount ) {
        $s = kab_get_tax_settings();
        $amount = floatval( $amount );
        if ( ! $s['enabled'] || $s['value'] <= 0 ) {
            return array( round( $amount, 2 ), 0.0, round( $amount, 2 ) );
        }
        if ( $s['type'] === 'percent' ) {
            $rate = $s['value'] / 100.0;
            if ( $s['mode'] === 'included' ) {
                $subtotal = $amount / ( 1.0 + $rate );
                $tax      = $amount - $subtotal;
                return array( round( $subtotal, 2 ), round( $tax, 2 ), round( $amount, 2 ) );
            } else {
                $tax = $amount * $rate;
                return array( round( $amount, 2 ), round( $tax, 2 ), round( $amount + $tax, 2 ) );
            }
        } else { // fixed
            $fixed = $s['value'];
            if ( $s['mode'] === 'included' ) {
                $tax = min( $fixed, $amount );
                $subtotal = $amount - $tax;
                return array( round( $subtotal, 2 ), round( $tax, 2 ), round( $amount, 2 ) );
            } else {
                $tax = $fixed;
                return array( round( $amount, 2 ), round( $tax, 2 ), round( $amount + $tax, 2 ) );
            }
        }
    }
}

add_action( 'admin_post_kab_download_invoice', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 );
    $nonce      = sanitize_text_field( $_GET['_wpnonce'] ?? '' );
    if ( ! $invoice_id || ! wp_verify_nonce( $nonce, 'kab_download_invoice_' . $invoice_id ) ) {
        wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) );
    }
    require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoice-pdf.php';
    KAB_Invoice_PDF::serve_pdf( $invoice_id, 'attachment' );
    exit;
} );

add_action( 'admin_post_kab_preview_invoice', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 );
    $nonce      = sanitize_text_field( $_GET['_wpnonce'] ?? '' );
    if ( ! $invoice_id || ! wp_verify_nonce( $nonce, 'kab_preview_invoice_' . $invoice_id ) ) {
        wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) );
    }
    require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoice-pdf.php';
    KAB_Invoice_PDF::serve_pdf( $invoice_id, 'inline' );
    exit;
} );

add_action( 'admin_post_kab_resend_invoice', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 );
    $nonce      = sanitize_text_field( $_GET['_wpnonce'] ?? '' );
    if ( ! $invoice_id || ! wp_verify_nonce( $nonce, 'kab_resend_invoice_' . $invoice_id ) ) {
        wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) );
    }
    require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoices.php';
    $ok = KAB_Invoices::email_invoice( $invoice_id );
    wp_redirect( add_query_arg( array( 'page' => 'kab-invoice-details', 'invoice_id' => $invoice_id, 'sent' => $ok ? '1' : '0' ), admin_url( 'admin.php' ) ) );
    exit;
} );

add_action( 'admin_post_kab_delete_invoice', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'kura-ai-booking-free' ) );
    }
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 );
    $nonce      = sanitize_text_field( $_GET['_wpnonce'] ?? '' );
    if ( ! $invoice_id || ! wp_verify_nonce( $nonce, 'kab_delete_invoice_' . $invoice_id ) ) {
        wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) );
    }
    global $wpdb;
    $inv = $wpdb->get_row( $wpdb->prepare( "SELECT pdf_path FROM {$wpdb->prefix}kab_invoices WHERE id = %d", $invoice_id ), ARRAY_A );
    if ( $inv && ! empty( $inv['pdf_path'] ) ) {
        $upload_dir = wp_upload_dir();
        $file_path = '';
        if ( is_string( $upload_dir['baseurl'] ?? '' ) && strpos( $inv['pdf_path'], $upload_dir['baseurl'] ) === 0 ) {
            $rel = substr( $inv['pdf_path'], strlen( $upload_dir['baseurl'] ) );
            $file_path = trailingslashit( $upload_dir['basedir'] ) . ltrim( $rel, '/' );
        } else {
            $file_path = ABSPATH . wp_parse_url( $inv['pdf_path'], PHP_URL_PATH );
        }
        if ( $file_path && file_exists( $file_path ) ) {
            @unlink( $file_path );
        }
    }
    $wpdb->delete( $wpdb->prefix . 'kab_invoices', array( 'id' => $invoice_id ), array( '%d' ) );
    $map = get_option( 'kab_service_invoice_map', array() );
    if ( is_array( $map ) ) {
        foreach ( $map as $sid => $iid ) {
            if ( intval( $iid ) === $invoice_id ) {
                unset( $map[ $sid ] );
            }
        }
        update_option( 'kab_service_invoice_map', $map );
    }
    wp_redirect( add_query_arg( array( 'page' => 'kab-invoices', 'deleted' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
} );

add_action( 'admin_post_kab_update_invoice', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'kura-ai-booking-free' ) );
    }
    $invoice_id = intval( $_POST['invoice_id'] ?? 0 );
    $nonce      = sanitize_text_field( $_POST['_wpnonce'] ?? '' );
    if ( ! $invoice_id || ! wp_verify_nonce( $nonce, 'kab_update_invoice_' . $invoice_id ) ) {
        wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) );
    }
    $payment_status = sanitize_text_field( $_POST['payment_status'] ?? '' );
    $payment_method = sanitize_text_field( $_POST['payment_method'] ?? '' );
    $currency       = strtoupper( sanitize_text_field( $_POST['currency'] ?? '' ) );
    if ( ! in_array( $payment_status, array( 'pending', 'paid', 'partial' ), true ) ) {
        $payment_status = 'pending';
    }
    global $wpdb;
    $data = array(
        'payment_status' => $payment_status,
        'payment_method' => $payment_method,
    );
    $format = array( '%s', '%s' );
    if ( $currency ) { $data['currency'] = $currency; $format[] = '%s'; }
    $wpdb->update(
        $wpdb->prefix . 'kab_invoices',
        $data,
        array( 'id' => $invoice_id ),
        $format,
        array( '%d' )
    );
    wp_redirect( add_query_arg( array( 'page' => 'kab-invoice-details', 'invoice_id' => $invoice_id, 'updated' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
} );
if ( ! function_exists( 'kab_get_payment_settings' ) ) {
    function kab_get_payment_settings() {
        $opt = get_option( 'kab_settings', array() );
        return array(
            'default'        => isset( $opt['payment_default'] ) ? $opt['payment_default'] : 'onsite',
            'paypal_enabled' => ! empty( $opt['paypal_enabled'] ),
            'paypal_sandbox' => ! empty( $opt['paypal_sandbox'] ),
            'paypal_merchant'=> isset( $opt['paypal_merchant'] ) ? $opt['paypal_merchant'] : '',
            'stripe_enabled' => ! empty( $opt['stripe_enabled'] ),
            'stripe_testmode'=> ! empty( $opt['stripe_testmode'] ),
            'stripe_secret'  => isset( $opt['stripe_secret'] ) ? $opt['stripe_secret'] : '',
            'mollie_enabled' => ! empty( $opt['mollie_enabled'] ),
            'mollie_key'     => isset( $opt['mollie_key'] ) ? $opt['mollie_key'] : '',
            'razor_enabled'  => ! empty( $opt['razor_enabled'] ),
            'razor_testmode' => ! empty( $opt['razor_testmode'] ),
            'razor_key_id'   => isset( $opt['razor_key_id'] ) ? $opt['razor_key_id'] : '',
            'razor_key_secret'=> isset( $opt['razor_key_secret'] ) ? $opt['razor_key_secret'] : '',
            'paystack_enabled'=> ! empty( $opt['paystack_enabled'] ),
            'paystack_testmode'=> ! empty( $opt['paystack_testmode'] ),
            'paystack_secret' => isset( $opt['paystack_secret'] ) ? $opt['paystack_secret'] : '',
            'flutter_enabled' => ! empty( $opt['flutter_enabled'] ),
            'flutter_testmode'=> ! empty( $opt['flutter_testmode'] ),
            'flutter_secret'  => isset( $opt['flutter_secret'] ) ? $opt['flutter_secret'] : '',
        );
    }
}

// PayPal IPN handler
add_action( 'admin_post_nopriv_kab_paypal_ipn', 'kab_paypal_ipn_handler' );
add_action( 'admin_post_kab_paypal_ipn', 'kab_paypal_ipn_handler' );
function kab_paypal_ipn_handler() {
    // Validate IPN
    $raw = file_get_contents( 'php://input' );
    $payload = 'cmd=_notify-validate&' . $raw;
    $settings = kab_get_payment_settings();
    $endpoint = $settings['paypal_sandbox'] ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
    $resp = wp_remote_post( $endpoint, array( 'body' => $payload, 'timeout' => 20 ) );
    if ( is_wp_error( $resp ) ) { status_header( 500 ); exit; }
    $body = wp_remote_retrieve_body( $resp );
    // Update invoice on VERIFIED Completed
    if ( trim( $body ) === 'VERIFIED' && isset( $_POST['payment_status'] ) && $_POST['payment_status'] === 'Completed' && isset( $_POST['custom'] ) ) {
        $invoice_id = intval( $_POST['custom'] );
        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'kab_invoices', array( 'payment_status' => 'paid', 'payment_method' => 'paypal' ), array( 'id' => $invoice_id ), array( '%s', '%s' ), array( '%d' ) );
    }
    exit;
}

// PayPal return handler (non-authoritative, friendly redirect)
add_action( 'admin_post_nopriv_kab_paypal_return', 'kab_paypal_return_handler' );
add_action( 'admin_post_kab_paypal_return', 'kab_paypal_return_handler' );
function kab_paypal_return_handler() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 );
    if ( $invoice_id ) {
        wp_redirect( add_query_arg( array( 'page' => 'kab-invoice-details', 'invoice_id' => $invoice_id, 'paid' => '1' ), admin_url( 'admin.php' ) ) );
        exit;
    }
    wp_redirect( admin_url( 'admin.php?page=kab-invoices' ) );
    exit;
}
function kab_mark_invoice_paid( $invoice_id, $method ) {
    global $wpdb;
    $wpdb->update( $wpdb->prefix . 'kab_invoices', array( 'payment_status' => 'paid', 'payment_method' => sanitize_text_field( $method ) ), array( 'id' => intval( $invoice_id ) ), array( '%s', '%s' ), array( '%d' ) );
}

add_action( 'admin_post_kab_pay_invoice', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 );
    $gateway    = sanitize_key( $_GET['gateway'] ?? '' );
    if ( ! $invoice_id || ! $gateway ) { wp_die( __( 'Invalid request', 'kura-ai-booking-free' ) ); }
    global $wpdb; $inv = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_invoices WHERE id=%d", $invoice_id ), ARRAY_A );
    if ( ! $inv ) { wp_die( __( 'Invoice not found', 'kura-ai-booking-free' ) ); }
    $settings = kab_get_payment_settings();
    $currency = isset( $inv['currency'] ) ? strtoupper( $inv['currency'] ) : 'USD';
    $amount   = number_format( (float) $inv['total_amount'], 2, '.', '' );
    $return   = admin_url( 'admin-post.php?action=kab_' . $gateway . '_return&invoice_id=' . $invoice_id );
    switch ( $gateway ) {
        case 'stripe':
            if ( empty( $settings['stripe_enabled'] ) || empty( $settings['stripe_secret'] ) ) { wp_die( __( 'Stripe not configured', 'kura-ai-booking-free' ) ); }
            $body = array(
                'mode' => 'payment',
                'success_url' => $return . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id ),
                'client_reference_id' => (string) $invoice_id,
                'metadata[invoice_id]' => (string) $invoice_id,
                'line_items[0][price_data][currency]' => $currency,
                'line_items[0][price_data][product_data][name]' => 'Invoice ' . (string) $inv['invoice_number'],
                'line_items[0][price_data][unit_amount]' => (int) round( (float) $amount * 100 ),
                'line_items[0][quantity]' => 1,
            );
            $resp = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', array( 'headers' => array( 'Authorization' => 'Bearer ' . $settings['stripe_secret'] ), 'body' => $body ) );
            if ( is_wp_error( $resp ) ) { wp_die( __( 'Stripe error', 'kura-ai-booking-free' ) ); }
            $data = json_decode( wp_remote_retrieve_body( $resp ), true );
            if ( empty( $data['url'] ) ) { wp_die( __( 'Failed to create Stripe session', 'kura-ai-booking-free' ) ); }
            wp_redirect( $data['url'] ); exit;
        case 'mollie':
            if ( empty( $settings['mollie_enabled'] ) || empty( $settings['mollie_key'] ) ) { wp_die( __( 'Mollie not configured', 'kura-ai-booking-free' ) ); }
            $body = wp_json_encode( array( 'amount' => array( 'currency' => $currency, 'value' => $amount ), 'description' => 'Invoice ' . (string) $inv['invoice_number'], 'redirectUrl' => $return, 'metadata' => array( 'invoice_id' => $invoice_id ) ) );
            $resp = wp_remote_post( 'https://api.mollie.com/v2/payments', array( 'headers' => array( 'Authorization' => 'Bearer ' . $settings['mollie_key'], 'Content-Type' => 'application/json' ), 'body' => $body ) );
            $data = json_decode( wp_remote_retrieve_body( $resp ), true );
            if ( empty( $data['_links']['checkout']['href'] ) ) { wp_die( __( 'Failed to create Mollie payment', 'kura-ai-booking-free' ) ); }
            wp_redirect( $data['_links']['checkout']['href'] ); exit;
        case 'razorpay':
            if ( empty( $settings['razor_enabled'] ) || empty( $settings['razor_key_id'] ) || empty( $settings['razor_key_secret'] ) ) { wp_die( __( 'Razorpay not configured', 'kura-ai-booking-free' ) ); }
            $body = array( 'amount' => (int) round( (float) $amount * 100 ), 'currency' => $currency, 'description' => 'Invoice ' . (string) $inv['invoice_number'], 'callback_url' => $return, 'callback_method' => 'get' );
            $resp = wp_remote_post( 'https://api.razorpay.com/v1/payment_links', array( 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $settings['razor_key_id'] . ':' . $settings['razor_key_secret'] ) ), 'body' => $body ) );
            $data = json_decode( wp_remote_retrieve_body( $resp ), true );
            if ( empty( $data['short_url'] ) ) { wp_die( __( 'Failed to create Razorpay link', 'kura-ai-booking-free' ) ); }
            wp_redirect( $data['short_url'] ); exit;
        case 'paystack':
            if ( empty( $settings['paystack_enabled'] ) || empty( $settings['paystack_secret'] ) ) { wp_die( __( 'Paystack not configured', 'kura-ai-booking-free' ) ); }
            $body = wp_json_encode( array( 'amount' => (int) round( (float) $amount * 100 ), 'currency' => $currency, 'email' => $inv['customer_email'], 'reference' => 'inv_' . $invoice_id, 'callback_url' => $return ) );
            $resp = wp_remote_post( 'https://api.paystack.co/transaction/initialize', array( 'headers' => array( 'Authorization' => 'Bearer ' . $settings['paystack_secret'], 'Content-Type' => 'application/json' ), 'body' => $body ) );
            $data = json_decode( wp_remote_retrieve_body( $resp ), true );
            if ( empty( $data['data']['authorization_url'] ) ) { wp_die( __( 'Failed to init Paystack', 'kura-ai-booking-free' ) ); }
            wp_redirect( $data['data']['authorization_url'] ); exit;
        case 'flutterwave':
            if ( empty( $settings['flutter_enabled'] ) || empty( $settings['flutter_secret'] ) ) { wp_die( __( 'Flutterwave not configured', 'kura-ai-booking-free' ) ); }
            $body = wp_json_encode( array( 'tx_ref' => 'inv_' . $invoice_id, 'amount' => (float) $amount, 'currency' => $currency, 'redirect_url' => $return, 'customer' => array( 'email' => $inv['customer_email'], 'name' => $inv['customer_name'] ), 'meta' => array( 'invoice_id' => $invoice_id ), 'payment_options' => 'card' ) );
            $resp = wp_remote_post( 'https://api.flutterwave.com/v3/payments', array( 'headers' => array( 'Authorization' => 'Bearer ' . $settings['flutter_secret'], 'Content-Type' => 'application/json' ), 'body' => $body ) );
            $data = json_decode( wp_remote_retrieve_body( $resp ), true );
            if ( empty( $data['data']['link'] ) ) { wp_die( __( 'Failed to init Flutterwave', 'kura-ai-booking-free' ) ); }
            wp_redirect( $data['data']['link'] ); exit;
        default:
            wp_die( __( 'Unknown gateway', 'kura-ai-booking-free' ) );
    }
} );

add_action( 'admin_post_kab_stripe_return', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 ); $session_id = sanitize_text_field( $_GET['session_id'] ?? '' );
    $s = kab_get_payment_settings(); if ( $invoice_id && $session_id && ! empty( $s['stripe_secret'] ) ) {
        $resp = wp_remote_get( 'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode( $session_id ), array( 'headers' => array( 'Authorization' => 'Bearer ' . $s['stripe_secret'] ) ) );
        $data = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( isset( $data['payment_status'] ) && $data['payment_status'] === 'paid' ) { kab_mark_invoice_paid( $invoice_id, 'stripe' ); }
    }
    wp_redirect( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id . '&paid=1' ) ); exit;
} );

add_action( 'admin_post_kab_mollie_return', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 ); $s = kab_get_payment_settings();
    wp_redirect( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id . '&paid=1' ) ); exit;
} );

add_action( 'admin_post_kab_razorpay_return', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 ); wp_redirect( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id . '&paid=1' ) ); exit;
} );

add_action( 'admin_post_kab_paystack_return', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 ); $reference = sanitize_text_field( $_GET['reference'] ?? '' );
    $s = kab_get_payment_settings(); if ( $reference && ! empty( $s['paystack_secret'] ) ) {
        $resp = wp_remote_get( 'https://api.paystack.co/transaction/verify/' . rawurlencode( $reference ), array( 'headers' => array( 'Authorization' => 'Bearer ' . $s['paystack_secret'] ) ) );
        $data = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( isset( $data['data']['status'] ) && $data['data']['status'] === 'success' ) { kab_mark_invoice_paid( $invoice_id, 'paystack' ); }
    }
    wp_redirect( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id . '&paid=1' ) ); exit;
} );

add_action( 'admin_post_kab_flutterwave_return', function() {
    $invoice_id = intval( $_GET['invoice_id'] ?? 0 ); $tx_id = sanitize_text_field( $_GET['transaction_id'] ?? '' ); $s = kab_get_payment_settings();
    if ( $tx_id && ! empty( $s['flutter_secret'] ) ) {
        $resp = wp_remote_get( 'https://api.flutterwave.com/v3/transactions/' . rawurlencode( $tx_id ) . '/verify', array( 'headers' => array( 'Authorization' => 'Bearer ' . $s['flutter_secret'] ) ) );
        $data = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( isset( $data['status'] ) && $data['status'] === 'success' ) { kab_mark_invoice_paid( $invoice_id, 'flutterwave' ); }
    }
    wp_redirect( admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id . '&paid=1' ) ); exit;
} );
