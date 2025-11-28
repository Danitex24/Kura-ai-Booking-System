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

	public function add_event( $data ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'kab_events',
			array(
				'name'        => sanitize_text_field( $data['name'] ),
				'description' => sanitize_textarea_field( $data['description'] ),
				'event_date'  => sanitize_text_field( $data['event_date'] ),
				'event_time'  => sanitize_text_field( $data['event_time'] ),
				'location'    => sanitize_text_field( $data['location'] ),
				'price'       => floatval( $data['price'] ),
				'capacity'    => intval( $data['capacity'] ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%f', '%d' )
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
				'location'    => sanitize_text_field( $data['location'] ),
				'price'       => floatval( $data['price'] ),
				'capacity'    => intval( $data['capacity'] ),
			),
			array( 'id' => intval( $id ) ),
			array( '%s', '%s', '%s', '%s', '%s', '%f', '%d' ),
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
