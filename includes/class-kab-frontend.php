<?php
/**
 * Kura-ai Booking System Frontend
 *
 * Handles shortcodes, booking forms, and ticket viewer.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Frontend {
	public function __construct() {
		add_shortcode( 'kuraai_booking_form', array( $this, 'render_booking_form_shortcode' ) );
		add_shortcode( 'kuraai_events_list', array( $this, 'render_events_list_shortcode' ) );
		add_shortcode( 'kuraai_ticket', array( $this, 'render_ticket_shortcode' ) );
		add_shortcode( 'kuraai_customer_dashboard', array( $this, 'render_customer_dashboard_shortcode' ) );
	}

	public function render_booking_form_shortcode( $atts ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/booking-form.php';
		return ob_get_clean();
	}

	public function render_events_list_shortcode( $atts ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/events-list.php';
		return ob_get_clean();
	}

	public function render_ticket_shortcode( $atts ) {
		$ticket_id = isset( $atts['ticket_id'] ) ? sanitize_text_field( $atts['ticket_id'] ) : '';
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/single-ticket.php';
		return ob_get_clean();
	}

	public function render_customer_dashboard_shortcode( $atts ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/customer-dashboard.php';
		return ob_get_clean();
	}
}
