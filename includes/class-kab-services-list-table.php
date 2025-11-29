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
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = $services_model->count_services();
        $offset = ( $current_page - 1 ) * $per_page;
        $this->items = $services_model->get_services( $per_page, $offset );

        $this->_column_headers = array( $this->get_columns(), array(), array() );
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ) );
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

        $view_url = add_query_arg(
            array(
                'page'   => 'kab-services',
                'action' => 'view',
                'service_id' => $item['id'],
            ),
            admin_url( 'admin.php' )
        );
        $invoice_id = 0;
        $map = get_option( 'kab_service_invoice_map', array() );
        if ( isset( $map[ $item['id'] ] ) ) { $invoice_id = intval( $map[ $item['id'] ] ); }
        if ( ! $invoice_id ) {
            global $wpdb;
            $like = '%"name":"' . $wpdb->esc_like( $item['name'] ) . '"%';
            $invoice_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}kab_invoices WHERE item_name LIKE %s ORDER BY id DESC LIMIT 1", $like ) ) );
        }
        $invoice_url = $invoice_id ? admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id ) : '';
        $buttons = array(
            sprintf( '<a href="%s" class="kab-btn kab-btn-secondary kab-btn-sm">%s</a>', esc_url( $view_url ), __( 'View', 'kura-ai-booking-free' ) ),
            sprintf( '<a href="%s" class="kab-btn kab-btn-primary kab-btn-sm">%s</a>', esc_url( $edit_url ), __( 'Edit', 'kura-ai-booking-free' ) ),
            sprintf( '<a href="%s" class="kab-btn kab-btn-danger kab-btn-sm kab-delete-service" data-service-name="%s">%s</a>', esc_url( $delete_url ), esc_attr( $item['name'] ), __( 'Delete', 'kura-ai-booking-free' ) ),
            $invoice_url ? sprintf( '<a href="%s" class="kab-btn kab-btn-success kab-btn-sm">%s</a>', esc_url( $invoice_url ), __( 'View Invoice', 'kura-ai-booking-free' ) ) : '',
        );
        $buttons = array_filter( $buttons );
        return sprintf( '%1$s <div class="kab-row-actions">%2$s</div>', esc_html( $item['name'] ), implode( ' ', $buttons ) );
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="services[]" value="%d" />', $item['id'] );
    }
}
