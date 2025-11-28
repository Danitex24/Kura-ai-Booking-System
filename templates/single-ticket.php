<?php
/**
 * Single Ticket Template
 *
 * Displays a single ticket with QR code for validation.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'kab-frontend', plugins_url( '../assets/css/frontend.css', __FILE__ ), array(), KAB_VERSION );
$ticket_id    = isset( $_GET['ticket_id'] ) ? sanitize_text_field( wp_unslash( $_GET['ticket_id'] ) ) : '';
$ticket_model = new KAB_Tickets();
$ticket       = $ticket_id ? $ticket_model->get_ticket_by_id( $ticket_id ) : null;
?>
<div class="kab-ticket-viewer">
	<?php if ( $ticket ) : ?>
		<h2><?php esc_html_e( 'Your Ticket', 'kura-ai-booking-free' ); ?></h2>
		<p><strong><?php esc_html_e( 'Event/Service:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $ticket['event_name'] ?? $ticket['service_name'] ); ?></p>
		<p><strong><?php esc_html_e( 'Customer:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $ticket['customer_name'] ); ?></p>
		<p><strong><?php esc_html_e( 'Booking ID:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $ticket['booking_id'] ); ?></p>
		<p><strong><?php esc_html_e( 'Ticket ID:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $ticket['ticket_id'] ); ?></p>
		<p><strong><?php esc_html_e( 'Date/Time:', 'kura-ai-booking-free' ); ?></strong> <?php echo esc_html( $ticket['event_date'] ?? $ticket['booking_date'] ); ?> <?php echo esc_html( $ticket['event_time'] ?? $ticket['booking_time'] ); ?></p>
		<?php if ( ! empty( $ticket['qr_code_path'] ) ) : ?>
			<p><img src="<?php echo esc_url( $ticket['qr_code_path'] ); ?>" alt="QR Code" style="max-width:180px;" /></p>
		<?php endif; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Ticket not found or invalid.', 'kura-ai-booking-free' ); ?></p>
	<?php endif; ?>
</div>
