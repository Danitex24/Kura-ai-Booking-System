<?php
/**
 * Kura-ai Booking System Deactivator
 *
 * Handles plugin deactivation tasks.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Deactivator {
	public static function deactivate() {
		// Clear scheduled events
		wp_clear_scheduled_hook( 'kab_free_daily_cleanup' );

		// Clean up transients
		delete_transient( 'kab_free_show_setup_wizard' );
		delete_transient( 'kab_free_show_deactivation_modal' );

		// Remove setup wizard data if user chose to uninstall
		if ( isset( $_GET['delete_data'] ) && $_GET['delete_data'] == '1' ) {
			self::cleanup_plugin_data();
		}
	}

	/**
	 * Clean up plugin data on uninstall.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function cleanup_plugin_data() {
		global $wpdb;

		// Drop plugin tables
		$tables = array(
			'kab_services',
			'kab_events',
			'kab_bookings',
			'kab_tickets',
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
		}

		// Delete options
		delete_option( 'kab_free_settings' );
		delete_option( 'kab_free_version' );
		delete_option( 'kab_free_setup_complete' );

		// Remove any other transients
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%kab_free_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_kab_free_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_kab_free_%'" );
	}
}
