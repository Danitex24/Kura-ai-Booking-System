<?php
/**
 * Customer Dashboard Template
 *
 * Displays the customer's booking history and allows cancellation.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_style( 'kab-frontend', KAB_FREE_PLUGIN_URL . 'assets/css/frontend.css', array(), KAB_VERSION );

// Handle booking cancellation.
if ( isset( $_POST['kab_cancel_booking_id'] ) && isset( $_POST['kab_cancel_booking_nonce'] ) ) {
	$booking_id = intval( wp_unslash( $_POST['kab_cancel_booking_id'] ) );
	$nonce      = sanitize_text_field( wp_unslash( $_POST['kab_cancel_booking_nonce'] ) );

	if ( wp_verify_nonce( $nonce, 'kab_cancel_booking_' . $booking_id ) ) {
		$user_id = get_current_user_id();
		require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-bookings.php';
		$result = KAB_Bookings::cancel_booking( $booking_id, $user_id );

		if ( $result ) {
			echo '<div class="kab-notice kab-notice-success">' . esc_html__( 'Booking cancelled successfully.', 'kura-ai-booking-free' ) . '</div>';
		} else {
			echo '<div class="kab-notice kab-notice-error">' . esc_html__( 'Unable to cancel booking. Please try again.', 'kura-ai-booking-free' ) . '</div>';
		}
	} else {
		echo '<div class="kab-notice kab-notice-error">' . esc_html__( 'Security verification failed.', 'kura-ai-booking-free' ) . '</div>';
	}
}

if ( ! is_user_logged_in() ) {
	echo '<p>' . esc_html__( 'You must be logged in to view your bookings.', 'kura-ai-booking-free' ) . '</p>';
	return;
}
$user_id = get_current_user_id();
global $wpdb;
$bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE user_id = %d ORDER BY created_at DESC", $user_id ), ARRAY_A );
echo '<div class="kab-customer-dashboard">';
echo '<h2>' . esc_html__( 'My Bookings', 'kura-ai-booking-free' ) . '</h2>';
if ( $bookings ) {
	echo '<table class="kab-bookings-table"><thead><tr>';
	echo '<th>' . esc_html__( 'Type', 'kura-ai-booking-free' ) . '</th>';
	echo '<th>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</th>';
	echo '<th>' . esc_html__( 'Time', 'kura-ai-booking-free' ) . '</th>';
	echo '<th>' . esc_html__( 'Status', 'kura-ai-booking-free' ) . '</th>';
	echo '<th>' . esc_html__( 'Ticket', 'kura-ai-booking-free' ) . '</th>';
	echo '<th>' . esc_html__( 'Action', 'kura-ai-booking-free' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $bookings as $booking ) {
		$ticket_url = add_query_arg( array( 'ticket_id' => $booking['ticket_id'] ), site_url( '/?kab_ticket_view=1' ) );
		echo '<tr>';
		echo '<td>' . esc_html( ucfirst( $booking['booking_type'] ) ) . '</td>';
		echo '<td>' . esc_html( $booking['booking_date'] ) . '</td>';
		echo '<td>' . esc_html( $booking['booking_time'] ) . '</td>';
		echo '<td>' . esc_html( $booking['status'] ) . '</td>';
		echo '<td><a href="' . esc_url( $ticket_url ) . '" target="_blank">' . esc_html__( 'View Ticket', 'kura-ai-booking-free' ) . '</a></td>';
		if ( 'pending' === $booking['status'] ) {
			$nonce = wp_create_nonce( 'kab_cancel_booking_' . $booking['id'] );
			echo '<td><form method="post"><input type="hidden" name="kab_cancel_booking_id" value="' . esc_attr( $booking['id'] ) . '" />';
			echo '<input type="hidden" name="kab_cancel_booking_nonce" value="' . esc_attr( $nonce ) . '" />';
			echo '<input type="submit" value="' . esc_attr__( 'Cancel', 'kura-ai-booking-free' ) . '" class="kab-cancel-booking-btn" /></form></td>';
		} else {
			echo '<td>-</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
} else {
	echo '<p>' . esc_html__( 'No bookings found.', 'kura-ai-booking-free' ) . '</p>';
}
echo '</div>';
