<?php
/**
 * Admin Menu Handler
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Admin_Menu {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menus' ), 20 );
	}

	public static function register_menus() {
		// Add submenu items for new features
		add_submenu_page(
			'kab-dashboard',
			__( 'Recurring Events', 'kura-ai-booking-free' ),
			__( 'Recurring Events', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-recurring-events',
			array( __CLASS__, 'recurring_events_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Email Reminders', 'kura-ai-booking-free' ),
			__( 'Email Reminders', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-reminders',
			array( __CLASS__, 'reminders_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Waitlist', 'kura-ai-booking-free' ),
			__( 'Waitlist', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-waitlist',
			array( __CLASS__, 'waitlist_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Cancellations & Refunds', 'kura-ai-booking-free' ),
			__( 'Cancellations', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-cancellations',
			array( __CLASS__, 'cancellations_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Reviews & Ratings', 'kura-ai-booking-free' ),
			__( 'Reviews', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-reviews',
			array( __CLASS__, 'reviews_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Event Categories', 'kura-ai-booking-free' ),
			__( 'Event Categories', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-event-categories',
			array( __CLASS__, 'event_categories_page' )
		);
	}

	public static function recurring_events_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/admin/pages/recurring-events.php';
	}

	public static function reminders_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/admin/pages/reminders.php';
	}

	public static function waitlist_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/admin/pages/waitlist.php';
	}

	public static function cancellations_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/admin/pages/cancellations.php';
	}

	public static function reviews_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/admin/pages/reviews.php';
	}

	public static function event_categories_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/admin/pages/event-categories.php';
	}
}

KAB_Admin_Menu::init();
