<?php
/**
 * Waitlist Admin Page
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Handle notifications
if ( isset( $_POST['kab_notify_waitlist'] ) && check_admin_referer( 'kab_notify_waitlist' ) ) {
	$entry_id = intval( $_POST['entry_id'] );
	$entry = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}kab_waitlist WHERE id = %d",
		$entry_id
	), ARRAY_A );

	if ( $entry ) {
		$result = KAB_Waitlist::notify_next_in_line( $entry['item_type'], $entry['item_id'], $entry['booking_date'] );
		if ( $result ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Notification sent successfully!', 'kura-ai-booking-free' ) . '</p></div>';
		} else {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to send notification.', 'kura-ai-booking-free' ) . '</p></div>';
		}
	}
}

// Handle removal
if ( isset( $_GET['action'] ) && $_GET['action'] === 'remove' && isset( $_GET['id'] ) && check_admin_referer( 'kab_remove_waitlist_' . intval( $_GET['id'] ) ) ) {
	$id = intval( $_GET['id'] );
	$deleted = $wpdb->delete( $wpdb->prefix . 'kab_waitlist', array( 'id' => $id ), array( '%d' ) );
	if ( $deleted ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Waitlist entry removed successfully!', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Fetch waitlist entries
$waitlist_entries = $wpdb->get_results(
	"SELECT w.*,
		CASE
			WHEN w.item_type = 'service' THEN s.name
			WHEN w.item_type = 'event' THEN e.name
			ELSE 'N/A'
		END as item_name
	FROM {$wpdb->prefix}kab_waitlist w
	LEFT JOIN {$wpdb->prefix}kab_services s ON w.item_type = 'service' AND w.item_id = s.id
	LEFT JOIN {$wpdb->prefix}kab_events e ON w.item_type = 'event' AND w.item_id = e.id
	WHERE w.status = 'active'
	ORDER BY w.item_type, w.item_id, w.priority ASC, w.created_at ASC"
);

// Group by item
$grouped_waitlist = array();
foreach ( $waitlist_entries as $entry ) {
	$key = $entry->item_type . '_' . $entry->item_id;
	if ( ! isset( $grouped_waitlist[ $key ] ) ) {
		$grouped_waitlist[ $key ] = array(
			'item_name' => $entry->item_name,
			'item_type' => $entry->item_type,
			'item_id' => $entry->item_id,
			'entries' => array(),
		);
	}
	$grouped_waitlist[ $key ]['entries'][] = $entry;
}

// Statistics
$stats = array(
	'active' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_waitlist WHERE status = 'active'" ),
	'notified' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_waitlist WHERE status = 'notified'" ),
	'converted' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_waitlist WHERE status = 'converted'" ),
);
?>

<div class="wrap">
	<h1><?php echo esc_html__( 'Waitlist Management', 'kura-ai-booking-free' ); ?></h1>

	<div class="kab-admin-container">
		<!-- Statistics Cards -->
		<div class="kab-stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #2271b1;"><?php echo esc_html( $stats['active'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Active Waitlist', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #dba617;"><?php echo esc_html( $stats['notified'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Notified', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php echo esc_html( $stats['converted'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Converted', 'kura-ai-booking-free' ); ?></p>
			</div>
		</div>

		<!-- Waitlist Entries by Item -->
		<?php if ( $grouped_waitlist ) : ?>
			<?php foreach ( $grouped_waitlist as $group ) : ?>
				<div class="kab-card">
					<h2>
						<?php echo esc_html( $group['item_name'] ); ?>
						<span style="font-size: 14px; color: #646970; font-weight: normal;">
							(<?php echo esc_html( ucfirst( $group['item_type'] ) ); ?>)
						</span>
					</h2>

					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 60px;"><?php echo esc_html__( 'Position', 'kura-ai-booking-free' ); ?></th>
								<th><?php echo esc_html__( 'Customer', 'kura-ai-booking-free' ); ?></th>
								<th><?php echo esc_html__( 'Email', 'kura-ai-booking-free' ); ?></th>
								<th><?php echo esc_html__( 'Phone', 'kura-ai-booking-free' ); ?></th>
								<th><?php echo esc_html__( 'Date', 'kura-ai-booking-free' ); ?></th>
								<th><?php echo esc_html__( 'Added', 'kura-ai-booking-free' ); ?></th>
								<th><?php echo esc_html__( 'Status', 'kura-ai-booking-free' ); ?></th>
								<th><?php echo esc_html__( 'Actions', 'kura-ai-booking-free' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$position = 1;
							foreach ( $group['entries'] as $entry ) :
							?>
								<tr>
									<td><strong>#<?php echo esc_html( $position ); ?></strong></td>
									<td><?php echo esc_html( $entry->customer_name ); ?></td>
									<td><?php echo esc_html( $entry->customer_email ); ?></td>
									<td><?php echo $entry->customer_phone ? esc_html( $entry->customer_phone ) : '&mdash;'; ?></td>
									<td>
										<?php
										if ( $entry->booking_date ) {
											echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $entry->booking_date ) ) );
										} else {
											echo esc_html__( 'Any Date', 'kura-ai-booking-free' );
										}
										?>
									</td>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $entry->created_at ) ) ); ?></td>
									<td>
										<?php
										if ( $entry->notified_at ) {
											echo '<span style="color: #dba617;">' . esc_html__( 'Notified', 'kura-ai-booking-free' ) . '</span>';
										} else {
											echo '<span style="color: #00a32a;">' . esc_html__( 'Active', 'kura-ai-booking-free' ) . '</span>';
										}
										?>
									</td>
									<td>
										<?php if ( ! $entry->notified_at ) : ?>
											<form method="post" style="display: inline;">
												<?php wp_nonce_field( 'kab_notify_waitlist' ); ?>
												<input type="hidden" name="entry_id" value="<?php echo esc_attr( $entry->id ); ?>">
												<input type="submit" name="kab_notify_waitlist" class="button button-small button-primary" value="<?php echo esc_attr__( 'Notify', 'kura-ai-booking-free' ); ?>">
											</form>
										<?php endif; ?>
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'remove', 'id' => $entry->id ) ), 'kab_remove_waitlist_' . $entry->id ) ); ?>"
										   class="button button-small"
										   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to remove this waitlist entry?', 'kura-ai-booking-free' ) ); ?>');">
											<?php echo esc_html__( 'Remove', 'kura-ai-booking-free' ); ?>
										</a>
									</td>
								</tr>
							<?php
								$position++;
							endforeach;
							?>
						</tbody>
					</table>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="kab-card">
				<p><?php echo esc_html__( 'No active waitlist entries found.', 'kura-ai-booking-free' ); ?></p>
			</div>
		<?php endif; ?>

		<!-- Information Box -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'How Waitlist Works', 'kura-ai-booking-free' ); ?></h2>
			<ul>
				<li><?php echo esc_html__( 'Customers are added to the waitlist when a service or event is at full capacity.', 'kura-ai-booking-free' ); ?></li>
				<li><?php echo esc_html__( 'Priority is assigned in the order customers join (first come, first served).', 'kura-ai-booking-free' ); ?></li>
				<li><?php echo esc_html__( 'When a spot becomes available (due to cancellation), the next person in line is automatically notified.', 'kura-ai-booking-free' ); ?></li>
				<li><?php echo esc_html__( 'You can also manually notify waitlist entries using the "Notify" button.', 'kura-ai-booking-free' ); ?></li>
			</ul>
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
