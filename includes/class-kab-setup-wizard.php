<?php
/**
 * Kura-ai Booking System Setup Wizard
 *
 * Handles the plugin setup wizard for initial configuration.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup Wizard class for Kura-ai Booking System.
 *
 * Handles multi-step setup process for plugin configuration.
 *
 * @since 1.0.0
 */
class KAB_Setup_Wizard {

	/**
	 * Current step of the setup wizard.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $current_step;

	/**
	 * Setup steps configuration.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $steps;

	/**
	 * Initialize the setup wizard.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_steps();
		$this->current_step = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : 'welcome';
		
		add_action( 'admin_init', array( $this, 'handle_setup_submission' ) );
	}

	/**
	 * Setup wizard steps configuration.
	 *
	 * @since 1.0.0
	 */
	private function setup_steps() {
		$this->steps = array(
			'welcome' => array(
				'name' => __( 'Welcome', 'kura-ai-booking-free' ),
				'title' => __( 'Welcome to Kura-ai Booking System', 'kura-ai-booking-free' ),
				'description' => __( 'Let\'s set up your booking system in a few simple steps.', 'kura-ai-booking-free' ),
			),
			'business' => array(
				'name' => __( 'Business Info', 'kura-ai-booking-free' ),
				'title' => __( 'Business Information', 'kura-ai-booking-free' ),
				'description' => __( 'Tell us about your business.', 'kura-ai-booking-free' ),
			),
			'services' => array(
				'name' => __( 'Services', 'kura-ai-booking-free' ),
				'title' => __( 'Default Services', 'kura-ai-booking-free' ),
				'description' => __( 'Set up your initial services.', 'kura-ai-booking-free' ),
			),
			'emails' => array(
				'name' => __( 'Email Settings', 'kura-ai-booking-free' ),
				'title' => __( 'Email Configuration', 'kura-ai-booking-free' ),
				'description' => __( 'Configure email notifications.', 'kura-ai-booking-free' ),
			),
			'finish' => array(
				'name' => __( 'Finish', 'kura-ai-booking-free' ),
				'title' => __( 'Setup Complete!', 'kura-ai-booking-free' ),
				'description' => __( 'Your booking system is ready to use.', 'kura-ai-booking-free' ),
			),
		);
	}

