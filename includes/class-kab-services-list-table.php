<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class KAB_Services_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Service', 'kura-ai-booking-free' ),
            'plural'   => __( 'Services', 'kura-ai-booking-free' ),
            'ajax'     => false
        ] );
    }

    public function get_columns() {
        return [
            'cb'       => '<input type="checkbox" />',
            'name'     => __( 'Name', 'kura-ai-booking-free' ),
            'duration' => __( 'Duration', 'kura-ai-booking-free' ),
            'price'    => __( 'Price', 'kura-ai-booking-free' ),
        ];
    }

    public function prepare_items() {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
        $services_model = new KAB_Services();
        $this->items = $services_model->get_services();

        $this->_column_headers = array( $this->get_columns(), array(), array() );
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'duration':
                return $item[ $column_name ] . ' ' . __( 'minutes', 'kura-ai-booking-free' );
            case 'price':
                return number_format( $item[ $column_name ], 2 );
            default:
                return $item[ $column_name ];
        }
    }

    protected function column_name( $item ) {
        $edit_url = add_query_arg(
            array(
                'page'   => 'kab-services',
                'action' => 'edit',
                'service_id' => $item['id'],
            ),
            admin_url( 'admin.php' )
        );

        $delete_url = add_query_arg(
            array(
                'action'   => 'kab_delete_service',
                'service_id' => $item['id'],
                '_wpnonce' => wp_create_nonce( 'kab_delete_service_' . $item['id'] ),
            ),
            admin_url( 'admin-post.php' )
        );

        $actions = array(
            'edit'   => sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'kura-ai-booking-free' ) ),
            'delete' => sprintf( '<a href="%s" class="kab-delete-service" data-service-name="%s">%s</a>', esc_url( $delete_url ), esc_attr( $item['name'] ), __( 'Delete', 'kura-ai-booking-free' ) ),
        );

        return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="services[]" value="%d" />', $item['id'] );
    }
}
