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

// Define plugin constants
if ( ! defined( 'KAB_FREE_PLUGIN_DIR' ) ) {
	define( 'KAB_FREE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'KAB_FREE_PLUGIN_URL' ) ) {
	define( 'KAB_FREE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Activation hook
register_activation_hook( __FILE__, 'kab_free_activate_plugin' );
function kab_free_activate_plugin() {
	set_transient( 'kab_free_show_setup_wizard', true, 60 );
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'kab_free_deactivate_plugin' );
function kab_free_deactivate_plugin() {
	set_transient( 'kab_free_show_deactivation_modal', true, 60 );
}

// Enqueue SweetAlert2 in admin
add_action( 'admin_enqueue_scripts', 'kab_free_enqueue_admin_scripts' );
function kab_free_enqueue_admin_scripts() {
	wp_enqueue_script( 'sweetalert2', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.3/sweetalert2.all.min.js', array(), '11.26.3', true );
	wp_enqueue_style( 'sweetalert2', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.3/sweetalert2.min.css', array(), '11.26.3' );
	wp_enqueue_script( 'kab-free-admin', KAB_FREE_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery', 'sweetalert2' ), '1.0.0', true );
}

// Setup wizard admin notice/redirect
add_action( 'admin_init', 'kab_free_maybe_show_setup_wizard' );
function kab_free_maybe_show_setup_wizard() {
	if ( get_transient( 'kab_free_show_setup_wizard' ) ) {
		delete_transient( 'kab_free_show_setup_wizard' );
		wp_safe_redirect( admin_url( 'admin.php?page=kab-setup-wizard' ) );
		exit;
	}
}

// Deactivation modal logic
add_action( 'admin_notices', 'kab_free_maybe_show_deactivation_modal' );
function kab_free_maybe_show_deactivation_modal() {
	if ( get_transient( 'kab_free_show_deactivation_modal' ) ) {
		delete_transient( 'kab_free_show_deactivation_modal' );
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
				window.location.href = "' . admin_url( 'plugins.php?action=deactivate&plugin=kura-ai-booking-free/kura-ai-booking-free.php&delete_data=1' ) . '";
			}
		});
		});</script>';
	}
}

// Setup wizard page
add_action( 'admin_menu', 'kab_free_setup_wizard_menu' );
function kab_free_setup_wizard_menu() {
	add_menu_page( __( 'Kura-ai Setup Wizard', 'kura-ai-booking-free' ), __( 'Kura-ai Setup', 'kura-ai-booking-free' ), 'manage_options', 'kab-setup-wizard', 'kab_free_setup_wizard_page', 'dashicons-calendar-alt', 2 );
}
function kab_free_setup_wizard_page() {
	echo '<div class="wrap"><h1>' . esc_html__( 'Welcome to Kura-ai Booking System Setup', 'kura-ai-booking-free' ) . '</h1>';
	echo '<p>' . esc_html__( 'Let’s get started with your booking system setup.', 'kura-ai-booking-free' ) . '</p>';
	echo '</div>';
}
