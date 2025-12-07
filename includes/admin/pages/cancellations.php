<?php
/**
 * Cancellations & Refunds Admin Page
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Handle refund processing
if ( isset( $_POST['kab_process_refund'] ) && check_admin_referer( 'kab_process_refund' ) ) {
	$cancellation_id = intval( $_POST['cancellation_id'] );
	$result = KAB_Cancellations::process_refund( $cancellation_id );
	if ( $result ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Refund marked as processed!', 'kura-ai-booking-free' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to process refund.', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Fetch cancellations
$cancellations = $wpdb->get_results(
	"SELECT c.*, b.booking_date, b.booking_time, b.booking_type,
		CASE
			WHEN b.service_id IS NOT NULL THEN s.name
			WHEN b.event_id IS NOT NULL THEN e.name
			ELSE 'N/A'
		END as item_name,
		u.display_name as customer_name,
		u.user_email as customer_email
	FROM {$wpdb->prefix}kab_cancellations c
	LEFT JOIN {$wpdb->prefix}kab_bookings b ON c.booking_id = b.id
	LEFT JOIN {$wpdb->prefix}kab_services s ON b.service_id = s.id
	LEFT JOIN {$wpdb->prefix}kab_events e ON b.event_id = e.id
	LEFT JOIN {$wpdb->prefix}users u ON b.user_id = u.ID
	ORDER BY c.created_at DESC
	LIMIT 100"
);

// Statistics
$stats = array(
	'total' => count( $cancellations ),
	'pending_refunds' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_cancellations WHERE refund_status = 'pending' AND refund_amount > 0" ),
	'processed_refunds' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_cancellations WHERE refund_status = 'processed'" ),
	'total_refunded' => $wpdb->get_var( "SELECT SUM(refund_amount) FROM {$wpdb->prefix}kab_cancellations WHERE refund_status = 'processed'" ),
);

// Filter
$filter_status = isset( $_GET['filter_status'] ) ? sanitize_text_field( $_GET['filter_status'] ) : 'all';
if ( $filter_status !== 'all' ) {
	$cancellations = array_filter( $cancellations, function( $c ) use ( $filter_status ) {
		return $c->refund_status === $filter_status;
	});
}
?>

<div class="wrap">
	<h1><?php echo esc_html__( 'Cancellations & Refunds', 'kura-ai-booking-free' ); ?></h1>

	<div class="kab-admin-container">
		<!-- Statistics Cards -->
		<div class="kab-stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #646970;"><?php echo esc_html( $stats['total'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Total Cancellations', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #dba617;"><?php echo esc_html( $stats['pending_refunds'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Pending Refunds', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php echo esc_html( $stats['processed_refunds'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Processed', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #2271b1;"><?php echo esc_html( kab_format_currency( $stats['total_refunded'] ?: 0 ) ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Total Refunded', 'kura-ai-booking-free' ); ?></p>
			</div>
		</div>

		<!-- Filters -->
		<div class="kab-card">
			<form method="get" style="margin-bottom: 0;">
				<input type="hidden" name="page" value="kab-cancellations">
				<label for="filter_status"><?php echo esc_html__( 'Filter by Refund Status:', 'kura-ai-booking-free' ); ?></label>
				<select name="filter_status" id="filter_status" onchange="this.form.submit()">
					<option value="all" <?php selected( $filter_status, 'all' ); ?>><?php echo esc_html__( 'All', 'kura-ai-booking-free' ); ?></option>
					<option value="pending" <?php selected( $filter_status, 'pending' ); ?>><?php echo esc_html__( 'Pending', 'kura-ai-booking-free' ); ?></option>
					<option value="processed" <?php selected( $filter_status, 'processed' ); ?>><?php echo esc_html__( 'Processed', 'kura-ai-booking-free' ); ?></option>
					<option value="rejected" <?php selected( $filter_status, 'rejected' ); ?>><?php echo esc_html__( 'Rejected', 'kura-ai-booking-free' ); ?></option>
				</select>
			</form>
		</div>

		<!-- Cancellations List -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Cancellation Requests', 'kura-ai-booking-free' ); ?></h2>

			<?php if ( $cancellations ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Booking ID', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Customer', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Service/Event', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Booking Date', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Cancelled By', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Refund Amount', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Fee', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Date', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Actions', 'kura-ai-booking-free' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cancellations as $cancel ) : ?>
							<tr>
								<td>#<?php echo esc_html( $cancel->booking_id ); ?></td>
								<td>
									<?php echo esc_html( $cancel->customer_name ); ?><br>
									<small><?php echo esc_html( $cancel->customer_email ); ?></small>
								</td>
								<td><?php echo esc_html( $cancel->item_name ); ?></td>
								<td>
									<?php
									if ( $cancel->booking_date ) {
										echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $cancel->booking_date ) ) );
									}
									?>
								</td>
								<td><?php echo esc_html( ucfirst( $cancel->cancelled_by ) ); ?></td>
								<td><?php echo esc_html( kab_format_currency( $cancel->refund_amount ) ); ?></td>
								<td><?php echo esc_html( kab_format_currency( $cancel->cancellation_fee ) ); ?></td>
								<td>
									<?php
									$status_colors = array(
										'pending' => '#dba617',
										'processed' => '#00a32a',
										'rejected' => '#d63638',
									);
									$color = isset( $status_colors[ $cancel->refund_status ] ) ? $status_colors[ $cancel->refund_status ] : '#646970';
									?>
									<span style="color: <?php echo esc_attr( $color ); ?>; font-weight: 600;">
										<?php echo esc_html( ucfirst( $cancel->refund_status ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $cancel->created_at ) ) ); ?></td>
								<td>
									<?php if ( $cancel->refund_status === 'pending' && $cancel->refund_amount > 0 ) : ?>
										<form method="post" style="display: inline;">
											<?php wp_nonce_field( 'kab_process_refund' ); ?>
											<input type="hidden" name="cancellation_id" value="<?php echo esc_attr( $cancel->id ); ?>">
											<input type="submit" name="kab_process_refund" class="button button-small button-primary" value="<?php echo esc_attr__( 'Mark Processed', 'kura-ai-booking-free' ); ?>">
										</form>
									<?php elseif ( $cancel->refund_processed_at ) : ?>
										<small><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $cancel->refund_processed_at ) ) ); ?></small>
									<?php else : ?>
										&mdash;
									<?php endif; ?>
								</td>
							</tr>
							<?php if ( $cancel->reason ) : ?>
								<tr>
									<td colspan="10" style="background: #f9f9f9; padding-left: 40px;">
										<strong><?php echo esc_html__( 'Reason:', 'kura-ai-booking-free' ); ?></strong>
										<?php echo esc_html( $cancel->reason ); ?>
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php echo esc_html__( 'No cancellations found.', 'kura-ai-booking-free' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Cancellation Policy Info -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Cancellation Policy', 'kura-ai-booking-free' ); ?></h2>
			<?php
			$settings = get_option( 'kab_settings', array() );
			$min_hours = isset( $settings['cancellation_min_hours'] ) ? $settings['cancellation_min_hours'] : 24;
			?>
			<p><?php echo esc_html( sprintf( __( 'Minimum notice required: %d hours', 'kura-ai-booking-free' ), $min_hours ) ); ?></p>
			<p><strong><?php echo esc_html__( 'Refund Policy:', 'kura-ai-booking-free' ); ?></strong></p>
			<ul>
				<li><?php echo esc_html__( 'More than 48 hours notice: 100% refund', 'kura-ai-booking-free' ); ?></li>
				<li><?php echo esc_html__( '24-48 hours notice: 50% refund', 'kura-ai-booking-free' ); ?></li>
				<li><?php echo esc_html__( 'Less than 24 hours notice: No refund', 'kura-ai-booking-free' ); ?></li>
			</ul>
			<p class="description"><?php echo esc_html__( 'Note: This is the default policy. You can customize it in plugin settings.', 'kura-ai-booking-free' ); ?></p>
		</div>
	</div>
</div>

<style>
.kab-admin-container {
	max-width: 1400px;
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
