<?php
/**
 * Kura-ai Booking System - Setup Wizard
 *
 * Handles the multi-step setup wizard for initial plugin configuration.
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup Wizard Class
 */
class KAB_Setup_Wizard {

	/**
	 * Wizard steps
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Current step
	 *
	 * @var int
	 */
	private $current_step = 1;

	/**
	 * Brand colors
	 *
	 * @var array
	 */
	private $colors = array(
		'primary'    => '#E67E22',
		'secondary'  => '#628141',
		'accent'     => '#8BAE66',
		'background' => '#EBD5AB',
	);

	/**
	 * Initialize setup wizard
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_setup_page' ) );
		add_action( 'admin_init', array( $this, 'handle_setup' ) );
		add_action( 'admin_post_kab_complete_setup', array( $this, 'complete_setup' ) );

		$this->setup_steps();
	}

	/**
	 * Setup wizard steps
	 */
	private function setup_steps() {
		$this->steps = array(
			1 => __( 'Welcome', 'kura-ai-booking-free' ),
			2 => __( 'Company Information', 'kura-ai-booking-free' ),
			3 => __( 'Email Settings', 'kura-ai-booking-free' ),
			4 => __( 'Features', 'kura-ai-booking-free' ),
			5 => __( 'Complete', 'kura-ai-booking-free' ),
		);
	}

	/**
	 * Add setup page to admin menu
	 */
	public function add_setup_page() {
		// Only show setup wizard if not completed or user wants to rerun
		if ( ! get_option( 'kab_setup_completed' ) || ( isset( $_GET['page'] ) && 'kab-setup' === $_GET['page'] ) ) {
			add_submenu_page(
				null,
				__( 'Kura-ai Setup Wizard', 'kura-ai-booking-free' ),
				__( 'Setup Wizard', 'kura-ai-booking-free' ),
				'manage_options',
				'kab-setup',
				array( $this, 'render_setup_page' )
			);
		}
	}

	/**
	 * Handle setup form submissions
	 */
	public function handle_setup() {
		if ( ! isset( $_POST['kab_setup_nonce'] ) || ! wp_verify_nonce( $_POST['kab_setup_nonce'], 'kab_setup_step_' . $this->current_step ) ) {
			return;
		}

		// Handle step-specific data processing
		switch ( $this->current_step ) {
			case 2:
				$this->save_company_info();
				break;
			case 3:
				$this->save_email_settings();
				break;
			case 4:
				$this->save_feature_settings();
				break;
		}

		// Move to next step
		++$this->current_step;
		if ( $this->current_step > count( $this->steps ) ) {
			$this->complete_setup();
		}
	}

	/**
	 * Save company information
	 */
	private function save_company_info() {
		$settings = get_option( 'kab_settings', array() );

		$settings['company_name']  = sanitize_text_field( $_POST['company_name'] );
		$settings['company_logo']  = esc_url_raw( $_POST['company_logo'] );
		$settings['support_email'] = sanitize_email( $_POST['support_email'] );

		update_option( 'kab_settings', $settings );
	}

	/**
	 * Save email settings
	 */
	private function save_email_settings() {
		$settings = get_option( 'kab_settings', array() );

		$settings['email_from_name']  = sanitize_text_field( $_POST['email_from_name'] );
		$settings['email_from_email'] = sanitize_email( $_POST['email_from_email'] );

		update_option( 'kab_settings', $settings );
	}

	/**
	 * Save feature settings
	 */
	private function save_feature_settings() {
		$settings = get_option( 'kab_settings', array() );

		$settings['enable_tickets'] = isset( $_POST['enable_tickets'] ) ? 'yes' : 'no';

		update_option( 'kab_settings', $settings );
	}

	/**
	 * Complete setup process
	 */
	public function complete_setup() {
		update_option( 'kab_setup_completed', true );
		wp_redirect( admin_url( 'admin.php?page=kab-dashboard' ) );
		exit;
	}

	/**
	 * Render setup wizard page
	 */
	public function render_setup_page() {
		$this->current_step = isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;

		// Output the setup wizard HTML with brand colors
		$this->render_wizard_html();
	}

