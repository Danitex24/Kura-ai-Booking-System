<?php
/**
 * Kura-ai Booking System - Admin Dashboard
 *
 * Handles admin menus, pages, and settings.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kura-ai Booking System Admin Class
 */
class KAB_Admin {

	/**
	 * Initialize admin hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
	}

	/**
	 * Add admin menus
	 */
	public function add_admin_menus() {
		add_menu_page(
			__( 'Kura-ai Booking', 'kura-ai-booking-free' ),
			__( 'Kura-ai Booking', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-calendar-alt',
			25
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Services', 'kura-ai-booking-free' ),
			__( 'Services', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-services',
			array( $this, 'render_services_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Events', 'kura-ai-booking-free' ),
			__( 'Events', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-events',
			array( $this, 'render_events_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Customers', 'kura-ai-booking-free' ),
			__( 'Customers', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-customers',
			array( $this, 'render_customers_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Settings', 'kura-ai-booking-free' ),
			__( 'Settings', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-settings',
			array( $this, 'render_settings_page' )
		);

		// Add validation panel as a submenu page
		add_submenu_page(
			'kab-dashboard',
			__( 'Ticket Validation', 'kura-ai-booking-free' ),
			__( 'Ticket Validation', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-validation',
			array( $this, 'render_validation_page' )
		);
	}

	/**
	 * Render dashboard page
	 */
	public function render_dashboard_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Kura-ai Booking Dashboard', 'kura-ai-booking-free' ) . '</h1>';
		echo '<p>' . esc_html__( 'Welcome to your booking system dashboard.', 'kura-ai-booking-free' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render services page
	 */
	public function render_services_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
		$services_model = new KAB_Services();

		// Handle service deletion
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['service_id'] ) && isset( $_GET['_wpnonce'] ) ) {
			$service_id = intval( $_GET['service_id'] );
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'kab_delete_service_' . $service_id ) ) {
				$services_model->delete_service( $service_id );
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Service deleted successfully.', 'kura-ai-booking-free' ) . '</p></div>';
			}
		}

		// Handle service form submissions
		if ( isset( $_POST['kab_add_service_nonce'] ) && wp_verify_nonce( $_POST['kab_add_service_nonce'], 'kab_add_service' ) ) {
			$service_data = array(
				'name'        => sanitize_text_field( $_POST['name'] ),
				'description' => sanitize_textarea_field( $_POST['description'] ),
				'duration'    => intval( $_POST['duration'] ),
				'price'       => floatval( $_POST['price'] ),
			);
			$service_id   = $services_model->create_service( $service_data );
			if ( $service_id ) {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Service added successfully.', 'kura-ai-booking-free' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Error adding service. Please try again.', 'kura-ai-booking-free' ) . '</p></div>';
			}
		}

		if ( isset( $_POST['kab_edit_service_nonce'] ) && wp_verify_nonce( $_POST['kab_edit_service_nonce'], 'kab_edit_service' ) ) {
			$service_id   = intval( $_POST['service_id'] );
			$service_data = array(
				'name'        => sanitize_text_field( $_POST['name'] ),
				'description' => sanitize_textarea_field( $_POST['description'] ),
				'duration'    => intval( $_POST['duration'] ),
				'price'       => floatval( $_POST['price'] ),
			);
			$result       = $services_model->update_service( $service_id, $service_data );
			if ( $result ) {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Service updated successfully.', 'kura-ai-booking-free' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Error updating service. Please try again.', 'kura-ai-booking-free' ) . '</p></div>';
			}
		}

		// Handle service editing
		if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['service_id'] ) ) {
			$service_id = intval( $_GET['service_id'] );
			$service    = $services_model->get_service( $service_id );

			if ( $service ) {
				// Edit Service Form
				echo '<h2>' . esc_html__( 'Edit Service', 'kura-ai-booking-free' ) . '</h2>';
				echo '<form method="post">';
				echo '<input type="hidden" name="kab_edit_service_nonce" value="' . wp_create_nonce( 'kab_edit_service' ) . '" />';
				echo '<input type="hidden" name="service_id" value="' . esc_attr( $service['id'] ) . '" />';
				echo '<p><label>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="name" value="' . esc_attr( $service['name'] ) . '" required></p>';
				echo '<p><label>' . esc_html__( 'Description', 'kura-ai-booking-free' ) . '</label><br><textarea name="description" required>' . esc_textarea( $service['description'] ) . '</textarea></p>';
				echo '<p><label>' . esc_html__( 'Duration (minutes)', 'kura-ai-booking-free' ) . '</label><br><input type="number" name="duration" value="' . esc_attr( $service['duration'] ) . '" required></p>';
				echo '<p><label>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</label><br><input type="number" step="0.01" name="price" value="' . esc_attr( $service['price'] ) . '" required></p>';
				echo '<p><input type="submit" class="button-primary" value="' . esc_attr__( 'Save Changes', 'kura-ai-booking-free' ) . '"></p></form>';
			}
		} else {
			// Add Service Form
			echo '<h2>' . esc_html__( 'Add New Service', 'kura-ai-booking-free' ) . '</h2>';
			echo '<form method="post">';
			echo '<input type="hidden" name="kab_add_service_nonce" value="' . wp_create_nonce( 'kab_add_service' ) . '" />';
			echo '<p><label>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="name" required></p>';
			echo '<p><label>' . esc_html__( 'Description', 'kura-ai-booking-free' ) . '</label><br><textarea name="description" required></textarea></p>';
			echo '<p><label>' . esc_html__( 'Duration (minutes)', 'kura-ai-booking-free' ) . '</label><br><input type="number" name="duration" required></p>';
			echo '<p><label>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</label><br><input type="number" step="0.01" name="price" required></p>';
			echo '<p><input type="submit" class="button-primary" value="' . esc_attr__( 'Add Service', 'kura-ai-booking-free' ) . '"></p></form>';
		}

		// Services Table
		$services = $services_model->get_services();
		echo '<h2>' . esc_html__( 'Services List', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Duration', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'kura-ai-booking-free' ) . '</th>';
		echo '</tr></thead><tbody>';

		if ( $services ) {
			foreach ( $services as $service ) {
				$edit_url   = add_query_arg(
					array(
						'action'     => 'edit',
						'service_id' => $service['id'],
					),
					menu_page_url( 'kab-services', false )
				);
				$delete_url = wp_nonce_url(
					add_query_arg(
						array(
							'action'     => 'delete',
							'service_id' => $service['id'],
						),
						menu_page_url( 'kab-services', false )
					),
					'kab_delete_service_' . $service['id']
				);
				echo '<tr>';
				echo '<td>' . esc_html( $service['name'] ) . '</td>';
				echo '<td>' . esc_html( $service['duration'] ) . ' ' . esc_html__( 'minutes', 'kura-ai-booking-free' ) . '</td>';
				echo '<td>' . esc_html( number_format( $service['price'], 2 ) ) . '</td>';
				echo '<td>';
				echo '<a href="' . esc_url( $edit_url ) . '" class="button">' . esc_html__( 'Edit', 'kura-ai-booking-free' ) . '</a> ';
				echo '<a href="' . esc_url( $delete_url ) . '" class="button kab-delete-service" data-service-name="' . esc_attr( $service['name'] ) . '">' . esc_html__( 'Delete', 'kura-ai-booking-free' ) . '</a>';
				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="4">' . esc_html__( 'No services found.', 'kura-ai-booking-free' ) . '</td></tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Render events page
	 */
	public function render_events_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
		$events_model = new KAB_Events();

		// Handle event deletion
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['event_id'] ) && isset( $_GET['_wpnonce'] ) ) {
			$event_id = intval( $_GET['event_id'] );
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'kab_delete_event_' . $event_id ) ) {
				$events_model->delete_event( $event_id );
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Event deleted successfully.', 'kura-ai-booking-free' ) . '</p></div>';
			}
		}

		// Handle event form submissions
		if ( isset( $_POST['kab_add_event_nonce'] ) && wp_verify_nonce( $_POST['kab_add_event_nonce'], 'kab_add_event' ) ) {
			$event_data = array(
				'name'        => sanitize_text_field( $_POST['name'] ),
				'description' => sanitize_textarea_field( $_POST['description'] ),
				'event_date'  => sanitize_text_field( $_POST['event_date'] ),
				'event_time'  => sanitize_text_field( $_POST['event_time'] ),
				'location'    => sanitize_text_field( $_POST['location'] ),
				'price'       => floatval( $_POST['price'] ),
				'capacity'    => intval( $_POST['capacity'] ),
			);
			$event_id   = $events_model->add_event( $event_data );
			if ( $event_id ) {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Event added successfully.', 'kura-ai-booking-free' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Error adding event. Please try again.', 'kura-ai-booking-free' ) . '</p></div>';
			}
		}

		if ( isset( $_POST['kab_edit_event_nonce'] ) && wp_verify_nonce( $_POST['kab_edit_event_nonce'], 'kab_edit_event' ) ) {
			$event_id   = intval( $_POST['event_id'] );
			$event_data = array(
				'name'        => sanitize_text_field( $_POST['name'] ),
				'description' => sanitize_textarea_field( $_POST['description'] ),
				'event_date'  => sanitize_text_field( $_POST['event_date'] ),
				'event_time'  => sanitize_text_field( $_POST['event_time'] ),
				'location'    => sanitize_text_field( $_POST['location'] ),
				'price'       => floatval( $_POST['price'] ),
				'capacity'    => intval( $_POST['capacity'] ),
			);
			$result     = $events_model->update_event( $event_id, $event_data );
			if ( false !== $result ) {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Event updated successfully.', 'kura-ai-booking-free' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Error updating event. Please try again.', 'kura-ai-booking-free' ) . '</p></div>';
			}
		}

		// Handle event editing
		if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['event_id'] ) ) {
			$event_id = intval( $_GET['event_id'] );
			$event    = $events_model->get_event( $event_id );

			if ( $event ) {
				// Edit Event Form
				echo '<h2>' . esc_html__( 'Edit Event', 'kura-ai-booking-free' ) . '</h2>';
				echo '<form method="post">';
				echo '<input type="hidden" name="kab_edit_event_nonce" value="' . wp_create_nonce( 'kab_edit_event' ) . '" />';
				echo '<input type="hidden" name="event_id" value="' . esc_attr( $event['id'] ) . '" />';
				echo '<p><label>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="name" value="' . esc_attr( $event['name'] ) . '" required></p>';
				echo '<p><label>' . esc_html__( 'Description', 'kura-ai-booking-free' ) . '</label><br><textarea name="description" required>' . esc_textarea( $event['description'] ) . '</textarea></p>';
				echo '<p><label>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</label><br><input type="date" name="event_date" value="' . esc_attr( $event['event_date'] ) . '" required></p>';
				echo '<p><label>' . esc_html__( 'Time', 'kura-ai-booking-free' ) . '</label><br><input type="time" name="event_time" value="' . esc_attr( $event['event_time'] ) . '" required></p>';
				echo '<p><label>' . esc_html__( 'Location', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="location" value="' . esc_attr( $event['location'] ) . '" required></p>';
				echo '<p><label>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</label><br><input type="number" step="0.01" name="price" value="' . esc_attr( $event['price'] ) . '" required></p>';
				echo '<p><label>' . esc_html__( 'Capacity', 'kura-ai-booking-free' ) . '</label><br><input type="number" name="capacity" value="' . esc_attr( $event['capacity'] ) . '" required></p>';
				echo '<p><input type="submit" class="button-primary" value="' . esc_attr__( 'Save Changes', 'kura-ai-booking-free' ) . '"></p></form>';
			}
		} else {
			// Add Event Form
			echo '<h2>' . esc_html__( 'Add New Event', 'kura-ai-booking-free' ) . '</h2>';
			echo '<form method="post">';
			echo '<input type="hidden" name="kab_add_event_nonce" value="' . wp_create_nonce( 'kab_add_event' ) . '" />';
			echo '<p><label>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="name" required></p>';
			echo '<p><label>' . esc_html__( 'Description', 'kura-ai-booking-free' ) . '</label><br><textarea name="description" required></textarea></p>';
			echo '<p><label>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</label><br><input type="date" name="event_date" required></p>';
			echo '<p><label>' . esc_html__( 'Time', 'kura-ai-booking-free' ) . '</label><br><input type="time" name="event_time" required></p>';
			echo '<p><label>' . esc_html__( 'Location', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="location" required></p>';
			echo '<p><label>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</label><br><input type="number" step="0.01" name="price" required></p>';
			echo '<p><label>' . esc_html__( 'Capacity', 'kura-ai-booking-free' ) . '</label><br><input type="number" name="capacity" required></p>';
			echo '<p><input type="submit" class="button-primary" value="' . esc_attr__( 'Add Event', 'kura-ai-booking-free' ) . '"></p></form>';
		}

		// Events Table
		$events = $events_model->get_events();
		echo '<h2>' . esc_html__( 'Events List', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Time', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Location', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Capacity', 'kura-ai-booking-free' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'kura-ai-booking-free' ) . '</th>';
		echo '</tr></thead><tbody>';

		if ( $events ) {
			foreach ( $events as $event ) {
				$edit_url   = add_query_arg(
					array(
						'action'   => 'edit',
						'event_id' => $event['id'],
					),
					menu_page_url( 'kab-events', false )
				);
				$delete_url = wp_nonce_url(
					add_query_arg(
						array(
							'action'   => 'delete',
							'event_id' => $event['id'],
						),
						menu_page_url( 'kab-events', false )
					),
					'kab_delete_event_' . $event['id']
				);
				echo '<tr>';
				echo '<td>' . esc_html( $event['name'] ) . '</td>';
				echo '<td>' . esc_html( $event['event_date'] ) . '</td>';
				echo '<td>' . esc_html( $event['event_time'] ) . '</td>';
				echo '<td>' . esc_html( $event['location'] ) . '</td>';
				echo '<td>' . esc_html( number_format( $event['price'], 2 ) ) . '</td>';
				echo '<td>' . esc_html( $event['capacity'] ) . '</td>';
				echo '<td>';
				echo '<a href="' . esc_url( $edit_url ) . '" class="button">' . esc_html__( 'Edit', 'kura-ai-booking-free' ) . '</a> ';
				echo '<a href="' . esc_url( $delete_url ) . '" class="button kab-delete-event" data-event-name="' . esc_attr( $event['name'] ) . '">' . esc_html__( 'Delete', 'kura-ai-booking-free' ) . '</a>';
				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="7">' . esc_html__( 'No events found.', 'kura-ai-booking-free' ) . '</td></tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Render customers page
	 */
	public function render_customers_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Customers', 'kura-ai-booking-free' ) . '</h1></div>';
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		// Handle form submission
		if ( isset( $_POST['kab_settings_nonce'] ) && wp_verify_nonce( $_POST['kab_settings_nonce'], 'kab_save_settings' ) ) {
			$settings = array(
				'company_name'     => sanitize_text_field( $_POST['company_name'] ),
				'company_logo'     => esc_url_raw( $_POST['company_logo'] ),
				'support_email'    => sanitize_email( $_POST['support_email'] ),
				'enable_tickets'   => isset( $_POST['enable_tickets'] ) ? 'yes' : 'no',
				'email_from_name'  => sanitize_text_field( $_POST['email_from_name'] ),
				'email_from_email' => sanitize_email( $_POST['email_from_email'] ),
			);
			update_option( 'kab_settings', $settings );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully.', 'kura-ai-booking-free' ) . '</p></div>';
		}

		// Get current settings
		$settings = get_option(
			'kab_settings',
			array(
				'company_name'     => get_bloginfo( 'name' ),
				'company_logo'     => '',
				'support_email'    => get_option( 'admin_email' ),
				'enable_tickets'   => 'yes',
				'email_from_name'  => get_bloginfo( 'name' ),
				'email_from_email' => get_option( 'admin_email' ),
			)
		);

		echo '<div class="wrap"><h1>' . esc_html__( 'Kura-ai Booking Settings', 'kura-ai-booking-free' ) . '</h1>';
		echo '<form method="post">';
		wp_nonce_field( 'kab_save_settings', 'kab_settings_nonce' );

		// Company Information
		echo '<h2>' . esc_html__( 'Company Information', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="company_name">' . esc_html__( 'Company Name', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="text" name="company_name" id="company_name" value="' . esc_attr( $settings['company_name'] ) . '" class="regular-text"></td></tr>';
		echo '<tr><th scope="row"><label for="company_logo">' . esc_html__( 'Company Logo URL', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="url" name="company_logo" id="company_logo" value="' . esc_attr( $settings['company_logo'] ) . '" class="regular-text"><p class="description">' . esc_html__( 'Full URL to your company logo image', 'kura-ai-booking-free' ) . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="support_email">' . esc_html__( 'Support Email', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="email" name="support_email" id="support_email" value="' . esc_attr( $settings['support_email'] ) . '" class="regular-text"></td></tr>';
		echo '</table>';

		// Email Settings
		echo '<h2>' . esc_html__( 'Email Settings', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="email_from_name">' . esc_html__( 'Email From Name', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="text" name="email_from_name" id="email_from_name" value="' . esc_attr( $settings['email_from_name'] ) . '" class="regular-text"></td></tr>';
		echo '<tr><th scope="row"><label for="email_from_email">' . esc_html__( 'Email From Address', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="email" name="email_from_email" id="email_from_email" value="' . esc_attr( $settings['email_from_email'] ) . '" class="regular-text"></td></tr>';
		echo '</table>';

		// Ticket Settings
		echo '<h2>' . esc_html__( 'Ticket Settings', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="enable_tickets">' . esc_html__( 'Enable E-Tickets', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="checkbox" name="enable_tickets" id="enable_tickets" value="1" ' . checked( $settings['enable_tickets'], 'yes', false ) . '> ' . esc_html__( 'Enable QR code e-ticket generation', 'kura-ai-booking-free' ) . '</td></tr>';
		echo '</table>';

		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . esc_attr__( 'Save Settings', 'kura-ai-booking-free' ) . '"></p>';
		echo '</form></div>';
	}

	/**
	 * Render ticket validation page
	 */
	public function render_validation_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Ticket Validation Panel', 'kura-ai-booking-free' ) . '</h1>';

		// Handle ticket validation form submission
		if ( isset( $_POST['kab_validate_ticket_nonce'] ) && wp_verify_nonce( $_POST['kab_validate_ticket_nonce'], 'kab_validate_ticket' ) ) {
			$ticket_id = sanitize_text_field( $_POST['ticket_id'] );

			// Validate ticket using REST API endpoint
			$validation_result = $this->validate_ticket( $ticket_id );

			if ( $validation_result['valid'] ) {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'VALID TICKET', 'kura-ai-booking-free' ) . '</p></div>';
				echo '<div class="ticket-details">';
				echo '<h3>' . esc_html__( 'Ticket Details', 'kura-ai-booking-free' ) . '</h3>';
				echo '<p><strong>' . esc_html__( 'Ticket ID:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $ticket_id ) . '</p>';
				echo '<p><strong>' . esc_html__( 'Booking ID:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $validation_result['booking_id'] ) . '</p>';
				echo '<p><strong>' . esc_html__( 'Customer:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $validation_result['customer_name'] ) . '</p>';
				echo '<p><strong>' . esc_html__( 'Event/Service:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $validation_result['item_name'] ) . '</p>';
				echo '<p><strong>' . esc_html__( 'Date/Time:', 'kura-ai-booking-free' ) . '</strong> ' . esc_html( $validation_result['booking_date'] ) . ' ' . esc_html( $validation_result['booking_time'] ) . '</p>';
				echo '</div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'INVALID TICKET', 'kura-ai-booking-free' ) . '</p></div>';
			}
		}

		// Display validation form
		echo '<form method="post" style="margin: 20px 0;">';
		wp_nonce_field( 'kab_validate_ticket', 'kab_validate_ticket_nonce' );
		echo '<h3>' . esc_html__( 'Validate Ticket', 'kura-ai-booking-free' ) . '</h3>';
		echo '<p><input type="text" name="ticket_id" placeholder="' . esc_attr__( 'Enter Ticket ID', 'kura-ai-booking-free' ) . '" style="width: 300px; padding: 8px;" required></p>';
		echo '<p><input type="submit" class="button button-primary" value="' . esc_attr__( 'Validate Ticket', 'kura-ai-booking-free' ) . '"></p>';
		echo '</form>';

		echo '</div>';
	}

	/**
	 * Validate ticket using internal REST API call
	 *
	 * @param string $ticket_id Ticket ID to validate
	 * @return array Validation result with ticket details
	 */
	private function validate_ticket( $ticket_id ) {
		// Use the REST API endpoint internally
		require_once KAB_FREE_PLUGIN_DIR . 'includes/rest/class-kab-rest-controller.php';
		$rest_controller = new KAB_REST_Controller();

		// Call the validation method directly
		$result = $rest_controller->validate_ticket( array( 'ticket_id' => $ticket_id ) );

		if ( is_wp_error( $result ) ) {
			return array( 'valid' => false );
		}

		return $result;
	}
}
