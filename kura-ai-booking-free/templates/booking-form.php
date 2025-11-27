<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Fetch available services and events
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-events.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-bookings.php';
$events_model = new KAB_Events();
$events = $events_model->get_events();

// Service fetching would be similar (class-kab-services.php)
$services = array(); // Placeholder for services

// Handle booking submission
if ( isset( $_POST['kab_booking_nonce'] ) && wp_verify_nonce( $_POST['kab_booking_nonce'], 'kab_booking_form' ) ) {
	$name = sanitize_text_field( $_POST['customer_name'] );
	$email = sanitize_email( $_POST['customer_email'] );
	$type = sanitize_text_field( $_POST['booking_type'] );
	$date = sanitize_text_field( $_POST['booking_date'] );
	$time = sanitize_text_field( $_POST['booking_time'] );
	$service_id = isset( $_POST['service_id'] ) ? intval( $_POST['service_id'] ) : null;
	$event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : null;

	// Booking logic placeholder
	// $booking_id = KAB_Bookings::create_booking(...);
	// Show success message
	echo '<div class="kab-booking-success">' . esc_html__( 'Booking submitted! You will receive a confirmation email.', 'kura-ai-booking-free' ) . '</div>';
}
?>
<form method="post" class="kab-booking-form">
	<input type="hidden" name="kab_booking_nonce" value="<?php echo esc_attr( wp_create_nonce( 'kab_booking_form' ) ); ?>" />
	<p>
		<label><?php esc_html_e( 'Name', 'kura-ai-booking-free' ); ?></label><br>
		<input type="text" name="customer_name" required />
	</p>
	<p>
		<label><?php esc_html_e( 'Email', 'kura-ai-booking-free' ); ?></label><br>
		<input type="email" name="customer_email" required />
	</p>
	<p>
		<label><?php esc_html_e( 'Booking Type', 'kura-ai-booking-free' ); ?></label><br>
		<select name="booking_type" required onchange="document.querySelector('.kab-service-select').style.display = this.value === 'service' ? 'block' : 'none';document.querySelector('.kab-event-select').style.display = this.value === 'event' ? 'block' : 'none';">
			<option value="service"><?php esc_html_e( 'Service', 'kura-ai-booking-free' ); ?></option>
			<option value="event"><?php esc_html_e( 'Event', 'kura-ai-booking-free' ); ?></option>
		</select>
	</p>
	<p class="kab-service-select" style="display:block;">
		<label><?php esc_html_e( 'Select Service', 'kura-ai-booking-free' ); ?></label><br>
		<select name="service_id">
			<option value="">--</option>
			<?php foreach ( $services as $service ) : ?>
				<option value="<?php echo esc_attr( $service['id'] ); ?>"><?php echo esc_html( $service['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="kab-event-select" style="display:none;">
		<label><?php esc_html_e( 'Select Event', 'kura-ai-booking-free' ); ?></label><br>
		<select name="event_id">
			<option value="">--</option>
			<?php foreach ( $events as $event ) : ?>
				<option value="<?php echo esc_attr( $event['id'] ); ?>"><?php echo esc_html( $event['name'] ); ?> (<?php echo esc_html( $event['event_date'] ); ?>)</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label><?php esc_html_e( 'Date', 'kura-ai-booking-free' ); ?></label><br>
		<input type="date" name="booking_date" required />
	</p>
	<p>
		<label><?php esc_html_e( 'Time', 'kura-ai-booking-free' ); ?></label><br>
		<input type="time" name="booking_time" required />
	</p>
	<p>
		<input type="submit" value="<?php esc_attr_e( 'Book Now', 'kura-ai-booking-free' ); ?>" />
	</p>
</form>
