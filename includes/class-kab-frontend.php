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
		
		// Register AJAX handlers
		add_action( 'wp_ajax_kab_book_appointment', array( $this, 'book_appointment' ) );
		add_action( 'wp_ajax_nopriv_kab_book_appointment', array( $this, 'book_appointment' ) );
		add_action( 'wp_ajax_kab_cancel_booking', array( $this, 'cancel_booking' ) );
		add_action( 'wp_ajax_nopriv_kab_cancel_booking', array( $this, 'cancel_booking' ) );
		
		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
	
	/**
	 * Enqueue frontend scripts and styles
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'kab-frontend', KAB_FREE_PLUGIN_URL . 'assets/css/frontend.css', array(), KAB_VERSION );
		wp_enqueue_script( 'kab-frontend', KAB_FREE_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery' ), KAB_VERSION, true );
		
		wp_localize_script( 'kab-frontend', 'kab_frontend', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'kab_booking_nonce' ),
			'i18n' => array(
				'booking_success' => __( 'Booking successful!', 'kura-ai-booking-free' ),
				'booking_error' => __( 'Error booking appointment.', 'kura-ai-booking-free' ),
				'cancel_confirm' => __( 'Are you sure you want to cancel this booking?', 'kura-ai-booking-free' )
			)
		));
	}
	
	/**
	 * Handle appointment booking via AJAX
	 */
	public function book_appointment() {
		check_ajax_referer( 'kab_booking_nonce', 'nonce' );
		
		// Sanitize and validate input data
		$booking_data = array(
			'booking_type' => sanitize_text_field( $_POST['booking_type'] ),
			'booking_date' => sanitize_text_field( $_POST['booking_date'] ),
			'booking_time' => sanitize_text_field( $_POST['booking_time'] ),
			'customer_name' => sanitize_text_field( $_POST['customer_name'] ),
			'customer_email' => sanitize_email( $_POST['customer_email'] ),
			'customer_phone' => sanitize_text_field( $_POST['customer_phone'] ),
			'service_id' => isset( $_POST['service_id'] ) ? intval( $_POST['service_id'] ) : 0,
			'event_id' => isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0
		);
		
		// Validate required fields
		if ( empty( $booking_data['booking_type'] ) || empty( $booking_data['booking_date'] ) || 
			 empty( $booking_data['booking_time'] ) || empty( $booking_data['customer_email'] ) ) {
			wp_send_json_error( __( 'Please fill all required fields.', 'kura-ai-booking-free' ) );
		}
		
		try {
			// Create booking using the bookings class
			$booking_id = KAB_Bookings::create_booking( $booking_data );
			
			if ( $booking_id ) {
				wp_send_json_success( array(
					'message' => __( 'Booking successful!', 'kura-ai-booking-free' ),
					'booking_id' => $booking_id
				));
			} else {
				wp_send_json_error( __( 'Sorry, the selected time slot is not available.', 'kura-ai-booking-free' ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Error booking appointment. Please try again.', 'kura-ai-booking-free' ) );
		}
	}
	
	/**
	 * Handle booking cancellation via AJAX
	 */
	public function cancel_booking() {
		check_ajax_referer( 'kab_booking_nonce', 'nonce' );
		
		$booking_id = intval( $_POST['booking_id'] );
		$user_id = get_current_user_id();
		
		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID.', 'kura-ai-booking-free' ) );
		}
		
		try {
			$result = KAB_Bookings::cancel_booking( $booking_id, $user_id );
			
			if ( $result ) {
				wp_send_json_success( __( 'Booking cancelled successfully.', 'kura-ai-booking-free' ) );
			} else {
				wp_send_json_error( __( 'Unable to cancel booking. It may have already been processed.', 'kura-ai-booking-free' ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Error cancelling booking. Please try again.', 'kura-ai-booking-free' ) );
		}
	}
}
