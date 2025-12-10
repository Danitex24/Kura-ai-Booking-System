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
		add_shortcode( 'kab_booking_form', array( __CLASS__, 'booking_form_shortcode' ) );
		add_shortcode( 'kab_waitlist_form', array( __CLASS__, 'waitlist_form_shortcode' ) );
		add_shortcode( 'kab_review_form', array( __CLASS__, 'review_form_shortcode' ) );
		add_shortcode( 'kab_cancel_booking', array( __CLASS__, 'cancel_booking_shortcode' ) );
		add_shortcode( 'kab_reviews_display', array( __CLASS__, 'reviews_display_shortcode' ) );
		add_shortcode( 'kab_services', array( __CLASS__, 'services_shortcode' ) );
		add_shortcode( 'kab_events_calendar', array( __CLASS__, 'events_calendar_shortcode' ) );
		add_shortcode( 'kab_my_bookings', array( __CLASS__, 'my_bookings_shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_scripts' ) );
		add_action( 'wp_ajax_kab_submit_waitlist', array( __CLASS__, 'handle_waitlist_submission' ) );
		add_action( 'wp_ajax_nopriv_kab_submit_waitlist', array( __CLASS__, 'handle_waitlist_submission' ) );
		add_action( 'wp_ajax_kab_submit_review', array( __CLASS__, 'handle_review_submission' ) );
		add_action( 'wp_ajax_nopriv_kab_submit_review', array( __CLASS__, 'handle_review_submission' ) );
		add_action( 'wp_ajax_kab_cancel_booking', array( __CLASS__, 'handle_cancellation' ) );
		add_action( 'wp_ajax_kab_submit_booking', array( __CLASS__, 'handle_booking_submission' ) );
		add_action( 'wp_ajax_nopriv_kab_submit_booking', array( __CLASS__, 'handle_booking_submission' ) );
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

	/**
	 * Booking Form Shortcode
	 * Usage: [kab_booking_form type="service" id="1"]
	 */
	public static function booking_form_shortcode( $atts ) {
		global $wpdb;

		$atts = shortcode_atts( array(
			'type' => 'service',
			'id' => 0,
		), $atts );

		// Load required classes
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';

		$services_model = new KAB_Services();
		$events_model = new KAB_Events();

		ob_start();
		?>
		<div class="kab-form-container kab-booking-form">
			<h3><?php echo esc_html__( 'Book Your Appointment', 'kura-ai-booking-free' ); ?></h3>

			<form class="kab-booking-submission">
				<div class="kab-form-group">
					<label for="booking_type"><?php echo esc_html__( 'Booking Type', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<select id="booking_type" name="booking_type" required>
						<option value=""><?php echo esc_html__( 'Select type', 'kura-ai-booking-free' ); ?></option>
						<option value="service" <?php selected( $atts['type'], 'service' ); ?>><?php echo esc_html__( 'Service', 'kura-ai-booking-free' ); ?></option>
						<option value="event" <?php selected( $atts['type'], 'event' ); ?>><?php echo esc_html__( 'Event', 'kura-ai-booking-free' ); ?></option>
					</select>
				</div>

				<div class="kab-form-group kab-service-select" style="<?php echo ( 'service' !== $atts['type'] ) ? 'display:none;' : ''; ?>">
					<label for="service_id"><?php echo esc_html__( 'Select Service', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<select id="service_id" name="service_id">
						<option value=""><?php echo esc_html__( 'Select a service', 'kura-ai-booking-free' ); ?></option>
						<?php
						$services = $services_model->get_services();
						foreach ( $services as $service ) :
						?>
							<option value="<?php echo esc_attr( $service['id'] ); ?>" <?php selected( $atts['id'], $service['id'] ); ?> data-price="<?php echo esc_attr( $service['price'] ); ?>" data-duration="<?php echo esc_attr( $service['duration'] ); ?>">
								<?php echo esc_html( $service['name'] ); ?> - <?php echo esc_html( $service['currency'] . ' ' . number_format( $service['price'], 2 ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="kab-form-group kab-event-select" style="<?php echo ( 'event' !== $atts['type'] ) ? 'display:none;' : ''; ?>">
					<label for="event_id"><?php echo esc_html__( 'Select Event', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<select id="event_id" name="event_id">
						<option value=""><?php echo esc_html__( 'Select an event', 'kura-ai-booking-free' ); ?></option>
						<?php
						$events = $events_model->get_events( array( 'number' => 100 ) );
						foreach ( $events as $event ) :
						?>
							<option value="<?php echo esc_attr( $event['id'] ); ?>" <?php selected( $atts['id'], $event['id'] ); ?> data-price="<?php echo esc_attr( $event['price'] ); ?>" data-date="<?php echo esc_attr( $event['event_date'] ); ?>">
								<?php echo esc_html( $event['name'] ); ?> - <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event['event_date'] ) ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="kab-form-group">
					<label for="booking_date"><?php echo esc_html__( 'Date', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="date" id="booking_date" name="booking_date" required min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
				</div>

				<div class="kab-form-group">
					<label for="booking_time"><?php echo esc_html__( 'Time', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="time" id="booking_time" name="booking_time" required>
				</div>

				<div class="kab-form-group">
					<label for="customer_name"><?php echo esc_html__( 'Your Name', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="text" id="customer_name" name="customer_name" required>
				</div>

				<div class="kab-form-group">
					<label for="customer_email"><?php echo esc_html__( 'Email Address', 'kura-ai-booking-free' ); ?> <span class="required">*</span></label>
					<input type="email" id="customer_email" name="customer_email" required>
				</div>

				<div class="kab-form-group">
					<label for="customer_phone"><?php echo esc_html__( 'Phone Number', 'kura-ai-booking-free' ); ?></label>
					<input type="tel" id="customer_phone" name="customer_phone">
				</div>

				<div class="kab-form-group">
					<label for="notes"><?php echo esc_html__( 'Additional Notes', 'kura-ai-booking-free' ); ?></label>
					<textarea id="notes" name="notes" rows="3"></textarea>
				</div>

				<div class="kab-form-message"></div>

				<button type="submit" class="kab-btn kab-btn-primary">
					<?php echo esc_html__( 'Book Now', 'kura-ai-booking-free' ); ?>
				</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Services List Shortcode
	 * Usage: [kab_services layout="grid" columns="3"]
	 */
	public static function services_shortcode( $atts ) {
		global $wpdb;

		$atts = shortcode_atts( array(
			'layout' => 'grid',
			'columns' => '3',
			'show_price' => 'true',
		), $atts );

		// Load required class
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';

		$services_model = new KAB_Services();
		$services = $services_model->get_services();

		if ( empty( $services ) ) {
			return '<p>' . esc_html__( 'No services available at the moment.', 'kura-ai-booking-free' ) . '</p>';
		}

		$columns_class = 'kab-columns-' . esc_attr( $atts['columns'] );
		$layout_class = 'kab-layout-' . esc_attr( $atts['layout'] );

		ob_start();
		?>
		<div class="kab-services-container <?php echo esc_attr( $layout_class ); ?>">
			<div class="kab-services-grid <?php echo esc_attr( $columns_class ); ?>">
				<?php foreach ( $services as $service ) : ?>
					<div class="kab-service-card">
						<div class="kab-service-header">
							<h3 class="kab-service-title"><?php echo esc_html( $service['name'] ); ?></h3>
							<?php if ( 'true' === $atts['show_price'] ) : ?>
								<div class="kab-service-price">
									<?php echo esc_html( $service['currency'] . ' ' . number_format( $service['price'], 2 ) ); ?>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $service['description'] ) ) : ?>
							<div class="kab-service-description">
								<?php echo wp_kses_post( wpautop( $service['description'] ) ); ?>
							</div>
						<?php endif; ?>

						<div class="kab-service-meta">
							<span class="kab-service-duration">
								<span class="dashicons dashicons-clock"></span>
								<?php echo esc_html( sprintf( _n( '%d minute', '%d minutes', $service['duration'], 'kura-ai-booking-free' ), $service['duration'] ) ); ?>
							</span>
							<?php if ( isset( $service['avg_rating'] ) && $service['avg_rating'] > 0 ) : ?>
								<span class="kab-service-rating">
									<span class="dashicons dashicons-star-filled"></span>
									<?php echo esc_html( number_format( $service['avg_rating'], 1 ) ); ?>
								</span>
							<?php endif; ?>
						</div>

						<div class="kab-service-actions">
							<a href="<?php echo esc_url( add_query_arg( array( 'service_id' => $service['id'] ), get_permalink() ) ); ?>" class="kab-btn kab-btn-primary">
								<?php echo esc_html__( 'Book Now', 'kura-ai-booking-free' ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Events Calendar Shortcode
	 * Usage: [kab_events_calendar view="month"]
	 */
	public static function events_calendar_shortcode( $atts ) {
		global $wpdb;

		$atts = shortcode_atts( array(
			'view' => 'month',
			'category' => '',
		), $atts );

		// Load required class
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';

		$events_model = new KAB_Events();
		$events = $events_model->get_events( array( 'number' => 100 ) );

		// Filter by category if specified.
		if ( ! empty( $atts['category'] ) ) {
			$events = array_filter( $events, function( $event ) use ( $atts ) {
				return isset( $event['tags'] ) && false !== strpos( $event['tags'], $atts['category'] );
			});
		}

		if ( empty( $events ) ) {
			return '<p>' . esc_html__( 'No upcoming events.', 'kura-ai-booking-free' ) . '</p>';
		}

		ob_start();
		?>
		<div class="kab-events-calendar kab-view-<?php echo esc_attr( $atts['view'] ); ?>">
			<div class="kab-events-header">
				<h3><?php echo esc_html__( 'Upcoming Events', 'kura-ai-booking-free' ); ?></h3>
			</div>

			<div class="kab-events-list">
				<?php foreach ( $events as $event ) :
					$event_datetime = strtotime( $event['event_date'] . ' ' . $event['event_time'] );
					$is_past = $event_datetime < time();

					// Get booking count.
					$booked_count = intval( $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings WHERE event_id = %d AND booking_type = 'event' AND status != 'cancelled'",
						$event['id']
					) ) );

					$capacity = intval( $event['capacity'] );
					$available_spots = max( 0, $capacity - $booked_count );
					$is_full = $available_spots <= 0;
				?>
					<div class="kab-event-card <?php echo $is_past ? 'kab-event-past' : ''; ?> <?php echo $is_full ? 'kab-event-full' : ''; ?>">
						<div class="kab-event-date-badge">
							<div class="kab-event-day"><?php echo esc_html( date_i18n( 'd', $event_datetime ) ); ?></div>
							<div class="kab-event-month"><?php echo esc_html( date_i18n( 'M', $event_datetime ) ); ?></div>
						</div>

						<div class="kab-event-details">
							<h4 class="kab-event-title"><?php echo esc_html( $event['name'] ); ?></h4>

							<?php if ( ! empty( $event['description'] ) ) : ?>
								<div class="kab-event-description">
									<?php echo wp_kses_post( wp_trim_words( $event['description'], 30 ) ); ?>
								</div>
							<?php endif; ?>

							<div class="kab-event-meta">
								<span class="kab-event-time">
									<span class="dashicons dashicons-clock"></span>
									<?php echo esc_html( date_i18n( get_option( 'time_format' ), $event_datetime ) ); ?>
									<?php if ( ! empty( $event['event_end_time'] ) ) : ?>
										- <?php echo esc_html( $event['event_end_time'] ); ?>
									<?php endif; ?>
								</span>

								<?php if ( ! empty( $event['location'] ) ) : ?>
									<span class="kab-event-location">
										<span class="dashicons dashicons-location"></span>
										<?php echo esc_html( $event['location'] ); ?>
									</span>
								<?php endif; ?>

								<span class="kab-event-capacity">
									<span class="dashicons dashicons-groups"></span>
									<?php echo esc_html( sprintf( __( '%d / %d spots filled', 'kura-ai-booking-free' ), $booked_count, $capacity ) ); ?>
								</span>

								<?php if ( $event['price'] > 0 ) : ?>
									<span class="kab-event-price">
										<span class="dashicons dashicons-tag"></span>
										<?php echo esc_html( 'USD ' . number_format( $event['price'], 2 ) ); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>

						<div class="kab-event-actions">
							<?php if ( $is_past ) : ?>
								<span class="kab-event-status"><?php echo esc_html__( 'Past Event', 'kura-ai-booking-free' ); ?></span>
							<?php elseif ( $is_full ) : ?>
								<span class="kab-event-status"><?php echo esc_html__( 'Fully Booked', 'kura-ai-booking-free' ); ?></span>
							<?php else : ?>
								<a href="<?php echo esc_url( add_query_arg( array( 'event_id' => $event['id'] ), get_permalink() ) ); ?>" class="kab-btn kab-btn-primary">
									<?php echo esc_html__( 'Book Now', 'kura-ai-booking-free' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * My Bookings Shortcode
	 * Usage: [kab_my_bookings status="all"]
	 */
	public static function my_bookings_shortcode( $atts ) {
		global $wpdb;

		if ( ! is_user_logged_in() ) {
			return '<div class="kab-login-required"><p>' . esc_html__( 'Please log in to view your bookings.', 'kura-ai-booking-free' ) . '</p><a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="kab-btn kab-btn-primary">' . esc_html__( 'Log In', 'kura-ai-booking-free' ) . '</a></div>';
		}

		$atts = shortcode_atts( array(
			'status' => 'all',
		), $atts );

		$user_id = get_current_user_id();

		// Build query.
		$where = $wpdb->prepare( 'WHERE user_id = %d', $user_id );
		if ( 'all' !== $atts['status'] ) {
			$where .= $wpdb->prepare( ' AND status = %s', $atts['status'] );
		}

		$bookings = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}kab_bookings {$where} ORDER BY created_at DESC",
			ARRAY_A
		);

		ob_start();
		?>
		<div class="kab-my-bookings-container">
			<div class="kab-my-bookings-header">
				<h3><?php echo esc_html__( 'My Bookings', 'kura-ai-booking-free' ); ?></h3>
			</div>

			<?php if ( empty( $bookings ) ) : ?>
				<div class="kab-no-bookings">
					<p><?php echo esc_html__( 'You have no bookings yet.', 'kura-ai-booking-free' ); ?></p>
				</div>
			<?php else : ?>
				<div class="kab-bookings-list">
					<?php foreach ( $bookings as $booking ) :
						$booking_datetime = strtotime( $booking['booking_date'] . ' ' . $booking['booking_time'] );
						$is_upcoming = $booking_datetime >= time();
						$status_class = 'kab-status-' . $booking['status'];

						// Get service or event details.
						$item_name = '';
						$item_price = 0;
						if ( 'service' === $booking['booking_type'] && $booking['service_id'] ) {
							$service = $wpdb->get_row( $wpdb->prepare(
								"SELECT name, price FROM {$wpdb->prefix}kab_services WHERE id = %d",
								$booking['service_id']
							), ARRAY_A );
							if ( $service ) {
								$item_name = $service['name'];
								$item_price = $service['price'];
							}
						} elseif ( 'event' === $booking['booking_type'] && $booking['event_id'] ) {
							$event = $wpdb->get_row( $wpdb->prepare(
								"SELECT name, price FROM {$wpdb->prefix}kab_events WHERE id = %d",
								$booking['event_id']
							), ARRAY_A );
							if ( $event ) {
								$item_name = $event['name'];
								$item_price = $event['price'];
							}
						}
					?>
						<div class="kab-booking-card <?php echo esc_attr( $status_class ); ?>">
							<div class="kab-booking-header">
								<div class="kab-booking-type-badge">
									<?php echo esc_html( ucfirst( $booking['booking_type'] ) ); ?>
								</div>
								<div class="kab-booking-status">
									<?php echo esc_html( ucfirst( $booking['status'] ) ); ?>
								</div>
							</div>

							<div class="kab-booking-body">
								<h4 class="kab-booking-title"><?php echo esc_html( $item_name ); ?></h4>

								<div class="kab-booking-details">
									<div class="kab-booking-detail">
										<span class="dashicons dashicons-calendar"></span>
										<strong><?php echo esc_html__( 'Date:', 'kura-ai-booking-free' ); ?></strong>
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), $booking_datetime ) ); ?>
									</div>

									<div class="kab-booking-detail">
										<span class="dashicons dashicons-clock"></span>
										<strong><?php echo esc_html__( 'Time:', 'kura-ai-booking-free' ); ?></strong>
										<?php echo esc_html( date_i18n( get_option( 'time_format' ), $booking_datetime ) ); ?>
									</div>

									<?php if ( $item_price > 0 ) : ?>
										<div class="kab-booking-detail">
											<span class="dashicons dashicons-tag"></span>
											<strong><?php echo esc_html__( 'Price:', 'kura-ai-booking-free' ); ?></strong>
											<?php echo esc_html( 'USD ' . number_format( $item_price, 2 ) ); ?>
										</div>
									<?php endif; ?>

									<div class="kab-booking-detail">
										<span class="dashicons dashicons-tickets-alt"></span>
										<strong><?php echo esc_html__( 'Booking ID:', 'kura-ai-booking-free' ); ?></strong>
										<?php echo esc_html( $booking['id'] ); ?>
									</div>

									<?php if ( ! empty( $booking['ticket_id'] ) ) : ?>
										<div class="kab-booking-detail">
											<span class="dashicons dashicons-admin-network"></span>
											<strong><?php echo esc_html__( 'Ticket ID:', 'kura-ai-booking-free' ); ?></strong>
											<?php echo esc_html( $booking['ticket_id'] ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<div class="kab-booking-actions">
								<?php if ( $is_upcoming && 'cancelled' !== $booking['status'] ) : ?>
									<a href="<?php echo esc_url( add_query_arg( array( 'cancel_booking_id' => $booking['id'] ), get_permalink() ) ); ?>" class="kab-btn kab-btn-secondary">
										<?php echo esc_html__( 'Cancel Booking', 'kura-ai-booking-free' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( ! empty( $booking['ticket_id'] ) ) : ?>
									<a href="<?php echo esc_url( add_query_arg( array( 'view_ticket' => $booking['ticket_id'] ), get_permalink() ) ); ?>" class="kab-btn kab-btn-primary">
										<?php echo esc_html__( 'View Ticket', 'kura-ai-booking-free' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle Booking Submission
	 */
	public static function handle_booking_submission() {
		check_ajax_referer( 'kab_frontend_forms', 'nonce' );

		$data = array(
			'booking_type' => sanitize_text_field( $_POST['booking_type'] ),
			'service_id' => isset( $_POST['service_id'] ) ? intval( $_POST['service_id'] ) : null,
			'event_id' => isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : null,
			'booking_date' => sanitize_text_field( $_POST['booking_date'] ),
			'booking_time' => sanitize_text_field( $_POST['booking_time'] ),
			'customer_name' => sanitize_text_field( $_POST['customer_name'] ),
			'customer_email' => sanitize_email( $_POST['customer_email'] ),
			'customer_phone' => sanitize_text_field( $_POST['customer_phone'] ),
			'notes' => sanitize_textarea_field( $_POST['notes'] ),
		);

		$result = KAB_Bookings::create_booking( $data );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Booking successful! You will receive a confirmation email with your ticket shortly.', 'kura-ai-booking-free' ),
				'booking_id' => $result,
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to create booking. The slot may no longer be available or you may have already booked this time.', 'kura-ai-booking-free' ),
			) );
		}
	}
}

KAB_Frontend_Forms::init();
