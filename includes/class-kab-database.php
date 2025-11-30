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
            currency VARCHAR(3) NOT NULL DEFAULT 'USD',
            payment_methods TEXT,
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
			event_end_time TIME NULL,
			organizer VARCHAR(255) NULL,
			location VARCHAR(255),
			price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			capacity INT NOT NULL DEFAULT 1,
			booking_open DATETIME NULL,
			booking_close DATETIME NULL,
			tags TEXT,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$wpdb->prefix}kab_bookings (
			id INT NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			employee_id INT,
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

        $tables[] = "CREATE TABLE {$wpdb->prefix}kab_employees (
            id INT NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(191) NOT NULL,
            phone VARCHAR(50),
            location VARCHAR(255),
            wp_user_id BIGINT UNSIGNED,
            timezone VARCHAR(100),
            photo_url TEXT,
            badge VARCHAR(100),
            description TEXT,
            internal_note TEXT,
            status VARCHAR(20) NOT NULL DEFAULT 'available',
            show_on_site TINYINT(1) NOT NULL DEFAULT 0,
            google_access_token TEXT,
            google_refresh_token TEXT,
            google_calendar_id VARCHAR(255),
            google_token_expires DATETIME,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}kab_employee_services (
            employee_id INT NOT NULL,
            service_id INT NOT NULL,
            price DECIMAL(10,2) DEFAULT NULL,
            capacity INT DEFAULT NULL,
            PRIMARY KEY (employee_id, service_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}kab_employee_workhours (
            id INT NOT NULL AUTO_INCREMENT,
            employee_id INT NOT NULL,
            weekday TINYINT NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            PRIMARY KEY (id),
            KEY employee_id (employee_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}kab_employee_daysoff (
            id INT NOT NULL AUTO_INCREMENT,
            employee_id INT NOT NULL,
            day_off DATE NOT NULL,
            reason VARCHAR(255),
            PRIMARY KEY (id),
            KEY employee_id (employee_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}kab_employee_specialdays (
            id INT NOT NULL AUTO_INCREMENT,
            employee_id INT NOT NULL,
            special_date DATE NOT NULL,
            start_time TIME,
            end_time TIME,
            services TEXT,
            PRIMARY KEY (id),
            KEY employee_id (employee_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}kab_custom_fields (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(64) NOT NULL,
            label VARCHAR(255) NOT NULL,
            type VARCHAR(20) NOT NULL,
            options TEXT,
            required TINYINT(1) NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}kab_booking_meta (
            id INT NOT NULL AUTO_INCREMENT,
            booking_id INT NOT NULL,
            field_id INT NOT NULL,
            value TEXT,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY field_id (field_id)
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
            currency VARCHAR(3) NOT NULL DEFAULT 'USD',
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
