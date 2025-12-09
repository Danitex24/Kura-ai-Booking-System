<?php
/**
 * Frontend Forms for Customer Interaction
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Frontend_Forms {

	public static function init() {
		add_shortcode( 'kab_waitlist_form', array( __CLASS__, 'waitlist_form_shortcode' ) );
		add_shortcode( 'kab_review_form', array( __CLASS__, 'review_form_shortcode' ) );
		add_shortcode( 'kab_cancel_booking', array( __CLASS__, 'cancel_booking_shortcode' ) );
		add_shortcode( 'kab_reviews_display', array( __CLASS__, 'reviews_display_shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_scripts' ) );
		add_action( 'wp_ajax_kab_submit_waitlist', array( __CLASS__, 'handle_waitlist_submission' ) );
		add_action( 'wp_ajax_nopriv_kab_submit_waitlist', array( __CLASS__, 'handle_waitlist_submission' ) );
		add_action( 'wp_ajax_kab_submit_review', array( __CLASS__, 'handle_review_submission' ) );
		add_action( 'wp_ajax_nopriv_kab_submit_review', array( __CLASS__, 'handle_review_submission' ) );
		add_action( 'wp_ajax_kab_cancel_booking', array( __CLASS__, 'handle_cancellation' ) );
	}

	public static function enqueue_frontend_scripts() {
		$settings = get_option( 'kab_settings', array() );
		$primary_color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#E67E22';
		$secondary_color = isset( $settings['secondary_color'] ) ? $settings['secondary_color'] : '#628141';

		wp_enqueue_style( 'kab-frontend-forms', KAB_FREE_PLUGIN_URL . 'assets/css/frontend-forms.css', array(), KAB_VERSION );

		// Add inline CSS for brand colors
		$custom_css = "
			.kab-form-container .kab-btn-primary {
				background-color: {$primary_color};
				border-color: {$primary_color};
			}
			.kab-form-container .kab-btn-primary:hover {
				background-color: " . self::adjust_brightness( $primary_color, -20 ) . ";
			}
			.kab-form-container .kab-star-rating .star.active,
			.kab-form-container .kab-star-rating .star:hover {
				color: #FFC107;
			}
			.kab-review-card .review-rating {
				color: #FFC107;
			}
		";
		wp_add_inline_style( 'kab-frontend-forms', $custom_css );

		wp_enqueue_script( 'kab-frontend-forms', KAB_FREE_PLUGIN_URL . 'assets/js/frontend-forms.js', array( 'jquery' ), KAB_VERSION, true );
		wp_localize_script( 'kab-frontend-forms', 'kabForms', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'kab_frontend_forms' ),
		) );
	}

	private static function adjust_brightness( $hex, $steps ) {
		$hex = str_replace( '#', '', $hex );
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		$r = max( 0, min( 255, $r + $steps ) );
		$g = max( 0, min( 255, $g + $steps ) );
		$b = max( 0, min( 255, $b + $steps ) );

		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT )
			. str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT )
			. str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}

	/**
	 * Waitlist Form Shortcode
	 * Usage: [kab_waitlist_form item_type="service" item_id="1"]
	 */
	public static function waitlist_form_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'item_type' => 'service',
			'item_id' => 0,
		), $atts );

		ob_start();
		?>
		<div class="kab-form-container kab-waitlist-form">
			<h3><?php echo esc_html__( 'Join the Waitlist', 'kura-ai-booking-free' ); ?></h3>
			<p><?php echo esc_html__( 'This service/event is currently at full capacity. Join our waitlist and we\'ll notify you when a spot becomes available!', 'kura-ai-booking-free' ); ?></p>

			<form class="kab-waitlist-submission" data-item-type="<?php echo esc_attr( $atts['item_type'] ); ?>" data-item-id="<?php echo esc_attr( $atts['item_id'] ); ?>">
				<div class="kab-form-group">
					<label for="waitlist_name"><?php echo esc_html__( 'Your Name', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="text" id="waitlist_name" name="customer_name" required>
				</div>

				<div class="kab-form-group">
					<label for="waitlist_email"><?php echo esc_html__( 'Email Address', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="email" id="waitlist_email" name="customer_email" required>
				</div>

				<div class="kab-form-group">
					<label for="waitlist_phone"><?php echo esc_html__( 'Phone Number', 'kura-ai-booking-free' ); ?></label>
					<input type="tel" id="waitlist_phone" name="customer_phone">
				</div>

				<div class="kab-form-group">
					<label for="waitlist_date"><?php echo esc_html__( 'Preferred Date (Optional)', 'kura-ai-booking-free' ); ?></label>
					<input type="date" id="waitlist_date" name="booking_date">
					<small><?php echo esc_html__( 'Leave blank if you\'re flexible on dates', 'kura-ai-booking-free' ); ?></small>
				</div>

				<div class="kab-form-message"></div>

				<button type="submit" class="kab-btn kab-btn-primary">
					<?php echo esc_html__( 'Join Waitlist', 'kura-ai-booking-free' ); ?>
				</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Review Form Shortcode
	 * Usage: [kab_review_form booking_id="123"]
	 */
	public static function review_form_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'booking_id' => 0,
		), $atts );

		if ( ! $atts['booking_id'] ) {
			return '<p>' . esc_html__( 'Invalid booking ID.', 'kura-ai-booking-free' ) . '</p>';
		}

		ob_start();
		?>
		<div class="kab-form-container kab-review-form">
			<h3><?php echo esc_html__( 'Leave a Review', 'kura-ai-booking-free' ); ?></h3>
			<p><?php echo esc_html__( 'Share your experience with us!', 'kura-ai-booking-free' ); ?></p>

			<form class="kab-review-submission" data-booking-id="<?php echo esc_attr( $atts['booking_id'] ); ?>">
				<div class="kab-form-group">
					<label><?php echo esc_html__( 'Rating', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<div class="kab-star-rating">
						<span class="star" data-rating="1">☆</span>
						<span class="star" data-rating="2">☆</span>
						<span class="star" data-rating="3">☆</span>
						<span class="star" data-rating="4">☆</span>
						<span class="star" data-rating="5">☆</span>
					</div>
					<input type="hidden" name="rating" id="review_rating" required>
				</div>

				<div class="kab-form-group">
					<label for="review_title"><?php echo esc_html__( 'Review Title', 'kura-ai-booking-free' ); ?></label>
					<input type="text" id="review_title" name="title" placeholder="<?php echo esc_attr__( 'Summarize your experience', 'kura-ai-booking-free' ); ?>">
				</div>

				<div class="kab-form-group">
					<label for="review_comment"><?php echo esc_html__( 'Your Review', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<textarea id="review_comment" name="comment" rows="5" required placeholder="<?php echo esc_attr__( 'Tell us about your experience...', 'kura-ai-booking-free' ); ?>"></textarea>
				</div>

				<div class="kab-form-group">
					<label for="review_name"><?php echo esc_html__( 'Your Name', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="text" id="review_name" name="customer_name" required>
				</div>

				<div class="kab-form-group">
					<label for="review_email"><?php echo esc_html__( 'Email Address', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="email" id="review_email" name="customer_email" required>
				</div>

				<div class="kab-form-message"></div>

				<button type="submit" class="kab-btn kab-btn-primary">
					<?php echo esc_html__( 'Submit Review', 'kura-ai-booking-free' ); ?>
				</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Cancel Booking Shortcode
	 * Usage: [kab_cancel_booking]
	 */
	public static function cancel_booking_shortcode( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to cancel your booking.', 'kura-ai-booking-free' ) . '</p>';
		}

		ob_start();
		?>
		<div class="kab-form-container kab-cancel-form">
			<h3><?php echo esc_html__( 'Cancel Booking', 'kura-ai-booking-free' ); ?></h3>
			<p><?php echo esc_html__( 'Enter your booking ID to request a cancellation.', 'kura-ai-booking-free' ); ?></p>

			<form class="kab-cancellation-submission">
				<div class="kab-form-group">
					<label for="cancel_booking_id"><?php echo esc_html__( 'Booking ID', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="text" id="cancel_booking_id" name="booking_id" required placeholder="<?php echo esc_attr__( 'e.g., 123', 'kura-ai-booking-free' ); ?>">
				</div>

				<div class="kab-form-group">
					<label for="cancel_reason"><?php echo esc_html__( 'Reason for Cancellation', 'kura-ai-booking-free' ); ?></label>
					<textarea id="cancel_reason" name="reason" rows="4" placeholder="<?php echo esc_attr__( 'Optional - tell us why you\'re cancelling', 'kura-ai-booking-free' ); ?>"></textarea>
				</div>

				<div class="kab-cancellation-policy" style="background: #f9f9f9; padding: 15px; border-left: 4px solid #2271b1; margin: 15px 0;">
					<h4><?php echo esc_html__( 'Cancellation Policy', 'kura-ai-booking-free' ); ?></h4>
					<ul style="margin: 10px 0; padding-left: 20px;">
						<li><?php echo esc_html__( 'More than 48 hours notice: 100% refund', 'kura-ai-booking-free' ); ?></li>
						<li><?php echo esc_html__( '24-48 hours notice: 50% refund', 'kura-ai-booking-free' ); ?></li>
						<li><?php echo esc_html__( 'Less than 24 hours notice: No refund', 'kura-ai-booking-free' ); ?></li>
					</ul>
				</div>

				<div class="kab-form-message"></div>

				<button type="submit" class="kab-btn kab-btn-primary">
					<?php echo esc_html__( 'Request Cancellation', 'kura-ai-booking-free' ); ?>
				</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Reviews Display Shortcode
	 * Usage: [kab_reviews_display item_type="service" item_id="1"]
	 */
	public static function reviews_display_shortcode( $atts ) {
		global $wpdb;

		$atts = shortcode_atts( array(
			'item_type' => 'service',
			'item_id' => 0,
			'limit' => 10,
		), $atts );

		if ( ! $atts['item_id'] ) {
			return '';
		}

		// Get reviews
		$reviews = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kab_reviews
			WHERE item_type = %s AND item_id = %d AND status = 'approved'
			ORDER BY created_at DESC
			LIMIT %d",
			$atts['item_type'],
			$atts['item_id'],
			$atts['limit']
		) );

		// Get rating stats
		$stats = KAB_Reviews::get_rating_stats( $atts['item_type'], $atts['item_id'] );

		ob_start();
		?>
		<div class="kab-reviews-container">
			<?php if ( $stats['count'] > 0 ) : ?>
				<div class="kab-reviews-summary" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center;">
					<div style="font-size: 48px; font-weight: bold; color: #333;">
						<?php echo esc_html( number_format( $stats['average'], 1 ) ); ?>
					</div>
					<div style="font-size: 24px; color: #FFC107; margin: 10px 0;">
						<?php echo str_repeat( '★', floor( $stats['average'] ) ) . str_repeat( '☆', 5 - floor( $stats['average'] ) ); ?>
					</div>
					<div style="color: #646970;">
						<?php echo esc_html( sprintf( _n( '%s review', '%s reviews', $stats['count'], 'kura-ai-booking-free' ), number_format_i18n( $stats['count'] ) ) ); ?>
					</div>

					<!-- Rating Distribution -->
					<?php if ( ! empty( $stats['distribution'] ) ) : ?>
						<div style="max-width: 400px; margin: 20px auto 0;">
							<?php for ( $i = 5; $i >= 1; $i-- ) :
								$count = isset( $stats['distribution'][ $i ] ) ? $stats['distribution'][ $i ] : 0;
								$percentage = $stats['count'] > 0 ? ( $count / $stats['count'] ) * 100 : 0;
							?>
								<div style="display: flex; align-items: center; margin: 5px 0;">
									<span style="width: 60px; text-align: right; margin-right: 10px;"><?php echo esc_html( $i ); ?> ★</span>
									<div style="flex: 1; background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">
										<div style="background: #FFC107; height: 100%; width: <?php echo esc_attr( $percentage ); ?>%;"></div>
									</div>
									<span style="width: 40px; text-align: right; margin-left: 10px; font-size: 12px; color: #646970;">
										<?php echo esc_html( $count ); ?>
									</span>
								</div>
							<?php endfor; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="kab-reviews-list">
				<?php if ( $reviews ) : ?>
					<?php foreach ( $reviews as $review ) : ?>
						<div class="kab-review-card" style="background: #fff; border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
							<div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
								<div>
									<strong style="font-size: 16px;"><?php echo esc_html( $review->customer_name ); ?></strong>
									<?php if ( $review->title ) : ?>
										<div style="color: #646970; margin-top: 5px;"><?php echo esc_html( $review->title ); ?></div>
									<?php endif; ?>
								</div>
								<div style="text-align: right;">
									<div class="review-rating" style="font-size: 18px;">
										<?php echo str_repeat( '★', $review->rating ) . str_repeat( '☆', 5 - $review->rating ); ?>
									</div>
									<small style="color: #646970;">
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $review->created_at ) ) ); ?>
									</small>
								</div>
							</div>

							<?php if ( $review->comment ) : ?>
								<div style="line-height: 1.6; color: #333;">
									<?php echo nl2br( esc_html( $review->comment ) ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p style="text-align: center; color: #646970; padding: 40px 0;">
						<?php echo esc_html__( 'No reviews yet. Be the first to leave a review!', 'kura-ai-booking-free' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle Waitlist Submission
	 */
	public static function handle_waitlist_submission() {
		check_ajax_referer( 'kab_frontend_forms', 'nonce' );

		$data = array(
			'item_type' => sanitize_text_field( $_POST['item_type'] ),
			'item_id' => intval( $_POST['item_id'] ),
			'customer_name' => sanitize_text_field( $_POST['customer_name'] ),
			'customer_email' => sanitize_email( $_POST['customer_email'] ),
			'customer_phone' => sanitize_text_field( $_POST['customer_phone'] ),
			'booking_date' => ! empty( $_POST['booking_date'] ) ? sanitize_text_field( $_POST['booking_date'] ) : null,
		);

		$result = KAB_Waitlist::add_to_waitlist( $data );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'You have been added to the waitlist! We\'ll notify you when a spot becomes available.', 'kura-ai-booking-free' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to add you to the waitlist. Please try again.', 'kura-ai-booking-free' ),
			) );
		}
	}

	/**
	 * Handle Review Submission
	 */
	public static function handle_review_submission() {
		check_ajax_referer( 'kab_frontend_forms', 'nonce' );

		$data = array(
			'booking_id' => intval( $_POST['booking_id'] ),
			'rating' => intval( $_POST['rating'] ),
			'title' => sanitize_text_field( $_POST['title'] ),
			'comment' => sanitize_textarea_field( $_POST['comment'] ),
			'customer_name' => sanitize_text_field( $_POST['customer_name'] ),
			'customer_email' => sanitize_email( $_POST['customer_email'] ),
		);

		$result = KAB_Reviews::submit_review( $data );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Thank you for your review! It will be published after moderation.', 'kura-ai-booking-free' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to submit review. You may have already reviewed this booking.', 'kura-ai-booking-free' ),
			) );
		}
	}

	/**
	 * Handle Cancellation
	 */
	public static function handle_cancellation() {
		check_ajax_referer( 'kab_frontend_forms', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array(
				'message' => __( 'You must be logged in to cancel a booking.', 'kura-ai-booking-free' ),
			) );
		}

		$booking_id = intval( $_POST['booking_id'] );
		$reason = sanitize_textarea_field( $_POST['reason'] );

		$result = KAB_Cancellations::request_cancellation( $booking_id, array(
			'reason' => $reason,
			'cancelled_by' => 'customer',
		) );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Your cancellation request has been processed. You will receive a confirmation email shortly.', 'kura-ai-booking-free' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to process cancellation. Please contact support.', 'kura-ai-booking-free' ),
			) );
		}
	}
}

KAB_Frontend_Forms::init();
