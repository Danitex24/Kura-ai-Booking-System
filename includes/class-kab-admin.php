<?php
/**
 * Kura-ai Booking System - Admin Dashboard
 *
 * Handles admin menus, pages, and settings.
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kura-ai Booking System Admin Class
 */
class KAB_Admin {

	/**
	 * Initialize admin hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_post_kab_add_service', array( $this, 'handle_add_service_action' ) );
		add_action( 'admin_post_kab_edit_service', array( $this, 'handle_edit_service_action' ) );
		add_action( 'admin_post_kab_delete_service', array( $this, 'handle_delete_service_action' ) );
        add_action( 'admin_post_kab_add_event', array( $this, 'handle_add_event_action' ) );
        add_action( 'admin_post_kab_edit_event', array( $this, 'handle_edit_event_action' ) );
        add_action( 'admin_post_kab_delete_event', array( $this, 'handle_delete_event_action' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
	}

	/**
	 * Render static header for admin pages
	 *
	 * @param string $active_page The active page slug
	 */
	protected function render_static_header( $active_page = 'dashboard' ) {
		?>
		<div class="kab-admin-header">
			<div class="kab-admin-header-inner">
				<div class="kab-logo">
					<span class="kab-logo-icon dashicons dashicons-calendar-alt"></span>
					<span class="kab-logo-text"><?php echo esc_html__( 'Kura-ai Booking', 'kura-ai-booking-free' ); ?></span>
				</div>
				<nav class="kab-header-nav">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-dashboard' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>"><?php echo esc_html__( 'Dashboard', 'kura-ai-booking-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'services' ? 'active' : ''; ?>"><?php echo esc_html__( 'Services', 'kura-ai-booking-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'events' ? 'active' : ''; ?>"><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-customers' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'customers' ? 'active' : ''; ?>"><?php echo esc_html__( 'Customers', 'kura-ai-booking-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-finance' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'finance' ? 'active' : ''; ?>"><?php echo esc_html__( 'Finance', 'kura-ai-booking-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-invoices' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'invoices' ? 'active' : ''; ?>"><?php echo esc_html__( 'Invoices', 'kura-ai-booking-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'settings' ? 'active' : ''; ?>"><?php echo esc_html__( 'Settings', 'kura-ai-booking-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-validation' ) ); ?>" class="kab-nav-link <?php echo $active_page === 'validation' ? 'active' : ''; ?>"><?php echo esc_html__( 'Validation', 'kura-ai-booking-free' ); ?></a>
				</nav>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin styles
	 */
	public function enqueue_admin_styles( $hook ) {
		if ( ! is_string( $hook ) ) {
			return;
		}
		// Only load on our plugin pages
		if ( strpos( $hook, 'kab-' ) === false ) {
			return;
		}
		wp_enqueue_style( 'kab-admin-styles', KAB_FREE_PLUGIN_URL . 'assets/css/admin.css', array(), KAB_VERSION );
		wp_enqueue_script( 'kab-admin-scripts', KAB_FREE_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), KAB_VERSION, true );
	}

