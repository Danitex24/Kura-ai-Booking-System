<?php
/**
 * Reviews & Ratings Admin Page
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Handle moderation
if ( isset( $_POST['kab_moderate_review'] ) && check_admin_referer( 'kab_moderate_review' ) ) {
	$review_id = intval( $_POST['review_id'] );
	$status = sanitize_text_field( $_POST['review_status'] );
	$result = KAB_Reviews::moderate_review( $review_id, $status );
	if ( $result ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Review status updated successfully!', 'kura-ai-booking-free' ) . '</p></div>';
	} else {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to update review status.', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Handle delete
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) && check_admin_referer( 'kab_delete_review_' . intval( $_GET['id'] ) ) ) {
	$id = intval( $_GET['id'] );
	$deleted = $wpdb->delete( $wpdb->prefix . 'kab_reviews', array( 'id' => $id ), array( '%d' ) );
	if ( $deleted ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Review deleted successfully!', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Fetch reviews
$reviews = $wpdb->get_results(
	"SELECT r.*,
		CASE
			WHEN r.item_type = 'service' THEN s.name
			WHEN r.item_type = 'event' THEN e.name
			ELSE 'N/A'
		END as item_name
	FROM {$wpdb->prefix}kab_reviews r
	LEFT JOIN {$wpdb->prefix}kab_services s ON r.item_type = 'service' AND r.item_id = s.id
	LEFT JOIN {$wpdb->prefix}kab_events e ON r.item_type = 'event' AND r.item_id = e.id
	ORDER BY r.created_at DESC
	LIMIT 100"
);

// Statistics
$stats = array(
	'total' => count( $reviews ),
	'approved' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_reviews WHERE status = 'approved'" ),
	'pending' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_reviews WHERE status = 'pending'" ),
	'rejected' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_reviews WHERE status = 'rejected'" ),
	'avg_rating' => $wpdb->get_var( "SELECT AVG(rating) FROM {$wpdb->prefix}kab_reviews WHERE status = 'approved'" ),
);

// Filter
$filter_status = isset( $_GET['filter_status'] ) ? sanitize_text_field( $_GET['filter_status'] ) : 'all';
if ( $filter_status !== 'all' ) {
	$reviews = array_filter( $reviews, function( $r ) use ( $filter_status ) {
		return $r->status === $filter_status;
	});
}
?>

<div class="wrap">
	<h1><?php echo esc_html__( 'Reviews & Ratings', 'kura-ai-booking-free' ); ?></h1>

	<div class="kab-admin-container">
		<!-- Statistics Cards -->
		<div class="kab-stats-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 20px;">
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #646970;"><?php echo esc_html( $stats['total'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Total Reviews', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php echo esc_html( $stats['approved'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Approved', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #dba617;"><?php echo esc_html( $stats['pending'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Pending', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #d63638;"><?php echo esc_html( $stats['rejected'] ); ?></h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Rejected', 'kura-ai-booking-free' ); ?></p>
			</div>
			<div class="kab-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin: 0 0 10px 0; color: #2271b1;">
					<?php echo esc_html( number_format( (float) $stats['avg_rating'], 1 ) ); ?> ★
				</h3>
				<p style="margin: 0; color: #646970;"><?php echo esc_html__( 'Average Rating', 'kura-ai-booking-free' ); ?></p>
			</div>
		</div>

		<!-- Filters -->
		<div class="kab-card">
			<form method="get" style="margin-bottom: 0;">
				<input type="hidden" name="page" value="kab-reviews">
				<label for="filter_status"><?php echo esc_html__( 'Filter by Status:', 'kura-ai-booking-free' ); ?></label>
				<select name="filter_status" id="filter_status" onchange="this.form.submit()">
					<option value="all" <?php selected( $filter_status, 'all' ); ?>><?php echo esc_html__( 'All', 'kura-ai-booking-free' ); ?></option>
					<option value="approved" <?php selected( $filter_status, 'approved' ); ?>><?php echo esc_html__( 'Approved', 'kura-ai-booking-free' ); ?></option>
					<option value="pending" <?php selected( $filter_status, 'pending' ); ?>><?php echo esc_html__( 'Pending', 'kura-ai-booking-free' ); ?></option>
					<option value="rejected" <?php selected( $filter_status, 'rejected' ); ?>><?php echo esc_html__( 'Rejected', 'kura-ai-booking-free' ); ?></option>
				</select>
			</form>
		</div>

		<!-- Reviews List -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Customer Reviews', 'kura-ai-booking-free' ); ?></h2>

			<?php if ( $reviews ) : ?>
				<?php foreach ( $reviews as $review ) : ?>
					<div class="kab-review-item" style="border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 20px; border-radius: 6px;">
						<div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
							<div>
								<strong style="font-size: 16px;"><?php echo esc_html( $review->customer_name ); ?></strong>
								<?php if ( $review->title ) : ?>
									<span style="color: #646970;"> - <?php echo esc_html( $review->title ); ?></span>
								<?php endif; ?>
								<br>
								<small style="color: #646970;"><?php echo esc_html( $review->customer_email ); ?></small>
							</div>
							<div style="text-align: right;">
								<?php
								// Render stars
								for ( $i = 1; $i <= 5; $i++ ) {
									if ( $i <= $review->rating ) {
										echo '<span style="color: #FFC107; font-size: 18px;">★</span>';
									} else {
										echo '<span style="color: #ddd; font-size: 18px;">☆</span>';
									}
								}
								?>
								<br>
								<small style="color: #646970;">
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $review->created_at ) ) ); ?>
								</small>
							</div>
						</div>

						<div style="margin-bottom: 15px;">
							<strong><?php echo esc_html__( 'For:', 'kura-ai-booking-free' ); ?></strong>
							<?php echo esc_html( $review->item_name ); ?>
							<span style="color: #646970;">(<?php echo esc_html( ucfirst( $review->item_type ) ); ?>)</span>
						</div>

						<?php if ( $review->comment ) : ?>
							<div style="background: #f9f9f9; padding: 15px; border-left: 3px solid #2271b1; margin-bottom: 15px;">
								<?php echo nl2br( esc_html( $review->comment ) ); ?>
							</div>
						<?php endif; ?>

						<div style="display: flex; justify-content: space-between; align-items: center;">
							<div>
								<?php
								$status_colors = array(
									'approved' => '#00a32a',
									'pending' => '#dba617',
									'rejected' => '#d63638',
								);
								$color = isset( $status_colors[ $review->status ] ) ? $status_colors[ $review->status ] : '#646970';
								?>
								<strong><?php echo esc_html__( 'Status:', 'kura-ai-booking-free' ); ?></strong>
								<span style="color: <?php echo esc_attr( $color ); ?>; font-weight: 600;">
									<?php echo esc_html( ucfirst( $review->status ) ); ?>
								</span>
							</div>

							<div>
								<?php if ( $review->status !== 'approved' ) : ?>
									<form method="post" style="display: inline;">
										<?php wp_nonce_field( 'kab_moderate_review' ); ?>
										<input type="hidden" name="review_id" value="<?php echo esc_attr( $review->id ); ?>">
										<input type="hidden" name="review_status" value="approved">
										<input type="submit" name="kab_moderate_review" class="button button-small button-primary" value="<?php echo esc_attr__( 'Approve', 'kura-ai-booking-free' ); ?>">
									</form>
								<?php endif; ?>

								<?php if ( $review->status !== 'rejected' ) : ?>
									<form method="post" style="display: inline;">
										<?php wp_nonce_field( 'kab_moderate_review' ); ?>
										<input type="hidden" name="review_id" value="<?php echo esc_attr( $review->id ); ?>">
										<input type="hidden" name="review_status" value="rejected">
										<input type="submit" name="kab_moderate_review" class="button button-small" value="<?php echo esc_attr__( 'Reject', 'kura-ai-booking-free' ); ?>">
									</form>
								<?php endif; ?>

								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $review->id ) ), 'kab_delete_review_' . $review->id ) ); ?>"
								   class="button button-small"
								   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this review?', 'kura-ai-booking-free' ) ); ?>');">
									<?php echo esc_html__( 'Delete', 'kura-ai-booking-free' ); ?>
								</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php echo esc_html__( 'No reviews found.', 'kura-ai-booking-free' ); ?></p>
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
.kab-review-item:hover {
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
