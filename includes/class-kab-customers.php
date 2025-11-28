<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KAB_Customers {

    public function get_customers( $args = array() ) {
        $defaults = array(
            'number'  => 20,
            'offset'  => 0,
            'orderby' => 'display_name',
            'order'   => 'ASC',
        );

        $args = wp_parse_args( $args, $defaults );

        $users = get_users( $args );

        $customers = array();
        foreach ( $users as $user ) {
            $customers[] = array(
                'ID'              => $user->ID,
                'name'            => $user->display_name,
                'email'           => $user->user_email,
                'total_bookings'  => $this->get_customer_booking_count( $user->ID ),
                'date_registered' => date( 'Y-m-d', strtotime( $user->user_registered ) ),
            );
        }

        return $customers;
    }

    public function get_customers_count() {
        $result = count_users();
        return $result['total_users'];
    }

    public function get_customer_booking_count( $user_id ) {
        global $wpdb;

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}kab_bookings WHERE user_id = %d",
            $user_id
        ) );

        return $count ? $count : 0;
    }

    public function delete_customer( $user_id ) {
        global $wpdb;

        // Delete bookings associated with the user
        $wpdb->delete(
            $wpdb->prefix . 'kab_bookings',
            [ 'user_id' => $user_id ],
            [ '%d' ]
        );

        // Delete the user
        wp_delete_user( $user_id );
    }
}
