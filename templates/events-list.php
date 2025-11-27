<?php
/**
 * Events List Template
 *
 * Displays a list of upcoming events for booking.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_style( 'kab-frontend', plugins_url( '../assets/css/frontend.css', __FILE__ ), array(), KAB_VERSION );
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-events.php';
$events_model = new KAB_Events();
$events       = $events_model->get_events();
?>
<div class="kab-events-list">
	<h2><?php esc_html_e( 'Upcoming Events', 'kura-ai-booking-free' ); ?></h2>
	<?php if ( $events ) : ?>
		<?php foreach ( $events as $event ) : ?>
			<div class="kab-event-card">
				<h3><?php echo esc_html( $event['name'] ); ?></h3>
				<div class="kab-event-meta">
					<?php echo esc_html( $event['event_date'] ); ?> <?php echo esc_html( $event['event_time'] ); ?> | <?php echo esc_html( $event['location'] ); ?>
				</div>
				<div class="kab-event-meta">
					<?php echo esc_html__( 'Price:', 'kura-ai-booking-free' ); ?> <?php echo esc_html( number_format( $event['price'], 2 ) ); ?>
				</div>
				<form method="post">
					<input type="hidden" name="kab_booking_nonce" value="<?php echo esc_attr( wp_create_nonce( 'kab_booking_form' ) ); ?>" />
					<input type="hidden" name="booking_type" value="event" />
					<input type="hidden" name="event_id" value="<?php echo esc_attr( $event['id'] ); ?>" />
					<input type="hidden" name="booking_date" value="<?php echo esc_attr( $event['event_date'] ); ?>" />
					<input type="hidden" name="booking_time" value="<?php echo esc_attr( $event['event_time'] ); ?>" />
					<input type="text" name="customer_name" placeholder="<?php esc_attr_e( 'Your Name', 'kura-ai-booking-free' ); ?>" required />
					<input type="email" name="customer_email" placeholder="<?php esc_attr_e( 'Your Email', 'kura-ai-booking-free' ); ?>" required />
					<button type="submit" class="kab-event-book-btn"><?php esc_html_e( 'Book Event', 'kura-ai-booking-free' ); ?></button>
				</form>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No events available.', 'kura-ai-booking-free' ); ?></p>
	<?php endif; ?>
</div>
