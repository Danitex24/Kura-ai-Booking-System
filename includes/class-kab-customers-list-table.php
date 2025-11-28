<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class KAB_Customers_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Customer', 'kura-ai-booking' ),
            'plural'   => __( 'Customers', 'kura-ai-booking' ),
            'ajax'     => false,
        ] );
    }

    public function get_columns() {
        return [
            'cb'           => '<input type="checkbox" />',
            'name'         => __( 'Name', 'kura-ai-booking' ),
            'email'        => __( 'Email', 'kura-ai-booking' ),
            'total_bookings' => __( 'Total Bookings', 'kura-ai-booking' ),
            'date_registered' => __( 'Date Registered', 'kura-ai-booking' ),
        ];
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'name':
            case 'email':
            case 'total_bookings':
            case 'date_registered':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="customer[]" value="%s" />', $item['ID']
        );
    }

    public function get_sortable_columns() {
        return [
            'name' => [ 'display_name', true ],
            'email' => [ 'user_email', false ],
            'date_registered' => [ 'user_registered', false ],
        ];
    }

    public function column_total_bookings( $item ) {
        return sprintf(
            '<a href="%s">%d</a>',
            esc_url( add_query_arg( [ 'page' => 'kab-bookings', 'user_id' => $item['ID'] ], admin_url( 'admin.php' ) ) ),
            $item['total_bookings']
        );
    }

    function column_name( $item ) {
        $delete_nonce = wp_create_nonce( 'kab_delete_customer' );
        $actions = [
            'edit' => sprintf( '<a href="user-edit.php?user_id=%s">%s</a>', $item['ID'], __( 'Edit', 'kura-ai-booking' ) ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">%s</a>', $_REQUEST['page'], 'delete', $item['ID'], $delete_nonce, __( 'Delete', 'kura-ai-booking' ) ),
        ];

        return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );
    }

    function get_bulk_actions() {
        $actions = [
            'delete' => __( 'Delete', 'kura-ai-booking' ),
        ];
        return $actions;
    }

    public function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'kab_delete_customer' ) ) {
                die( 'Go get a life script kiddies' );
            }
            else {
                require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-customers.php';
                $customers_model = new KAB_Customers();
                
                // Handle single customer deletion
                if ( isset( $_GET['customer'] ) ) {
                    $customers_model->delete_customer( absint( $_GET['customer'] ) );
                }
                // Handle bulk deletion
                elseif ( isset( $_POST['customer'] ) && is_array( $_POST['customer'] ) ) {
                    foreach ( $_POST['customer'] as $customer_id ) {
                        $customers_model->delete_customer( absint( $customer_id ) );
                    }
                }

                wp_redirect( esc_url( remove_query_arg( array( 'action', 'customer', '_wpnonce' ) ) ) );
                exit;
            }
        }
    }

    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();

        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-customers.php';
        $customers_model = new KAB_Customers();

        $per_page     = $this->get_items_per_page( 'customers_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items  = $customers_model->get_customers_count();

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );

        $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'display_name';
        $order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_key( $_REQUEST['order'] ) : 'asc';

        $customers = $customers_model->get_customers( [
            'number'  => $per_page,
            'offset'  => ( $current_page - 1 ) * $per_page,
            'orderby' => $orderby,
            'order'   => $order,
        ] );

        $this->items = array_map( function( $customer ) use ( $customers_model ) {
            $customer['total_bookings'] = $customers_model->get_customer_booking_count( $customer['ID'] );
            return $customer;
        }, $customers );
    }
}
