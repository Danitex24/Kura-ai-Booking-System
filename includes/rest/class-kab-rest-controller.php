<?php
/**
 * Kura-ai Booking System - REST API Controller
 *
 * Handles REST API endpoints for ticket validation and booking.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_REST_Controller {

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route(
			'kuraai/v1',
			'/validate-ticket/(?P<ticket_id>[\w-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'validate_ticket' ),
				'args'                => array(
					'ticket_id' => array(
						'required'          => true,
						'validate_callback' => array( $this, 'validate_ticket_id' ),
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'kuraai/v1',
			'/book-event',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'book_event' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'kuraai/v1',
			'/book-service',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'book_service' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Validate ticket ID format
	 *
	 * @param mixed           $param Parameter value
	 * @param WP_REST_Request $request Request object
	 * @param string          $key Parameter key
	 * @return bool True if valid, false otherwise
	 */
	public function validate_ticket_id( $param, $request, $key ) {
		return preg_match( '/^kab_[a-zA-Z0-9]+$/', $param ) === 1;
	}

	/**
	 * Validate ticket endpoint
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function validate_ticket( $request ) {
		$ticket_id = sanitize_text_field( $request['ticket_id'] );

		require_once plugin_dir_path( __FILE__ ) . '../class-kab-tickets.php';
		$is_valid = KAB_Tickets::validate_ticket( $ticket_id );

		return rest_ensure_response(
			array(
				'valid'     => $is_valid,
				'ticket_id' => $ticket_id,
				'message'   => $is_valid ? 'Ticket is valid' : 'Ticket not found or already used',
			)
		);
	}

	/**
	 * Book event endpoint
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function book_event( $request ) {
		$params = $request->get_json_params();

		// Validate required fields
		if ( empty( $params['event_id'] ) || empty( $params['booking_date'] ) || empty( $params['booking_time'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Missing required fields: event_id, booking_date, booking_time',
				),
				400
			);
		}

		// Prepare booking data
		$booking_data = array(
			'booking_type'   => 'event',
			'event_id'       => intval( $params['event_id'] ),
			'booking_date'   => sanitize_text_field( $params['booking_date'] ),
			'booking_time'   => sanitize_text_field( $params['booking_time'] ),
			'customer_name'  => isset( $params['customer_name'] ) ? sanitize_text_field( $params['customer_name'] ) : '',
			'customer_email' => isset( $params['customer_email'] ) ? sanitize_email( $params['customer_email'] ) : '',
		);

		require_once plugin_dir_path( __FILE__ ) . '../class-kab-bookings.php';
		$booking_id = KAB_Bookings::create_booking( $booking_data );

		if ( $booking_id ) {
			return rest_ensure_response(
				array(
					'success'    => true,
					'booking_id' => $booking_id,
					'message'    => 'Event booked successfully',
				)
			);
		} else {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Failed to book event. Please check availability.',
				),
				400
			);
		}
	}

	/**
	 * Book service endpoint
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function book_service( $request ) {
		$params = $request->get_json_params();

		// Validate required fields
		if ( empty( $params['service_id'] ) || empty( $params['booking_date'] ) || empty( $params['booking_time'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Missing required fields: service_id, booking_date, booking_time',
				),
				400
			);
		}

		// Prepare booking data
		$booking_data = array(
			'booking_type'   => 'service',
			'service_id'     => intval( $params['service_id'] ),
			'booking_date'   => sanitize_text_field( $params['booking_date'] ),
			'booking_time'   => sanitize_text_field( $params['booking_time'] ),
			'customer_name'  => isset( $params['customer_name'] ) ? sanitize_text_field( $params['customer_name'] ) : '',
			'customer_email' => isset( $params['customer_email'] ) ? sanitize_email( $params['customer_email'] ) : '',
		);

		require_once plugin_dir_path( __FILE__ ) . '../class-kab-bookings.php';
		$booking_id = KAB_Bookings::create_booking( $booking_data );

		if ( $booking_id ) {
			return rest_ensure_response(
				array(
					'success'    => true,
					'booking_id' => $booking_id,
					'message'    => 'Service booked successfully',
				)
			);
		} else {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Failed to book service. Please check availability.',
				),
				400
			);
		}
	}
}
