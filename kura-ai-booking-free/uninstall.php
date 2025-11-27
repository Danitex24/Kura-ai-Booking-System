<?php
/**
 * Uninstall Kura-ai Booking System (Free)
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin data if requested
if ( isset( $_GET['delete_data'] ) && $_GET['delete_data'] == '1' ) {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}kab_services" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}kab_events" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}kab_bookings" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}kab_tickets" );
	// Delete plugin options
	delete_option( 'kab_free_settings' );
}