	/**
	 * Handle setup form submissions.
	 *
	 * @since 1.0.0
	 */
	public function handle_setup_submission() {
		if ( ! isset( $_POST['kab_setup_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kab_setup_nonce'] ) ), 'kab_setup_action' ) ) {
			return;
		}

		if ( isset( $_POST['save_step'] ) ) {
			$this->save_current_step();
		}

		if ( isset( $_POST['next_step'] ) ) {
			if ( $this->save_current_step() ) {
				$this->redirect_to_next_step();
			}
		}
	}

	/**
	 * Save current step data.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function save_current_step() {
		switch ( $this->current_step ) {
			case 'business':
				return $this->save_business_info();
			case 'services':
				return $this->save_services();
			case 'emails':
				return $this->save_email_settings();
			default:
				return true;
		}
	}

	/**
	 * Save business information.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function save_business_info() {
		$business_name = isset( $_POST['business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['business_name'] ) ) : '';
		$business_email = isset( $_POST['business_email'] ) ? sanitize_email( wp_unslash( $_POST['business_email'] ) ) : '';
		$business_phone = isset( $_POST['business_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['business_phone'] ) ) : '';
		$business_address = isset( $_POST['business_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['business_address'] ) ) : '';

		update_option( 'kab_business_name', $business_name );
		update_option( 'kab_business_email', $business_email );
		update_option( 'kab_business_phone', $business_phone );
		update_option( 'kab_business_address', $business_address );

		return true;
	}

	/**
	 * Save default services.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function save_services() {
		if ( isset( $_POST['services'] ) && is_array( $_POST['services'] ) ) {
			$services = array();
			foreach ( $_POST['services'] as $service ) {
				if ( ! empty( $service['name'] ) ) {
					$services[] = array(
						'name' => sanitize_text_field( $service['name'] ),
						'duration' => absint( $service['duration'] ),
						'price' => floatval( $service['price'] ),
						'description' => sanitize_textarea_field( $service['description'] ),
					);
				}
			}
			
			if ( ! empty( $services ) ) {
				global $wpdb;
				foreach ( $services as $service_data ) {
					$wpdb->insert(
						$wpdb->prefix . 'kab_services',
						array(
							'name' => $service_data['name'],
							'description' => $service_data['description'],
							'duration' => $service_data['duration'],
							'price' => $service_data['price'],
							'status' => 'active',
							'created_at' => current_time( 'mysql' ),
						),
						array( '%s', '%s', '%d', '%f', '%s', '%s' )
					);
				}
			}
		}

		return true;
	}

	/**
	 * Save email settings.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function save_email_settings() {
		$email_from_name = isset( $_POST['email_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['email_from_name'] ) ) : '';
		$email_from_address = isset( $_POST['email_from_address'] ) ? sanitize_email( wp_unslash( $_POST['email_from_address'] ) ) : '';
		$email_subject = isset( $_POST['email_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['email_subject'] ) ) : '';

		update_option( 'kab_email_from_name', $email_from_name );
		update_option( 'kab_email_from_address', $email_from_address );
		update_option( 'kab_email_subject', $email_subject );

		return true;
	}

	/**
	 * Redirect to the next step.
	 *
	 * @since 1.0.0
	 */
	private function redirect_to_next_step() {
		$steps = array_keys( $this->steps );
		$current_index = array_search( $this->current_step, $steps, true );
		
		if ( false !== $current_index && isset( $steps[ $current_index + 1 ] ) ) {
			$next_step = $steps[ $current_index + 1 ];
			wp_safe_redirect( admin_url( 'admin.php?page=kab-setup-wizard&step=' . $next_step ) );
			exit;
		}
	}

	/**
	 * Render the setup wizard page.
	 *
	 * @since 1.0.0
	 */
	public function render() {
		wp_enqueue_style( 'kab-setup-wizard', KAB_FREE_PLUGIN_URL . 'assets/css/setup-wizard.css', array(), KAB_VERSION );
		wp_enqueue_script( 'kab-setup-wizard', KAB_FREE_PLUGIN_URL . 'assets/js/setup-wizard.js', array( 'jquery' ), KAB_VERSION, true );
		
		// Localize script for JavaScript translations
		wp_localize_script( 'kab-setup-wizard', 'kabSetupWizard', array(
			'i18n' => array(
				'service' => __( 'Service', 'kura-ai-booking-free' ),
				'remove' => __( 'Remove', 'kura-ai-booking-free' ),
				'serviceName' => __( 'Service Name', 'kura-ai-booking-free' ),
				'serviceNamePlaceholder' => __( 'e.g., Consultation, Massage, Class', 'kura-ai-booking-free' ),
				'duration' => __( 'Duration', 'kura-ai-booking-free' ),
				'minutes' => __( 'minutes', 'kura-ai-booking-free' ),
				'price' => __( 'Price', 'kura-ai-booking-free' ),
				'freeServiceNote' => __( 'Set to 0 for free services', 'kura-ai-booking-free' ),
				'description' => __( 'Description', 'kura-ai-booking-free' ),
				'descriptionPlaceholder' => __( 'Brief description of the service...', 'kura-ai-booking-free' ),
				'cannotRemoveLastService' => __( 'You cannot remove the last service. Please add another service first.', 'kura-ai-booking-free' ),
				'invalidDuration' => __( 'Invalid duration for service "%s". Duration must be at least 5 minutes.', 'kura-ai-booking-free' ),
				'invalidPrice' => __( 'Invalid price for service "%s". Price cannot be negative.', 'kura-ai-booking-free' ),
				'atLeastOneService' => __( 'Please add at least one service.', 'kura-ai-booking-free' ),
				'businessNameRequired' => __( 'Business name is required.', 'kura-ai-booking-free' ),
				'businessEmailRequired' => __( 'Business email is required.', 'kura-ai-booking-free' ),
				'invalidEmail' => __( 'Please enter a valid email address.', 'kura-ai-booking-free' ),
				'validationErrors' => __( 'Please fix the following errors:', 'kura-ai-booking-free' ),
			),
		));

		?>
		<div class="wrap kab-setup-wizard">
			<div class="kab-setup-header">
				<h1><?php echo esc_html( $this->steps[ $this->current_step ]['title'] ); ?></h1>
				<p><?php echo esc_html( $this->steps[ $this->current_step ]['description'] ); ?></p>
			</div>

			<div class="kab-setup-progress">
				<ul>
					<?php foreach ( $this->steps as $step_key => $step ) : ?>
						<li class="<?php echo $step_key === $this->current_step ? 'active' : ( array_search( $step_key, array_keys( $this->steps ), true ) < array_search( $this->current_step, array_keys( $this->steps ), true ) ? 'completed' : '' ); ?>">
							<span class="step-number"><?php echo esc_html( array_search( $step_key, array_keys( $this->steps ), true ) + 1 ); ?></span>
							<span class="step-name"><?php echo esc_html( $step['name'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="kab-setup-content">
				<form method="post" class="kab-setup-form">
					<?php wp_nonce_field( 'kab_setup_action', 'kab_setup_nonce' ); ?>
					
					<?php $this->render_current_step(); ?>

					<div class="kab-setup-actions">
						<?php if ( 'welcome' === $this->current_step ) : ?>
							<button type="submit" name="next_step" class="button button-primary button-large">
								<?php esc_html_e( 'Get Started', 'kura-ai-booking-free' ); ?>
							</button>
						<?php elseif ( 'finish' === $this->current_step ) : ?>
							<button type="submit" name="save_step" class="button button-primary button-large">
								<?php esc_html_e( 'Finish Setup', 'kura-ai-booking-free' ); ?>
							</button>
						<?php else : ?>
							<button type="submit" name="save_step" class="button button-secondary">
								<?php esc_html_e( 'Save and Continue Later', 'kura-ai-booking-free' ); ?>
							</button>
							<button type="submit" name="next_step" class="button button-primary">
								<?php esc_html_e( 'Next Step', 'kura-ai-booking-free' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the current step content.
	 *
	 * @since 1.0.0
	 */
	private function render_current_step() {
		switch ( $this->current_step ) {
			case 'welcome':
				$this->render_welcome_step();
				break;
			case 'business':
				$this->render_business_step();
				break;
			case 'services':
				$this->render_services_step();
				break;
			case 'emails':
				$this->render_emails_step();
				break;
			case 'finish':
				$this->render_finish_step();
				break;
		}
	}

	/**
	 * Render welcome step.
	 *
	 * @since 1.0.0
	 */
	private function render_welcome_step() {
		?>
		<div class="kab-welcome-step">
			<div class="welcome-hero">
				<h2><?php esc_html_e( 'Welcome to Kura-ai Booking System', 'kura-ai-booking-free' ); ?></h2>
				<p><?php esc_html_e( 'Thank you for choosing our booking system! This setup wizard will help you configure the basic settings for your booking system.', 'kura-ai-booking-free' ); ?></p>
				
				<div class="welcome-features">
					<h3><?php esc_html_e( 'What you\'ll set up:', 'kura-ai-booking-free' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Business information', 'kura-ai-booking-free' ); ?></li>
						<li><?php esc_html_e( 'Default services', 'kura-ai-booking-free' ); ?></li>
						<li><?php esc_html_e( 'Email notifications', 'kura-ai-booking-free' ); ?></li>
						<li><?php esc_html_e( 'Basic configuration', 'kura-ai-booking-free' ); ?></li>
					</ul>
				</div>
				
				<p><strong><?php esc_html_e( 'This should only take about 5 minutes.', 'kura-ai-booking-free' ); ?></strong></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render business information step.
	 *
	 * @since 1.0.0
	 */
	private function render_business_step() {
		$business_name = get_option( 'kab_business_name', '' );
		$business_email = get_option( 'kab_business_email', '' );
		$business_phone = get_option( 'kab_business_phone', '' );
		$business_address = get_option( 'kab_business_address', '' );
		?>
		<div class="kab-business-step">
			<h3><?php esc_html_e( 'Business Information', 'kura-ai-booking-free' ); ?></h3>
			<p><?php esc_html_e( 'Please provide your business details. This information will be used in emails and booking confirmations.', 'kura-ai-booking-free' ); ?></p>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="business_name"><?php esc_html_e( 'Business Name', 'kura-ai-booking-free' ); ?></label>
					</th>
					<td>
						<input type="text" id="business_name" name="business_name" value="<?php echo esc_attr( $business_name ); ?>" class="regular-text" required>
						<p class="description"><?php esc_html_e( 'Your business or company name', 'kura-ai-booking-free' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="business_email"><?php esc_html_e( 'Email Address', 'kura-ai-booking-free' ); ?></label>
					</th>
					<td>
						<input type="email" id="business_email" name="business_email" value="<?php echo esc_attr( $business_email ); ?>" class="regular-text" required>
						<p class="description"><?php esc_html_e( 'Where booking notifications should be sent', 'kura-ai-booking-free' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="business_phone"><?php esc_html_e( 'Phone Number', 'kura-ai-booking-free' ); ?></label>
					</th>
					<td>
						<input type="tel" id="business_phone" name="business_phone" value="<?php echo esc_attr( $business_phone ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Optional business phone number', 'kura-ai-booking-free' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="business_address"><?php esc_html_e( 'Business Address', 'kura-ai-booking-free' ); ?></label>
					</th>
					<td>
						<textarea id="business_address" name="business_address" rows="3" class="large-text"><?php echo esc_textarea( $business_address ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Optional business address for physical locations', 'kura-ai-booking-free' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render services step.
	 *
	 * @since 1.0.0
	 */
	private function render_services_step() {
		?>
		<div class="kab-services-step">
			<h3><?php esc_html_e( 'Default Services', 'kura-ai-booking-free' ); ?></h3>
			<p><?php esc_html_e( 'Set up your initial services. You can add more services later from the admin panel.', 'kura-ai-booking-free' ); ?></p>
			
			<div id="kab-services-container">
				<div class="kab-service-item">
					<h4><?php esc_html_e( 'Service #1', 'kura-ai-booking-free' ); ?></h4>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Service Name', 'kura-ai-booking-free' ); ?></label>
							</th>
							<td>
								<input type="text" name="services[0][name]" class="regular-text" placeholder="<?php esc_attr_e( 'e.g., Consultation, Massage, Class', 'kura-ai-booking-free' ); ?>">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Duration (minutes)', 'kura-ai-booking-free' ); ?></label>
							</th>
							<td>
								<input type="number" name="services[0][duration]" value="60" min="5" step="5" class="small-text">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></label>
							</th>
							<td>
								<input type="number" name="services[0][price]" value="0" min="0" step="0.01" class="small-text">
								<span class="description"><?php esc_html_e( 'Set to 0 for free services', 'kura-ai-booking-free' ); ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Description', 'kura-ai-booking-free' ); ?></label>
							</th>
							<td>
								<textarea name="services[0][description]" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Brief description of the service...', 'kura-ai-booking-free' ); ?>"></textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<p>
				<button type="button" id="kab-add-service" class="button button-secondary">
					<?php esc_html_e( '+ Add Another Service', 'kura-ai-booking-free' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Render email settings step.
	 *
	 * @since 1.0.0
	 */
	private function render_emails_step() {
		$email_from_name = get_option( 'kab_email_from_name', get_bloginfo( 'name' ) );
		$email_from_address = get_option( 'kab_email_from_address', get_option( 'admin_email' ) );
		$email_subject = get_option( 'kab_email_subject', __( 'Your Booking Confirmation', 'kura-ai-booking-free' ) );
		?>
		<div class="kab-emails-step">
			<h3><?php esc_html_e( 'Email Settings', 'kura-ai-booking-free' ); ?></h3>
			<p><?php esc_html_e( 'Configure how booking confirmation emails will be sent to your customers.', 'kura-ai-booking-free' ); ?></p>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="email_from_name"><?php esc_html_e( 'From Name', 'kura-ai-booking-free' ); ?></label>
					</th>
					<td>
						<input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr( $email_from_name ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'The name that will appear in the \'From\' field', 'kura-ai-booking-free' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="email_from_address"><?php esc_html_e( 'From Email', 'kura-ai-booking-free' ); ?></label>
					</th>
					<td>
						<input type="email" id="email_from_address" name="email_from_address" value="<?php echo esc_attr( $email_from_address ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'The email address that will send notifications', 'kura-ai-booking-free' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="email_subject"><?php esc_html_e( 'Email Subject', 'kura-ai-booking-free' ); ?></label>
					</th>
					<td>
						<input type="text" id="email_subject" name="email_subject" value="<?php echo esc_attr( $email_subject ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Subject line for booking confirmation emails', 'kura-ai-booking-free' ); ?></p>
					</td>
				</tr>
			</table>
			
			<div class="notice notice-info">
				<p><?php esc_html_e( 'Note: You can customize the email content and templates later in the plugin settings.', 'kura-ai-booking-free' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render finish step.
	 *
	 * @since 1.0.0
	 */
	private function render_finish_step() {
		// Mark setup as complete
		update_option( 'kab_setup_completed', true );
		delete_transient( 'kab_free_show_setup_wizard' );
		?>
		<div class="kab-finish-step">
			<div class="finish-hero">
				<div class="dashicons dashicons-yes-alt" style="font-size: 80px; color: #46b450; width: 80px; height: 80px;"></div>
				<h2><?php esc_html_e( 'Setup Complete!', 'kura-ai-booking-free' ); ?></h2>
				<p><?php esc_html_e( 'Congratulations! Your Kura-ai Booking System is now ready to use.', 'kura-ai-booking-free' ); ?></p>
			</div>
			
			<div class="next-steps">
				<h3><?php esc_html_e( 'What\'s Next?', 'kura-ai-booking-free' ); ?></h3>
				<ul>
					<li>
						<strong><?php esc_html_e( 'Add your services', 'kura-ai-booking-free' ); ?></strong><br>
						<?php esc_html_e( 'Go to Services to add more services or edit existing ones.', 'kura-ai-booking-free' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Configure booking settings', 'kura-ai-booking-free' ); ?></strong><br>
						<?php esc_html_e( 'Adjust availability, time slots, and other settings.', 'kura-ai-booking-free' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Add booking forms to your site', 'kura-ai-booking-free' ); ?></strong><br>
						<?php esc_html_e( 'Use the [kuraai_booking_form] shortcode on any page or post.', 'kura-ai-booking-free' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Set up events', 'kura-ai-booking-free' ); ?></strong><br>
						<?php esc_html_e( 'Create events that customers can book through the events list shortcode.', 'kura-ai-booking-free' ); ?>
					</li>
				</ul>
			</div>
			
			<div class="setup-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-dashboard' ) ); ?>" class="button button-primary button-large">
					<?php esc_html_e( 'Go to Dashboard', 'kura-ai-booking-free' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Manage Services', 'kura-ai-booking-free' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Plugin Settings', 'kura-ai-booking-free' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if setup is completed.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_setup_completed() {
		return (bool) get_option( 'kab_setup_completed', false );
	}

	/**
	 * Reset setup completion status.
	 *
	 * @since 1.0.0
	 */
	public static function reset_setup() {
		delete_option( 'kab_setup_completed' );
		set_transient( 'kab_free_show_setup_wizard', true, 60 );
	}
}