	/**
	 * Render wizard HTML with brand colors
	 */
	private function render_wizard_html() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'Kura-ai Booking System Setup', 'kura-ai-booking-free' ); ?></title>
			<?php wp_print_styles( array( 'dashicons', 'common' ) ); ?>
			<style>
				body {
					background: <?php echo $this->colors['background']; ?>;
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
					margin: 0;
					padding: 20px;
				}
				
				.kab-setup-wizard {
					max-width: 700px;
					margin: 40px auto;
					background: white;
					border-radius: 8px;
					box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
					overflow: hidden;
				}
				
				.kab-header {
					background: <?php echo $this->colors['primary']; ?>;
					color: white;
					padding: 30px 40px;
					text-align: center;
				}
				
				.kab-header h1 {
					margin: 0 0 10px 0;
					font-size: 28px;
					font-weight: 600;
				}
				
				.kab-header p {
					margin: 0;
					opacity: 0.9;
					font-size: 16px;
				}
				
				.kab-progress {
					display: flex;
					justify-content: space-between;
					padding: 20px 40px;
					background: #f8f9fa;
					border-bottom: 1px solid #e9ecef;
				}
				
				.kab-step {
					text-align: center;
					flex: 1;
					position: relative;
				}
				
				.kab-step:not(:last-child):after {
					content: '';
					position: absolute;
					top: 20px;
					right: -50%;
					width: 100%;
					height: 2px;
					background: #dee2e6;
					z-index: 1;
				}
				
				.kab-step-number {
					width: 40px;
					height: 40px;
					border-radius: 50%;
					background: #6c757d;
					color: white;
					display: flex;
					align-items: center;
					justify-content: center;
					margin: 0 auto 10px;
					font-weight: bold;
					position: relative;
					z-index: 2;
				}
				
				.kab-step.active .kab-step-number {
					background: <?php echo $this->colors['secondary']; ?>;
				}
				
				.kab-step.completed .kab-step-number {
					background: <?php echo $this->colors['accent']; ?>;
				}
				
				.kab-step-label {
					font-size: 12px;
					color: #6c757d;
					font-weight: 500;
				}
				
				.kab-step.active .kab-step-label {
					color: <?php echo $this->colors['secondary']; ?>;
					font-weight: 600;
				}
				
				.kab-content {
					padding: 40px;
				}
				
				.kab-form-group {
					margin-bottom: 20px;
				}
				
				.kab-form-group label {
					display: block;
					margin-bottom: 5px;
					font-weight: 500;
					color: #495057;
				}
				
				.kab-form-group input[type="text"],
				.kab-form-group input[type="email"],
				.kab-form-group input[type="url"] {
					width: 100%;
					padding: 12px;
					border: 2px solid #e9ecef;
					border-radius: 6px;
					font-size: 14px;
					transition: border-color 0.2s;
				}
				
				.kab-form-group input:focus {
					outline: none;
					border-color: <?php echo $this->colors['primary']; ?>;
				}
				
				.kab-footer {
					padding: 30px 40px;
					background: #f8f9fa;
					border-top: 1px solid #e9ecef;
					display: flex;
					justify-content: space-between;
					align-items: center;
				}
				
				.kab-button {
					padding: 12px 24px;
					border: none;
					border-radius: 6px;
					font-size: 14px;
					font-weight: 500;
					cursor: pointer;
					transition: all 0.2s;
				}
				
				.kab-button-primary {
					background: <?php echo $this->colors['primary']; ?>;
					color: white;
				}
				
				.kab-button-primary:hover {
					background: <?php echo $this->adjust_brightness( $this->colors['primary'], -20 ); ?>;
				}
				
				.kab-button-secondary {
					background: #6c757d;
					color: white;
				}
				
				.kab-button-secondary:hover {
					background: #5a6268;
				}
				
				.kab-checkbox {
					display: flex;
					align-items: center;
					gap: 10px;
				}
				
				.kab-checkbox input[type="checkbox"] {
					width: 18px;
					height: 18px;
				}
				
				<?php echo $this->get_step_styles(); ?>
			</style>
		</head>
		<body>
			<div class="kab-setup-wizard">
				<div class="kab-header">
					<h1><?php esc_html_e( 'Kura-ai Booking System', 'kura-ai-booking-free' ); ?></h1>
					<p><?php esc_html_e( 'Setup Wizard', 'kura-ai-booking-free' ); ?></p>
				</div>
				
				<div class="kab-progress">
					<?php foreach ( $this->steps as $step_number => $step_label ) : ?>
						<div class="kab-step <?php echo $step_number === $this->current_step ? 'active' : ''; ?> <?php echo $step_number < $this->current_step ? 'completed' : ''; ?>">
							<div class="kab-step-number">
								<?php if ( $step_number < $this->current_step ) : ?>
									✓
								<?php else : ?>
									<?php echo $step_number; ?>
								<?php endif; ?>
							</div>
							<div class="kab-step-label"><?php echo esc_html( $step_label ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<div class="kab-content">
					<form method="post">
						<?php wp_nonce_field( 'kab_setup_step_' . $this->current_step, 'kab_setup_nonce' ); ?>
						<input type="hidden" name="step" value="<?php echo $this->current_step; ?>">
						
						<?php $this->render_step_content(); ?>
					</form>
				</div>
				
				<div class="kab-footer">
					<?php $this->render_footer_buttons(); ?>
				</div>
			</div>
		</body>
		</html>
		<?php
	}

	/**
	 * Adjust color brightness
	 *
	 * @param string $hex Color hex code
	 * @param int    $steps Steps to adjust (-255 to 255)
	 * @return string Adjusted hex color
	 */
	private function adjust_brightness( $hex, $steps ) {
		$steps = max( -255, min( 255, $steps ) );
		$hex   = str_replace( '#', '', $hex );

		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		$r = max( 0, min( 255, $r + $steps ) );
		$g = max( 0, min( 255, $g + $steps ) );
		$b = max( 0, min( 255, $b + $steps ) );

		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) .
				str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) .
				str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}

	/**
	 * Get step-specific styles
	 *
	 * @return string CSS styles
	 */
	private function get_step_styles() {
		$styles = '';

		// Add step-specific styles if needed
		return $styles;
	}

	/**
	 * Render step content
	 */
	private function render_step_content() {
		switch ( $this->current_step ) {
			case 1:
				$this->render_welcome_step();
				break;
			case 2:
				$this->render_company_step();
				break;
			case 3:
				$this->render_email_step();
				break;
			case 4:
				$this->render_features_step();
				break;
			case 5:
				$this->render_complete_step();
				break;
		}
	}

	/**
	 * Render footer buttons
	 */
	private function render_footer_buttons() {
		if ( $this->current_step > 1 ) {
			echo '<a href="' . esc_url( add_query_arg( 'step', $this->current_step - 1 ) ) . '" class="kab-button kab-button-secondary">' . esc_html__( 'Previous', 'kura-ai-booking-free' ) . '</a>';
		} else {
			echo '<span></span>'; // Empty span for flex spacing
		}

		if ( $this->current_step < count( $this->steps ) ) {
			echo '<button type="submit" class="kab-button kab-button-primary">' . esc_html__( 'Next', 'kura-ai-booking-free' ) . '</button>';
		} else {
			echo '<button type="submit" class="kab-button kab-button-primary" name="complete_setup">' . esc_html__( 'Complete Setup', 'kura-ai-booking-free' ) . '</button>';
		}
	}

	/**
	 * Render welcome step
	 */
	private function render_welcome_step() {
		?>
		<h2><?php esc_html_e( 'Welcome to Kura-ai Booking System', 'kura-ai-booking-free' ); ?></h2>
		<p><?php esc_html_e( 'Thank you for choosing Kura-ai Booking System! This wizard will help you configure the basic settings for your booking platform.', 'kura-ai-booking-free' ); ?></p>
		
		<div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">
			<h3 style="margin-top: 0;"><?php esc_html_e( 'What you\'ll configure:', 'kura-ai-booking-free' ); ?></h3>
			<ul style="margin: 0;">
				<li><?php esc_html_e( 'Company information', 'kura-ai-booking-free' ); ?></li>
				<li><?php esc_html_e( 'Email settings', 'kura-ai-booking-free' ); ?></li>
				<li><?php esc_html_e( 'Feature preferences', 'kura-ai-booking-free' ); ?></li>
			</ul>
		</div>
		
		<p><?php esc_html_e( 'Click "Next" to continue with the setup process.', 'kura-ai-booking-free' ); ?></p>
		<?php
	}

	/**
	 * Render company information step
	 */
	private function render_company_step() {
		$settings = get_option( 'kab_settings', array() );
		?>
		<h2><?php esc_html_e( 'Company Information', 'kura-ai-booking-free' ); ?></h2>
		<p><?php esc_html_e( 'Please provide your company details. This information will be used in emails and tickets.', 'kura-ai-booking-free' ); ?></p>
		
		<div class="kab-form-group">
			<label for="company_name"><?php esc_html_e( 'Company Name', 'kura-ai-booking-free' ); ?></label>
			<input type="text" id="company_name" name="company_name" value="<?php echo esc_attr( $settings['company_name'] ?? get_bloginfo( 'name' ) ); ?>" required>
		</div>
		
		<div class="kab-form-group">
			<label for="company_logo"><?php esc_html_e( 'Company Logo URL', 'kura-ai-booking-free' ); ?></label>
			<input type="url" id="company_logo" name="company_logo" value="<?php echo esc_attr( $settings['company_logo'] ?? '' ); ?>" placeholder="https://example.com/logo.png">
		</div>
		
		<div class="kab-form-group">
			<label for="support_email"><?php esc_html_e( 'Support Email', 'kura-ai-booking-free' ); ?></label>
			<input type="email" id="support_email" name="support_email" value="<?php echo esc_attr( $settings['support_email'] ?? get_option( 'admin_email' ) ); ?>" required>
		</div>
		<?php
	}

	/**
	 * Render email settings step
	 */
	private function render_email_step() {
		$settings = get_option( 'kab_settings', array() );
		?>
		<h2><?php esc_html_e( 'Email Settings', 'kura-ai-booking-free' ); ?></h2>
		<p><?php esc_html_e( 'Configure how emails from your booking system will appear to customers.', 'kura-ai-booking-free' ); ?></p>
		
		<div class="kab-form-group">
			<label for="email_from_name"><?php esc_html_e( 'Email From Name', 'kura-ai-booking-free' ); ?></label>
			<input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr( $settings['email_from_name'] ?? get_bloginfo( 'name' ) ); ?>" required>
		</div>
		
		<div class="kab-form-group">
			<label for="email_from_email"><?php esc_html_e( 'Email From Address', 'kura-ai-booking-free' ); ?></label>
			<input type="email" id="email_from_email" name="email_from_email" value="<?php echo esc_attr( $settings['email_from_email'] ?? get_option( 'admin_email' ) ); ?>" required>
		</div>
		<?php
	}

	/**
	 * Render features step
	 */
	private function render_features_step() {
		$settings = get_option( 'kab_settings', array() );
		?>
		<h2><?php esc_html_e( 'Feature Preferences', 'kura-ai-booking-free' ); ?></h2>
		<p><?php esc_html_e( 'Choose which features you want to enable for your booking system.', 'kura-ai-booking-free' ); ?></p>
		
		<div class="kab-form-group">
			<div class="kab-checkbox">
				<input type="checkbox" id="enable_tickets" name="enable_tickets" value="1" <?php checked( $settings['enable_tickets'] ?? 'yes', 'yes' ); ?>>
				<label for="enable_tickets"><?php esc_html_e( 'Enable QR Code E-Tickets', 'kura-ai-booking-free' ); ?></label>
			</div>
			<p style="margin: 5px 0 0 28px; font-size: 13px; color: #6c757d;">
				<?php esc_html_e( 'Generate digital tickets with QR codes for easy validation at events.', 'kura-ai-booking-free' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render complete step
	 */
	private function render_complete_step() {
		?>
		<h2><?php esc_html_e( 'Setup Complete!', 'kura-ai-booking-free' ); ?></h2>
		
		<div style="text-align: center; margin: 30px 0;">
			<div style="font-size: 48px; color: <?php echo $this->colors['accent']; ?>; margin-bottom: 20px;">✓</div>
			<h3 style="color: <?php echo $this->colors['secondary']; ?>;"><?php esc_html_e( 'Your booking system is ready!', 'kura-ai-booking-free' ); ?></h3>
			<p><?php esc_html_e( 'You can now start creating services, events, and accepting bookings.', 'kura-ai-booking-free' ); ?></p>
		</div>
		
		<div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">
			<h4 style="margin-top: 0;"><?php esc_html_e( 'Next Steps:', 'kura-ai-booking-free' ); ?></h4>
			<ol style="margin: 0;">
				<li><?php esc_html_e( 'Create your first service or event', 'kura-ai-booking-free' ); ?></li>
				<li><?php esc_html_e( 'Add booking forms to your pages using shortcodes', 'kura-ai-booking-free' ); ?></li>
				<li><?php esc_html_e( 'Test the booking process', 'kura-ai-booking-free' ); ?></li>
			</ol>
		</div>
		<?php
	}
}

// Initialize the setup wizard
new KAB_Setup_Wizard();