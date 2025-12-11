<?php
/**
 * Shortcodes Reference Page
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KAB_Shortcodes_Page {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	public static function register_page() {
		// Hidden from sidebar - registered in class-kab-admin.php instead
		// This just provides the render callback
		add_submenu_page(
			null,
			__( 'Shortcodes', 'kura-ai-booking-free' ),
			__( 'Shortcodes', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-shortcodes-hidden',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( $hook !== 'kura-ai-booking_page_kab-shortcodes' ) {
			return;
		}

		wp_enqueue_style( 'kab-shortcodes-page', KAB_FREE_PLUGIN_URL . 'assets/css/shortcodes-page.css', array(), KAB_VERSION );
		wp_enqueue_script( 'kab-shortcodes-page', KAB_FREE_PLUGIN_URL . 'assets/js/shortcodes-page.js', array( 'jquery' ), KAB_VERSION, true );
	}

	public static function render_page() {
		$settings = get_option( 'kab_settings', array() );
		$primary_color = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#E67E22';
		$secondary_color = isset( $settings['secondary_color'] ) ? $settings['secondary_color'] : '#628141';
		?>
		<div class="wrap kab-shortcodes-wrap">
			<h1 class="kab-page-title">
				<span class="dashicons dashicons-shortcode"></span>
				<?php echo esc_html__( 'Available Shortcodes', 'kura-ai-booking-free' ); ?>
			</h1>
			<p class="kab-page-subtitle">
				<?php echo esc_html__( 'Copy and paste these shortcodes into your pages, posts, or widgets to display booking features on your website.', 'kura-ai-booking-free' ); ?>
			</p>

			<!-- Search Bar -->
			<div class="kab-search-container">
				<input type="text" id="kab-shortcode-search" placeholder="<?php echo esc_attr__( 'Search shortcodes...', 'kura-ai-booking-free' ); ?>">
				<span class="dashicons dashicons-search"></span>
			</div>

			<!-- Shortcodes Grid -->
			<div class="kab-shortcodes-grid">

				<!-- Booking Form -->
				<div class="kab-shortcode-card" data-category="booking">
					<div class="kab-card-header" style="background: linear-gradient(135deg, <?php echo esc_attr( $primary_color ); ?> 0%, <?php echo esc_attr( self::adjust_brightness( $primary_color, -30 ) ); ?> 100%);">
						<span class="kab-card-icon dashicons dashicons-calendar-alt"></span>
						<h3><?php echo esc_html__( 'Booking Form', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-primary"><?php echo esc_html__( 'Core', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Display the main booking form where customers can select services/events and create bookings.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_booking_form]</code>
							<button class="kab-copy-btn" data-shortcode="[kab_booking_form]">
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><code>type</code> - "service" or "event" (default: all)</li>
								<li><code>id</code> - Specific service/event ID (optional)</li>
							</ul>
						</div>
					</div>
				</div>

				<!-- Waitlist Form -->
				<div class="kab-shortcode-card" data-category="waitlist">
					<div class="kab-card-header" style="background: linear-gradient(135deg, <?php echo esc_attr( $secondary_color ); ?> 0%, <?php echo esc_attr( self::adjust_brightness( $secondary_color, -30 ) ); ?> 100%);">
						<span class="kab-card-icon dashicons dashicons-groups"></span>
						<h3><?php echo esc_html__( 'Waitlist Form', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-new"><?php echo esc_html__( 'New', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Allow customers to join a waitlist when a service or event is at full capacity.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_waitlist_form item_type="service" item_id="1"]</code>
							<button class="kab-copy-btn" data-shortcode='[kab_waitlist_form item_type="service" item_id="1"]'>
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><code>item_type</code> - "service" or "event" <span class="required">*required</span></li>
								<li><code>item_id</code> - ID of the service/event <span class="required">*required</span></li>
							</ul>
						</div>
					</div>
				</div>

				<!-- Review Form -->
				<div class="kab-shortcode-card" data-category="reviews">
					<div class="kab-card-header" style="background: linear-gradient(135deg, #FFC107 0%, #FFA000 100%);">
						<span class="kab-card-icon dashicons dashicons-star-filled"></span>
						<h3><?php echo esc_html__( 'Review Form', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-new"><?php echo esc_html__( 'New', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Display a form where customers can leave reviews and ratings (1-5 stars) for their bookings.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_review_form booking_id="123"]</code>
							<button class="kab-copy-btn" data-shortcode='[kab_review_form booking_id="123"]'>
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><code>booking_id</code> - ID of the booking to review <span class="required">*required</span></li>
							</ul>
							<div class="kab-note">
								<span class="dashicons dashicons-info"></span>
								<?php echo esc_html__( 'Prevents duplicate reviews per booking', 'kura-ai-booking-free' ); ?>
							</div>
						</div>
					</div>
				</div>

				<!-- Reviews Display -->
				<div class="kab-shortcode-card" data-category="reviews">
					<div class="kab-card-header" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);">
						<span class="kab-card-icon dashicons dashicons-format-chat"></span>
						<h3><?php echo esc_html__( 'Reviews Display', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-new"><?php echo esc_html__( 'New', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Show customer reviews and ratings with beautiful cards, rating distribution, and average score.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_reviews_display item_type="service" item_id="1" limit="10"]</code>
							<button class="kab-copy-btn" data-shortcode='[kab_reviews_display item_type="service" item_id="1" limit="10"]'>
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><code>item_type</code> - "service" or "event" <span class="required">*required</span></li>
								<li><code>item_id</code> - ID of the service/event <span class="required">*required</span></li>
								<li><code>limit</code> - Number of reviews to display (default: 10)</li>
							</ul>
						</div>
					</div>
				</div>

				<!-- Cancel Booking -->
				<div class="kab-shortcode-card" data-category="booking">
					<div class="kab-card-header" style="background: linear-gradient(135deg, #F44336 0%, #D32F2F 100%);">
						<span class="kab-card-icon dashicons dashicons-dismiss"></span>
						<h3><?php echo esc_html__( 'Cancel Booking', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-new"><?php echo esc_html__( 'New', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Allow logged-in customers to cancel their bookings with automatic refund calculation based on timing.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_cancel_booking]</code>
							<button class="kab-copy-btn" data-shortcode="[kab_cancel_booking]">
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><?php echo esc_html__( 'No parameters required', 'kura-ai-booking-free' ); ?></li>
							</ul>
							<div class="kab-note kab-note-warning">
								<span class="dashicons dashicons-lock"></span>
								<?php echo esc_html__( 'Requires user to be logged in', 'kura-ai-booking-free' ); ?>
							</div>
						</div>
					</div>
				</div>

				<!-- Service List -->
				<div class="kab-shortcode-card" data-category="display">
					<div class="kab-card-header" style="background: linear-gradient(135deg, #00BCD4 0%, #0097A7 100%);">
						<span class="kab-card-icon dashicons dashicons-list-view"></span>
						<h3><?php echo esc_html__( 'Service List', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-primary"><?php echo esc_html__( 'Core', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Display a grid or list of available services with pricing, duration, and booking buttons.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_services layout="grid" columns="3"]</code>
							<button class="kab-copy-btn" data-shortcode='[kab_services layout="grid" columns="3"]'>
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><code>layout</code> - "grid" or "list" (default: grid)</li>
								<li><code>columns</code> - 2, 3, or 4 (default: 3)</li>
								<li><code>show_price</code> - true/false (default: true)</li>
							</ul>
						</div>
					</div>
				</div>

				<!-- Event Calendar -->
				<div class="kab-shortcode-card" data-category="display">
					<div class="kab-card-header" style="background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);">
						<span class="kab-card-icon dashicons dashicons-calendar"></span>
						<h3><?php echo esc_html__( 'Event Calendar', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-primary"><?php echo esc_html__( 'Core', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Show an interactive calendar displaying all upcoming events with filtering and search.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_events_calendar view="month"]</code>
							<button class="kab-copy-btn" data-shortcode='[kab_events_calendar view="month"]'>
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><code>view</code> - "month", "week", or "list" (default: month)</li>
								<li><code>category</code> - Filter by category slug (optional)</li>
							</ul>
						</div>
					</div>
				</div>

				<!-- My Bookings -->
				<div class="kab-shortcode-card" data-category="account">
					<div class="kab-card-header" style="background: linear-gradient(135deg, #3F51B5 0%, #303F9F 100%);">
						<span class="kab-card-icon dashicons dashicons-admin-users"></span>
						<h3><?php echo esc_html__( 'My Bookings', 'kura-ai-booking-free' ); ?></h3>
						<span class="kab-badge kab-badge-primary"><?php echo esc_html__( 'Core', 'kura-ai-booking-free' ); ?></span>
					</div>
					<div class="kab-card-body">
						<p class="kab-card-description">
							<?php echo esc_html__( 'Display a dashboard for logged-in users to view and manage their bookings.', 'kura-ai-booking-free' ); ?>
						</p>

						<div class="kab-shortcode-example">
							<code class="kab-shortcode-code">[kab_my_bookings]</code>
							<button class="kab-copy-btn" data-shortcode="[kab_my_bookings]">
								<span class="dashicons dashicons-admin-page"></span>
								<?php echo esc_html__( 'Copy', 'kura-ai-booking-free' ); ?>
							</button>
						</div>

						<div class="kab-card-footer">
							<strong><?php echo esc_html__( 'Parameters:', 'kura-ai-booking-free' ); ?></strong>
							<ul class="kab-params-list">
								<li><code>status</code> - "all", "upcoming", or "past" (default: all)</li>
							</ul>
							<div class="kab-note kab-note-warning">
								<span class="dashicons dashicons-lock"></span>
								<?php echo esc_html__( 'Requires user to be logged in', 'kura-ai-booking-free' ); ?>
							</div>
						</div>
					</div>
				</div>

			</div>

			<!-- Tips & Best Practices -->
			<div class="kab-tips-section">
				<h2>
					<span class="dashicons dashicons-lightbulb"></span>
					<?php echo esc_html__( 'Tips & Best Practices', 'kura-ai-booking-free' ); ?>
				</h2>
				<div class="kab-tips-grid">
					<div class="kab-tip-card">
						<span class="dashicons dashicons-admin-page"></span>
						<h4><?php echo esc_html__( 'Dedicated Pages', 'kura-ai-booking-free' ); ?></h4>
						<p><?php echo esc_html__( 'Create separate pages for booking, reviews, and account management for better user experience.', 'kura-ai-booking-free' ); ?></p>
					</div>
					<div class="kab-tip-card">
						<span class="dashicons dashicons-smartphone"></span>
						<h4><?php echo esc_html__( 'Mobile Responsive', 'kura-ai-booking-free' ); ?></h4>
						<p><?php echo esc_html__( 'All shortcodes are fully responsive and mobile-optimized out of the box.', 'kura-ai-booking-free' ); ?></p>
					</div>
					<div class="kab-tip-card">
						<span class="dashicons dashicons-admin-customizer"></span>
						<h4><?php echo esc_html__( 'Styling', 'kura-ai-booking-free' ); ?></h4>
						<p><?php echo esc_html__( 'Forms inherit your brand colors from Setup Wizard settings automatically.', 'kura-ai-booking-free' ); ?></p>
					</div>
					<div class="kab-tip-card">
						<span class="dashicons dashicons-performance"></span>
						<h4><?php echo esc_html__( 'Performance', 'kura-ai-booking-free' ); ?></h4>
						<p><?php echo esc_html__( 'Scripts and styles load only on pages where shortcodes are used.', 'kura-ai-booking-free' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Copy Success Toast -->
			<div class="kab-toast" id="kab-copy-toast">
				<span class="dashicons dashicons-yes-alt"></span>
				<?php echo esc_html__( 'Shortcode copied to clipboard!', 'kura-ai-booking-free' ); ?>
			</div>
		</div>

		<style>
			:root {
				--kab-primary: <?php echo esc_attr( $primary_color ); ?>;
				--kab-secondary: <?php echo esc_attr( $secondary_color ); ?>;
			}
		</style>
		<?php
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
}

KAB_Shortcodes_Page::init();
