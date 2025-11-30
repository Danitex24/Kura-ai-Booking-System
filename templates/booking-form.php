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

wp_enqueue_style( 'kab-frontend', KAB_FREE_PLUGIN_URL . 'assets/css/frontend.css', array(), KAB_VERSION );
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-events.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-services.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-bookings.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-kab-employees.php';

$events_model   = new KAB_Events();
$services_model = new KAB_Services();
$employees_model = new KAB_Employees();
$events         = $events_model->get_events();
$services       = $services_model->get_services();
global $wpdb; $employees = $wpdb->get_results( "SELECT e.*, GROUP_CONCAT(es.service_id) as svc FROM {$wpdb->prefix}kab_employees e LEFT JOIN {$wpdb->prefix}kab_employee_services es ON es.employee_id=e.id WHERE e.show_on_site=1 GROUP BY e.id ORDER BY e.last_name", ARRAY_A );
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
		<select name="service_id" id="kab-service-select">
			<option value="">--</option>
			<?php foreach ( $services as $service ) : ?>
				<option value="<?php echo esc_attr( $service['id'] ); ?>"><?php echo esc_html( $service['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="kab-service-select" style="display:block;">
		<label><?php esc_html_e( 'Select Employee', 'kura-ai-booking-free' ); ?></label><br>
		<select name="employee_id" id="kab-employee-select">
			<option value="">--</option>
			<?php foreach ( $employees as $emp ) : $name = $emp['first_name'].' '.$emp['last_name']; ?>
				<option data-services="<?php echo esc_attr( $emp['svc'] ); ?>" value="<?php echo esc_attr( $emp['id'] ); ?>"><?php echo esc_html( $name ); ?></option>
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
<script>(function(){var svc=document.getElementById('kab-service-select');var emp=document.getElementById('kab-employee-select');if(svc&&emp){var filter=function(){var s=svc.value;[].slice.call(emp.options).forEach(function(o){if(!o.value)return;var list=(o.getAttribute('data-services')||'').split(',');o.style.display=(s && list.indexOf(s)>=0) ? '' : 'none';});};svc.addEventListener('change',filter);filter();}})();</script>
$cf_model = new KAB_Custom_Fields(); $custom_fields = $cf_model->get_fields();
<?php if ( $custom_fields ) : ?>
<div class="kab-custom-fields">
    <?php foreach ( $custom_fields as $f ) : ?>
        <p>
            <label><?php echo esc_html( $f['label'] ); ?></label><br>
            <?php if ( $f['type'] === 'text' ) : ?>
                <input type="text" name="cf[<?php echo esc_attr( $f['id'] ); ?>]" <?php echo intval( $f['required'] ) ? 'required' : ''; ?> />
            <?php elseif ( $f['type'] === 'number' ) : ?>
                <input type="number" name="cf[<?php echo esc_attr( $f['id'] ); ?>]" <?php echo intval( $f['required'] ) ? 'required' : ''; ?> />
            <?php elseif ( $f['type'] === 'textarea' ) : ?>
                <textarea name="cf[<?php echo esc_attr( $f['id'] ); ?>]" <?php echo intval( $f['required'] ) ? 'required' : ''; ?>></textarea>
            <?php elseif ( $f['type'] === 'select' ) : $ops = array_map( 'trim', explode( ',', $f['options'] ) ); ?>
                <select name="cf[<?php echo esc_attr( $f['id'] ); ?>]" <?php echo intval( $f['required'] ) ? 'required' : ''; ?>>
                    <option value="">--</option>
                    <?php foreach ( $ops as $op ) : ?>
                        <option value="<?php echo esc_attr( $op ); ?>"><?php echo esc_html( $op ); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ( $f['type'] === 'checkbox' ) : ?>
                <input type="checkbox" name="cf[<?php echo esc_attr( $f['id'] ); ?>]" value="1" />
            <?php elseif ( $f['type'] === 'date' ) : ?>
                <input type="date" name="cf[<?php echo esc_attr( $f['id'] ); ?>]" <?php echo intval( $f['required'] ) ? 'required' : ''; ?> />
            <?php endif; ?>
        </p>
    <?php endforeach; ?>
</div>
<?php endif; ?>
