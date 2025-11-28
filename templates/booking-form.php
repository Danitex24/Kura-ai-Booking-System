<?php
/**
 * Booking form template for Kura-ai Booking System
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'kab-frontend', plugins_url( '../assets/css/frontend.css', __FILE__ ), array(), KAB_VERSION );
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-events.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-services.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-bookings.php';

$events_model   = new KAB_Events();
$services_model = new KAB_Services();
$events         = $events_model->get_events();
$services       = $services_model->get_services();
?>

<div id="kab-booking-messages"></div>
<form method="post" class="kab-booking-form">
	<h2><?php esc_html_e( 'Book an Appointment or Event', 'kura-ai-booking-free' ); ?></h2>
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
				<option value="<?php echo esc_attr( $event['id'] ); ?>">
					<?php echo esc_html( $event['name'] ); ?> (<?php echo esc_html( $event['event_date'] ); ?> <?php echo esc_html( $event['event_time'] ); ?>)
				</option>
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
