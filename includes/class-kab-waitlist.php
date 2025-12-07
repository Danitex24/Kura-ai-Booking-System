<?php
/**
 * Kura-ai Booking System - Waitlist Management
 *
 * Handles event/service capacity and waitlist functionality.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Waitlist {

	/**
	 * Check if event/service is at capacity
	 *
	 * @param string $type Type: 'event' or 'service'
	 * @param int    $item_id Event or Service ID
	 * @param string $date Booking date (for events)
	 * @return bool True if at capacity
	 */
	public static function is_at_capacity( $type, $item_id, $date = null ) {
		global $wpdb;

		// Get capacity limit
		$table = $type === 'event' ? 'kab_events' : 'kab_services';
		$capacity = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT capacity FROM {$wpdb->prefix}{$table} WHERE id = %d",
				$item_id
			)
		);

		if ( ! $capacity ) {
			return false; // No capacity limit
		}

		// Count confirmed bookings
		$where_clause = $type === 'event' ? 'event_id = %d' : 'service_id = %d';

		if ( $date && $type === 'event' ) {
			$booking_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings
					WHERE {$where_clause}
					AND booking_date = %s
					AND status IN ('confirmed', 'pending')",
					$item_id,
					$date
				)
			);
		} else {
			$booking_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings
					WHERE {$where_clause}
					AND status IN ('confirmed', 'pending')",
					$item_id
				)
			);
		}

		return intval( $booking_count ) >= intval( $capacity );
	}

	/**
	 * Add customer to waitlist
	 *
	 * @param array $data Waitlist entry data
	 * @return int|false Waitlist ID on success, false on failure
	 */
	public static function add_to_waitlist( $data ) {
		global $wpdb;

		try {
			// Check if already on waitlist
			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}kab_waitlist
					WHERE item_type = %s
					AND item_id = %d
					AND customer_email = %s
					AND status = 'active'",
					sanitize_text_field( $data['item_type'] ),
					intval( $data['item_id'] ),
					sanitize_email( $data['customer_email'] )
				)
			);

			if ( $existing ) {
				return false; // Already on waitlist
			}

			// Add to waitlist
			$result = $wpdb->insert(
				$wpdb->prefix . 'kab_waitlist',
				array(
					'item_type'      => sanitize_text_field( $data['item_type'] ), // 'event' or 'service'
					'item_id'        => intval( $data['item_id'] ),
					'booking_date'   => isset( $data['booking_date'] ) ? sanitize_text_field( $data['booking_date'] ) : null,
					'customer_name'  => sanitize_text_field( $data['customer_name'] ),
					'customer_email' => sanitize_email( $data['customer_email'] ),
					'customer_phone' => isset( $data['customer_phone'] ) ? sanitize_text_field( $data['customer_phone'] ) : null,
					'status'         => 'active',
					'priority'       => self::get_next_priority( $data['item_type'], $data['item_id'] ),
				),
				array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d' )
			);

			if ( $result ) {
				// Send waitlist confirmation email
				self::send_waitlist_confirmation( $wpdb->insert_id );
				return $wpdb->insert_id;
			}

			return false;

		} catch ( Exception $e ) {
			error_log( 'KAB Waitlist Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get next priority number for waitlist
	 *
	 * @param string $type Item type
	 * @param int    $item_id Item ID
	 * @return int Next priority number
	 */
	private static function get_next_priority( $type, $item_id ) {
		global $wpdb;

		$max_priority = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(priority) FROM {$wpdb->prefix}kab_waitlist
				WHERE item_type = %s AND item_id = %d",
				$type,
				$item_id
			)
		);

		return intval( $max_priority ) + 1;
	}

	/**
	 * Notify next person on waitlist when spot becomes available
	 *
	 * @param string $type Item type
	 * @param int    $item_id Item ID
	 * @param string $date Booking date (optional)
	 * @return bool Success status
	 */
	public static function notify_next_in_line( $type, $item_id, $date = null ) {
		global $wpdb;

		// Get next person on waitlist
		$where = "item_type = %s AND item_id = %d AND status = 'active'";
		$params = array( $type, $item_id );

		if ( $date ) {
			$where .= " AND (booking_date = %s OR booking_date IS NULL)";
			$params[] = $date;
		}

		$waitlist_entry = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_waitlist
				WHERE {$where}
				ORDER BY priority ASC, created_at ASC
				LIMIT 1",
				$params
			),
			ARRAY_A
		);

		if ( ! $waitlist_entry ) {
			return false; // No one on waitlist
		}

		// Send notification email
		if ( self::send_spot_available_email( $waitlist_entry ) ) {
			// Update waitlist status
			$wpdb->update(
				$wpdb->prefix . 'kab_waitlist',
				array( 'status' => 'notified', 'notified_at' => current_time( 'mysql' ) ),
				array( 'id' => $waitlist_entry['id'] ),
				array( '%s', '%s' ),
				array( '%d' )
			);

			return true;
		}

		return false;
	}

	/**
	 * Send waitlist confirmation email
	 *
	 * @param int $waitlist_id Waitlist entry ID
	 * @return bool Success status
	 */
	private static function send_waitlist_confirmation( $waitlist_id ) {
		global $wpdb;

		$entry = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_waitlist WHERE id = %d",
				$waitlist_id
			),
			ARRAY_A
		);

		if ( ! $entry ) {
			return false;
		}

		// Get item details
		$table = $entry['item_type'] === 'event' ? 'kab_events' : 'kab_services';
		$item = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT name FROM {$wpdb->prefix}{$table} WHERE id = %d",
				$entry['item_id']
			),
			ARRAY_A
		);

		$settings = get_option( 'kab_settings', array() );
		$company_name = isset( $settings['company_name'] ) ? $settings['company_name'] : get_bloginfo( 'name' );
		$primary_color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#E67E22';

		$subject = sprintf( __( 'You have been added to the waitlist - %s', 'kura-ai-booking-free' ), $company_name );

		$message = sprintf(
			__( 'Hi %s,\n\nYou have been successfully added to the waitlist for "%s".\n\nYou are currently #%d in line. We will notify you by email if a spot becomes available.\n\nThank you for your patience!\n\n%s', 'kura-ai-booking-free' ),
			$entry['customer_name'],
			$item['name'],
			$entry['priority'],
			$company_name
		);

		return wp_mail( $entry['customer_email'], $subject, $message );
	}

	/**
	 * Send spot available notification email
	 *
	 * @param array $entry Waitlist entry
	 * @return bool Success status
	 */
	private static function send_spot_available_email( $entry ) {
		// Get item details
		global $wpdb;
		$table = $entry['item_type'] === 'event' ? 'kab_events' : 'kab_services';
		$item = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}{$table} WHERE id = %d",
				$entry['item_id']
			),
			ARRAY_A
		);

		$settings = get_option( 'kab_settings', array() );
		$company_name = isset( $settings['company_name'] ) ? $settings['company_name'] : get_bloginfo( 'name' );
		$primary_color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#E67E22';

		$subject = sprintf( __( 'Good news! A spot is now available - %s', 'kura-ai-booking-free' ), $company_name );

		// Build booking URL (you can customize this)
		$booking_url = home_url( '/booking/' ); // Adjust as needed

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 20px auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; }
				.header { background-color: <?php echo esc_attr( $primary_color ); ?>; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
				.content { padding: 30px; }
				.btn { display: inline-block; padding: 12px 24px; background-color: <?php echo esc_attr( $primary_color ); ?>; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php echo esc_html__( 'A Spot is Now Available!', 'kura-ai-booking-free' ); ?></h1>
				</div>
				<div class="content">
					<p><?php echo esc_html( sprintf( __( 'Hi %s,', 'kura-ai-booking-free' ), $entry['customer_name'] ) ); ?></p>
					<p><?php echo esc_html( sprintf( __( 'Great news! A spot has become available for "%s".', 'kura-ai-booking-free' ), $item['name'] ) ); ?></p>
					<p><?php echo esc_html__( 'Please book soon as spots are limited and available on a first-come, first-served basis.', 'kura-ai-booking-free' ); ?></p>
					<a href="<?php echo esc_url( $booking_url ); ?>" class="btn"><?php echo esc_html__( 'Book Now', 'kura-ai-booking-free' ); ?></a>
					<p style="margin-top: 20px;"><?php echo esc_html__( 'If you no longer wish to book, no action is needed.', 'kura-ai-booking-free' ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		$message = ob_get_clean();

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		return wp_mail( $entry['customer_email'], $subject, $message, $headers );
	}

	/**
	 * Remove from waitlist
	 *
	 * @param int $waitlist_id Waitlist entry ID
	 * @return bool Success status
	 */
	public static function remove_from_waitlist( $waitlist_id ) {
		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . 'kab_waitlist',
			array( 'status' => 'removed' ),
			array( 'id' => intval( $waitlist_id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Get waitlist for an item
	 *
	 * @param string $type Item type
	 * @param int    $item_id Item ID
	 * @return array Waitlist entries
	 */
	public static function get_waitlist( $type, $item_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_waitlist
				WHERE item_type = %s AND item_id = %d AND status IN ('active', 'notified')
				ORDER BY priority ASC, created_at ASC",
				$type,
				$item_id
			),
			ARRAY_A
		);
	}
}
