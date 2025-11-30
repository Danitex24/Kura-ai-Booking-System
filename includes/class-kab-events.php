<?php
/**
 * Kura-ai Booking System Events
 *
 * Handles event CRUD and queries.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Events {
	public function __construct() {}

    public function get_events( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'number'  => 20,
            'offset'  => 0,
            'orderby' => 'id',
            'order'   => 'ASC',
            'status'  => 'active',
        );

        $args = wp_parse_args( $args, $defaults );

        $where = '';
        if ( 'active' === $args['status'] ) {
            $where = "WHERE status = 'active'";
        } elseif ( 'deleted' === $args['status'] ) {
            $where = "WHERE status = 'deleted'";
        }

        $query = "SELECT * FROM {$wpdb->prefix}kab_events {$where} ORDER BY " . sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] ) . " LIMIT %d OFFSET %d";

        return $wpdb->get_results( $wpdb->prepare( $query, $args['number'], $args['offset'] ), ARRAY_A );
    }

    public function get_events_count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}kab_events WHERE status = 'active'" );
    }

	public function get_event( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_events WHERE id = %d", $id ), ARRAY_A );
	}

	/**
	 * Create a new event
	 *
	 * @param array $data Event data
	 * @return int|false Event ID on success, false on failure
	 */
	public function create_event( $data ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'kab_events',
			array(
				'name'        => sanitize_text_field( $data['name'] ),
				'description' => sanitize_textarea_field( $data['description'] ),
				'event_date'  => sanitize_text_field( $data['event_date'] ),
				'event_time'  => sanitize_text_field( $data['event_time'] ),
				'event_end_time' => isset( $data['event_end_time'] ) ? sanitize_text_field( $data['event_end_time'] ) : null,
				'organizer'   => isset( $data['organizer'] ) ? sanitize_text_field( $data['organizer'] ) : null,
				'location'    => sanitize_text_field( $data['location'] ),
				'price'       => floatval( $data['price'] ),
				'capacity'    => intval( $data['capacity'] ),
				'booking_open'=> isset( $data['booking_open'] ) ? sanitize_text_field( $data['booking_open'] ) : null,
				'booking_close'=> isset( $data['booking_close'] ) ? sanitize_text_field( $data['booking_close'] ) : null,
				'tags'        => isset( $data['tags'] ) ? sanitize_text_field( $data['tags'] ) : null,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s', '%s' )
		);
		return $wpdb->insert_id;
	}

	public function update_event( $id, $data ) {
		global $wpdb;
		return $wpdb->update(
			$wpdb->prefix . 'kab_events',
			array(
				'name'        => sanitize_text_field( $data['name'] ),
				'description' => sanitize_textarea_field( $data['description'] ),
				'event_date'  => sanitize_text_field( $data['event_date'] ),
				'event_time'  => sanitize_text_field( $data['event_time'] ),
				'event_end_time' => isset( $data['event_end_time'] ) ? sanitize_text_field( $data['event_end_time'] ) : null,
				'organizer'   => isset( $data['organizer'] ) ? sanitize_text_field( $data['organizer'] ) : null,
				'location'    => sanitize_text_field( $data['location'] ),
				'price'       => floatval( $data['price'] ),
				'capacity'    => intval( $data['capacity'] ),
				'booking_open'=> isset( $data['booking_open'] ) ? sanitize_text_field( $data['booking_open'] ) : null,
				'booking_close'=> isset( $data['booking_close'] ) ? sanitize_text_field( $data['booking_close'] ) : null,
				'tags'        => isset( $data['tags'] ) ? sanitize_text_field( $data['tags'] ) : null,
			),
			array( 'id' => intval( $id ) ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

    public function delete_event( $id ) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->prefix . 'kab_events',
            array( 'status' => 'deleted' ),
            array( 'id' => intval( $id ) ),
            array( '%s' ),
            array( '%d' )
        );
    }
}
