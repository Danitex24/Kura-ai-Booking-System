<?php
/**
 * Kura-ai Booking System Activator
 *
 * Handles plugin activation tasks.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Activator {
	public static function activate() {
		// Create database tables
		KAB_Database::create_tables();
		
		// Set default options
		if ( ! get_option( 'kab_free_settings' ) ) {
			update_option( 'kab_free_settings', array(
				'company_name' => get_bloginfo( 'name' ),
				'company_email' => get_bloginfo( 'admin_email' ),
				'enable_tickets' => 'yes',
				'enable_email_notifications' => 'yes',
				'currency' => 'USD',
				'currency_position' => 'left',
				'date_format' => 'Y-m-d',
				'time_format' => 'H:i',
			) );
		}
		
		// Set plugin version
		update_option( 'kab_free_version', KAB_VERSION );
		
		// Schedule cleanup cron job
		if ( ! wp_next_scheduled( 'kab_free_daily_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'kab_free_daily_cleanup' );
		}
	}
}
