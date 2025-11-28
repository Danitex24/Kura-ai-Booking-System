<?php
/**
 * Kura-ai Booking System - Services Model
 *
 * Handles service CRUD and queries.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Services {

	/**
	 * Get all active services
	 *
	 * @return array List of services
	 */
	public function get_services() {
		global $wpdb;

		$services = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_services WHERE status = %s ORDER BY name ASC",
				'active'
			),
			ARRAY_A
		);

		return $services ? $services : array();
	}

	/**
	 * Get service by ID
	 *
	 * @param int $service_id Service ID
	 * @return array|false Service data or false if not found
	 */
	public function get_service( $service_id ) {
		global $wpdb;

		$service = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_services WHERE id = %d",
				$service_id
			),
			ARRAY_A
		);

		return $service ? $service : false;
	}

	/**
	 * Create a new service
	 *
	 * @param array $data Service data
	 * @return int|false Service ID or false on failure
	 */
	public function create_service( $data ) {
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'kab_services',
			array(
				'name'        => sanitize_text_field( $data['name'] ),
				'description' => sanitize_textarea_field( $data['description'] ),
				'duration'    => intval( $data['duration'] ),
				'price'       => floatval( $data['price'] ),
				'status'      => 'active',
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%f', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Update a service
	 *
	 * @param int   $service_id Service ID
	 * @param array $data Service data
	 * @return bool True on success, false on failure
	 */
	public function update_service( $service_id, $data ) {
		global $wpdb;

		$update_data = array();
		$format      = array();

		if ( isset( $data['name'] ) ) {
			$update_data['name'] = sanitize_text_field( $data['name'] );
			$format[]            = '%s';
		}

		if ( isset( $data['description'] ) ) {
			$update_data['description'] = sanitize_textarea_field( $data['description'] );
			$format[]                   = '%s';
		}

		if ( isset( $data['duration'] ) ) {
			$update_data['duration'] = intval( $data['duration'] );
			$format[]                = '%d';
		}

		if ( isset( $data['price'] ) ) {
			$update_data['price'] = floatval( $data['price'] );
			$format[]             = '%f';
		}

		if ( isset( $data['status'] ) ) {
			$update_data['status'] = sanitize_text_field( $data['status'] );
			$format[]              = '%s';
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		$result = $wpdb->update(
			$wpdb->prefix . 'kab_services',
			$update_data,
			array( 'id' => $service_id ),
			$format,
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Delete a service
	 *
	 * @param int $service_id Service ID
	 * @return bool True on success, false on failure
	 */
	public function delete_service( $service_id ) {
		return $this->update_service( $service_id, array( 'status' => 'deleted' ) );
	}

	/**
	 * Check if service is available for booking
	 *
	 * @param int    $service_id Service ID
	 * @param string $date Booking date
	 * @param string $time Booking time
	 * @return bool True if available, false if not
	 */
	public function is_service_available( $service_id, $date, $time ) {
		global $wpdb;

		// Check if service exists and is active
		$service = $this->get_service( $service_id );
		if ( ! $service || $service['status'] !== 'active' ) {
			return false;
		}

		// Check for existing bookings at the same time
		$existing_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings 
			WHERE service_id = %d AND booking_date = %s AND booking_time = %s AND status != 'cancelled'",
				$service_id,
				$date,
				$time
			)
		);

		return $existing_bookings == 0;
	}
}
