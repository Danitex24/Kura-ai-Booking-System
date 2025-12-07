<?php
/**
 * Email Reminders Admin Page
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Handle manual send
if ( isset( $_POST['kab_send_reminder'] ) && check_admin_referer( 'kab_send_reminder' ) ) {
	$booking_id = intval( $_POST['booking_id'] );
	$result = KAB_Reminders::send_manual_reminder( $booking_id );
	if ( $result ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Reminder sent successfully!', 'kura-ai-booking-free' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to send reminder.', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Fetch reminder statistics
$stats = array(
	'scheduled' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_reminders WHERE status = 'scheduled'" ),
	'sent' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_reminders WHERE status = 'sent'" ),
	'failed' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_reminders WHERE status = 'failed'" ),
);

// Fetch recent reminders
$reminders = $wpdb->get_results(
	"SELECT r.*, b.booking_date, b.booking_time, b.booking_type,
		CASE
			WHEN b.service_id IS NOT NULL THEN s.name
			WHEN b.event_id IS NOT NULL THEN e.name
			ELSE 'N/A'
		END as item_name,
		u.display_name as customer_name
	FROM {$wpdb->prefix}kab_reminders r
	LEFT JOIN {$wpdb->prefix}kab_bookings b ON r.booking_id = b.id
	LEFT JOIN {$wpdb->prefix}kab_services s ON b.service_id = s.id
	LEFT JOIN {$wpdb->prefix}kab_events e ON b.event_id = e.id
	LEFT JOIN {$wpdb->prefix}users u ON b.user_id = u.ID
	ORDER BY r.created_at DESC
	LIMIT 100"
);

// Fetch upcoming bookings without reminders
$upcoming_bookings = $wpdb->get_results(
	"SELECT b.id, b.booking_date, b.booking_time, b.booking_type,
		CASE
			WHEN b.service_id IS NOT NULL THEN s.name
			WHEN b.event_id IS NOT NULL THEN e.name
			ELSE 'N/A'
		END as item_name,
		u.display_name as customer_name
	FROM {$wpdb->prefix}kab_bookings b
	LEFT JOIN {$wpdb->prefix}kab_services s ON b.service_id = s.id
	LEFT JOIN {$wpdb->prefix}kab_events e ON b.event_id = e.id
	LEFT JOIN {$wpdb->prefix}users u ON b.user_id = u.ID
	LEFT JOIN {$wpdb->prefix}kab_reminders r ON b.id = r.booking_id
	WHERE b.status IN ('confirmed', 'pending')
		AND CONCAT(b.booking_date, ' ', b.booking_time) > NOW()
		AND r.id IS NULL
	ORDER BY b.booking_date ASC, b.booking_time ASC
	LIMIT 20"
);
?>

<div class="wrap">
	<h1><?php echo esc_html__( 'Email Reminders', 'kura-ai-booking-free' ); ?></h1>

	<div class="kab-admin-container">
		<!-- Statistics Cards -->
		<div class="kab-stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #2271b1;"><?php echo esc_html( $stats['scheduled'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Scheduled', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php echo esc_html( $stats['sent'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Sent', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #d63638;"><?php echo esc_html( $stats['failed'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Failed', 'kura-ai-booking-free' ); ?></p>
			</div>
		</div>

		<!-- Upcoming Bookings Without Reminders -->
		<?php if ( $upcoming_bookings ) : ?>
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Upcoming Bookings (No Reminder Sent)', 'kura-ai-booking-free' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'Booking ID', 'kura-ai-booking-free' ); ?></th>
						<th><?php echo esc_html__( 'Customer', 'kura-ai-booking-free' ); ?></th>
						<th><?php echo esc_html__( 'Service/Event', 'kura-ai-booking-free' ); ?></th>
						<th><?php echo esc_html__( 'Date & Time', 'kura-ai-booking-free' ); ?></th>
						<th><?php echo esc_html__( 'Actions', 'kura-ai-booking-free' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $upcoming_bookings as $booking ) : ?>
						<tr>
							<td>#<?php echo esc_html( $booking->id ); ?></td>
							<td><?php echo esc_html( $booking->customer_name ); ?></td>
							<td><?php echo esc_html( $booking->item_name ); ?></td>
							<td>
								<?php
								echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->booking_date ) ) );
								echo ' ' . esc_html( date_i18n( get_option( 'time_format' ), strtotime( $booking->booking_time ) ) );
								?>
							</td>
							<td>
								<form method="post" style="display: inline;">
									<?php wp_nonce_field( 'kab_send_reminder' ); ?>
									<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
									<input type="submit" name="kab_send_reminder" class="button button-small button-primary" value="<?php echo esc_attr__( 'Send Now', 'kura-ai-booking-free' ); ?>">
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<!-- Recent Reminders -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Recent Reminders', 'kura-ai-booking-free' ); ?></h2>

			<?php if ( $reminders ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Booking ID', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Customer', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Service/Event', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Booking Date', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Type', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Sent At', 'kura-ai-booking-free' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $reminders as $reminder ) : ?>
							<tr>
								<td>#<?php echo esc_html( $reminder->booking_id ); ?></td>
								<td><?php echo esc_html( $reminder->customer_name ); ?></td>
								<td><?php echo esc_html( $reminder->item_name ); ?></td>
								<td>
									<?php
									if ( $reminder->booking_date ) {
										echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $reminder->booking_date ) ) );
										echo ' ' . esc_html( date_i18n( get_option( 'time_format' ), strtotime( $reminder->booking_time ) ) );
									}
									?>
								</td>
								<td><?php echo esc_html( ucfirst( $reminder->reminder_type ) ); ?></td>
								<td>
									<?php
									$status_colors = array(
										'scheduled' => '#2271b1',
										'sent' => '#00a32a',
										'failed' => '#d63638',
									);
									$color = isset( $status_colors[ $reminder->status ] ) ? $status_colors[ $reminder->status ] : '#646970';
									?>
									<span style="color: <?php echo esc_attr( $color ); ?>; font-weight: 600;">
										<?php echo esc_html( ucfirst( $reminder->status ) ); ?>
									</span>
								</td>
								<td>
									<?php
									if ( $reminder->sent_at ) {
										echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $reminder->sent_at ) ) );
									} else {
										echo '&mdash;';
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php echo esc_html__( 'No reminders found.', 'kura-ai-booking-free' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Cron Information -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Automatic Reminder System', 'kura-ai-booking-free' ); ?></h2>
			<p><?php echo esc_html__( 'Email reminders are sent automatically 24 hours before each booking. The system runs hourly via WordPress cron.', 'kura-ai-booking-free' ); ?></p>

			<?php
			$next_run = wp_next_scheduled( 'kab_send_reminders' );
			if ( $next_run ) :
			?>
				<p>
					<strong><?php echo esc_html__( 'Next Scheduled Run:', 'kura-ai-booking-free' ); ?></strong>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_run ) ); ?>
				</p>
			<?php else : ?>
				<p style="color: #d63638;">
					<strong><?php echo esc_html__( 'Warning:', 'kura-ai-booking-free' ); ?></strong>
					<?php echo esc_html__( 'Automatic reminders are not scheduled. Please deactivate and reactivate the plugin.', 'kura-ai-booking-free' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</div>

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
