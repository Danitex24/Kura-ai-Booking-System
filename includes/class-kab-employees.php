<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class KAB_Employees {
    public function get_employees( $args = array() ) {
        global $wpdb;
        $defaults = array( 'number' => 20, 'offset' => 0, 'search' => '', 'service_id' => 0, 'location' => '', 'order' => 'ASC' );
        $args = wp_parse_args( $args, $defaults );
        $where = array(); $params = array(); $join = '';
        if ( $args['search'] ) { $where[] = '(CONCAT(first_name, " ", last_name) LIKE %s OR email LIKE %s)'; $like = '%' . $wpdb->esc_like( $args['search'] ) . '%'; $params[] = $like; $params[] = $like; }
        if ( $args['location'] ) { $where[] = 'location = %s'; $params[] = $args['location']; }
        if ( $args['service_id'] ) { $join = " LEFT JOIN {$wpdb->prefix}kab_employee_services es ON es.employee_id = e.id "; $where[] = 'es.service_id = %d'; $params[] = intval( $args['service_id'] ); }
        $sql = "SELECT e.* FROM {$wpdb->prefix}kab_employees e{$join} ";
        if ( $where ) { $sql .= 'WHERE ' . implode( ' AND ', $where ); }
        $sql .= ' ORDER BY last_name ' . ( strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC' );
        $sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', intval( $args['number'] ), intval( $args['offset'] ) );
        if ( $params ) { $sql = $wpdb->prepare( $sql, $params ); }
        return $wpdb->get_results( $sql, ARRAY_A );
    }

    public function count_employees( $args = array() ) {
        global $wpdb; $where = array(); $params = array(); $join = '';
        if ( ! empty( $args['search'] ) ) { $where[] = '(CONCAT(first_name, " ", last_name) LIKE %s OR email LIKE %s)'; $like = '%' . $wpdb->esc_like( $args['search'] ) . '%'; $params[] = $like; $params[] = $like; }
        if ( ! empty( $args['location'] ) ) { $where[] = 'location = %s'; $params[] = $args['location']; }
        if ( ! empty( $args['service_id'] ) ) { $join = " LEFT JOIN {$wpdb->prefix}kab_employee_services es ON es.employee_id = e.id "; $where[] = 'es.service_id = %d'; $params[] = intval( $args['service_id'] ); }
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}kab_employees e{$join} ";
        if ( $where ) { $sql .= 'WHERE ' . implode( ' AND ', $where ); }
        if ( $params ) { $sql = $wpdb->prepare( $sql, $params ); }
        return intval( $wpdb->get_var( $sql ) );
    }

    public function get_employee( $id ) {
        global $wpdb; return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_employees WHERE id=%d", $id ), ARRAY_A );
    }

    public function create_employee( $data ) {
        global $wpdb; $wpdb->insert( $wpdb->prefix . 'kab_employees', array(
            'first_name' => sanitize_text_field( $data['first_name'] ),
            'last_name'  => sanitize_text_field( $data['last_name'] ),
            'email'      => sanitize_email( $data['email'] ),
            'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
            'location'   => sanitize_text_field( $data['location'] ?? '' ),
            'wp_user_id' => intval( $data['wp_user_id'] ?? 0 ),
            'timezone'   => sanitize_text_field( $data['timezone'] ?? '' ),
            'photo_url'  => esc_url_raw( $data['photo_url'] ?? '' ),
            'badge'      => sanitize_text_field( $data['badge'] ?? '' ),
            'description'=> sanitize_textarea_field( $data['description'] ?? '' ),
            'internal_note'=> sanitize_textarea_field( $data['internal_note'] ?? '' ),
            'status'     => sanitize_text_field( $data['status'] ?? 'available' ),
            'show_on_site'=> isset( $data['show_on_site'] ) ? 1 : 0,
        ), array('%s','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%d') );
        return $wpdb->insert_id;
    }

    public function update_employee( $id, $data ) {
        global $wpdb; return $wpdb->update( $wpdb->prefix . 'kab_employees', array(
            'first_name' => sanitize_text_field( $data['first_name'] ),
            'last_name'  => sanitize_text_field( $data['last_name'] ),
            'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
            'location'   => sanitize_text_field( $data['location'] ?? '' ),
            'wp_user_id' => intval( $data['wp_user_id'] ?? 0 ),
            'timezone'   => sanitize_text_field( $data['timezone'] ?? '' ),
            'photo_url'  => esc_url_raw( $data['photo_url'] ?? '' ),
            'badge'      => sanitize_text_field( $data['badge'] ?? '' ),
            'description'=> sanitize_textarea_field( $data['description'] ?? '' ),
            'internal_note'=> sanitize_textarea_field( $data['internal_note'] ?? '' ),
            'status'     => sanitize_text_field( $data['status'] ?? 'available' ),
            'show_on_site'=> isset( $data['show_on_site'] ) ? 1 : 0,
        ), array( 'id' => intval( $id ) ), array('%s','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%d'), array('%d') );
    }

    public function hide_employee( $id ) { global $wpdb; return $wpdb->update( $wpdb->prefix.'kab_employees', array('show_on_site'=>0), array('id'=>intval($id)), array('%d'), array('%d') ); }
    public function delete_employee( $id ) { global $wpdb; return $wpdb->delete( $wpdb->prefix.'kab_employees', array('id'=>intval($id)), array('%d') ); }

    public function duplicate_employee( $id ) {
        $e = $this->get_employee( $id ); if ( ! $e ) return false;
        unset( $e['id'] ); $e['email'] = ''; $e['show_on_site'] = 0;
        return $this->create_employee( $e );
    }

    public function set_services( $id, $services ) {
        global $wpdb; $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}kab_employee_services WHERE employee_id=%d", $id ) );
        foreach ( (array) $services as $row ) {
            $wpdb->insert( $wpdb->prefix.'kab_employee_services', array(
                'employee_id' => intval( $id ),
                'service_id'  => intval( $row['service_id'] ),
                'price'       => isset( $row['price'] ) ? floatval( $row['price'] ) : null,
                'capacity'    => isset( $row['capacity'] ) ? intval( $row['capacity'] ) : null,
            ), array('%d','%d','%f','%d') );
        }
    }

    public function get_employee_services( $id ) {
        global $wpdb; return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_employee_services WHERE employee_id=%d", $id ), ARRAY_A );
    }

    public function set_workhours( $id, $rows ) {
        global $wpdb; $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}kab_employee_workhours WHERE employee_id=%d", $id ) );
        foreach ( (array) $rows as $r ) {
            if ( ! isset( $r['weekday'], $r['start_time'], $r['end_time'] ) ) { continue; }
            $wpdb->insert( $wpdb->prefix.'kab_employee_workhours', array(
                'employee_id' => intval( $id ),
                'weekday'     => intval( $r['weekday'] ),
                'start_time'  => sanitize_text_field( $r['start_time'] ),
                'end_time'    => sanitize_text_field( $r['end_time'] ),
            ), array('%d','%d','%s','%s') );
        }
    }

    public function get_workhours( $id ) {
        global $wpdb; return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_employee_workhours WHERE employee_id=%d ORDER BY weekday, start_time", $id ), ARRAY_A );
    }

    public function set_daysoff( $id, $dates ) {
        global $wpdb; $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}kab_employee_daysoff WHERE employee_id=%d", $id ) );
        foreach ( (array) $dates as $d ) {
            if ( empty( $d['day_off'] ) ) { continue; }
            $wpdb->insert( $wpdb->prefix.'kab_employee_daysoff', array(
                'employee_id' => intval( $id ),
                'day_off'     => sanitize_text_field( $d['day_off'] ),
                'reason'      => sanitize_text_field( $d['reason'] ?? '' ),
            ), array('%d','%s','%s') );
        }
    }

    public function get_daysoff( $id ) {
        global $wpdb; return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_employee_daysoff WHERE employee_id=%d ORDER BY day_off", $id ), ARRAY_A );
    }

    public function set_specialdays( $id, $rows ) {
        global $wpdb; $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}kab_employee_specialdays WHERE employee_id=%d", $id ) );
        foreach ( (array) $rows as $r ) {
            if ( empty( $r['special_date'] ) ) { continue; }
            $wpdb->insert( $wpdb->prefix.'kab_employee_specialdays', array(
                'employee_id' => intval( $id ),
                'special_date'=> sanitize_text_field( $r['special_date'] ),
                'start_time'  => sanitize_text_field( $r['start_time'] ?? '' ),
                'end_time'    => sanitize_text_field( $r['end_time'] ?? '' ),
                'services'    => isset( $r['services'] ) ? wp_json_encode( (array) $r['services'] ) : null,
            ), array('%d','%s','%s','%s','%s') );
        }
    }

    public function get_specialdays( $id ) {
        global $wpdb; return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_employee_specialdays WHERE employee_id=%d ORDER BY special_date", $id ), ARRAY_A );
    }
}
