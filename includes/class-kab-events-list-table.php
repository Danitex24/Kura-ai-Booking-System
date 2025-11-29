<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class KAB_Events_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Event', 'kura-ai-booking-free' ),
            'plural'   => __( 'Events', 'kura-ai-booking-free' ),
            'ajax'     => false
        ] );
    }

    public function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'name'       => __( 'Name', 'kura-ai-booking-free' ),
            'event_date' => __( 'Date', 'kura-ai-booking-free' ),
            'event_time' => __( 'Time', 'kura-ai-booking-free' ),
            'location'   => __( 'Location', 'kura-ai-booking-free' ),
            'price'      => __( 'Price', 'kura-ai-booking-free' ),
            'capacity'   => __( 'Capacity', 'kura-ai-booking-free' ),
        ];
    }

    public function prepare_items() {
        $this->_column_headers = array( $this->get_columns(), array(), array() );

        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
        $events_model = new KAB_Events();

        $per_page     = $this->get_items_per_page( 'events_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items  = $events_model->get_events_count();

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );

        $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'event_date';
        $order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_key( $_REQUEST['order'] ) : 'asc';

        $this->items = $events_model->get_events( [
            'number'  => $per_page,
            'offset'  => ( $current_page - 1 ) * $per_page,
            'orderby' => $orderby,
            'order'   => $order,
        ] );
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'price':
                return kab_format_currency( $item[ $column_name ], kab_currency_symbol( 'USD' ) );
            default:
                return $item[ $column_name ];
        }
    }

    protected function column_name( $item ) {
        $edit_url = add_query_arg(
            array(
                'page'     => 'kab-events',
                'action'   => 'edit',
                'event_id' => $item['id'],
            ),
            admin_url( 'admin.php' )
        );

        $delete_url = add_query_arg(
            array(
                'action'   => 'kab_delete_event',
                'event_id' => $item['id'],
                '_wpnonce' => wp_create_nonce( 'kab_delete_event_' . $item['id'] ),
            ),
            admin_url( 'admin-post.php' )
        );

        $view_url = add_query_arg(
            array(
                'page'     => 'kab-events',
                'action'   => 'view',
                'event_id' => $item['id'],
            ),
            admin_url( 'admin.php' )
        );

        $buttons = array(
            sprintf( '<a href="%s" class="kab-btn kab-btn-secondary kab-btn-sm">%s</a>', esc_url( $view_url ), __( 'View', 'kura-ai-booking-free' ) ),
            sprintf( '<a href="%s" class="kab-btn kab-btn-primary kab-btn-sm">%s</a>', esc_url( $edit_url ), __( 'Edit', 'kura-ai-booking-free' ) ),
            sprintf( '<a href="%s" class="kab-btn kab-btn-danger kab-btn-sm kab-delete-event" data-event-name="%s">%s</a>', esc_url( $delete_url ), esc_attr( $item['name'] ), __( 'Delete', 'kura-ai-booking-free' ) ),
        );
        $buttons = implode( ' ', $buttons );
        return sprintf( '%1$s <div class="kab-row-actions">%2$s</div>', esc_html( $item['name'] ), $buttons );
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="events[]" value="%d" />', $item['id'] );
    }
}
