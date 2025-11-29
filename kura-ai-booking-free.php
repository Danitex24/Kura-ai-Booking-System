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

if ( ! function_exists( 'kab_format_currency' ) ) {
    function kab_format_currency( $amount, $symbol = null ) {
        $symbol = $symbol ?: get_option( 'kab_currency_symbol', '$' );
        return $symbol . number_format( (float) $amount, 2 );
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
    if ( ! in_array( $payment_status, array( 'pending', 'paid', 'partial' ), true ) ) {
        $payment_status = 'pending';
    }
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'kab_invoices',
        array(
            'payment_status' => $payment_status,
            'payment_method' => $payment_method,
        ),
        array( 'id' => $invoice_id ),
        array( '%s', '%s' ),
        array( '%d' )
    );
    wp_redirect( add_query_arg( array( 'page' => 'kab-invoice-details', 'invoice_id' => $invoice_id, 'updated' => '1' ), admin_url( 'admin.php' ) ) );
    exit;
} );
