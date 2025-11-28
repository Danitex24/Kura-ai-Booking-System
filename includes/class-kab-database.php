<?php
/**
 * Kura-ai Booking System Database
 *
 * Handles table creation and upgrades.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Database {
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$tables = array();

		$tables[] = "CREATE TABLE {$wpdb->prefix}kab_services (
			id INT NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			duration INT NOT NULL,
			price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}kab_events (
			id INT NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			event_date DATE NOT NULL,
			event_time TIME NOT NULL,
			location VARCHAR(255),
			price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			capacity INT NOT NULL DEFAULT 1,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}kab_bookings (
			id INT NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			service_id INT,
			event_id INT,
			booking_type VARCHAR(20) NOT NULL,
			booking_date DATE NOT NULL,
			booking_time TIME NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			ticket_id VARCHAR(64),
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}kab_tickets (
			id INT NOT NULL AUTO_INCREMENT,
			booking_id INT NOT NULL,
			ticket_id VARCHAR(64) NOT NULL,
			qr_code_path VARCHAR(255),
			pdf_path VARCHAR(255),
			status VARCHAR(20) NOT NULL DEFAULT 'valid',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY ticket_id (ticket_id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}kab_invoices (
			id INT NOT NULL AUTO_INCREMENT,
			invoice_number VARCHAR(20) NOT NULL,
			booking_id INT NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			customer_name TEXT NOT NULL,
			customer_email TEXT NOT NULL,
			item_name TEXT NOT NULL,
			invoice_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
			payment_method VARCHAR(50),
			pdf_path TEXT,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY invoice_number (invoice_number),
			KEY booking_id (booking_id),
			KEY user_id (user_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}
	}

	public static function upgrade() {
		// Upgrade logic here
	}
}
