<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class KAB_Custom_Fields {
    public function get_fields( $args = array() ) {
        global $wpdb; $sql = "SELECT * FROM {$wpdb->prefix}kab_custom_fields WHERE status='active' ORDER BY created_at DESC"; return $wpdb->get_results( $sql, ARRAY_A );
    }
    public function get_field( $id ) { global $wpdb; return $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}kab_custom_fields WHERE id=%d", $id ), ARRAY_A ); }
    public function create_field( $data ) {
        global $wpdb; $wpdb->insert( $wpdb->prefix.'kab_custom_fields', array(
            'name'     => sanitize_key( $data['name'] ),
            'label'    => sanitize_text_field( $data['label'] ),
            'type'     => sanitize_text_field( $data['type'] ),
            'options'  => sanitize_textarea_field( $data['options'] ?? '' ),
            'required' => isset( $data['required'] ) ? 1 : 0,
            'status'   => 'active',
        ), array('%s','%s','%s','%s','%d','%s') ); return $wpdb->insert_id;
    }
    public function update_field( $id, $data ) {
        global $wpdb; return $wpdb->update( $wpdb->prefix.'kab_custom_fields', array(
            'label'    => sanitize_text_field( $data['label'] ),
            'type'     => sanitize_text_field( $data['type'] ),
            'options'  => sanitize_textarea_field( $data['options'] ?? '' ),
            'required' => isset( $data['required'] ) ? 1 : 0,
        ), array('id'=>intval($id)), array('%s','%s','%s','%d'), array('%d') );
    }
    public function delete_field( $id ) { global $wpdb; return $wpdb->delete( $wpdb->prefix.'kab_custom_fields', array('id'=>intval($id)), array('%d') ); }
}

