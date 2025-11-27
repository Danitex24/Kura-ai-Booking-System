<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-events.php';
$events_model = new KAB_Events();
$events = $events_model->get_events();
?>
<div class="kab-events-list">
	<h2><?php esc_html_e( 'Upcoming Events', 'kura-ai-booking-free' ); ?></h2>
	<?php if ( $events ) : ?>
		<ul>
			<?php foreach ( $events as $event ) : ?>
				<li class="kab-event-item">
					<strong><?php echo esc_html( $event['name'] ); ?></strong><br>
					<?php echo esc_html( $event['event_date'] ); ?> <?php echo esc_html( $event['event_time'] ); ?><br>
					<?php echo esc_html( $event['location'] ); ?><br>
					<?php echo esc_html__( 'Price:', 'kura-ai-booking-free' ); ?> <?php echo esc_html( number_format( $event['price'], 2 ) ); ?><br>
					<form method="post" style="display:inline;">
						<input type="hidden" name="kab_booking_nonce" value="<?php echo esc_attr( wp_create_nonce( 'kab_booking_form' ) ); ?>" />
						<input type="hidden" name="booking_type" value="event" />
						<input type="hidden" name="event_id" value="<?php echo esc_attr( $event['id'] ); ?>" />
						<input type="hidden" name="booking_date" value="<?php echo esc_attr( $event['event_date'] ); ?>" />
						<input type="hidden" name="booking_time" value="<?php echo esc_attr( $event['event_time'] ); ?>" />
						<input type="text" name="customer_name" placeholder="Your Name" required />
						<input type="email" name="customer_email" placeholder="Your Email" required />
						<input type="submit" value="<?php esc_attr_e( 'Book Event', 'kura-ai-booking-free' ); ?>" />
					</form>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p><?php esc_html_e( 'No events available.', 'kura-ai-booking-free' ); ?></p>
	<?php endif; ?>
</div>