	/**
	 * Add admin menus
	 */
	public function add_admin_menus() {
		add_menu_page(
			__( 'Kura-ai Booking', 'kura-ai-booking-free' ),
			__( 'Kura-ai Booking', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-calendar-alt',
			25
		);

        // Finance submenu under Kura-ai Booking
        add_submenu_page(
            'kab-dashboard',
            __( 'Finance', 'kura-ai-booking-free' ),
            __( 'Finance', 'kura-ai-booking-free' ),
            'manage_options',
            'kab-finance',
            array( $this, 'render_finance_page' )
        );

		add_submenu_page(
			'kab-dashboard',
			__( 'Services', 'kura-ai-booking-free' ),
			__( 'Services', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-services',
			array( $this, 'render_services_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Events', 'kura-ai-booking-free' ),
			__( 'Events', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-events',
			array( $this, 'render_events_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Customers', 'kura-ai-booking-free' ),
			__( 'Customers', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-customers',
			array( $this, 'render_customers_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Settings', 'kura-ai-booking-free' ),
			__( 'Settings', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-settings',
			array( $this, 'render_settings_page' )
		);

        // Ticket Validation under Kura-ai Booking
        add_submenu_page(
            'kab-dashboard',
            __( 'Ticket Validation', 'kura-ai-booking-free' ),
            __( 'Ticket Validation', 'kura-ai-booking-free' ),
            'manage_options',
            'kab-validation',
            array( $this, 'render_validation_page' )
        );
	}

	/**
	 * Render dashboard page
	 */
	public function render_dashboard_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-bookings.php';
		$upcoming_appointments = KAB_Bookings::get_upcoming_appointments();
		$recent_bookings = KAB_Bookings::get_recent_bookings();
		?>
		<div class="wrap kab-admin-wrapper">
			<!-- Static Header -->
			<div class="kab-admin-header">
				<div class="kab-admin-header-inner">
					<div class="kab-logo">
						<span class="kab-logo-icon dashicons dashicons-calendar-alt"></span>
						<span class="kab-logo-text"><?php echo esc_html__( 'Kura-ai Booking', 'kura-ai-booking-free' ); ?></span>
					</div>
					<nav class="kab-header-nav">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-dashboard' ) ); ?>" class="kab-nav-link active"><?php echo esc_html__( 'Dashboard', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Services', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-customers' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Customers', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Settings', 'kura-ai-booking-free' ); ?></a>
					</nav>
				</div>
			</div>

			<!-- Page Content -->
			<div class="kab-card">
				<div class="kab-card-header">
					<h1 class="kab-card-title">
						<span class="dashicons dashicons-dashboard"></span>
						<?php echo esc_html__( 'Dashboard Overview', 'kura-ai-booking-free' ); ?>
					</h1>
				</div>
				<div class="kab-card-body">
					<p><?php echo esc_html__( 'Welcome to your booking system dashboard. Manage your services, events, and customer bookings.', 'kura-ai-booking-free' ); ?></p>
				</div>
			</div>

			<div class="kab-dashboard-widgets">
				<div class="kab-card">
					<div class="kab-card-header">
						<h2 class="kab-card-title">
							<span class="dashicons dashicons-clock"></span>
							<?php echo esc_html__( 'Upcoming Appointments', 'kura-ai-booking-free' ); ?>
						</h2>
					</div>
					<div class="kab-card-body">
						<?php if ( ! empty( $upcoming_appointments ) ) : ?>
							<ul>
								<?php foreach ( $upcoming_appointments as $appointment ) : ?>
									<li>
										<?php
										$booking_type = $appointment['booking_type'] === 'service' ? 'Service' : 'Event';
										$item_id = $appointment['booking_type'] === 'service' ? $appointment['service_id'] : $appointment['event_id'];
										// You would typically have a method to get the service/event name by its ID.
										// For now, we'll just display the ID.
										echo esc_html( $booking_type . ' #' . $item_id );
										?>
										- <?php echo esc_html( $appointment['booking_date'] . ' @ ' . $appointment['booking_time'] ); ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p><?php echo esc_html__( 'You have no upcoming appointments.', 'kura-ai-booking-free' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="kab-card">
					<div class="kab-card-header">
						<h2 class="kab-card-title">
							<span class="dashicons dashicons-calendar"></span>
							<?php echo esc_html__( 'Recent Bookings', 'kura-ai-booking-free' ); ?>
						</h2>
					</div>
					<div class="kab-card-body">
						<?php if ( ! empty( $recent_bookings ) ) : ?>
							<ul>
								<?php foreach ( $recent_bookings as $booking ) : ?>
									<li>
										<?php
										$booking_type = $booking['booking_type'] === 'service' ? 'Service' : 'Event';
										$item_id = $booking['booking_type'] === 'service' ? $booking['service_id'] : $booking['event_id'];
										// You would typically have a method to get the service/event name by its ID.
										// For now, we'll just display the ID.
										echo esc_html( $booking_type . ' #' . $item_id );
										?>
										- Booked on <?php echo esc_html( $booking['created_at'] ); ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p><?php echo esc_html__( 'No recent bookings found.', 'kura-ai-booking-free' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="kab-card">
					<div class="kab-card-header">
						<h2 class="kab-card-title">
							<span class="dashicons dashicons-plus-alt"></span>
							<?php echo esc_html__( 'Quick Actions', 'kura-ai-booking-free' ); ?>
						</h2>
					</div>
					<div class="kab-card-body">
						<div class="kab-mb-2">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services&action=add' ) ); ?>" class="kab-btn kab-btn-primary">
								<span class="dashicons dashicons-plus"></span>
								<?php echo esc_html__( 'Add New Service', 'kura-ai-booking-free' ); ?>
							</a>
						</div>
						<div>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events&action=add' ) ); ?>" class="kab-btn kab-btn-primary">
								<span class="dashicons dashicons-plus"></span>
								<?php echo esc_html__( 'Add New Event', 'kura-ai-booking-free' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render services page
	 */
	public function render_services_page() {
		$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
		$service_id = isset( $_GET['service_id'] ) ? intval( $_GET['service_id'] ) : 0;

		switch ( $action ) {
			case 'add':
                $this->render_service_form();
                break;
            case 'edit':
                $this->render_service_form( $service_id );
                break;
            case 'view':
                $this->render_service_view( $service_id );
                break;
            default:
                $this->render_services_list();
                break;
        }
	}

	/**
	 * Render services list table
	 */
	private function render_services_list() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services-list-table.php';
		$services_table = new KAB_Services_List_Table();
		$services_table->prepare_items();
		?>
		<div class="wrap kab-admin-wrapper">
			<!-- Static Header -->
			<div class="kab-admin-header">
				<div class="kab-admin-header-inner">
					<div class="kab-logo">
						<span class="kab-logo-icon dashicons dashicons-calendar-alt"></span>
						<span class="kab-logo-text"><?php echo esc_html__( 'Kura-ai Booking', 'kura-ai-booking-free' ); ?></span>
					</div>
					<nav class="kab-header-nav">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-dashboard' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Dashboard', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services' ) ); ?>" class="kab-nav-link active"><?php echo esc_html__( 'Services', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-customers' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Customers', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Settings', 'kura-ai-booking-free' ); ?></a>
					</nav>
				</div>
			</div>

			<!-- Page Content -->
			<div class="kab-card">
				<div class="kab-card-header">
					<h1 class="kab-card-title">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php echo esc_html__( 'Services Management', 'kura-ai-booking-free' ); ?>
					</h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services&action=add' ) ); ?>" class="kab-btn kab-btn-primary">
						<span class="dashicons dashicons-plus"></span>
						<?php echo esc_html__( 'Add New Service', 'kura-ai-booking-free' ); ?>
					</a>
				</div>
				<div class="kab-card-body">
                    <form method="post">
                        <?php
                        $services_table->display();
                        ?>
                    </form>
                    <script>document.addEventListener('DOMContentLoaded',function(){var suc=new URLSearchParams(window.location.search).get('success');if(typeof Swal!=='undefined'&&suc){var map={'0':'<?php echo esc_js( __( 'Operation failed', 'kura-ai-booking-free' ) ); ?>','1':'<?php echo esc_js( __( 'Service created', 'kura-ai-booking-free' ) ); ?>','2':'<?php echo esc_js( __( 'Service updated', 'kura-ai-booking-free' ) ); ?>','3':'<?php echo esc_js( __( 'Service deleted', 'kura-ai-booking-free' ) ); ?>'};if(map[suc]){Swal.fire({title:map[suc],icon:suc==='0'?'error':'success'});}}document.querySelectorAll('.kab-delete-service').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();var name=a.getAttribute('data-service-name');if(typeof Swal!=='undefined'){Swal.fire({title:'<?php echo esc_js( __( 'Delete service?', 'kura-ai-booking-free' ) ); ?>',text:name,icon:'warning',showCancelButton:true}).then(function(r){if(r.isConfirmed){window.location.href=a.href;}});}else{if(confirm('Delete '+name+'?'))window.location.href=a.href;}});});});</script>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Render Finance overview page
	 */
	public function render_finance_page() {
		?>
		<div class="wrap kab-admin-wrapper">
			<?php $this->render_static_header( 'finance' ); ?>
			<div class="kab-card">
				<div class="kab-card-header">
					<h2><?php echo esc_html__( 'Finance', 'kura-ai-booking-free' ); ?></h2>
				</div>
				<div class="kab-card-body">
					<p><?php echo esc_html__( 'Access invoices and ticket validation tools.', 'kura-ai-booking-free' ); ?></p>
					<div class="kab-invoice-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-invoices' ) ); ?>" class="kab-btn kab-btn-primary">
							<span class="dashicons dashicons-media-spreadsheet"></span>
							<?php echo esc_html__( 'Manage Invoices', 'kura-ai-booking-free' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-validation' ) ); ?>" class="kab-btn kab-btn-secondary">
							<span class="dashicons dashicons-yes"></span>
							<?php echo esc_html__( 'Ticket Validation', 'kura-ai-booking-free' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render service add/edit form
	 *
	 * @param int $service_id Service ID for editing. Default 0 for adding.
	 */
    private function render_service_form( $service_id = 0 ) {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
		$services_model = new KAB_Services();
		$service = null;

		if ( $service_id ) {
			$service = $services_model->get_service( $service_id );
		}

		$page_title = $service_id ? __( 'Edit Service', 'kura-ai-booking-free' ) : __( 'Add New Service', 'kura-ai-booking-free' );
		$button_text = $service_id ? __( 'Save Changes', 'kura-ai-booking-free' ) : __( 'Add Service', 'kura-ai-booking-free' );
		$nonce_action = $service_id ? 'kab_edit_service' : 'kab_add_service';
		$nonce_name = $service_id ? 'kab_edit_service_nonce' : 'kab_add_service_nonce';

		?>
		<div class="wrap kab-admin-wrapper">
			<!-- Static Header -->
			<div class="kab-admin-header">
				<div class="kab-admin-header-inner">
					<div class="kab-logo">
						<span class="kab-logo-icon dashicons dashicons-calendar-alt"></span>
						<span class="kab-logo-text"><?php echo esc_html__( 'Kura-ai Booking', 'kura-ai-booking-free' ); ?></span>
					</div>
					<nav class="kab-header-nav">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-dashboard' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Dashboard', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services' ) ); ?>" class="kab-nav-link active"><?php echo esc_html__( 'Services', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-customers' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Customers', 'kura-ai-booking-free' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="kab-nav-link"><?php echo esc_html__( 'Settings', 'kura-ai-booking-free' ); ?></a>
					</nav>
				</div>
			</div>

			<!-- Page Content -->
			<div class="kab-card">
				<div class="kab-card-header">
					<h1 class="kab-card-title">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php echo esc_html( $page_title ); ?>
					</h1>
				</div>
                <div class="kab-card-body">
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="<?php echo $service_id ? 'kab_edit_service' : 'kab_add_service'; ?>" />
                        <?php if ( $service_id ) : ?>
                            <input type="hidden" name="service_id" value="<?php echo esc_attr( $service_id ); ?>" />
                        <?php endif; ?>
                        <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>

                        <div class="kab-form-grid">
                            <div class="kab-col">
                                <div class="kab-form-group">
                                    <label for="name" class="kab-form-label"><?php esc_html_e( 'Service Name', 'kura-ai-booking-free' ); ?></label>
                                    <input type="text" name="name" id="name" class="kab-form-control" value="<?php echo $service ? esc_attr( $service['name'] ) : ''; ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="description" class="kab-form-label"><?php esc_html_e( 'Description', 'kura-ai-booking-free' ); ?></label>
                                    <textarea name="description" id="description" rows="5" class="kab-form-control" required><?php echo $service ? esc_textarea( $service['description'] ) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="kab-col">
                                <div class="kab-form-group">
                                    <label for="duration_date" class="kab-form-label"><?php esc_html_e( 'Duration (date)', 'kura-ai-booking-free' ); ?></label>
                                    <input type="date" name="duration_date" id="duration_date" class="kab-form-control" value="" required>
                                    <input type="hidden" name="duration" value="<?php echo $service ? esc_attr( $service['duration'] ) : 0; ?>">
                                </div>
                                <div class="kab-form-group">
                                    <label for="price" class="kab-form-label"><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></label>
                                    <input type="number" step="0.01" name="price" id="price" class="kab-form-control" value="<?php echo $service ? esc_attr( $service['price'] ) : ''; ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="currency" class="kab-form-label"><?php esc_html_e( 'Currency', 'kura-ai-booking-free' ); ?></label>
                                    <select name="currency" id="currency" class="kab-form-control" required>
                                        <?php $curr = $service ? esc_attr( $service['currency'] ?? 'USD' ) : 'USD'; ?>
                                        <option value="USD" <?php selected( $curr, 'USD' ); ?>>USD (&#36;)</option>
                                        <option value="EUR" <?php selected( $curr, 'EUR' ); ?>>EUR (&euro;)</option>
                                        <option value="GBP" <?php selected( $curr, 'GBP' ); ?>>GBP (&pound;)</option>
                                        <option value="JPY" <?php selected( $curr, 'JPY' ); ?>>JPY (&yen;)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="kab-form-group">
                            <label class="kab-form-label"><?php esc_html_e( 'Customer', 'kura-ai-booking-free' ); ?></label>
                            <?php echo wp_dropdown_users( array( 'echo' => false, 'name' => 'user_id', 'show_option_none' => __( 'Select existing customer', 'kura-ai-booking-free' ) ) ); ?>
                            <p style="margin-top:8px;"><?php esc_html_e( 'Or enter new customer details:', 'kura-ai-booking-free' ); ?></p>
                            <input type="text" name="customer_name" class="kab-form-control" placeholder="<?php esc_attr_e( 'Customer name', 'kura-ai-booking-free' ); ?>">
                            <input type="email" name="customer_email" class="kab-form-control" placeholder="<?php esc_attr_e( 'Customer email', 'kura-ai-booking-free' ); ?>">
                        </div>
                        <div class="kab-form-group">
                            <button type="submit" class="kab-btn kab-btn-primary">
                                <span class="dashicons dashicons-yes"></span>
                                <?php echo esc_html( $button_text ); ?>
                            </button>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services' ) ); ?>" class="kab-btn kab-btn-outline">
                                <span class="dashicons dashicons-no"></span>
                                <?php esc_html_e( 'Cancel', 'kura-ai-booking-free' ); ?>
                            </a>
                        </div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle add service form submission
	 */
    public function handle_add_service_action() {
		if ( ! isset( $_POST['kab_add_service_nonce'] ) || ! wp_verify_nonce( $_POST['kab_add_service_nonce'], 'kab_add_service' ) ) {
			return;
		}

		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
		$services_model = new KAB_Services();

        $duration_date = sanitize_text_field( $_POST['duration_date'] ?? '' );
        $service_data = array(
            'name'        => sanitize_text_field( $_POST['name'] ),
            'description' => sanitize_textarea_field( $_POST['description'] . ( $duration_date ? "\n" . sprintf( __( 'Duration date: %s', 'kura-ai-booking-free' ), $duration_date ) : '' ) ),
            'duration'    => intval( $_POST['duration'] ),
            'price'       => floatval( $_POST['price'] ),
            'currency'    => strtoupper( sanitize_text_field( $_POST['currency'] ?? 'USD' ) ),
        );

        $service_id = $services_model->create_service( $service_data );

        // Auto-generate invoice
        if ( $service_id ) {
            require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-invoices.php';
            $user_id = intval( $_POST['user_id'] ?? 0 );
            $cust_name = sanitize_text_field( $_POST['customer_name'] ?? '' );
            $cust_email = sanitize_email( $_POST['customer_email'] ?? '' );
            if ( $user_id ) {
                $u = get_user_by( 'id', $user_id );
                if ( $u ) { $cust_name = $u->display_name; $cust_email = $u->user_email; }
            }
            if ( $cust_name && $cust_email ) {
                $item = wp_json_encode( array( array( 'name' => $service_data['name'], 'amount' => $service_data['price'] ) ) );
                $invoice_id = KAB_Invoices::create_manual_invoice( $user_id ?: 0, $cust_name, $cust_email, $item, $service_data['price'], $service_data['currency'] );
                if ( $invoice_id ) {
                    $map = get_option( 'kab_service_invoice_map', array() );
                    $map[ $service_id ] = $invoice_id;
                    update_option( 'kab_service_invoice_map', $map );
                }
            }
        }

        $redirect_url = add_query_arg(
            array(
                'page'    => 'kab-services',
                'action'  => 'list',
                'success' => $service_id ? '1' : '0',
            ),
            admin_url( 'admin.php' )
        );

		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle edit service form submission
	 */
	public function handle_edit_service_action() {
		if ( ! isset( $_POST['kab_edit_service_nonce'] ) || ! wp_verify_nonce( $_POST['kab_edit_service_nonce'], 'kab_edit_service' ) ) {
			return;
		}

		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
		$services_model = new KAB_Services();

		$service_id = intval( $_POST['service_id'] );
        $service_data = array(
            'name'        => sanitize_text_field( $_POST['name'] ),
            'description' => sanitize_textarea_field( $_POST['description'] ),
            'duration'    => intval( $_POST['duration'] ),
            'price'       => floatval( $_POST['price'] ),
            'currency'    => strtoupper( sanitize_text_field( $_POST['currency'] ?? 'USD' ) ),
        );

		$result = $services_model->update_service( $service_id, $service_data );

		$redirect_url = add_query_arg(
			array(
				'page'    => 'kab-services',
				'action'  => 'list',
				'success' => $result ? '2' : '0',
			),
			admin_url( 'admin.php' )
		);

		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle delete service action
	 */
	public function handle_delete_service_action() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'kab_delete_service_' . $_GET['service_id'] ) ) {
			return;
		}

		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
		$services_model = new KAB_Services();

		$service_id = intval( $_GET['service_id'] );
		$result = $services_model->delete_service( $service_id );

		$redirect_url = add_query_arg(
			array(
				'page'    => 'kab-services',
				'action'  => 'list',
				'success' => $result ? '3' : '0',
			),
			admin_url( 'admin.php' )
		);

		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render events page
	 */
    public function render_events_page() {
        $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
        $event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

        switch ( $action ) {
            case 'add':
                $this->render_event_form();
                break;
            case 'edit':
                $this->render_event_form( $event_id );
                break;
            case 'view':
                $this->render_event_view( $event_id );
                break;
            default:
                $this->render_events_list();
                break;
        }
    }

	/**
	 * Render events list table
	 */
    private function render_events_list() {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events-list-table.php';
        $events_table = new KAB_Events_List_Table();
        $events_table->prepare_items();
        ?>
        <div class="kab-admin-wrapper">
            <?php $this->render_static_header( 'events' ); ?>
            
            <div class="kab-card">
                <div class="kab-card-header">
                    <h2><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></h2>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events&action=add' ) ); ?>" class="kab-btn kab-btn-primary">
                        <span class="dashicons dashicons-plus"></span>
                        <?php echo esc_html__( 'Add New', 'kura-ai-booking-free' ); ?>
                    </a>
                </div>
                <div class="kab-card-body">
                    <form method="post">
                        <?php
                        $events_table->display();
                        ?>
                    </form>
                </div>
            </div>
            <script>(function(){function waitSwal(cb){if(window.Swal){cb(window.Swal);return;}var i=setInterval(function(){if(window.Swal){clearInterval(i);cb(window.Swal);}},50);}document.querySelectorAll('.kab-delete-event').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();var href=a.getAttribute('href');var name=a.getAttribute('data-event-name');waitSwal(function(Swal){Swal.fire({title:'<?php echo esc_js( __( 'Delete event?', 'kura-ai-booking-free' ) ); ?>',text:name||'',icon:'warning',showCancelButton:true,confirmButtonText:'<?php echo esc_js( __( 'Delete', 'kura-ai-booking-free' ) ); ?>'}).then(function(r){if(r.isConfirmed){window.location.href=href;}});});});});var params=new URLSearchParams(window.location.search);if(params.has('success')){var code=params.get('success');var msg='';if(code==='4'){msg='<?php echo esc_js( __( 'Event created successfully', 'kura-ai-booking-free' ) ); ?>';}else if(code==='5'){msg='<?php echo esc_js( __( 'Event updated successfully', 'kura-ai-booking-free' ) ); ?>';}else if(code==='6'){msg='<?php echo esc_js( __( 'Event deleted successfully', 'kura-ai-booking-free' ) ); ?>';}if(msg){waitSwal(function(Swal){Swal.fire({title:msg,icon:'success'});});}}})();</script>
        </div>
        <?php
    }

    /**
     * Render service view page
     */
    private function render_service_view( $service_id ) {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
        $services_model = new KAB_Services();
        $service = $services_model->get_service( $service_id );
        if ( ! $service ) {
            wp_die( __( 'Service not found', 'kura-ai-booking-free' ) );
        }
        $map = get_option( 'kab_service_invoice_map', array() );
        $invoice_id = isset( $map[ $service_id ] ) ? intval( $map[ $service_id ] ) : 0;
        if ( ! $invoice_id ) {
            global $wpdb;
            // Try to find an invoice by matching the service name in the JSON payload
            $like = '%"name":"' . $wpdb->esc_like( $service['name'] ) . '"%';
            $invoice_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}kab_invoices WHERE item_name LIKE %s ORDER BY id DESC LIMIT 1", $like ) ) );
        }
        $invoice_url = $invoice_id ? admin_url( 'admin.php?page=kab-invoice-details&invoice_id=' . $invoice_id ) : '';
        ?>
        <div class="wrap kab-admin-wrapper">
            <?php $this->render_static_header( 'services' ); ?>
            <div class="kab-card">
                <div class="kab-card-header">
                    <h2><?php echo esc_html__( 'Service Details', 'kura-ai-booking-free' ); ?></h2>
                    <div>
                        <?php if ( $invoice_url ) : ?>
                            <a href="<?php echo esc_url( $invoice_url ); ?>" class="kab-btn kab-btn-success" style="margin-right:8px;"><?php esc_html_e( 'View Invoice', 'kura-ai-booking-free' ); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services' ) ); ?>" class="kab-btn">&larr; <?php echo esc_html__( 'Back', 'kura-ai-booking-free' ); ?></a>
                    </div>
                </div>
                <div class="kab-card-body">
                    <table class="kab-meta-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Field', 'kura-ai-booking-free' ); ?></th>
                                <th><?php esc_html_e( 'Value', 'kura-ai-booking-free' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><?php esc_html_e( 'Name', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $service['name'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Description', 'kura-ai-booking-free' ); ?></td><td><?php echo nl2br( esc_html( $service['description'] ) ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Duration', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $service['duration'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( kab_format_currency( (float) $service['price'], kab_currency_symbol( $service['currency'] ?? 'USD' ) ) ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Created', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $service['created_at'] ); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render event view page
     */
    private function render_event_view( $event_id ) {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
        $events_model = new KAB_Events();
        $event = $events_model->get_event( $event_id );
        if ( ! $event ) {
            wp_die( __( 'Event not found', 'kura-ai-booking-free' ) );
        }
        ?>
        <div class="wrap kab-admin-wrapper">
            <?php $this->render_static_header( 'events' ); ?>
            <div class="kab-card">
                <div class="kab-card-header">
                    <h2><?php echo esc_html__( 'Event Details', 'kura-ai-booking-free' ); ?></h2>
                    <div>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-btn">&larr; <?php echo esc_html__( 'Back', 'kura-ai-booking-free' ); ?></a>
                    </div>
                </div>
                <div class="kab-card-body">
                    <table class="kab-meta-table">
                        <thead>
                            <tr><th><?php esc_html_e( 'Field', 'kura-ai-booking-free' ); ?></th><th><?php esc_html_e( 'Value', 'kura-ai-booking-free' ); ?></th></tr>
                        </thead>
                        <tbody>
                            <tr><td><?php esc_html_e( 'Name', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['name'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Description', 'kura-ai-booking-free' ); ?></td><td><?php echo nl2br( esc_html( $event['description'] ) ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Date', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['event_date'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Time', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['event_time'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Location', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['location'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( kab_format_currency( (float) $event['price'], kab_currency_symbol( 'USD' ) ) ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Capacity', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['capacity'] ); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

	/**
	 * Render event add/edit form
	 *
	 * @param int $event_id Event ID for editing. Default 0 for adding.
	 */
    private function render_event_form( $event_id = 0 ) {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
        $events_model = new KAB_Events();
        $event = null;

        if ( $event_id ) {
            $event = $events_model->get_event( $event_id );
        }

        $page_title = $event_id ? __( 'Edit Event', 'kura-ai-booking-free' ) : __( 'Add New Event', 'kura-ai-booking-free' );
        $button_text = $event_id ? __( 'Save Changes', 'kura-ai-booking-free' ) : __( 'Add Event', 'kura-ai-booking-free' );
        $nonce_action = $event_id ? 'kab_edit_event' : 'kab_add_event';
        $nonce_name = $event_id ? 'kab_edit_event_nonce' : 'kab_add_event_nonce';

        ?>
        <div class="kab-admin-wrapper">
            <?php $this->render_static_header( 'events' ); ?>
            
            <div class="kab-card">
                <div class="kab-card-header">
                    <h2><?php echo esc_html( $page_title ); ?></h2>
                </div>
                <div class="kab-card-body">
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="<?php echo $event_id ? 'kab_edit_event' : 'kab_add_event'; ?>" />
                        <?php if ( $event_id ) : ?>
                            <input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
                        <?php endif; ?>
                        <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>

                        <div class="kab-form-grid">
                            <div class="kab-col">
                                <div class="kab-form-group">
                                    <label for="name" class="kab-form-label"><?php esc_html_e( 'Name', 'kura-ai-booking-free' ); ?></label>
                                    <input type="text" name="name" id="name" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['name'] ) : ''; ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="description" class="kab-form-label"><?php esc_html_e( 'Description', 'kura-ai-booking-free' ); ?></label>
                                    <textarea name="description" id="description" rows="5" class="kab-form-control" required><?php echo $event ? esc_textarea( $event['description'] ) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="kab-col">
                                <div class="kab-form-group">
                                    <label for="event_date" class="kab-form-label"><?php esc_html_e( 'Date', 'kura-ai-booking-free' ); ?></label>
                                    <input type="date" name="event_date" id="event_date" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['event_date'] ) : ''; ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="event_time" class="kab-form-label"><?php esc_html_e( 'Time', 'kura-ai-booking-free' ); ?></label>
                                    <input type="time" name="event_time" id="event_time" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['event_time'] ) : ''; ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="location" class="kab-form-label"><?php esc_html_e( 'Location', 'kura-ai-booking-free' ); ?></label>
                                    <input type="text" name="location" id="location" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['location'] ) : ''; ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="price" class="kab-form-label"><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></label>
                                    <input type="number" step="0.01" name="price" id="price" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['price'] ) : ''; ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="capacity" class="kab-form-label"><?php esc_html_e( 'Capacity', 'kura-ai-booking-free' ); ?></label>
                                    <input type="number" name="capacity" id="capacity" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['capacity'] ) : ''; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="kab-form-group">
                            <button type="submit" class="kab-btn kab-btn-primary">
                                <span class="dashicons dashicons-yes"></span>
                                <?php echo esc_html( $button_text ); ?>
                            </button>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-btn kab-btn-outline">
                                <span class="dashicons dashicons-no"></span>
                                <?php esc_html_e( 'Cancel', 'kura-ai-booking-free' ); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

	/**
	 * Handle add event form submission
	 */
    public function handle_add_event_action() {
        if ( ! isset( $_POST['kab_add_event_nonce'] ) || ! wp_verify_nonce( $_POST['kab_add_event_nonce'], 'kab_add_event' ) ) {
            return;
        }

        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
        $events_model = new KAB_Events();

        $event_data = array(
            'name'        => sanitize_text_field( $_POST['name'] ),
            'description' => sanitize_textarea_field( $_POST['description'] ),
            'event_date'  => sanitize_text_field( $_POST['event_date'] ),
            'event_time'  => sanitize_text_field( $_POST['event_time'] ),
            'location'    => sanitize_text_field( $_POST['location'] ),
            'price'       => floatval( $_POST['price'] ),
            'capacity'    => intval( $_POST['capacity'] ),
        );

        $event_id = $events_model->create_event( $event_data );

        $redirect_url = add_query_arg(
            array(
                'page'    => 'kab-events',
                'action'  => 'list',
                'success' => $event_id ? '4' : '0',
            ),
            admin_url( 'admin.php' )
        );

        wp_redirect( $redirect_url );
        exit;
    }

	/**
	 * Handle edit event form submission
	 */
    public function handle_edit_event_action() {
        if ( ! isset( $_POST['kab_edit_event_nonce'] ) || ! wp_verify_nonce( $_POST['kab_edit_event_nonce'], 'kab_edit_event' ) ) {
            return;
        }

        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
        $events_model = new KAB_Events();

        $event_id = intval( $_POST['event_id'] );
        $event_data = array(
            'name'        => sanitize_text_field( $_POST['name'] ),
            'description' => sanitize_textarea_field( $_POST['description'] ),
            'event_date'  => sanitize_text_field( $_POST['event_date'] ),
            'event_time'  => sanitize_text_field( $_POST['event_time'] ),
            'location'    => sanitize_text_field( $_POST['location'] ),
            'price'       => floatval( $_POST['price'] ),
            'capacity'    => intval( $_POST['capacity'] ),
        );

        $result = $events_model->update_event( $event_id, $event_data );

        $redirect_url = add_query_arg(
            array(
                'page'    => 'kab-events',
                'action'  => 'list',
                'success' => $result ? '5' : '0',
            ),
            admin_url( 'admin.php' )
        );

        wp_redirect( $redirect_url );
        exit;
    }

	/**
	 * Handle delete event action
	 */
    public function handle_delete_event_action() {
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'kab_delete_event_' . $_GET['event_id'] ) ) {
            return;
        }

        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
        $events_model = new KAB_Events();

        $event_id = intval( $_GET['event_id'] );
        $result = $events_model->delete_event( $event_id );

        $redirect_url = add_query_arg(
            array(
                'page'    => 'kab-events',
                'action'  => 'list',
                'success' => $result ? '6' : '0',
            ),
            admin_url( 'admin.php' )
        );

        wp_redirect( $redirect_url );
        exit;
    }

	/**
	 * Render customers page
	 */
	public function render_customers_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-customers-list-table.php';
		$customers_table = new KAB_Customers_List_Table();
		$customers_table->prepare_items();
		?>
		<div class="kab-admin-wrapper">
			<?php $this->render_static_header( 'customers' ); ?>
			
			<div class="kab-card">
				<div class="kab-card-header">
					<h2><?php echo esc_html__( 'Customers', 'kura-ai-booking-free' ); ?></h2>
				</div>
				<div class="kab-card-body">
					<form method="post">
						<?php
						$customers_table->display();
						?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		// Register a new setting for "kab-settings" page
		register_setting( 'kab-settings-group', 'kab_settings', array( $this, 'sanitize_settings' ) );

		// Add a new section to the "kab-settings" page
		add_settings_section(
			'kab_general_settings_section',
			__( 'General Settings', 'kura-ai-booking-free' ),
			array( $this, 'render_general_settings_section' ),
			'kab-settings'
		);

		// Add fields to the "kab_general_settings_section" section
		add_settings_field(
			'kab_company_name',
			__( 'Company Name', 'kura-ai-booking-free' ),
			array( $this, 'render_company_name_field' ),
			'kab-settings',
			'kab_general_settings_section'
		);

		add_settings_field(
			'kab_support_email',
			__( 'Support Email', 'kura-ai-booking-free' ),
			array( $this, 'render_support_email_field' ),
			'kab-settings',
			'kab_general_settings_section'
		);

		add_settings_field(
			'kab_enable_tickets',
			__( 'Enable E-Tickets', 'kura-ai-booking-free' ),
			array( $this, 'render_enable_tickets_field' ),
			'kab-settings',
			'kab_general_settings_section'
		);
	}

	/**
	 * Sanitize settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['company_name'] ) ) {
			$sanitized['company_name'] = sanitize_text_field( $input['company_name'] );
		}

		if ( isset( $input['support_email'] ) ) {
			$sanitized['support_email'] = sanitize_email( $input['support_email'] );
		}

		if ( isset( $input['enable_tickets'] ) ) {
			$sanitized['enable_tickets'] = absint( $input['enable_tickets'] );
		}

		return $sanitized;
	}

	/**
	 * Render general settings section
	 */
	public function render_general_settings_section() {
		echo '<p>' . esc_html__( 'Configure general settings for your booking system.', 'kura-ai-booking-free' ) . '</p>';
	}

	/**
	 * Render company name field
	 */
	public function render_company_name_field() {
		$options = get_option( 'kab_settings' );
		$value = isset( $options['company_name'] ) ? $options['company_name'] : '';
		?>
		<input type="text" name="kab_settings[company_name]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Render support email field
	 */
	public function render_support_email_field() {
		$options = get_option( 'kab_settings' );
		$value = isset( $options['support_email'] ) ? $options['support_email'] : '';
		?>
		<input type="email" name="kab_settings[support_email]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Render enable tickets field
	 */
	public function render_enable_tickets_field() {
		$options = get_option( 'kab_settings' );
		$value = isset( $options['enable_tickets'] ) ? $options['enable_tickets'] : 0;
		?>
		<label>
			<input type="checkbox" name="kab_settings[enable_tickets]" value="1" <?php checked( $value, 1 ); ?> />
			<?php esc_html_e( 'Enable QR code e-tickets for bookings', 'kura-ai-booking-free' ); ?>
		</label>
		<?php
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		?>
		<div class="kab-admin-wrapper">
			<?php $this->render_static_header( 'settings' ); ?>
			
			<div class="kab-card">
				<div class="kab-card-header">
					<h2><?php echo esc_html__( 'Settings', 'kura-ai-booking-free' ); ?></h2>
				</div>
				<div class="kab-card-body">
					<form method="post" action="options.php">
						<?php
						settings_fields( 'kab-settings-group' );
						do_settings_sections( 'kab-settings' );
						?>
						<div class="kab-form-group">
							<button type="submit" class="kab-btn kab-btn-primary">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render validation page
	 */
	public function render_validation_page() {
		?>
		<div class="kab-admin-wrapper">
			<?php $this->render_static_header( 'validation' ); ?>
			
			<div class="kab-card">
				<div class="kab-card-header">
					<h2><?php echo esc_html__( 'Ticket Validation', 'kura-ai-booking-free' ); ?></h2>
				</div>
				<div class="kab-card-body">
					<p><?php echo esc_html__( 'Enter a ticket ID to validate it.', 'kura-ai-booking-free' ); ?></p>
					
					<form method="post" action="" id="kab-validate-ticket-form">
						<div class="kab-form-group">
							<label for="ticket_id" class="kab-form-label"><?php esc_html_e( 'Ticket ID', 'kura-ai-booking-free' ); ?></label>
							<input type="text" name="ticket_id" id="ticket_id" class="kab-form-control" value="" required>
						</div>
						<div class="kab-form-group">
							<button type="submit" class="kab-btn kab-btn-primary">
								<span class="dashicons dashicons-search"></span>
								<?php esc_html_e( 'Validate Ticket', 'kura-ai-booking-free' ); ?>
							</button>
						</div>
					</form>
					
					<div id="kab-validation-result" style="display: none;"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show admin notices
	 */
	public function show_admin_notices() {
		if ( ! isset( $_GET['success'] ) ) {
			return;
		}

		$success_code = intval( $_GET['success'] );
		$messages = array(
			1 => __( 'Service created successfully.', 'kura-ai-booking-free' ),
			2 => __( 'Service updated successfully.', 'kura-ai-booking-free' ),
			3 => __( 'Service deleted successfully.', 'kura-ai-booking-free' ),
            4 => __( 'Event created successfully.', 'kura-ai-booking-free' ),
            5 => __( 'Event updated successfully.', 'kura-ai-booking-free' ),
            6 => __( 'Event deleted successfully.', 'kura-ai-booking-free' ),
		);

		if ( isset( $messages[ $success_code ] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $messages[ $success_code ] ) . '</p></div>';
		} elseif ( $success_code === 0 ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'An error occurred.', 'kura-ai-booking-free' ) . '</p></div>';
		}
	}
}
