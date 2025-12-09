<?php
/**
 * Kura-ai Booking System - Recurring Events
 *
 * Handles recurring event patterns and instance generation.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Recurring_Events {

	/**
	 * Create recurring event pattern
	 *
	 * @param int   $event_id Parent event ID
	 * @param array $recurrence_data Recurrence settings
	 * @return bool Success status
	 */
	public static function create_recurrence( $event_id, $recurrence_data ) {
		global $wpdb;

		try {
			// Validate recurrence data
			if ( empty( $recurrence_data['frequency'] ) || empty( $recurrence_data['start_date'] ) ) {
				return false;
			}

			// Insert recurrence pattern
			$result = $wpdb->insert(
				$wpdb->prefix . 'kab_event_recurrence',
				array(
					'event_id'            => intval( $event_id ),
					'frequency'           => sanitize_text_field( $recurrence_data['frequency'] ), // daily, weekly, monthly
					'recurrence_interval' => isset( $recurrence_data['interval'] ) ? intval( $recurrence_data['interval'] ) : 1,
					'start_date'          => sanitize_text_field( $recurrence_data['start_date'] ),
					'end_date'            => isset( $recurrence_data['end_date'] ) ? sanitize_text_field( $recurrence_data['end_date'] ) : null,
					'occurrences'         => isset( $recurrence_data['occurrences'] ) ? intval( $recurrence_data['occurrences'] ) : null,
					'days_of_week'        => isset( $recurrence_data['days_of_week'] ) ? sanitize_text_field( $recurrence_data['days_of_week'] ) : null,
					'day_of_month'        => isset( $recurrence_data['day_of_month'] ) ? intval( $recurrence_data['day_of_month'] ) : null,
				),
				array( '%d', '%s', '%d', '%s', '%s', '%d', '%s', '%d' )
			);

			if ( ! $result ) {
				return false;
			}

			$recurrence_id = $wpdb->insert_id;

			// Generate initial instances
			self::generate_instances( $recurrence_id );

			return true;

		} catch ( Exception $e ) {
			error_log( 'KAB Recurring Events Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Generate event instances from recurrence pattern
	 *
	 * @param int $recurrence_id Recurrence pattern ID
	 * @return int Number of instances generated
	 */
	public static function generate_instances( $recurrence_id ) {
		global $wpdb;

		$pattern = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_event_recurrence WHERE id = %d",
				$recurrence_id
			),
			ARRAY_A
		);

		if ( ! $pattern ) {
			return 0;
		}

		// Get parent event details
		$parent_event = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_events WHERE id = %d",
				$pattern['event_id']
			),
			ARRAY_A
		);

		if ( ! $parent_event ) {
			return 0;
		}

		$start_date = new DateTime( $pattern['start_date'] );
		$end_condition = $pattern['end_date'] ? new DateTime( $pattern['end_date'] ) : null;
		$max_occurrences = $pattern['occurrences'] ? intval( $pattern['occurrences'] ) : 100; // Limit to 100 instances
		$instances_generated = 0;

		$current_date = clone $start_date;

		while ( $instances_generated < $max_occurrences ) {
			// Check end date condition
			if ( $end_condition && $current_date > $end_condition ) {
				break;
			}

			// Check if instance should be created based on frequency rules
			if ( self::should_create_instance( $current_date, $pattern ) ) {
				// Create event instance
				$instance_date = $current_date->format( 'Y-m-d' );

				// Check if instance already exists
				$existing = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$wpdb->prefix}kab_event_instances
						WHERE recurrence_id = %d AND instance_date = %s",
						$recurrence_id,
						$instance_date
					)
				);

				if ( ! $existing ) {
					$wpdb->insert(
						$wpdb->prefix . 'kab_event_instances',
						array(
							'recurrence_id' => $recurrence_id,
							'event_id'      => $pattern['event_id'],
							'instance_date' => $instance_date,
							'status'        => 'active',
						),
						array( '%d', '%d', '%s', '%s' )
					);

					$instances_generated++;
				}
			}

			// Move to next date based on frequency
			$current_date = self::get_next_date( $current_date, $pattern );

			// Safety break to prevent infinite loops
			if ( $instances_generated > 500 ) {
				break;
			}
		}

		return $instances_generated;
	}

	/**
	 * Check if instance should be created based on recurrence rules
	 *
	 * @param DateTime $date Date to check
	 * @param array    $pattern Recurrence pattern
	 * @return bool True if instance should be created
	 */
	private static function should_create_instance( $date, $pattern ) {
		// For weekly recurrence, check days of week
		if ( $pattern['frequency'] === 'weekly' && ! empty( $pattern['days_of_week'] ) ) {
			$allowed_days = explode( ',', $pattern['days_of_week'] ); // e.g., "1,3,5" for Mon, Wed, Fri
			$current_day = $date->format( 'N' ); // 1 (Monday) to 7 (Sunday)

			return in_array( $current_day, $allowed_days );
		}

		// For monthly recurrence, check day of month
		if ( $pattern['frequency'] === 'monthly' && ! empty( $pattern['day_of_month'] ) ) {
			return $date->format( 'd' ) == $pattern['day_of_month'];
		}

		return true;
	}

	/**
	 * Get next date based on recurrence pattern
	 *
	 * @param DateTime $current_date Current date
	 * @param array    $pattern Recurrence pattern
	 * @return DateTime Next date
	 */
	private static function get_next_date( $current_date, $pattern ) {
		$next_date = clone $current_date;
		$interval = intval( $pattern['recurrence_interval'] );

		switch ( $pattern['frequency'] ) {
			case 'daily':
				$next_date->modify( "+{$interval} day" );
				break;

			case 'weekly':
				$next_date->modify( "+{$interval} week" );
				break;

			case 'monthly':
				$next_date->modify( "+{$interval} month" );
				break;

			default:
				$next_date->modify( '+1 day' );
				break;
		}

		return $next_date;
	}

	/**
	 * Get all event instances for a recurrence pattern
	 *
	 * @param int $recurrence_id Recurrence pattern ID
	 * @return array Event instances
	 */
	public static function get_instances( $recurrence_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_event_instances
				WHERE recurrence_id = %d AND status = 'active'
				ORDER BY instance_date ASC",
				$recurrence_id
			),
			ARRAY_A
		);
	}

	/**
	 * Delete all future instances of a recurring event
	 *
	 * @param int $recurrence_id Recurrence pattern ID
	 * @return bool Success status
	 */
	public static function delete_future_instances( $recurrence_id ) {
		global $wpdb;

		$today = current_time( 'Y-m-d' );

		return $wpdb->update(
			$wpdb->prefix . 'kab_event_instances',
			array( 'status' => 'deleted' ),
			array(
				'recurrence_id' => intval( $recurrence_id ),
			),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete a recurrence pattern and all its instances
	 *
	 * @param int $recurrence_id Recurrence pattern ID
	 * @return bool Success status
	 */
	public static function delete_recurrence( $recurrence_id ) {
		global $wpdb;

		// Delete all instances
		$wpdb->delete(
			$wpdb->prefix . 'kab_event_instances',
			array( 'recurrence_id' => intval( $recurrence_id ) ),
			array( '%d' )
		);

		// Delete recurrence pattern
		return $wpdb->delete(
			$wpdb->prefix . 'kab_event_recurrence',
			array( 'id' => intval( $recurrence_id ) ),
			array( '%d' )
		);
	}
}
