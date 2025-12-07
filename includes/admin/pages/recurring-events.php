<?php
/**
 * Recurring Events Admin Page
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Handle form submissions
if ( isset( $_POST['kab_create_recurrence'] ) && check_admin_referer( 'kab_create_recurrence' ) ) {
	$event_id = intval( $_POST['event_id'] );
	$recurrence_data = array(
		'frequency' => sanitize_text_field( $_POST['frequency'] ),
		'interval' => intval( $_POST['interval'] ),
		'start_date' => sanitize_text_field( $_POST['start_date'] ),
		'end_date' => ! empty( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : null,
		'occurrences' => ! empty( $_POST['occurrences'] ) ? intval( $_POST['occurrences'] ) : null,
		'days_of_week' => ! empty( $_POST['days_of_week'] ) ? sanitize_text_field( $_POST['days_of_week'] ) : null,
		'day_of_month' => ! empty( $_POST['day_of_month'] ) ? intval( $_POST['day_of_month'] ) : null,
	);

	$result = KAB_Recurring_Events::create_recurrence( $event_id, $recurrence_data );
	if ( $result ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Recurring event pattern created successfully!', 'kura-ai-booking-free' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to create recurring event pattern.', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Handle delete
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) && check_admin_referer( 'kab_delete_recurrence_' . intval( $_GET['id'] ) ) ) {
	$id = intval( $_GET['id'] );
	$deleted = KAB_Recurring_Events::delete_recurrence( $id );
	if ( $deleted ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Recurring event pattern deleted successfully!', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Fetch all recurring events
$recurrences = $wpdb->get_results(
	"SELECT r.*, e.name as event_name
	FROM {$wpdb->prefix}kab_event_recurrence r
	LEFT JOIN {$wpdb->prefix}kab_events e ON r.event_id = e.id
	ORDER BY r.created_at DESC"
);

// Fetch all events for dropdown
$events = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}kab_events WHERE status = 'active' ORDER BY name ASC" );
?>

<div class="wrap">
	<h1><?php echo esc_html__( 'Recurring Events', 'kura-ai-booking-free' ); ?></h1>

	<div class="kab-admin-container">
		<!-- Create New Recurrence Form -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Create Recurring Event Pattern', 'kura-ai-booking-free' ); ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'kab_create_recurrence' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="event_id"><?php echo esc_html__( 'Event', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<select name="event_id" id="event_id" required class="regular-text">
								<option value=""><?php echo esc_html__( '-- Select Event --', 'kura-ai-booking-free' ); ?></option>
								<?php foreach ( $events as $event ) : ?>
									<option value="<?php echo esc_attr( $event->id ); ?>">
										<?php echo esc_html( $event->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="frequency"><?php echo esc_html__( 'Frequency', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<select name="frequency" id="frequency" required class="regular-text">
								<option value="daily"><?php echo esc_html__( 'Daily', 'kura-ai-booking-free' ); ?></option>
								<option value="weekly"><?php echo esc_html__( 'Weekly', 'kura-ai-booking-free' ); ?></option>
								<option value="monthly"><?php echo esc_html__( 'Monthly', 'kura-ai-booking-free' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="interval"><?php echo esc_html__( 'Interval', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="number" name="interval" id="interval" value="1" min="1" required class="small-text">
							<p class="description"><?php echo esc_html__( 'E.g., 1 = every week, 2 = every 2 weeks', 'kura-ai-booking-free' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="start_date"><?php echo esc_html__( 'Start Date', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="date" name="start_date" id="start_date" required class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="end_date"><?php echo esc_html__( 'End Date', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="date" name="end_date" id="end_date" class="regular-text">
							<p class="description"><?php echo esc_html__( 'Optional - leave empty for unlimited', 'kura-ai-booking-free' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="occurrences"><?php echo esc_html__( 'Max Occurrences', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="number" name="occurrences" id="occurrences" min="1" class="small-text">
							<p class="description"><?php echo esc_html__( 'Optional - maximum number of instances to generate', 'kura-ai-booking-free' ); ?></p>
						</td>
					</tr>

					<tr id="days_of_week_row" style="display:none;">
						<th scope="row">
							<label><?php echo esc_html__( 'Days of Week', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<label><input type="checkbox" name="days_of_week[]" value="1"> <?php echo esc_html__( 'Monday', 'kura-ai-booking-free' ); ?></label><br>
							<label><input type="checkbox" name="days_of_week[]" value="2"> <?php echo esc_html__( 'Tuesday', 'kura-ai-booking-free' ); ?></label><br>
							<label><input type="checkbox" name="days_of_week[]" value="3"> <?php echo esc_html__( 'Wednesday', 'kura-ai-booking-free' ); ?></label><br>
							<label><input type="checkbox" name="days_of_week[]" value="4"> <?php echo esc_html__( 'Thursday', 'kura-ai-booking-free' ); ?></label><br>
							<label><input type="checkbox" name="days_of_week[]" value="5"> <?php echo esc_html__( 'Friday', 'kura-ai-booking-free' ); ?></label><br>
							<label><input type="checkbox" name="days_of_week[]" value="6"> <?php echo esc_html__( 'Saturday', 'kura-ai-booking-free' ); ?></label><br>
							<label><input type="checkbox" name="days_of_week[]" value="0"> <?php echo esc_html__( 'Sunday', 'kura-ai-booking-free' ); ?></label>
						</td>
					</tr>

					<tr id="day_of_month_row" style="display:none;">
						<th scope="row">
							<label for="day_of_month"><?php echo esc_html__( 'Day of Month', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="number" name="day_of_month" id="day_of_month" min="1" max="31" class="small-text">
							<p class="description"><?php echo esc_html__( 'E.g., 15 for the 15th of each month', 'kura-ai-booking-free' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="kab_create_recurrence" class="button button-primary" value="<?php echo esc_attr__( 'Create Pattern', 'kura-ai-booking-free' ); ?>">
				</p>
			</form>
		</div>

		<!-- List of Recurring Events -->
		<div class="kab-card" style="margin-top: 20px;">
			<h2><?php echo esc_html__( 'Existing Recurring Patterns', 'kura-ai-booking-free' ); ?></h2>

			<?php if ( $recurrences ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Event', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Frequency', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Interval', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Start Date', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'End Date', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Instances', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Actions', 'kura-ai-booking-free' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recurrences as $recurrence ) :
							$instance_count = $wpdb->get_var( $wpdb->prepare(
								"SELECT COUNT(*) FROM {$wpdb->prefix}kab_event_instances WHERE recurrence_id = %d",
								$recurrence->id
							) );
						?>
							<tr>
								<td><?php echo esc_html( $recurrence->event_name ); ?></td>
								<td><?php echo esc_html( ucfirst( $recurrence->frequency ) ); ?></td>
								<td><?php echo esc_html( $recurrence->interval ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $recurrence->start_date ) ) ); ?></td>
								<td><?php echo $recurrence->end_date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $recurrence->end_date ) ) ) : '&mdash;'; ?></td>
								<td><?php echo esc_html( $instance_count ); ?></td>
								<td>
									<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $recurrence->id ) ), 'kab_delete_recurrence_' . $recurrence->id ) ); ?>"
									   class="button button-small"
									   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this recurring pattern?', 'kura-ai-booking-free' ) ); ?>');">
										<?php echo esc_html__( 'Delete', 'kura-ai-booking-free' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php echo esc_html__( 'No recurring patterns found.', 'kura-ai-booking-free' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Show/hide frequency-specific fields
	$('#frequency').on('change', function() {
		var freq = $(this).val();
		$('#days_of_week_row, #day_of_month_row').hide();

		if (freq === 'weekly') {
			$('#days_of_week_row').show();
		} else if (freq === 'monthly') {
			$('#day_of_month_row').show();
		}
	});

	// Handle days_of_week array conversion
	$('form').on('submit', function() {
		var days = [];
		$('input[name="days_of_week[]"]:checked').each(function() {
			days.push($(this).val());
		});
		if (days.length > 0) {
			$('<input>').attr({
				type: 'hidden',
				name: 'days_of_week',
				value: days.join(',')
			}).appendTo(this);
		}
		$('input[name="days_of_week[]"]').prop('disabled', true);
	});
});
</script>

<style>
.kab-admin-container {
	max-width: 1200px;
}
.kab-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
	padding: 20px;
	margin-bottom: 20px;
}
.kab-card h2 {
	margin-top: 0;
	padding-bottom: 10px;
	border-bottom: 1px solid #eee;
}
</style>
