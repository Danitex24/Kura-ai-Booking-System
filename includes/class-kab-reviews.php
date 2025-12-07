<?php
/**
 * Kura-ai Booking System - Reviews & Ratings
 *
 * Handles customer reviews and ratings for services/events.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Reviews {

	/**
	 * Submit a review
	 *
	 * @param array $data Review data
	 * @return int|false Review ID on success, false on failure
	 */
	public static function submit_review( $data ) {
		global $wpdb;

		try {
			// Validate required fields
			if ( empty( $data['booking_id'] ) || empty( $data['rating'] ) ) {
				return false;
			}

			// Check if booking exists and is completed
			$booking = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}kab_bookings WHERE id = %d",
					intval( $data['booking_id'] )
				),
				ARRAY_A
			);

			if ( ! $booking ) {
				return false;
			}

			// Check if already reviewed
			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}kab_reviews WHERE booking_id = %d",
					intval( $data['booking_id'] )
				)
			);

			if ( $existing ) {
				return false; // Already reviewed
			}

			// Insert review
			$result = $wpdb->insert(
				$wpdb->prefix . 'kab_reviews',
				array(
					'booking_id'     => intval( $data['booking_id'] ),
					'item_type'      => sanitize_text_field( $booking['booking_type'] ), // 'event' or 'service'
					'item_id'        => $booking['booking_type'] === 'event' ? intval( $booking['event_id'] ) : intval( $booking['service_id'] ),
					'customer_name'  => sanitize_text_field( $booking['customer_name'] ),
					'customer_email' => sanitize_email( $booking['customer_email'] ),
					'rating'         => intval( $data['rating'] ), // 1-5 stars
					'title'          => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : null,
					'comment'        => isset( $data['comment'] ) ? sanitize_textarea_field( $data['comment'] ) : null,
					'status'         => 'approved', // or 'pending' for moderation
				),
				array( '%d', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
			);

			if ( $result ) {
				// Update average rating for the item
				self::update_average_rating( $booking['booking_type'], $booking['booking_type'] === 'event' ? $booking['event_id'] : $booking['service_id'] );

				return $wpdb->insert_id;
			}

			return false;

		} catch ( Exception $e ) {
			error_log( 'KAB Reviews Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Update average rating for an item
	 *
	 * @param string $type Item type ('event' or 'service')
	 * @param int    $item_id Item ID
	 * @return bool Success status
	 */
	private static function update_average_rating( $type, $item_id ) {
		global $wpdb;

		// Calculate average rating
		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
				FROM {$wpdb->prefix}kab_reviews
				WHERE item_type = %s AND item_id = %d AND status = 'approved'",
				$type,
				$item_id
			),
			ARRAY_A
		);

		if ( ! $stats ) {
			return false;
		}

		$avg_rating = round( floatval( $stats['avg_rating'] ), 2 );
		$review_count = intval( $stats['review_count'] );

		// Update item table with average rating
		$table = $type === 'event' ? 'kab_events' : 'kab_services';

		return $wpdb->update(
			$wpdb->prefix . $table,
			array(
				'avg_rating' => $avg_rating,
				'review_count' => $review_count,
			),
			array( 'id' => $item_id ),
			array( '%f', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Get reviews for an item
	 *
	 * @param string $type Item type
	 * @param int    $item_id Item ID
	 * @param array  $args Query arguments
	 * @return array Reviews
	 */
	public static function get_reviews( $type, $item_id, $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status' => 'approved',
			'limit' => 10,
			'offset' => 0,
			'orderby' => 'created_at',
			'order' => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$query = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kab_reviews
			WHERE item_type = %s AND item_id = %d AND status = %s
			ORDER BY {$args['orderby']} {$args['order']}
			LIMIT %d OFFSET %d",
			$type,
			$item_id,
			$args['status'],
			$args['limit'],
			$args['offset']
		);

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Get average rating for an item
	 *
	 * @param string $type Item type
	 * @param int    $item_id Item ID
	 * @return array Rating statistics
	 */
	public static function get_rating_stats( $type, $item_id ) {
		global $wpdb;

		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					AVG(rating) as average,
					COUNT(*) as count,
					SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
					SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
					SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
					SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
					SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
				FROM {$wpdb->prefix}kab_reviews
				WHERE item_type = %s AND item_id = %d AND status = 'approved'",
				$type,
				$item_id
			),
			ARRAY_A
		);

		if ( ! $stats || ! $stats['count'] ) {
			return array(
				'average' => 0,
				'count' => 0,
				'distribution' => array(),
			);
		}

		return array(
			'average' => round( floatval( $stats['average'] ), 1 ),
			'count' => intval( $stats['count'] ),
			'distribution' => array(
				5 => intval( $stats['five_star'] ),
				4 => intval( $stats['four_star'] ),
				3 => intval( $stats['three_star'] ),
				2 => intval( $stats['two_star'] ),
				1 => intval( $stats['one_star'] ),
			),
		);
	}

	/**
	 * Render star rating HTML
	 *
	 * @param float $rating Rating value
	 * @param bool  $show_count Show review count
	 * @param int   $count Review count
	 * @return string HTML output
	 */
	public static function render_stars( $rating, $show_count = true, $count = 0 ) {
		$full_stars = floor( $rating );
		$half_star = ( $rating - $full_stars ) >= 0.5;
		$empty_stars = 5 - $full_stars - ( $half_star ? 1 : 0 );

		$html = '<div class="kab-star-rating">';

		// Full stars
		for ( $i = 0; $i < $full_stars; $i++ ) {
			$html .= '<span class="kab-star kab-star-full">★</span>';
		}

		// Half star
		if ( $half_star ) {
			$html .= '<span class="kab-star kab-star-half">★</span>';
		}

		// Empty stars
		for ( $i = 0; $i < $empty_stars; $i++ ) {
			$html .= '<span class="kab-star kab-star-empty">☆</span>';
		}

		// Rating value
		$html .= ' <span class="kab-rating-value">' . number_format( $rating, 1 ) . '</span>';

		// Review count
		if ( $show_count && $count > 0 ) {
			$html .= ' <span class="kab-rating-count">(' . absint( $count ) . ')</span>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Delete a review
	 *
	 * @param int $review_id Review ID
	 * @return bool Success status
	 */
	public static function delete_review( $review_id ) {
		global $wpdb;

		$review = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_reviews WHERE id = %d",
				$review_id
			),
			ARRAY_A
		);

		if ( ! $review ) {
			return false;
		}

		$result = $wpdb->delete(
			$wpdb->prefix . 'kab_reviews',
			array( 'id' => $review_id ),
			array( '%d' )
		);

		if ( $result ) {
			// Update average rating
			self::update_average_rating( $review['item_type'], $review['item_id'] );
			return true;
		}

		return false;
	}

	/**
	 * Moderate a review (approve/reject)
	 *
	 * @param int    $review_id Review ID
	 * @param string $status New status
	 * @return bool Success status
	 */
	public static function moderate_review( $review_id, $status ) {
		global $wpdb;

		$valid_statuses = array( 'approved', 'pending', 'rejected' );

		if ( ! in_array( $status, $valid_statuses ) ) {
			return false;
		}

		$review = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kab_reviews WHERE id = %d",
				$review_id
			),
			ARRAY_A
		);

		if ( ! $review ) {
			return false;
		}

		$result = $wpdb->update(
			$wpdb->prefix . 'kab_reviews',
			array( 'status' => $status ),
			array( 'id' => $review_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( $result !== false ) {
			// Update average rating when status changes
			self::update_average_rating( $review['item_type'], $review['item_id'] );
			return true;
		}

		return false;
	}
}
