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

                    <div class="kab-nav-group">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-nav-link <?php echo in_array( $active_page, array('events','calendar','appointments','recurring-events','event-categories'), true ) ? 'active' : ''; ?>"><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></a>
                        <div class="kab-dropdown">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'events' ? 'active' : ''; ?>"><?php echo esc_html__( 'Manage Events', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-calendar' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'calendar' ? 'active' : ''; ?>"><?php echo esc_html__( 'Calendar', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-appointments' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'appointments' ? 'active' : ''; ?>"><?php echo esc_html__( 'Appointments', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-recurring-events' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'recurring-events' ? 'active' : ''; ?>"><?php echo esc_html__( 'Recurring Events', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-event-categories' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'event-categories' ? 'active' : ''; ?>"><?php echo esc_html__( 'Event Categories', 'kura-ai-booking-free' ); ?></a>
                        </div>
                    </div>

                    <div class="kab-nav-group">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-customers' ) ); ?>" class="kab-nav-link <?php echo in_array( $active_page, array('customers','waitlist','reviews','invoices','create-invoice','invoice-details'), true ) ? 'active' : ''; ?>"><?php echo esc_html__( 'Customers', 'kura-ai-booking-free' ); ?></a>
                        <div class="kab-dropdown">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-customers' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'customers' ? 'active' : ''; ?>"><?php echo esc_html__( 'All Customers', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-invoices' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'invoices' ? 'active' : ''; ?>"><?php echo esc_html__( 'Invoices', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-create-invoice' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'create-invoice' ? 'active' : ''; ?>"><?php echo esc_html__( 'Create Invoice', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-waitlist' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'waitlist' ? 'active' : ''; ?>"><?php echo esc_html__( 'Waitlist', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-reviews' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'reviews' ? 'active' : ''; ?>"><?php echo esc_html__( 'Reviews & Ratings', 'kura-ai-booking-free' ); ?></a>
                        </div>
                    </div>

                    <div class="kab-nav-group">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-finance' ) ); ?>" class="kab-nav-link <?php echo in_array( $active_page, array('finance','cancellations'), true ) ? 'active' : ''; ?>"><?php echo esc_html__( 'Finance', 'kura-ai-booking-free' ); ?></a>
                        <div class="kab-dropdown">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-finance' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'finance' ? 'active' : ''; ?>"><?php echo esc_html__( 'Overview', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-cancellations' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'cancellations' ? 'active' : ''; ?>"><?php echo esc_html__( 'Cancellations & Refunds', 'kura-ai-booking-free' ); ?></a>
                        </div>
                    </div>

                    <div class="kab-nav-group">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="kab-nav-link <?php echo in_array( $active_page, array('settings','notifications','customize','custom-fields','validation','reminders','employees','locations','shortcodes'), true ) ? 'active' : ''; ?>"><?php echo esc_html__( 'Settings', 'kura-ai-booking-free' ); ?></a>
                        <div class="kab-dropdown">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'settings' ? 'active' : ''; ?>"><?php echo esc_html__( 'General', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-employees' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'employees' ? 'active' : ''; ?>"><?php echo esc_html__( 'Employees', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-locations' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'locations' ? 'active' : ''; ?>"><?php echo esc_html__( 'Locations', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-reminders' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'reminders' ? 'active' : ''; ?>"><?php echo esc_html__( 'Email Reminders', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-notifications' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'notifications' ? 'active' : ''; ?>"><?php echo esc_html__( 'Notifications', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-customize' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'customize' ? 'active' : ''; ?>"><?php echo esc_html__( 'Customize', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-custom-fields' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'custom-fields' ? 'active' : ''; ?>"><?php echo esc_html__( 'Custom Fields', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-validation' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'validation' ? 'active' : ''; ?>"><?php echo esc_html__( 'Ticket Validation', 'kura-ai-booking-free' ); ?></a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-shortcodes' ) ); ?>" class="kab-dropdown-link <?php echo $active_page === 'shortcodes' ? 'active' : ''; ?>"><?php echo esc_html__( 'Shortcodes', 'kura-ai-booking-free' ); ?></a>
                        </div>
                    </div>
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

		// Load Chart.js on dashboard and finance pages
		if ( $hook === 'toplevel_page_kab-dashboard' || $hook === 'kura-ai-booking_page_kab-finance' ) {
			wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
		}
	}

	/**
	 * Add admin menus
	 */
	public function add_admin_menus() {
		// Main menu page
		add_menu_page(
			__( 'Kura-ai Booking', 'kura-ai-booking-free' ),
			__( 'Kura-ai Booking', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-calendar-alt',
			25
		);

		// Rename first submenu to Dashboard
		add_submenu_page(
			'kab-dashboard',
			__( 'Dashboard', 'kura-ai-booking-free' ),
			__( 'Dashboard', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-dashboard',
			array( $this, 'render_dashboard_page' )
		);

		// Core menu items (visible in sidebar)
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
			__( 'Calendar', 'kura-ai-booking-free' ),
			__( 'Calendar', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-calendar',
			array( $this, 'render_calendar_page' )
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
			__( 'Employees', 'kura-ai-booking-free' ),
			__( 'Employees', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-employees',
			array( $this, 'render_employees_page' )
		);

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
			__( 'Settings', 'kura-ai-booking-free' ),
			__( 'Settings', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'kab-dashboard',
			__( 'Shortcodes', 'kura-ai-booking-free' ),
			__( 'Shortcodes', 'kura-ai-booking-free' ),
			'manage_options',
			'kab-shortcodes',
			array( $this, 'render_shortcodes_page' )
		);

		// Hidden pages (accessible via top nav dropdowns only)
		add_submenu_page( null, __( 'Ticket Validation', 'kura-ai-booking-free' ), __( 'Ticket Validation', 'kura-ai-booking-free' ), 'manage_options', 'kab-validation', array( $this, 'render_validation_page' ) );
		add_submenu_page( null, __( 'Appointments', 'kura-ai-booking-free' ), __( 'Appointments', 'kura-ai-booking-free' ), 'manage_options', 'kab-appointments', array( $this, 'render_appointments_page' ) );
		add_submenu_page( null, __( 'Locations', 'kura-ai-booking-free' ), __( 'Locations', 'kura-ai-booking-free' ), 'manage_options', 'kab-locations', array( $this, 'render_locations_page' ) );
		add_submenu_page( null, __( 'Notifications', 'kura-ai-booking-free' ), __( 'Notifications', 'kura-ai-booking-free' ), 'manage_options', 'kab-notifications', array( $this, 'render_notifications_page' ) );
		add_submenu_page( null, __( 'Customize', 'kura-ai-booking-free' ), __( 'Customize', 'kura-ai-booking-free' ), 'manage_options', 'kab-customize', array( $this, 'render_customize_page' ) );
		add_submenu_page( null, __( 'Custom Fields', 'kura-ai-booking-free' ), __( 'Custom Fields', 'kura-ai-booking-free' ), 'manage_options', 'kab-custom-fields', array( $this, 'render_custom_fields_page' ) );
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
			<div class="kab-card" style="border-top:4px solid #E67E22;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
				<div class="kab-card-header" style="background:linear-gradient(135deg, #E67E22 0%, #d06918 100%);padding:20px 24px;">
					<h1 class="kab-card-title" style="color:#fff;font-size:24px;margin:0;">
						<span class="dashicons dashicons-dashboard" style="color:#fff;"></span>
						<?php echo esc_html__( 'Dashboard Overview', 'kura-ai-booking-free' ); ?>
					</h1>
				</div>
                <div class="kab-card-body" style="padding:24px;">
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;">
                        <div class="kab-widget" style="background:linear-gradient(135deg, #E67E22 0%, #c66a1a 100%);color:#fff;border:none;box-shadow:0 4px 12px rgba(230,126,34,0.25);">
                            <h2 style="color:rgba(255,255,255,0.9);"><span class="dashicons dashicons-calendar"></span><?php esc_html_e('Total Bookings','kura-ai-booking-free'); ?></h2>
                            <div id="kab-kpi-total" style="color:#fff;font-size:32px;font-weight:700;">—</div>
                        </div>
                        <div class="kab-widget" style="background:linear-gradient(135deg, #628141 0%, #4a6131 100%);color:#fff;border:none;box-shadow:0 4px 12px rgba(98,129,65,0.25);">
                            <h2 style="color:rgba(255,255,255,0.9);"><span class="dashicons dashicons-groups"></span><?php esc_html_e('Customers','kura-ai-booking-free'); ?></h2>
                            <div id="kab-kpi-customers" style="color:#fff;font-size:32px;font-weight:700;">—</div>
                        </div>
                        <div class="kab-widget" style="background:linear-gradient(135deg, #8BAE66 0%, #6d8e4e 100%);color:#fff;border:none;box-shadow:0 4px 12px rgba(139,174,102,0.25);">
                            <h2 style="color:rgba(255,255,255,0.9);"><span class="dashicons dashicons-money"></span><?php esc_html_e('Revenue (30d)','kura-ai-booking-free'); ?></h2>
                            <div id="kab-kpi-revenue" style="color:#fff;font-size:32px;font-weight:700;">—</div>
                        </div>
                        <div class="kab-widget" style="background:linear-gradient(135deg, #EBD5AB 0%, #d4be8f 100%);color:#333;border:none;box-shadow:0 4px 12px rgba(235,213,171,0.35);">
                            <h2 style="color:rgba(0,0,0,0.7);"><span class="dashicons dashicons-clock"></span><?php esc_html_e('Upcoming','kura-ai-booking-free'); ?></h2>
                            <div id="kab-kpi-upcoming" style="color:#333;font-size:32px;font-weight:700;">—</div>
                        </div>
                    </div>
                    <div class="kab-mt-2" style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start;">
                        <div class="kab-card" style="border-top:4px solid #E67E22;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                            <div class="kab-card-header" style="background:linear-gradient(to right, #fff 0%, #fef5f0 100%);">
                                <h2 class="kab-card-title" style="color:#E67E22;"><span class="dashicons dashicons-chart-line"></span><?php esc_html_e('Bookings (last 30 days)','kura-ai-booking-free'); ?></h2>
                            </div>
                            <div class="kab-card-body" style="padding:20px;">
                                <canvas id="kab-chart-bookings" width="600" height="200" style="max-width:100%"></canvas>
                            </div>
                        </div>
                        <div class="kab-card" style="border-top:4px solid #8BAE66;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                            <div class="kab-card-header" style="background:linear-gradient(to right, #fff 0%, #f5f9f2 100%);">
                                <h2 class="kab-card-title" style="color:#628141;"><span class="dashicons dashicons-chart-bar"></span><?php esc_html_e('Top Services','kura-ai-booking-free'); ?></h2>
                            </div>
                            <div class="kab-card-body" style="padding:20px;">
                                <canvas id="kab-chart-topservices" width="300" height="200" style="max-width:100%"></canvas>
                            </div>
                        </div>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded',function(){
                        if (typeof Chart === 'undefined') {
                            console.error('Chart.js not loaded');
                            return;
                        }

                        var bookingsChart = null;
                        var topServicesChart = null;

                        console.log('Fetching dashboard metrics from: <?php echo esc_url( rest_url('kuraai/v1/dashboard/metrics') ); ?>');

                        fetch('<?php echo esc_url( rest_url('kuraai/v1/dashboard/metrics') ); ?>', {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            }
                        })
                        .then(function(r){
                            console.log('Response status:', r.status);
                            if (!r.ok) {
                                return r.text().then(function(text) {
                                    console.error('Response error:', text);
                                    throw new Error('Network response failed: ' + r.status);
                                });
                            }
                            return r.json();
                        })
                        .then(function(d){
                            console.log('Dashboard data received:', d);

                            // Update KPI values
                            var setText=function(id,val){var el=document.getElementById(id); if(el) el.textContent=val;};
                            setText('kab-kpi-total', d.total_bookings || 0);
                            setText('kab-kpi-customers', d.customers || 0);
                            setText('kab-kpi-revenue', '<?php echo esc_html( kab_currency_symbol('USD') ); ?>' + (d.revenue_last_30||0).toFixed(2));
                            setText('kab-kpi-upcoming', d.upcoming || 0);

                            // Bookings Line Chart
                            var c1=document.getElementById('kab-chart-bookings');
                            if(c1){
                                if(bookingsChart) bookingsChart.destroy();
                                var ctx1 = c1.getContext('2d');
                                bookingsChart = new Chart(ctx1, {
                                    type: 'line',
                                    data: {
                                        labels: d.labels || [],
                                        datasets: [{
                                            label: '<?php echo esc_js(__('Bookings','kura-ai-booking-free')); ?>',
                                            data: d.bookings_series || [],
                                            borderColor: '#E67E22',
                                            backgroundColor: 'rgba(230, 126, 34, 0.1)',
                                            borderWidth: 3,
                                            fill: true,
                                            tension: 0.4,
                                            pointBackgroundColor: '#E67E22',
                                            pointBorderColor: '#fff',
                                            pointBorderWidth: 2,
                                            pointRadius: 4,
                                            pointHoverRadius: 6
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                backgroundColor: '#24321a',
                                                titleColor: '#fff',
                                                bodyColor: '#fff',
                                                borderColor: '#E67E22',
                                                borderWidth: 1,
                                                padding: 12,
                                                displayColors: false,
                                                callbacks: {
                                                    label: function(context) {
                                                        return '<?php echo esc_js(__('Bookings','kura-ai-booking-free')); ?>: ' + context.parsed.y;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    precision: 0,
                                                    color: '#666'
                                                },
                                                grid: {
                                                    color: 'rgba(0, 0, 0, 0.05)'
                                                }
                                            },
                                            x: {
                                                ticks: {
                                                    color: '#666'
                                                },
                                                grid: {
                                                    display: false
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                            // Top Services Bar Chart
                            var c2=document.getElementById('kab-chart-topservices');
                            if(c2){
                                if(topServicesChart) topServicesChart.destroy();
                                var topServices = d.top_services || [];
                                var serviceLabels = topServices.map(function(r){return r.name || 'Unknown'});
                                var serviceValues = topServices.map(function(r){return parseInt(r.cnt) || 0});

                                var ctx2 = c2.getContext('2d');
                                topServicesChart = new Chart(ctx2, {
                                    type: 'bar',
                                    data: {
                                        labels: serviceLabels,
                                        datasets: [{
                                            label: '<?php echo esc_js(__('Bookings','kura-ai-booking-free')); ?>',
                                            data: serviceValues,
                                            backgroundColor: [
                                                '#8BAE66',
                                                '#628141',
                                                '#E67E22',
                                                '#EBD5AB',
                                                '#24321a'
                                            ],
                                            borderColor: [
                                                '#628141',
                                                '#24321a',
                                                '#c66a1a',
                                                '#d4be8f',
                                                '#000'
                                            ],
                                            borderWidth: 2,
                                            borderRadius: 6
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                backgroundColor: '#24321a',
                                                titleColor: '#fff',
                                                bodyColor: '#fff',
                                                borderColor: '#8BAE66',
                                                borderWidth: 1,
                                                padding: 12,
                                                displayColors: false,
                                                callbacks: {
                                                    label: function(context) {
                                                        return '<?php echo esc_js(__('Bookings','kura-ai-booking-free')); ?>: ' + context.parsed.y;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    precision: 0,
                                                    color: '#666'
                                                },
                                                grid: {
                                                    color: 'rgba(0, 0, 0, 0.05)'
                                                }
                                            },
                                            x: {
                                                ticks: {
                                                    color: '#666',
                                                    maxRotation: 45,
                                                    minRotation: 0
                                                },
                                                grid: {
                                                    display: false
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                            // Pie Chart: Booking Status Distribution
                            var c3=document.getElementById('kab-chart-pie');
                            if(c3 && d.pie_labels && d.pie_values){
                                var ctx3 = c3.getContext('2d');
                                new Chart(ctx3, {
                                    type: 'pie',
                                    data: {
                                        labels: d.pie_labels,
                                        datasets: [{
                                            data: d.pie_values,
                                            backgroundColor: [
                                                '#E67E22',
                                                '#8BAE66',
                                                '#628141',
                                                '#EBD5AB',
                                                '#24321a',
                                                '#3498db',
                                                '#e74c3c'
                                            ],
                                            borderColor: '#ffffff',
                                            borderWidth: 2
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    padding: 15,
                                                    font: {
                                                        size: 12
                                                    }
                                                }
                                            },
                                            tooltip: {
                                                backgroundColor: '#24321a',
                                                titleColor: '#fff',
                                                bodyColor: '#fff',
                                                borderColor: '#E67E22',
                                                borderWidth: 1,
                                                padding: 12,
                                                callbacks: {
                                                    label: function(context) {
                                                        var label = context.label || '';
                                                        var value = context.parsed || 0;
                                                        var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                                        var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                                        return label + ': ' + value + ' (' + percentage + '%)';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                            // Radar Chart: Performance Metrics
                            var c4=document.getElementById('kab-chart-radar');
                            if(c4 && d.radar_labels && d.radar_data){
                                var ctx4 = c4.getContext('2d');
                                new Chart(ctx4, {
                                    type: 'radar',
                                    data: {
                                        labels: d.radar_labels,
                                        datasets: [{
                                            label: '<?php echo esc_js(__('Booking Metrics','kura-ai-booking-free')); ?>',
                                            data: d.radar_data,
                                            backgroundColor: 'rgba(230, 126, 34, 0.2)',
                                            borderColor: '#E67E22',
                                            borderWidth: 2,
                                            pointBackgroundColor: '#E67E22',
                                            pointBorderColor: '#fff',
                                            pointBorderWidth: 2,
                                            pointRadius: 4,
                                            pointHoverRadius: 6,
                                            pointHoverBackgroundColor: '#fff',
                                            pointHoverBorderColor: '#E67E22'
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                backgroundColor: '#24321a',
                                                titleColor: '#fff',
                                                bodyColor: '#fff',
                                                borderColor: '#E67E22',
                                                borderWidth: 1,
                                                padding: 12
                                            }
                                        },
                                        scales: {
                                            r: {
                                                beginAtZero: true,
                                                ticks: {
                                                    precision: 0,
                                                    color: '#666'
                                                },
                                                grid: {
                                                    color: 'rgba(0, 0, 0, 0.1)'
                                                },
                                                pointLabels: {
                                                    color: '#666',
                                                    font: {
                                                        size: 12
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                            // Line Chart: Revenue Trend (6 Months)
                            var c5=document.getElementById('kab-chart-revenue');
                            if(c5 && d.revenue_labels && d.revenue_values){
                                var ctx5 = c5.getContext('2d');
                                new Chart(ctx5, {
                                    type: 'line',
                                    data: {
                                        labels: d.revenue_labels,
                                        datasets: [{
                                            label: '<?php echo esc_js(__('Revenue','kura-ai-booking-free')); ?>',
                                            data: d.revenue_values,
                                            borderColor: '#8BAE66',
                                            backgroundColor: 'rgba(139, 174, 102, 0.1)',
                                            borderWidth: 3,
                                            fill: true,
                                            tension: 0.4,
                                            pointBackgroundColor: '#8BAE66',
                                            pointBorderColor: '#fff',
                                            pointBorderWidth: 2,
                                            pointRadius: 5,
                                            pointHoverRadius: 7
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                backgroundColor: '#24321a',
                                                titleColor: '#fff',
                                                bodyColor: '#fff',
                                                borderColor: '#8BAE66',
                                                borderWidth: 1,
                                                padding: 12,
                                                displayColors: false,
                                                callbacks: {
                                                    label: function(context) {
                                                        return '<?php echo esc_js(__('Revenue','kura-ai-booking-free')); ?>: <?php echo esc_js(kab_currency_symbol('USD')); ?>' + context.parsed.y.toFixed(2);
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    precision: 2,
                                                    color: '#666',
                                                    callback: function(value) {
                                                        return '<?php echo esc_js(kab_currency_symbol('USD')); ?>' + value.toFixed(2);
                                                    }
                                                },
                                                grid: {
                                                    color: 'rgba(0, 0, 0, 0.05)'
                                                }
                                            },
                                            x: {
                                                ticks: {
                                                    color: '#666'
                                                },
                                                grid: {
                                                    display: false
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        })
                        .catch(function(err){
                            console.error('Failed to load dashboard metrics:', err);
                            var setText=function(id,val){var el=document.getElementById(id); if(el) el.textContent=val;};
                            setText('kab-kpi-total', 'Error');
                            setText('kab-kpi-customers', 'Error');
                            setText('kab-kpi-revenue', 'Error');
                            setText('kab-kpi-upcoming', 'Error');
                        });
                    });
                    </script>
                </div>
            </div>

            <div class="kab-dashboard-row" style="margin-top:20px;">
                <div class="kab-card" style="border-top:4px solid #628141;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <div class="kab-card-header" style="background:linear-gradient(to right, #fff 0%, #f5f9f2 100%);">
                        <h2 class="kab-card-title" style="color:#628141;">
                            <span class="dashicons dashicons-clock"></span>
                            <?php echo esc_html__( 'Upcoming Appointments', 'kura-ai-booking-free' ); ?>
                        </h2>
                    </div>
                    <div class="kab-card-body">
                        <?php if ( ! empty( $upcoming_appointments ) ) : ?>
                            <table class="kab-table"><thead><tr><th><?php esc_html_e('Type','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Item','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Date','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Time','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Status','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Actions','kura-ai-booking-free'); ?></th></tr></thead><tbody>
                            <?php global $wpdb; foreach ( $upcoming_appointments as $appointment ) : $is_service = ( $appointment['booking_type'] === 'service' ); $name = $is_service ? $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}kab_services WHERE id=%d", $appointment['service_id'] ) ) : $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}kab_events WHERE id=%d", $appointment['event_id'] ) ); $name = $name ? $name : ( $is_service ? sprintf( __('Service #%d','kura-ai-booking-free'), intval($appointment['service_id']) ) : sprintf( __('Event #%d','kura-ai-booking-free'), intval($appointment['event_id']) ) ); $view_url = $is_service ? admin_url( 'admin.php?page=kab-services&action=view&service_id=' . intval( $appointment['service_id'] ) ) : admin_url( 'admin.php?page=kab-events&action=view&event_id=' . intval( $appointment['event_id'] ) ); ?>
                                <tr>
                                    <td><?php echo esc_html( $is_service ? __( 'Service','kura-ai-booking-free' ) : __( 'Event','kura-ai-booking-free' ) ); ?></td>
                                    <td><?php echo esc_html( $name ); ?></td>
                                    <td><?php echo esc_html( $appointment['booking_date'] ); ?></td>
                                    <td><?php echo esc_html( $appointment['booking_time'] ); ?></td>
                                    <td><span class="kab-status-badge"><?php echo esc_html( ucfirst( $appointment['status'] ) ); ?></span></td>
                                    <td><a class="kab-btn kab-btn-secondary kab-btn-sm" href="<?php echo esc_url( $view_url ); ?>"><?php esc_html_e('View','kura-ai-booking-free'); ?></a> <a class="kab-btn kab-btn-sm" href="<?php echo esc_url( admin_url('admin.php?page=kab-calendar') ); ?>"><?php esc_html_e('Calendar','kura-ai-booking-free'); ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody></table>
                        <?php else : ?>
                            <p><?php echo esc_html__( 'You have no upcoming appointments.', 'kura-ai-booking-free' ); ?></p>
                        <?php endif; ?>
                </div>
                </div>


                <!-- Quick Actions placed before Recent Bookings to share row with Upcoming -->
                <div class="kab-card" style="border-top:4px solid #EBD5AB;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <div class="kab-card-header" style="background:linear-gradient(to right, #fff 0%, #fdfbf7 100%);">
                        <h2 class="kab-card-title" style="color:#c6a566;">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php echo esc_html__( 'Quick Actions', 'kura-ai-booking-free' ); ?>
                        </h2>
                    </div>
                    <div class="kab-card-body">
                        <div class="kab-mb-2">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-services&action=add' ) ); ?>" class="kab-btn kab-btn-primary" style="background:linear-gradient(135deg, #E67E22 0%, #d06918 100%);border:none;padding:12px 24px;font-weight:600;box-shadow:0 2px 6px rgba(230,126,34,0.3);transition:all 0.3s ease;">
                                <span class="dashicons dashicons-plus"></span>
                                <?php echo esc_html__( 'Add New Service', 'kura-ai-booking-free' ); ?>
                            </a>
                        </div>
                        <div>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events&action=add' ) ); ?>" class="kab-btn kab-btn-primary" style="background:linear-gradient(135deg, #8BAE66 0%, #779854 100%);border:none;padding:12px 24px;font-weight:600;box-shadow:0 2px 6px rgba(139,174,102,0.3);transition:all 0.3s ease;">
                                <span class="dashicons dashicons-plus"></span>
                                <?php echo esc_html__( 'Add New Event', 'kura-ai-booking-free' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
			</div>

            <!-- Advanced Analytics Section -->
            <div class="kab-card" style="margin-top:20px;border-top:4px solid #8BAE66;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <div class="kab-card-header" style="background:linear-gradient(to right, #fff 0%, #f5f9f2 100%);">
                    <h2 class="kab-card-title" style="color:#628141;">
                        <span class="dashicons dashicons-chart-area"></span>
                        <?php echo esc_html__( 'Advanced Analytics', 'kura-ai-booking-free' ); ?>
                    </h2>
                </div>
                <div class="kab-card-body" style="padding:24px;">
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:start;">
                        <!-- Pie Chart: Booking Status Distribution -->
                        <div class="kab-card" style="border-left:3px solid #E67E22;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                            <div class="kab-card-header" style="background:#fef5f0;">
                                <h3 class="kab-card-title" style="font-size:15px;color:#E67E22;">
                                    <span class="dashicons dashicons-chart-pie"></span>
                                    <?php esc_html_e('Booking Status', 'kura-ai-booking-free'); ?>
                                </h3>
                            </div>
                            <div class="kab-card-body">
                                <canvas id="kab-chart-pie" width="300" height="300" style="max-width:100%"></canvas>
                            </div>
                        </div>

                        <!-- Radar Chart: Performance Metrics -->
                        <div class="kab-card" style="border-left:3px solid #628141;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                            <div class="kab-card-header" style="background:#f5f9f2;">
                                <h3 class="kab-card-title" style="font-size:15px;color:#628141;">
                                    <span class="dashicons dashicons-performance"></span>
                                    <?php esc_html_e('Performance Metrics', 'kura-ai-booking-free'); ?>
                                </h3>
                            </div>
                            <div class="kab-card-body">
                                <canvas id="kab-chart-radar" width="300" height="300" style="max-width:100%"></canvas>
                            </div>
                        </div>

                        <!-- Line Chart: Revenue Trend -->
                        <div class="kab-card" style="border-left:3px solid #8BAE66;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                            <div class="kab-card-header" style="background:#f9fcf7;">
                                <h3 class="kab-card-title" style="font-size:15px;color:#8BAE66;">
                                    <span class="dashicons dashicons-chart-line"></span>
                                    <?php esc_html_e('Revenue Trend (6 Months)', 'kura-ai-booking-free'); ?>
                                </h3>
                            </div>
                            <div class="kab-card-body">
                                <canvas id="kab-chart-revenue" width="300" height="200" style="max-width:100%"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

			<style>
				/* Enhanced Dashboard Styles */
				.kab-widget {
					transition: transform 0.3s ease, box-shadow 0.3s ease;
				}
				.kab-widget:hover {
					transform: translateY(-4px);
					box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
				}
				.kab-card {
					transition: transform 0.2s ease, box-shadow 0.2s ease;
				}
				.kab-card:hover {
					box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
				}
				.kab-btn {
					transition: all 0.3s ease;
				}
				.kab-btn:hover {
					transform: translateY(-2px);
					box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
				}
				.kab-status-badge {
					padding: 4px 12px;
					border-radius: 12px;
					font-size: 12px;
					font-weight: 600;
					background: linear-gradient(135deg, #8BAE66 0%, #6d8e4e 100%);
					color: #fff;
					display: inline-block;
				}
			</style>
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
            <div class="kab-dashboard-widgets" style="margin-top:1.5rem;">
                <div class="kab-card">
                    <div class="kab-card-header">
                        <h2 class="kab-card-title">
                            <span class="dashicons dashicons-calendar"></span>
                            <?php echo esc_html__( 'Recent Bookings', 'kura-ai-booking-free' ); ?>
                        </h2>
                    </div>
                    <div class="kab-card-body">
                        <?php if ( ! empty( $recent_bookings ) ) : ?>
                            <table class="kab-table"><thead><tr><th><?php esc_html_e('Type','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Item','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Booked On','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Status','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Actions','kura-ai-booking-free'); ?></th></tr></thead><tbody>
                            <?php global $wpdb; foreach ( $recent_bookings as $booking ) : $is_service = ( $booking['booking_type'] === 'service' ); $name = $is_service ? $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}kab_services WHERE id=%d", $booking['service_id'] ) ) : $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}kab_events WHERE id=%d", $booking['event_id'] ) ); $name = $name ? $name : ( $is_service ? sprintf( __('Service #%d','kura-ai-booking-free'), intval($booking['service_id']) ) : sprintf( __('Event #%d','kura-ai-booking-free'), intval($booking['event_id']) ) ); $view_url = $is_service ? admin_url( 'admin.php?page=kab-services&action=view&service_id=' . intval( $booking['service_id'] ) ) : admin_url( 'admin.php?page=kab-events&action=view&event_id=' . intval( $booking['event_id'] ) ); $inv_status = $wpdb->get_var( $wpdb->prepare( "SELECT payment_status FROM {$wpdb->prefix}kab_invoices WHERE booking_id=%d ORDER BY id DESC LIMIT 1", $booking['id'] ) ); ?>
                                <tr>
                                    <td><?php echo esc_html( $is_service ? __( 'Service','kura-ai-booking-free' ) : __( 'Event','kura-ai-booking-free' ) ); ?></td>
                                    <td><?php echo esc_html( $name ); ?></td>
                                    <td><?php echo esc_html( $booking['created_at'] ); ?></td>
                                    <td><span class="kab-status-badge"><?php echo esc_html( $inv_status ? ucfirst( $inv_status ) : ucfirst( $booking['status'] ) ); ?></span></td>
                                    <td><a class="kab-btn kab-btn-secondary kab-btn-sm" href="<?php echo esc_url( $view_url ); ?>"><?php esc_html_e('View','kura-ai-booking-free'); ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody></table>
                        <?php else : ?>
                            <p><?php echo esc_html__( 'No recent bookings found.', 'kura-ai-booking-free' ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Render Finance overview page
	 */
	public function render_finance_page() {
		global $wpdb;
		$prefix = $wpdb->prefix;

		// Calculate financial metrics
		$total_revenue = floatval( $wpdb->get_var( "SELECT COALESCE(SUM(total_amount), 0) FROM {$prefix}kab_invoices WHERE payment_status='paid'" ) );
		$pending_revenue = floatval( $wpdb->get_var( "SELECT COALESCE(SUM(total_amount), 0) FROM {$prefix}kab_invoices WHERE payment_status='pending'" ) );
		$total_invoices = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}kab_invoices" ) );
		$paid_invoices = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}kab_invoices WHERE payment_status='paid'" ) );
		$refunds_total = floatval( $wpdb->get_var( "SELECT COALESCE(SUM(refund_amount), 0) FROM {$prefix}kab_cancellations WHERE refund_status='completed'" ) );
		$refunds_count = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}kab_cancellations WHERE refund_status='completed'" ) );

		// Revenue this month
		$month_revenue = floatval( $wpdb->get_var( "SELECT COALESCE(SUM(total_amount), 0) FROM {$prefix}kab_invoices WHERE payment_status='paid' AND MONTH(invoice_date) = MONTH(CURDATE()) AND YEAR(invoice_date) = YEAR(CURDATE())" ) );

		// Average transaction value
		$avg_transaction = $paid_invoices > 0 ? ($total_revenue / $paid_invoices) : 0;

		?>
		<div class="wrap kab-admin-wrapper">
			<?php $this->render_static_header( 'finance' ); ?>

			<!-- Financial KPIs -->
			<div class="kab-card">
				<div class="kab-card-header">
					<h1 class="kab-card-title">
						<span class="dashicons dashicons-money-alt"></span>
						<?php echo esc_html__( 'Financial Overview', 'kura-ai-booking-free' ); ?>
					</h1>
				</div>
				<div class="kab-card-body">
					<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
						<div class="kab-widget">
							<h2><span class="dashicons dashicons-chart-line"></span><?php esc_html_e('Total Revenue','kura-ai-booking-free'); ?></h2>
							<div style="font-size:28px;font-weight:bold;color:#8BAE66;"><?php echo esc_html( kab_currency_symbol('USD') . number_format($total_revenue, 2) ); ?></div>
						</div>
						<div class="kab-widget">
							<h2><span class="dashicons dashicons-money"></span><?php esc_html_e('This Month','kura-ai-booking-free'); ?></h2>
							<div style="font-size:28px;font-weight:bold;color:#E67E22;"><?php echo esc_html( kab_currency_symbol('USD') . number_format($month_revenue, 2) ); ?></div>
						</div>
						<div class="kab-widget">
							<h2><span class="dashicons dashicons-clock"></span><?php esc_html_e('Pending','kura-ai-booking-free'); ?></h2>
							<div style="font-size:28px;font-weight:bold;color:#f39c12;"><?php echo esc_html( kab_currency_symbol('USD') . number_format($pending_revenue, 2) ); ?></div>
						</div>
						<div class="kab-widget">
							<h2><span class="dashicons dashicons-undo"></span><?php esc_html_e('Refunds','kura-ai-booking-free'); ?></h2>
							<div style="font-size:28px;font-weight:bold;color:#e74c3c;"><?php echo esc_html( kab_currency_symbol('USD') . number_format($refunds_total, 2) ); ?></div>
						</div>
					</div>

					<!-- Secondary Metrics -->
					<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
						<div class="kab-widget" style="background:#f8f9fa;">
							<h2><?php esc_html_e('Total Invoices','kura-ai-booking-free'); ?></h2>
							<div style="font-size:24px;font-weight:600;"><?php echo esc_html($total_invoices); ?></div>
						</div>
						<div class="kab-widget" style="background:#f8f9fa;">
							<h2><?php esc_html_e('Paid Invoices','kura-ai-booking-free'); ?></h2>
							<div style="font-size:24px;font-weight:600;color:#27ae60;"><?php echo esc_html($paid_invoices); ?></div>
						</div>
						<div class="kab-widget" style="background:#f8f9fa;">
							<h2><?php esc_html_e('Avg Transaction','kura-ai-booking-free'); ?></h2>
							<div style="font-size:24px;font-weight:600;"><?php echo esc_html( kab_currency_symbol('USD') . number_format($avg_transaction, 2) ); ?></div>
						</div>
						<div class="kab-widget" style="background:#f8f9fa;">
							<h2><?php esc_html_e('Refund Count','kura-ai-booking-free'); ?></h2>
							<div style="font-size:24px;font-weight:600;color:#e74c3c;"><?php echo esc_html($refunds_count); ?></div>
						</div>
					</div>
				</div>
			</div>

			<!-- Charts Section -->
			<div class="kab-card" style="margin-top:20px;">
				<div class="kab-card-header">
					<h2 class="kab-card-title">
						<span class="dashicons dashicons-chart-area"></span>
						<?php echo esc_html__( 'Financial Analytics', 'kura-ai-booking-free' ); ?>
					</h2>
				</div>
				<div class="kab-card-body">
					<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:20px;">
						<!-- Revenue Trend (12 Months) -->
						<div class="kab-card">
							<div class="kab-card-header">
								<h3 class="kab-card-title" style="font-size:16px;">
									<span class="dashicons dashicons-chart-line"></span>
									<?php esc_html_e('Monthly Revenue (12 Months)', 'kura-ai-booking-free'); ?>
								</h3>
							</div>
							<div class="kab-card-body">
								<canvas id="kab-finance-revenue-chart" width="400" height="250" style="max-width:100%"></canvas>
							</div>
						</div>

						<!-- Payment Methods -->
						<div class="kab-card">
							<div class="kab-card-header">
								<h3 class="kab-card-title" style="font-size:16px;">
									<span class="dashicons dashicons-cart"></span>
									<?php esc_html_e('Payment Methods', 'kura-ai-booking-free'); ?>
								</h3>
							</div>
							<div class="kab-card-body">
								<canvas id="kab-finance-payment-methods" width="400" height="250" style="max-width:100%"></canvas>
							</div>
						</div>
					</div>

					<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
						<!-- Invoice Status Distribution -->
						<div class="kab-card">
							<div class="kab-card-header">
								<h3 class="kab-card-title" style="font-size:16px;">
									<span class="dashicons dashicons-chart-pie"></span>
									<?php esc_html_e('Invoice Status', 'kura-ai-booking-free'); ?>
								</h3>
							</div>
							<div class="kab-card-body">
								<canvas id="kab-finance-invoice-status" width="300" height="300" style="max-width:100%"></canvas>
							</div>
						</div>

						<!-- Top Revenue Sources -->
						<div class="kab-card">
							<div class="kab-card-header">
								<h3 class="kab-card-title" style="font-size:16px;">
									<span class="dashicons dashicons-star-filled"></span>
									<?php esc_html_e('Revenue by Type', 'kura-ai-booking-free'); ?>
								</h3>
							</div>
							<div class="kab-card-body">
								<canvas id="kab-finance-revenue-type" width="300" height="300" style="max-width:100%"></canvas>
							</div>
						</div>

						<!-- Weekly Revenue Comparison -->
						<div class="kab-card">
							<div class="kab-card-header">
								<h3 class="kab-card-title" style="font-size:16px;">
									<span class="dashicons dashicons-calendar-alt"></span>
									<?php esc_html_e('Weekly Comparison', 'kura-ai-booking-free'); ?>
								</h3>
							</div>
							<div class="kab-card-body">
								<canvas id="kab-finance-weekly" width="300" height="300" style="max-width:100%"></canvas>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Quick Actions -->
			<div class="kab-card" style="margin-top:20px;">
				<div class="kab-card-header">
					<h2 class="kab-card-title">
						<span class="dashicons dashicons-admin-tools"></span>
						<?php echo esc_html__( 'Quick Actions', 'kura-ai-booking-free' ); ?>
					</h2>
				</div>
				<div class="kab-card-body">
					<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-invoices' ) ); ?>" class="kab-btn kab-btn-primary" style="text-align:center;">
							<span class="dashicons dashicons-media-spreadsheet"></span>
							<?php echo esc_html__( 'View All Invoices', 'kura-ai-booking-free' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-create-invoice' ) ); ?>" class="kab-btn kab-btn-secondary" style="text-align:center;">
							<span class="dashicons dashicons-plus-alt"></span>
							<?php echo esc_html__( 'Create Invoice', 'kura-ai-booking-free' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-cancellations' ) ); ?>" class="kab-btn kab-btn-secondary" style="text-align:center;">
							<span class="dashicons dashicons-undo"></span>
							<?php echo esc_html__( 'Refunds', 'kura-ai-booking-free' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-settings' ) ); ?>" class="kab-btn kab-btn-secondary" style="text-align:center;">
							<span class="dashicons dashicons-admin-settings"></span>
							<?php echo esc_html__( 'Payment Settings', 'kura-ai-booking-free' ); ?>
						</a>
					</div>
				</div>
			</div>

			<script>
			document.addEventListener('DOMContentLoaded', function(){
				if (typeof Chart === 'undefined') {
					console.error('Chart.js not loaded on finance page');
					return;
				}

				fetch('<?php echo esc_url( rest_url('kuraai/v1/finance/metrics') ); ?>')
				.then(r => r.json())
				.then(d => {
					console.log('Finance data:', d);

					// Monthly Revenue Chart
					new Chart(document.getElementById('kab-finance-revenue-chart').getContext('2d'), {
						type: 'line',
						data: {
							labels: d.revenue_months || [],
							datasets: [{
								label: 'Revenue',
								data: d.revenue_values || [],
								borderColor: '#8BAE66',
								backgroundColor: 'rgba(139, 174, 102, 0.1)',
								borderWidth: 3,
								fill: true,
								tension: 0.4
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: { legend: { display: false } },
							scales: {
								y: { beginAtZero: true, ticks: { callback: v => '<?php echo esc_js(kab_currency_symbol('USD')); ?>' + v.toFixed(2) } }
							}
						}
					});

					// Payment Methods
					new Chart(document.getElementById('kab-finance-payment-methods').getContext('2d'), {
						type: 'doughnut',
						data: {
							labels: d.payment_method_labels || ['No Data'],
							datasets: [{
								data: d.payment_method_values || [1],
								backgroundColor: ['#E67E22', '#8BAE66', '#628141', '#EBD5AB', '#3498db', '#e74c3c']
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: { legend: { position: 'bottom' } }
						}
					});

					// Invoice Status
					new Chart(document.getElementById('kab-finance-invoice-status').getContext('2d'), {
						type: 'pie',
						data: {
							labels: d.status_labels || ['No Data'],
							datasets: [{
								data: d.status_values || [1],
								backgroundColor: ['#27ae60', '#f39c12', '#e74c3c', '#95a5a6']
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: true,
							plugins: { legend: { position: 'bottom' } }
						}
					});

					// Revenue by Type
					new Chart(document.getElementById('kab-finance-revenue-type').getContext('2d'), {
						type: 'doughnut',
						data: {
							labels: d.type_labels || ['No Data'],
							datasets: [{
								data: d.type_values || [1],
								backgroundColor: ['#E67E22', '#8BAE66', '#628141']
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: true,
							plugins: { legend: { position: 'bottom' } }
						}
					});

					// Weekly Comparison
					new Chart(document.getElementById('kab-finance-weekly').getContext('2d'), {
						type: 'bar',
						data: {
							labels: d.weekly_labels || [],
							datasets: [{
								label: 'This Week',
								data: d.weekly_this || [],
								backgroundColor: '#8BAE66'
							}, {
								label: 'Last Week',
								data: d.weekly_last || [],
								backgroundColor: '#E67E22'
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: true,
							plugins: { legend: { position: 'bottom' } },
							scales: { y: { beginAtZero: true } }
						}
					});
				})
				.catch(err => console.error('Finance metrics error:', err));
			});
			</script>
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
                                    <input type="text" name="name" id="name" class="kab-form-control" value="<?php echo $service ? esc_attr( $service['name'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Service name', 'kura-ai-booking-free' ); ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="description" class="kab-form-label"><?php esc_html_e( 'Description', 'kura-ai-booking-free' ); ?></label>
                                    <textarea name="description" id="description" rows="5" class="kab-form-control" placeholder="<?php esc_attr_e( 'Describe the service', 'kura-ai-booking-free' ); ?>" required><?php echo $service ? esc_textarea( $service['description'] ) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="kab-col">
                                <div class="kab-form-group">
                                    <label for="duration_date" class="kab-form-label"><?php esc_html_e( 'Duration (date)', 'kura-ai-booking-free' ); ?></label>
                                    <input type="date" name="duration_date" id="duration_date" class="kab-form-control" value="" placeholder="<?php esc_attr_e( 'mm/dd/yyyy', 'kura-ai-booking-free' ); ?>" required>
                                    <input type="hidden" name="duration" value="<?php echo $service ? esc_attr( $service['duration'] ) : 0; ?>">
                                </div>
                                <div class="kab-form-group">
                                    <label for="price" class="kab-form-label"><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></label>
                                    <input type="number" step="0.01" name="price" id="price" class="kab-form-control" value="<?php echo $service ? esc_attr( $service['price'] ) : ''; ?>" placeholder="<?php esc_attr_e( '0.00', 'kura-ai-booking-free' ); ?>" required>
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
                                <div class="kab-form-group">
                                    <label class="kab-form-label"><?php esc_html_e( 'Allowed Payment Methods', 'kura-ai-booking-free' ); ?></label>
                                    <?php $pm = $service && ! empty( $service['payment_methods'] ) ? (array) json_decode( $service['payment_methods'], true ) : array(); ?>
                                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                                        <label><input type="checkbox" name="payment_methods[]" value="onsite" <?php checked( in_array( 'onsite', $pm, true ) ); ?> /> <?php esc_html_e( 'On-site', 'kura-ai-booking-free' ); ?></label>
                                        <label><input type="checkbox" name="payment_methods[]" value="paypal" <?php checked( in_array( 'paypal', $pm, true ) ); ?> /> <?php esc_html_e( 'PayPal', 'kura-ai-booking-free' ); ?></label>
                                        <label><input type="checkbox" name="payment_methods[]" value="stripe" <?php checked( in_array( 'stripe', $pm, true ) ); ?> /> <?php esc_html_e( 'Stripe', 'kura-ai-booking-free' ); ?></label>
                                        <label><input type="checkbox" name="payment_methods[]" value="mollie" <?php checked( in_array( 'mollie', $pm, true ) ); ?> /> <?php esc_html_e( 'Mollie', 'kura-ai-booking-free' ); ?></label>
                                        <label><input type="checkbox" name="payment_methods[]" value="razorpay" <?php checked( in_array( 'razorpay', $pm, true ) ); ?> /> <?php esc_html_e( 'Razorpay', 'kura-ai-booking-free' ); ?></label>
                                        <label><input type="checkbox" name="payment_methods[]" value="paystack" <?php checked( in_array( 'paystack', $pm, true ) ); ?> /> <?php esc_html_e( 'Paystack', 'kura-ai-booking-free' ); ?></label>
                                        <label><input type="checkbox" name="payment_methods[]" value="flutterwave" <?php checked( in_array( 'flutterwave', $pm, true ) ); ?> /> <?php esc_html_e( 'Flutterwave', 'kura-ai-booking-free' ); ?></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'If none is checked, global defaults apply.', 'kura-ai-booking-free' ); ?></p>
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

        $service_data['payment_methods'] = isset( $_POST['payment_methods'] ) ? array_map( 'sanitize_key', (array) $_POST['payment_methods'] ) : array();
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
        $service_data['payment_methods'] = isset( $_POST['payment_methods'] ) ? array_map( 'sanitize_key', (array) $_POST['payment_methods'] ) : array();

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
            case 'attendees':
                $this->render_event_attendees( $event_id );
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
                <div class="kab-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h2><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></h2>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events&action=add' ) ); ?>" class="kab-btn kab-btn-primary">
                        <span class="dashicons dashicons-plus"></span>
                        <?php echo esc_html__( 'New Event', 'kura-ai-booking-free' ); ?>
                    </a>
                </div>
                <div class="kab-card-body">
                    <?php
                    require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
                    $events_model = new KAB_Events();
                    $df = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : '';
                    $dt = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : '';
                    $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
                    $args = array( 'number' => 200, 'offset' => 0, 'orderby' => 'event_date', 'order' => 'ASC' );
                    $items = $events_model->get_events( $args );
                    if ( $df ) { $items = array_filter( $items, function($e) use ($df){ return $e['event_date'] >= $df; }); }
                    if ( $dt ) { $items = array_filter( $items, function($e) use ($dt){ return $e['event_date'] <= $dt; }); }
                    if ( $search ) { $s = mb_strtolower( $search ); $items = array_filter( $items, function($e) use ($s){ return strpos( mb_strtolower( $e['name'] ), $s ) !== false || strpos( mb_strtolower( $e['location'] ), $s ) !== false; }); }
                    ?>
                    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="kab-filter-row" style="margin-bottom:10px;">
                        <input type="hidden" name="page" value="kab-events" />
                        <div class="kab-filter-group"><label class="kab-filter-label"><?php esc_html_e('Date From','kura-ai-booking-free'); ?></label><input type="date" name="date_from" class="kab-filter-input" value="<?php echo esc_attr( $df ); ?>" /></div>
                        <div class="kab-filter-group"><label class="kab-filter-label"><?php esc_html_e('Date To','kura-ai-booking-free'); ?></label><input type="date" name="date_to" class="kab-filter-input" value="<?php echo esc_attr( $dt ); ?>" /></div>
                        <div class="kab-filter-group"><label class="kab-filter-label"><?php esc_html_e('Search','kura-ai-booking-free'); ?></label><input type="text" name="search" class="kab-filter-input" placeholder="<?php esc_attr_e('Search Events','kura-ai-booking-free'); ?>" value="<?php echo esc_attr( $search ); ?>" /></div>
                        <div class="kab-filter-group" style="align-self:flex-end"><button class="kab-btn kab-btn-primary" type="submit"><?php esc_html_e('Filter','kura-ai-booking-free'); ?></button></div>
                    </form>
                    <?php
                    $grouped = array();
                    foreach ( $items as $it ) { $grouped[ $it['event_date'] ][] = $it; }
                    ksort( $grouped );
                    global $wpdb;
                    foreach ( $grouped as $date => $rows ) {
                        echo '<div style="margin:10px 0 6px;color:#24321a;font-weight:600;">' . esc_html( date_i18n( get_option('date_format'), strtotime($date) ) ) . '</div>';
                        echo '<table class="kab-table" style="margin-bottom:20px;"><thead><tr><th>' . esc_html__('Time','kura-ai-booking-free') . '</th><th>' . esc_html__('Name','kura-ai-booking-free') . '</th><th>' . esc_html__('Organizer','kura-ai-booking-free') . '</th><th>' . esc_html__('Booked','kura-ai-booking-free') . '</th><th>' . esc_html__('Booking Opens','kura-ai-booking-free') . '</th><th>' . esc_html__('Booking Closes','kura-ai-booking-free') . '</th><th>' . esc_html__('Status','kura-ai-booking-free') . '</th><th>' . esc_html__('Actions','kura-ai-booking-free') . '</th></tr></thead><tbody>';
                        foreach ( $rows as $e ) {
                            $booked = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}kab_bookings WHERE booking_type='event' AND event_id=%d", $e['id'] ) );
                            $status = ( strtotime( $e['event_date'] . ' ' . $e['event_time'] ) > time() ) ? esc_html__('Opened','kura-ai-booking-free') : esc_html__('Closed','kura-ai-booking-free');
                            $edit_url = admin_url( 'admin.php?page=kab-events&action=edit&event_id=' . intval( $e['id'] ) );
                            echo '<tr>';
                            $timerange = $e['event_time'] . ( ! empty( $e['event_end_time'] ) ? ' - ' . $e['event_end_time'] : '' );
                            echo '<td>' . esc_html( $timerange ) . '</td>';
                            echo '<td>' . esc_html( $e['name'] ) . ' ' . ( ! empty( $e['tags'] ) ? '<small style="color:#6b7a5a">(' . esc_html( $e['tags'] ) . ')</small>' : '' ) . '</td>';
                            echo '<td>' . esc_html( $e['organizer'] ) . '</td>';
                            echo '<td>' . esc_html( $booked . ' / ' . (int) $e['capacity'] ) . '</td>';
                            echo '<td>' . esc_html( $e['booking_open'] ? date_i18n( get_option('date_format').' @ g:i a', strtotime( $e['booking_open'] ) ) : '—' ) . '</td>';
                            echo '<td>' . esc_html( $e['booking_close'] ? date_i18n( get_option('date_format').' @ g:i a', strtotime( $e['booking_close'] ) ) : '—' ) . '</td>';
                            echo '<td>' . esc_html( $status ) . '</td>';
                            $att_url = admin_url( 'admin.php?page=kab-events&action=attendees&event_id=' . intval( $e['id'] ) );
                            echo '<td><a href="' . esc_url( $att_url ) . '" class="kab-btn kab-btn-secondary kab-btn-sm">' . esc_html__('Attendees','kura-ai-booking-free') . '</a> <a href="' . esc_url( $edit_url ) . '" class="kab-btn kab-btn-primary kab-btn-sm">' . esc_html__('Edit','kura-ai-booking-free') . '</a></td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    }
                    ?>
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
                            <tr><td><?php esc_html_e( 'Time', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['event_time'] . ( $event['event_end_time'] ? ' - ' . $event['event_end_time'] : '' ) ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Organizer', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['organizer'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Location', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['location'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( kab_format_currency( (float) $event['price'], kab_currency_symbol( 'USD' ) ) ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Capacity', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['capacity'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Booking Opens', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['booking_open'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Booking Closes', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['booking_close'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Tags', 'kura-ai-booking-free' ); ?></td><td><?php echo esc_html( $event['tags'] ); ?></td></tr>
                            <tr><td><?php esc_html_e( 'Zoom Host URL', 'kura-ai-booking-free' ); ?></td><td><?php echo !empty($event['zoom_host_url'])?'<a href="'.esc_url($event['zoom_host_url']).'" target="_blank">'.esc_html__('Open','kura-ai-booking-free').'</a>':'—'; ?></td></tr>
                            <tr><td><?php esc_html_e( 'Zoom Join URL', 'kura-ai-booking-free' ); ?></td><td><?php echo !empty($event['zoom_join_url'])?'<a href="'.esc_url($event['zoom_join_url']).'" target="_blank">'.esc_html__('Open','kura-ai-booking-free').'</a>':'—'; ?></td></tr>
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
                                    <input type="text" name="name" id="name" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['name'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Event name', 'kura-ai-booking-free' ); ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="description" class="kab-form-label"><?php esc_html_e( 'Description', 'kura-ai-booking-free' ); ?></label>
                                    <textarea name="description" id="description" rows="5" class="kab-form-control" placeholder="<?php esc_attr_e( 'Describe the event', 'kura-ai-booking-free' ); ?>" required><?php echo $event ? esc_textarea( $event['description'] ) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="kab-col">
                                <div class="kab-form-group">
                                    <label for="event_date" class="kab-form-label"><?php esc_html_e( 'Date', 'kura-ai-booking-free' ); ?></label>
                                    <input type="date" name="event_date" id="event_date" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['event_date'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'YYYY-MM-DD', 'kura-ai-booking-free' ); ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="event_time" class="kab-form-label"><?php esc_html_e( 'Start Time', 'kura-ai-booking-free' ); ?></label>
                                    <input type="time" name="event_time" id="event_time" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['event_time'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'HH:MM', 'kura-ai-booking-free' ); ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="event_end_time" class="kab-form-label"><?php esc_html_e( 'End Time', 'kura-ai-booking-free' ); ?></label>
                                    <input type="time" name="event_end_time" id="event_end_time" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['event_end_time'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'HH:MM', 'kura-ai-booking-free' ); ?>">
                                </div>
                                <div class="kab-form-group">
                                    <label for="organizer" class="kab-form-label"><?php esc_html_e( 'Organizer', 'kura-ai-booking-free' ); ?></label>
                                    <input type="text" name="organizer" id="organizer" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['organizer'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Organizer name', 'kura-ai-booking-free' ); ?>">
                                </div>
                                <div class="kab-form-group">
                                    <label for="location" class="kab-form-label"><?php esc_html_e( 'Location', 'kura-ai-booking-free' ); ?></label>
                                    <input type="text" name="location" id="location" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['location'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Venue or meeting link', 'kura-ai-booking-free' ); ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="price" class="kab-form-label"><?php esc_html_e( 'Price', 'kura-ai-booking-free' ); ?></label>
                                    <input type="number" step="0.01" name="price" id="price" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['price'] ) : ''; ?>" placeholder="<?php esc_attr_e( '0.00', 'kura-ai-booking-free' ); ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label for="capacity" class="kab-form-label"><?php esc_html_e( 'Capacity', 'kura-ai-booking-free' ); ?></label>
                                    <input type="number" name="capacity" id="capacity" class="kab-form-control" value="<?php echo $event ? esc_attr( $event['capacity'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'e.g. 20', 'kura-ai-booking-free' ); ?>" required>
                                </div>
                                <div class="kab-form-group">
                                    <label class="kab-form-label"><?php esc_html_e( 'Zoom User', 'kura-ai-booking-free' ); ?></label>
                                    <?php if ( function_exists('kab_zoom_list_users') ) { $users = kab_zoom_list_users(); $current = $event ? ($event['zoom_user_id'] ?? '') : ''; echo '<select class="kab-form-control" name="zoom_user_id">'; echo '<option value="">'.esc_html__('None','kura-ai-booking-free').'</option>'; foreach($users as $u){ $id=$u['id']??''; $name=($u['first_name']??'').' '.($u['last_name']??''); $email=$u['email']??''; $label=trim($name)?$name:$email; echo '<option value="'.esc_attr($id).'" '.selected($current,$id,false).'>'.esc_html($label).' ('.esc_html($email).')</option>'; } echo '</select>'; } else { echo '<div class="description">'.esc_html__('Enable Zoom in Settings to load users','kura-ai-booking-free').'</div>'; } ?>
                                </div>
                            </div>
                        </div>

                        <div class="kab-form-group">
                            <button type="submit" class="kab-btn kab-btn-primary">
                                <span class="dashicons dashicons-yes"></span>
                                <?php echo esc_html( $button_text ); ?>
                            </button>
                            <?php if ( $event_id && ! empty( $event['zoom_join_url'] ) ) { echo '<a href="'.esc_url($event['zoom_join_url']).'" target="_blank" class="kab-btn">'.esc_html__('Join Zoom','kura-ai-booking-free').'</a>'; } ?>
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
            'event_end_time' => sanitize_text_field( $_POST['event_end_time'] ?? '' ),
            'organizer'   => sanitize_text_field( $_POST['organizer'] ?? '' ),
            'location'    => sanitize_text_field( $_POST['location'] ),
            'price'       => floatval( $_POST['price'] ),
            'capacity'    => intval( $_POST['capacity'] ),
            'booking_open'=> sanitize_text_field( $_POST['booking_open'] ?? '' ),
            'booking_close'=> sanitize_text_field( $_POST['booking_close'] ?? '' ),
            'tags'        => sanitize_text_field( $_POST['tags'] ?? '' ),
            'zoom_user_id'=> sanitize_text_field( $_POST['zoom_user_id'] ?? '' ),
        );

        $event_id = $events_model->create_event( $event_data );
        if ( $event_id && function_exists('kab_get_zoom_settings') ) {
            $z = kab_get_zoom_settings(); if ( ! empty( $z['enabled'] ) ) {
                $evt = $events_model->get_event( $event_id );
                if ( ! empty( $evt['zoom_user_id'] ) ) {
                    $start_ts = strtotime( $evt['event_date'] . ' ' . $evt['event_time'] ); $end_ts = !empty($evt['event_end_time']) ? strtotime( $evt['event_date'] . ' ' . $evt['event_end_time'] ) : ($start_ts + 3600);
                    $duration = max( 15, intval( ($end_ts - $start_ts)/60 ) );
                    $topic = str_replace( array('%event_name%'), array($evt['name']), $z['meeting_title'] );
                    $agenda = str_replace( array('%event_description%'), array($evt['description']), $z['meeting_agenda'] );
                    $res = function_exists('kab_zoom_create_meeting') ? kab_zoom_create_meeting( $evt['zoom_user_id'], $topic, $agenda, date('c',$start_ts), $duration ) : false;
                    if ( $res ) { global $wpdb; $wpdb->update( $wpdb->prefix.'kab_events', array( 'zoom_meeting_id'=>$res['id'], 'zoom_host_url'=>$res['host_url'], 'zoom_join_url'=>$res['join_url'] ), array('id'=>$event_id), array('%s','%s','%s'), array('%d') ); }
                }
            }
        }

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
            'event_end_time' => sanitize_text_field( $_POST['event_end_time'] ?? '' ),
            'organizer'   => sanitize_text_field( $_POST['organizer'] ?? '' ),
            'location'    => sanitize_text_field( $_POST['location'] ),
            'price'       => floatval( $_POST['price'] ),
            'capacity'    => intval( $_POST['capacity'] ),
            'booking_open'=> sanitize_text_field( $_POST['booking_open'] ?? '' ),
            'booking_close'=> sanitize_text_field( $_POST['booking_close'] ?? '' ),
            'tags'        => sanitize_text_field( $_POST['tags'] ?? '' ),
            'zoom_user_id'=> sanitize_text_field( $_POST['zoom_user_id'] ?? '' ),
        );

        $result = $events_model->update_event( $event_id, $event_data );
        if ( $result && function_exists('kab_get_zoom_settings') ) {
            $z = kab_get_zoom_settings(); if ( ! empty( $z['enabled'] ) ) {
                $evt = $events_model->get_event( $event_id );
                if ( ! empty( $evt['zoom_user_id'] ) ) {
                    $start_ts = strtotime( $evt['event_date'] . ' ' . $evt['event_time'] ); $end_ts = !empty($evt['event_end_time']) ? strtotime( $evt['event_date'] . ' ' . $evt['event_end_time'] ) : ($start_ts + 3600);
                    $duration = max( 15, intval( ($end_ts - $start_ts)/60 ) );
                    $topic = str_replace( array('%event_name%'), array($evt['name']), $z['meeting_title'] );
                    $agenda = str_replace( array('%event_description%'), array($evt['description']), $z['meeting_agenda'] );
                    if ( empty( $evt['zoom_meeting_id'] ) ) {
                        $res = function_exists('kab_zoom_create_meeting') ? kab_zoom_create_meeting( $evt['zoom_user_id'], $topic, $agenda, date('c',$start_ts), $duration ) : false;
                        if ( $res ) { global $wpdb; $wpdb->update( $wpdb->prefix.'kab_events', array( 'zoom_meeting_id'=>$res['id'], 'zoom_host_url'=>$res['host_url'], 'zoom_join_url'=>$res['join_url'] ), array('id'=>$event_id), array('%s','%s','%s'), array('%d') ); }
                    }
                }
            }
        }

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

        // Payments
        add_settings_field(
            'kab_payment_default',
            __( 'Default Payment Method', 'kura-ai-booking-free' ),
            array( $this, 'render_payment_default_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        // Google Calendar
        add_settings_field(
            'kab_google_client_id',
            __( 'Google Client ID', 'kura-ai-booking-free' ),
            array( $this, 'render_google_client_id_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_google_client_secret',
            __( 'Google Client Secret', 'kura-ai-booking-free' ),
            array( $this, 'render_google_client_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_google_remove_busy',
            __( 'Remove Google Busy Slots', 'kura-ai-booking-free' ),
            array( $this, 'render_google_remove_busy_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        // Zoom
        add_settings_field(
            'kab_zoom_enabled',
            __( 'Enable Zoom (Server-to-Server OAuth)', 'kura-ai-booking-free' ),
            array( $this, 'render_zoom_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_zoom_account_id',
            __( 'Zoom Account ID', 'kura-ai-booking-free' ),
            array( $this, 'render_zoom_account_id_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_zoom_client_id',
            __( 'Zoom Client ID', 'kura-ai-booking-free' ),
            array( $this, 'render_zoom_client_id_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_zoom_client_secret',
            __( 'Zoom Client Secret', 'kura-ai-booking-free' ),
            array( $this, 'render_zoom_client_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_zoom_meeting_title',
            __( 'Zoom Meeting Title', 'kura-ai-booking-free' ),
            array( $this, 'render_zoom_meeting_title_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_zoom_meeting_agenda',
            __( 'Zoom Meeting Agenda', 'kura-ai-booking-free' ),
            array( $this, 'render_zoom_meeting_agenda_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_zoom_create_pending',
            __( 'Create Meetings For Pending Appointments', 'kura-ai-booking-free' ),
            array( $this, 'render_zoom_create_pending_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paypal_enabled',
            __( 'Enable PayPal', 'kura-ai-booking-free' ),
            array( $this, 'render_paypal_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paypal_sandbox',
            __( 'PayPal Sandbox Mode', 'kura-ai-booking-free' ),
            array( $this, 'render_paypal_sandbox_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paypal_merchant',
            __( 'PayPal Merchant Email', 'kura-ai-booking-free' ),
            array( $this, 'render_paypal_merchant_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_stripe_enabled',
            __( 'Enable Stripe', 'kura-ai-booking-free' ),
            array( $this, 'render_stripe_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_stripe_testmode',
            __( 'Stripe Test Mode', 'kura-ai-booking-free' ),
            array( $this, 'render_stripe_testmode_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_stripe_secret',
            __( 'Stripe Secret Key', 'kura-ai-booking-free' ),
            array( $this, 'render_stripe_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_stripe_public',
            __( 'Stripe Public Key', 'kura-ai-booking-free' ),
            array( $this, 'render_stripe_public_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_stripe_webhook_secret',
            __( 'Stripe Webhook Secret', 'kura-ai-booking-free' ),
            array( $this, 'render_stripe_webhook_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_mollie_enabled',
            __( 'Enable Mollie', 'kura-ai-booking-free' ),
            array( $this, 'render_mollie_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_mollie_key',
            __( 'Mollie API Key', 'kura-ai-booking-free' ),
            array( $this, 'render_mollie_key_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_razor_enabled',
            __( 'Enable Razorpay', 'kura-ai-booking-free' ),
            array( $this, 'render_razor_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_razor_testmode',
            __( 'Razorpay Test Mode', 'kura-ai-booking-free' ),
            array( $this, 'render_razor_testmode_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_razor_key_id',
            __( 'Razorpay Key ID', 'kura-ai-booking-free' ),
            array( $this, 'render_razor_key_id_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_razor_key_secret',
            __( 'Razorpay Key Secret', 'kura-ai-booking-free' ),
            array( $this, 'render_razor_key_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_razor_webhook_secret',
            __( 'Razorpay Webhook Secret', 'kura-ai-booking-free' ),
            array( $this, 'render_razor_webhook_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paystack_enabled',
            __( 'Enable Paystack', 'kura-ai-booking-free' ),
            array( $this, 'render_paystack_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paystack_testmode',
            __( 'Paystack Test Mode', 'kura-ai-booking-free' ),
            array( $this, 'render_paystack_testmode_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paystack_secret',
            __( 'Paystack Secret Key', 'kura-ai-booking-free' ),
            array( $this, 'render_paystack_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paystack_public',
            __( 'Paystack Public Key', 'kura-ai-booking-free' ),
            array( $this, 'render_paystack_public_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_paystack_webhook_secret',
            __( 'Paystack Webhook Secret', 'kura-ai-booking-free' ),
            array( $this, 'render_paystack_webhook_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_flutter_enabled',
            __( 'Enable Flutterwave', 'kura-ai-booking-free' ),
            array( $this, 'render_flutter_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_flutter_testmode',
            __( 'Flutterwave Test Mode', 'kura-ai-booking-free' ),
            array( $this, 'render_flutter_testmode_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_flutter_secret',
            __( 'Flutterwave Secret Key', 'kura-ai-booking-free' ),
            array( $this, 'render_flutter_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_flutter_public',
            __( 'Flutterwave Public Key', 'kura-ai-booking-free' ),
            array( $this, 'render_flutter_public_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_flutter_encryption_key',
            __( 'Flutterwave Encryption Key', 'kura-ai-booking-free' ),
            array( $this, 'render_flutter_encryption_key_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_flutter_webhook_secret',
            __( 'Flutterwave Webhook Secret', 'kura-ai-booking-free' ),
            array( $this, 'render_flutter_webhook_secret_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );

        // Taxes settings
        add_settings_field(
            'kab_tax_enabled',
            __( 'Enable Taxes', 'kura-ai-booking-free' ),
            array( $this, 'render_tax_enabled_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_tax_mode',
            __( 'Tax Mode', 'kura-ai-booking-free' ),
            array( $this, 'render_tax_mode_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_tax_type',
            __( 'Tax Type', 'kura-ai-booking-free' ),
            array( $this, 'render_tax_type_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
        add_settings_field(
            'kab_tax_value',
            __( 'Tax Value', 'kura-ai-booking-free' ),
            array( $this, 'render_tax_value_field' ),
            'kab-settings',
            'kab_general_settings_section'
        );
	}

	/**
	 * Sanitize settings
	 */
    public function sanitize_settings( $input ) {
        $sanitized = get_option( 'kab_settings', array() );

		if ( isset( $input['company_name'] ) ) {
			$sanitized['company_name'] = sanitize_text_field( $input['company_name'] );
		}

		if ( isset( $input['support_email'] ) ) {
			$sanitized['support_email'] = sanitize_email( $input['support_email'] );
		}

        if ( isset( $input['enable_tickets'] ) ) {
            $sanitized['enable_tickets'] = absint( $input['enable_tickets'] );
        }

        // Payments
        if ( isset( $input['payment_default'] ) && in_array( $input['payment_default'], array( 'onsite', 'paypal' ), true ) ) {
            $sanitized['payment_default'] = $input['payment_default'];
        }
        $sanitized['paypal_enabled'] = isset( $input['paypal_enabled'] ) ? absint( $input['paypal_enabled'] ) : 0;
        $sanitized['paypal_sandbox'] = isset( $input['paypal_sandbox'] ) ? absint( $input['paypal_sandbox'] ) : 0;
        if ( isset( $input['paypal_merchant'] ) ) {
            $sanitized['paypal_merchant'] = sanitize_email( $input['paypal_merchant'] );
        }
        $sanitized['stripe_enabled']   = isset( $input['stripe_enabled'] ) ? absint( $input['stripe_enabled'] ) : 0;
        $sanitized['stripe_testmode']  = isset( $input['stripe_testmode'] ) ? absint( $input['stripe_testmode'] ) : 0;
        if ( isset( $input['stripe_secret'] ) ) { $sanitized['stripe_secret'] = sanitize_text_field( $input['stripe_secret'] ); }
        if ( isset( $input['stripe_public'] ) ) { $sanitized['stripe_public'] = sanitize_text_field( $input['stripe_public'] ); }
        if ( isset( $input['stripe_webhook_secret'] ) ) { $sanitized['stripe_webhook_secret'] = sanitize_text_field( $input['stripe_webhook_secret'] ); }
        $sanitized['mollie_enabled']   = isset( $input['mollie_enabled'] ) ? absint( $input['mollie_enabled'] ) : 0;
        if ( isset( $input['mollie_key'] ) ) { $sanitized['mollie_key'] = sanitize_text_field( $input['mollie_key'] ); }
        $sanitized['razor_enabled']    = isset( $input['razor_enabled'] ) ? absint( $input['razor_enabled'] ) : 0;
        $sanitized['razor_testmode']   = isset( $input['razor_testmode'] ) ? absint( $input['razor_testmode'] ) : 0;
        if ( isset( $input['razor_key_id'] ) ) { $sanitized['razor_key_id'] = sanitize_text_field( $input['razor_key_id'] ); }
        if ( isset( $input['razor_key_secret'] ) ) { $sanitized['razor_key_secret'] = sanitize_text_field( $input['razor_key_secret'] ); }
        if ( isset( $input['razor_webhook_secret'] ) ) { $sanitized['razor_webhook_secret'] = sanitize_text_field( $input['razor_webhook_secret'] ); }
        $sanitized['paystack_enabled'] = isset( $input['paystack_enabled'] ) ? absint( $input['paystack_enabled'] ) : 0;
        $sanitized['paystack_testmode']= isset( $input['paystack_testmode'] ) ? absint( $input['paystack_testmode'] ) : 0;
        if ( isset( $input['paystack_secret'] ) ) { $sanitized['paystack_secret'] = sanitize_text_field( $input['paystack_secret'] ); }
        if ( isset( $input['paystack_public'] ) ) { $sanitized['paystack_public'] = sanitize_text_field( $input['paystack_public'] ); }
        if ( isset( $input['paystack_webhook_secret'] ) ) { $sanitized['paystack_webhook_secret'] = sanitize_text_field( $input['paystack_webhook_secret'] ); }
        $sanitized['flutter_enabled']  = isset( $input['flutter_enabled'] ) ? absint( $input['flutter_enabled'] ) : 0;
        $sanitized['flutter_testmode'] = isset( $input['flutter_testmode'] ) ? absint( $input['flutter_testmode'] ) : 0;
        if ( isset( $input['flutter_secret'] ) ) { $sanitized['flutter_secret'] = sanitize_text_field( $input['flutter_secret'] ); }
        if ( isset( $input['flutter_public'] ) ) { $sanitized['flutter_public'] = sanitize_text_field( $input['flutter_public'] ); }
        if ( isset( $input['flutter_encryption_key'] ) ) { $sanitized['flutter_encryption_key'] = sanitize_text_field( $input['flutter_encryption_key'] ); }
        if ( isset( $input['flutter_webhook_secret'] ) ) { $sanitized['flutter_webhook_secret'] = sanitize_text_field( $input['flutter_webhook_secret'] ); }

        // Taxes
        $sanitized['tax_enabled'] = isset( $input['tax_enabled'] ) ? absint( $input['tax_enabled'] ) : 0;
        if ( isset( $input['tax_mode'] ) && in_array( $input['tax_mode'], array( 'excluded', 'included' ), true ) ) {
            $sanitized['tax_mode'] = $input['tax_mode'];
        }
        if ( isset( $input['tax_type'] ) && in_array( $input['tax_type'], array( 'percent', 'fixed' ), true ) ) {
            $sanitized['tax_type'] = $input['tax_type'];
        }
        if ( isset( $input['tax_value'] ) ) {
            $sanitized['tax_value'] = floatval( $input['tax_value'] );
        }

        // Zoom
        $sanitized['zoom_enabled'] = isset( $input['zoom_enabled'] ) ? absint( $input['zoom_enabled'] ) : 0;
        if ( isset( $input['zoom_account_id'] ) ) { $sanitized['zoom_account_id'] = sanitize_text_field( $input['zoom_account_id'] ); }
        if ( isset( $input['zoom_client_id'] ) ) { $sanitized['zoom_client_id'] = sanitize_text_field( $input['zoom_client_id'] ); }
        if ( isset( $input['zoom_client_secret'] ) ) { $sanitized['zoom_client_secret'] = sanitize_text_field( $input['zoom_client_secret'] ); }
        if ( isset( $input['zoom_meeting_title'] ) ) { $sanitized['zoom_meeting_title'] = sanitize_text_field( $input['zoom_meeting_title'] ); }
        if ( isset( $input['zoom_meeting_agenda'] ) ) { $sanitized['zoom_meeting_agenda'] = sanitize_textarea_field( $input['zoom_meeting_agenda'] ); }
        $sanitized['zoom_create_pending'] = isset( $input['zoom_create_pending'] ) ? absint( $input['zoom_create_pending'] ) : 0;

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
        <input type="text" name="kab_settings[company_name]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Company name', 'kura-ai-booking-free' ); ?>" />
        <?php
    }

	/**
	 * Render support email field
	 */
    public function render_support_email_field() {
        $options = get_option( 'kab_settings' );
        $value = isset( $options['support_email'] ) ? $options['support_email'] : '';
        ?>
        <input type="email" name="kab_settings[support_email]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'support@example.com', 'kura-ai-booking-free' ); ?>" />
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

    // Payments fields
    public function render_payment_default_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['payment_default'] ) ? $options['payment_default'] : 'onsite';
        ?>
        <select name="kab_settings[payment_default]">
            <option value="onsite" <?php selected( $val, 'onsite' ); ?>><?php esc_html_e( 'On-site', 'kura-ai-booking-free' ); ?></option>
            <option value="paypal" <?php selected( $val, 'paypal' ); ?>><?php esc_html_e( 'PayPal', 'kura-ai-booking-free' ); ?></option>
        </select>
        <?php
    }

    public function render_paypal_enabled_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['paypal_enabled'] ) ? absint( $options['paypal_enabled'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[paypal_enabled]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Allow PayPal payments', 'kura-ai-booking-free' ); ?></label>
        <?php
    }

    public function render_paypal_sandbox_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['paypal_sandbox'] ) ? absint( $options['paypal_sandbox'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[paypal_sandbox]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Use PayPal Sandbox (testing)', 'kura-ai-booking-free' ); ?></label>
        <?php
    }

    public function render_paypal_merchant_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['paypal_merchant'] ) ? $options['paypal_merchant'] : '';
        ?>
        <input type="email" name="kab_settings[paypal_merchant]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'merchant@example.com', 'kura-ai-booking-free' ); ?>" />
        <p class="description"><?php esc_html_e( 'PayPal business/merchant email for Standard payments.', 'kura-ai-booking-free' ); ?></p>
        <?php
    }

    public function render_google_client_id_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['google_client_id'] ) ? $options['google_client_id'] : '';
        echo '<input type="text" name="kab_settings[google_client_id]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Client ID" />';
        echo '<p class="description">' . esc_html__( 'Redirect URI:', 'kura-ai-booking-free' ) . ' ' . esc_html( admin_url( 'admin-post.php?action=kab_google_oauth_callback' ) ) . '</p>';
    }

    // Zoom fields
    public function render_zoom_enabled_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['zoom_enabled'] ) ? absint( $options['zoom_enabled'] ) : 0;
        echo '<label><input type="checkbox" name="kab_settings[zoom_enabled]" value="1" ' . checked( $val, 1, false ) . ' /> ' . esc_html__( 'Enable Server-to-Server OAuth integration', 'kura-ai-booking-free' ) . '</label>';
        echo '<p class="description">' . esc_html__( 'Use Zoom App Marketplace → Build App → Server-to-Server OAuth.', 'kura-ai-booking-free' ) . '</p>';
    }
    public function render_zoom_account_id_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['zoom_account_id'] ) ? $options['zoom_account_id'] : '';
        echo '<input type="text" name="kab_settings[zoom_account_id]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Account ID" />';
    }
    public function render_zoom_client_id_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['zoom_client_id'] ) ? $options['zoom_client_id'] : '';
        echo '<input type="text" name="kab_settings[zoom_client_id]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Client ID" />';
    }
    public function render_zoom_client_secret_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['zoom_client_secret'] ) ? $options['zoom_client_secret'] : '';
        echo '<input type="text" name="kab_settings[zoom_client_secret]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Client Secret" />';
    }
    public function render_zoom_meeting_title_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['zoom_meeting_title'] ) ? $options['zoom_meeting_title'] : '%service_name%';
        echo '<input type="text" name="kab_settings[zoom_meeting_title]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="%service_name%" />';
    }
    public function render_zoom_meeting_agenda_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['zoom_meeting_agenda'] ) ? $options['zoom_meeting_agenda'] : '%service_description%';
        echo '<textarea name="kab_settings[zoom_meeting_agenda]" rows="3" class="regular-text" placeholder="%service_description%">' . esc_textarea( $val ) . '</textarea>';
    }
    public function render_zoom_create_pending_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['zoom_create_pending'] ) ? absint( $options['zoom_create_pending'] ) : 0;
        echo '<label><input type="checkbox" name="kab_settings[zoom_create_pending]" value="1" ' . checked( $val, 1, false ) . ' /> ' . esc_html__( 'Also create Zoom meetings for Pending appointments', 'kura-ai-booking-free' ) . '</label>';
    }
    public function render_google_client_secret_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
        echo '<input type="text" name="kab_settings[google_client_secret]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Client Secret" />';
    }
    public function render_google_remove_busy_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['google_remove_busy'] ) ? absint( $options['google_remove_busy'] ) : 0;
        echo '<label><input type="checkbox" name="kab_settings[google_remove_busy]" value="1" ' . checked( $val, 1, false ) . ' /> ' . esc_html__( 'Block busy Google Calendar slots', 'kura-ai-booking-free' ) . '</label>';
    }

    public function render_stripe_enabled_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['stripe_enabled'] ) ? absint( $options['stripe_enabled'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[stripe_enabled]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Allow Stripe payments', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_stripe_testmode_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['stripe_testmode'] ) ? absint( $options['stripe_testmode'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[stripe_testmode]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Use Stripe Test Mode', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_stripe_secret_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['stripe_secret'] ) ? $options['stripe_secret'] : '';
        ?>
        <input type="text" name="kab_settings[stripe_secret]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'sk_test_... or sk_live_...', 'kura-ai-booking-free' ); ?>" />
        <?php
    }
    public function render_stripe_public_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['stripe_public'] ) ? $options['stripe_public'] : '';
        echo '<input type="text" name="kab_settings[stripe_public]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="pk_test_... or pk_live_..." />';
    }
    public function render_stripe_webhook_secret_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['stripe_webhook_secret'] ) ? $options['stripe_webhook_secret'] : '';
        $url = home_url( '/wp-json/kuraai/v1/webhook/stripe' );
        echo '<input type="text" name="kab_settings[stripe_webhook_secret]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="whsec_..." />';
        echo '<p class="description">' . esc_html__( 'Webhook URL:', 'kura-ai-booking-free' ) . ' ' . esc_html( $url ) . '</p>';
    }
    public function render_mollie_enabled_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['mollie_enabled'] ) ? absint( $options['mollie_enabled'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[mollie_enabled]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Allow Mollie payments', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_mollie_key_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['mollie_key'] ) ? $options['mollie_key'] : '';
        ?>
        <input type="text" name="kab_settings[mollie_key]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'test_xxx or live_xxx', 'kura-ai-booking-free' ); ?>" />
        <?php
    }
    public function render_razor_webhook_secret_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['razor_webhook_secret'] ) ? $options['razor_webhook_secret'] : '';
        $url = home_url( '/wp-json/kuraai/v1/webhook/razorpay' );
        echo '<input type="text" name="kab_settings[razor_webhook_secret]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Webhook Secret" />';
        echo '<p class="description">' . esc_html__( 'Webhook URL:', 'kura-ai-booking-free' ) . ' ' . esc_html( $url ) . '</p>';
    }
    public function render_razor_enabled_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['razor_enabled'] ) ? absint( $options['razor_enabled'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[razor_enabled]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Allow Razorpay payments', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_razor_testmode_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['razor_testmode'] ) ? absint( $options['razor_testmode'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[razor_testmode]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Use Razorpay Test Mode', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_razor_key_id_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['razor_key_id'] ) ? $options['razor_key_id'] : '';
        ?>
        <input type="text" name="kab_settings[razor_key_id]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'rzp_test_... or rzp_live_...', 'kura-ai-booking-free' ); ?>" />
        <?php
    }
    public function render_razor_key_secret_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['razor_key_secret'] ) ? $options['razor_key_secret'] : '';
        ?>
        <input type="text" name="kab_settings[razor_key_secret]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'your razorpay key secret', 'kura-ai-booking-free' ); ?>" />
        <?php
    }
    public function render_paystack_enabled_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['paystack_enabled'] ) ? absint( $options['paystack_enabled'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[paystack_enabled]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Allow Paystack payments', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_paystack_testmode_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['paystack_testmode'] ) ? absint( $options['paystack_testmode'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[paystack_testmode]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Use Paystack Test Mode', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_paystack_secret_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['paystack_secret'] ) ? $options['paystack_secret'] : '';
        ?>
        <input type="text" name="kab_settings[paystack_secret]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'sk_test_... or sk_live_...', 'kura-ai-booking-free' ); ?>" />
        <?php
    }
    public function render_paystack_public_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['paystack_public'] ) ? $options['paystack_public'] : '';
        echo '<input type="text" name="kab_settings[paystack_public]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="pk_test_... or pk_live_..." />';
    }
    public function render_paystack_webhook_secret_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['paystack_webhook_secret'] ) ? $options['paystack_webhook_secret'] : '';
        $url = home_url( '/wp-json/kuraai/v1/webhook/paystack' );
        echo '<input type="text" name="kab_settings[paystack_webhook_secret]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Webhook Secret" />';
        echo '<p class="description">' . esc_html__( 'Webhook URL:', 'kura-ai-booking-free' ) . ' ' . esc_html( $url ) . '</p>';
    }
    public function render_flutter_enabled_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['flutter_enabled'] ) ? absint( $options['flutter_enabled'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[flutter_enabled]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Allow Flutterwave payments', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_flutter_testmode_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['flutter_testmode'] ) ? absint( $options['flutter_testmode'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[flutter_testmode]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Use Flutterwave Test Mode', 'kura-ai-booking-free' ); ?></label>
        <?php
    }
    public function render_flutter_secret_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['flutter_secret'] ) ? $options['flutter_secret'] : '';
        ?>
        <input type="text" name="kab_settings[flutter_secret]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'FLWSECK-...', 'kura-ai-booking-free' ); ?>" />
        <?php
    }
    public function render_flutter_public_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['flutter_public'] ) ? $options['flutter_public'] : '';
        echo '<input type="text" name="kab_settings[flutter_public]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="FLWPUBK-..." />';
    }
    public function render_flutter_encryption_key_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['flutter_encryption_key'] ) ? $options['flutter_encryption_key'] : '';
        echo '<input type="text" name="kab_settings[flutter_encryption_key]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Encryption Key" />';
    }
    public function render_flutter_webhook_secret_field() {
        $options = get_option( 'kab_settings' ); $val = isset( $options['flutter_webhook_secret'] ) ? $options['flutter_webhook_secret'] : '';
        $url = home_url( '/wp-json/kuraai/v1/webhook/flutterwave' );
        echo '<input type="text" name="kab_settings[flutter_webhook_secret]" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="Webhook Secret" />';
        echo '<p class="description">' . esc_html__( 'Webhook URL:', 'kura-ai-booking-free' ) . ' ' . esc_html( $url ) . '</p>';
    }

    // Taxes fields
    public function render_tax_enabled_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['tax_enabled'] ) ? absint( $options['tax_enabled'] ) : 0;
        ?>
        <label><input type="checkbox" name="kab_settings[tax_enabled]" value="1" <?php checked( $val, 1 ); ?> /> <?php esc_html_e( 'Apply taxes to services and events', 'kura-ai-booking-free' ); ?></label>
        <?php
    }

    public function render_tax_mode_field() {
        $options = get_option( 'kab_settings' );
        $mode = isset( $options['tax_mode'] ) ? $options['tax_mode'] : 'excluded';
        ?>
        <select name="kab_settings[tax_mode]">
            <option value="excluded" <?php selected( $mode, 'excluded' ); ?>><?php esc_html_e( 'Excluded (show separately)', 'kura-ai-booking-free' ); ?></option>
            <option value="included" <?php selected( $mode, 'included' ); ?>><?php esc_html_e( 'Included (built into price)', 'kura-ai-booking-free' ); ?></option>
        </select>
        <?php
    }

    public function render_tax_type_field() {
        $options = get_option( 'kab_settings' );
        $type = isset( $options['tax_type'] ) ? $options['tax_type'] : 'percent';
        ?>
        <select name="kab_settings[tax_type]">
            <option value="percent" <?php selected( $type, 'percent' ); ?>><?php esc_html_e( 'Percentage (%)', 'kura-ai-booking-free' ); ?></option>
            <option value="fixed" <?php selected( $type, 'fixed' ); ?>><?php esc_html_e( 'Fixed amount', 'kura-ai-booking-free' ); ?></option>
        </select>
        <?php
    }

    public function render_tax_value_field() {
        $options = get_option( 'kab_settings' );
        $val = isset( $options['tax_value'] ) ? floatval( $options['tax_value'] ) : 0.0;
        ?>
        <input type="number" step="0.01" name="kab_settings[tax_value]" value="<?php echo esc_attr( $val ); ?>" placeholder="<?php esc_attr_e( 'e.g. 10 or 5.00', 'kura-ai-booking-free' ); ?>" />
        <p class="description"><?php esc_html_e( 'Enter percentage (e.g., 10 for 10%) or fixed amount, depending on Tax Type.', 'kura-ai-booking-free' ); ?></p>
        <?php
    }

	/**
	 * Render settings page
	 */
    public function render_settings_page() {
        ?>
        <div class="kab-admin-wrapper">
            <?php $this->render_static_header( 'settings' ); ?>
                <div class="kab-settings-nav">
                    <a class="kab-pill" href="#general"><span class="dashicons dashicons-admin-generic"></span> <?php echo esc_html__( 'General', 'kura-ai-booking-free' ); ?></a>
                    <a class="kab-pill" href="#payments-general"><span class="dashicons dashicons-cart"></span> <?php echo esc_html__( 'Payments', 'kura-ai-booking-free' ); ?></a>
                    <a class="kab-pill" href="#taxes"><span class="dashicons dashicons-money"></span> <?php echo esc_html__( 'Taxes', 'kura-ai-booking-free' ); ?></a>
                    <a class="kab-pill" href="#paypal"><span class="dashicons dashicons-admin-site"></span> PayPal</a>
                    <a class="kab-pill" href="#mollie"><span class="dashicons dashicons-networking"></span> Mollie</a>
                    <a class="kab-pill" href="#google-calendar"><span class="dashicons dashicons-calendar"></span> Google</a>
                    <a class="kab-pill" href="#razorpay"><span class="dashicons dashicons-lock"></span> Razorpay</a>
                    <a class="kab-pill" href="#stripe"><span class="dashicons dashicons-shield"></span> Stripe</a>
                    <a class="kab-pill" href="#paystack"><span class="dashicons dashicons-admin-network"></span> Paystack</a>
                    <a class="kab-pill" href="#flutterwave"><span class="dashicons dashicons-admin-plugins"></span> Flutterwave</a>
                    <a class="kab-pill" href="#zoom"><span class="dashicons dashicons-video-alt3"></span> Zoom</a>
                </div>
                <div class="kab-settings-grid">
                    <div id="general" class="kab-card kab-settings-card accent-secondary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-admin-users"></span> <?php echo esc_html__( 'Company', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                                <?php $this->render_company_name_field(); ?>
                                <?php $this->render_support_email_field(); ?>

                                <div class="kab-field-group" style="margin-top: 30px; padding: 20px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
                                    <h3 style="margin-top: 0; color: #0073aa;">
                                        <span class="dashicons dashicons-admin-tools" style="font-size: 20px;"></span>
                                        <?php echo esc_html__( 'Setup Wizard', 'kura-ai-booking-free' ); ?>
                                    </h3>
                                    <p style="margin-bottom: 15px;">
                                        <?php echo esc_html__( 'Run the setup wizard to configure your booking system settings, company information, and brand colors.', 'kura-ai-booking-free' ); ?>
                                    </p>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-setup-wizard' ) ); ?>" class="kab-btn kab-btn-secondary">
                                        <span class="dashicons dashicons-controls-play"></span>
                                        <?php echo esc_html__( 'Launch Setup Wizard', 'kura-ai-booking-free' ); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="taxes" class="kab-card kab-settings-card accent-success">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-money"></span> <?php echo esc_html__( 'Taxes', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                                <?php $this->render_tax_enabled_field(); ?>
                                <?php $this->render_tax_mode_field(); ?>
                                <?php $this->render_tax_type_field(); ?>
                                <?php $this->render_tax_value_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="payments-general" class="kab-card kab-settings-card accent-primary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-cart"></span> <?php echo esc_html__( 'Payments (General)', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                                <?php $this->render_payment_default_field(); ?>
                                <?php $this->render_enable_tickets_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="google-calendar" class="kab-card kab-settings-card accent-info">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-calendar"></span> <?php echo esc_html__( 'Google Calendar', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                                <?php $this->render_google_client_id_field(); ?>
                                <?php $this->render_google_client_secret_field(); ?>
                                <?php $this->render_google_remove_busy_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="zoom" class="kab-card kab-settings-card accent-warning">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-video-alt3"></span> <?php echo esc_html__( 'Zoom', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                                <?php $this->render_zoom_enabled_field(); ?>
                                <?php $this->render_zoom_account_id_field(); ?>
                                <?php $this->render_zoom_client_id_field(); ?>
                                <?php $this->render_zoom_client_secret_field(); ?>
                                <?php $this->render_zoom_meeting_title_field(); ?>
                                <?php $this->render_zoom_meeting_agenda_field(); ?>
                                <?php $this->render_zoom_create_pending_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="paypal" class="kab-card kab-settings-card accent-primary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-admin-site"></span> <?php echo esc_html__( 'PayPal', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                            <?php $this->render_paypal_enabled_field(); ?>
                            <?php $this->render_paypal_sandbox_field(); ?>
                            <?php $this->render_paypal_merchant_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="stripe" class="kab-card kab-settings-card accent-primary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-shield"></span> <?php echo esc_html__( 'Stripe', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                            <?php $this->render_stripe_enabled_field(); ?>
                            <?php $this->render_stripe_testmode_field(); ?>
                            <?php $this->render_stripe_secret_field(); ?>
                            <?php $this->render_stripe_public_field(); ?>
                            <?php $this->render_stripe_webhook_secret_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="mollie" class="kab-card kab-settings-card accent-primary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-networking"></span> <?php echo esc_html__( 'Mollie', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                            <?php $this->render_mollie_enabled_field(); ?>
                            <?php $this->render_mollie_key_field(); ?>
                            <p class="description"><?php echo esc_html__( 'Webhook URL:', 'kura-ai-booking-free' ) . ' ' . esc_html( home_url( '/wp-json/kuraai/v1/webhook/mollie' ) ); ?></p>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="razorpay" class="kab-card kab-settings-card accent-primary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-lock"></span> <?php echo esc_html__( 'Razorpay', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                            <?php $this->render_razor_enabled_field(); ?>
                            <?php $this->render_razor_testmode_field(); ?>
                            <?php $this->render_razor_key_id_field(); ?>
                            <?php $this->render_razor_key_secret_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="paystack" class="kab-card kab-settings-card accent-primary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-admin-network"></span> <?php echo esc_html__( 'Paystack', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                            <?php $this->render_paystack_enabled_field(); ?>
                            <?php $this->render_paystack_testmode_field(); ?>
                            <?php $this->render_paystack_secret_field(); ?>
                            <?php $this->render_paystack_public_field(); ?>
                            <?php $this->render_paystack_webhook_secret_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
                        </form>
                    </div>

                    <div id="flutterwave" class="kab-card kab-settings-card accent-primary">
                        <div class="kab-card-header"><h2 class="kab-card-title"><span class="dashicons dashicons-admin-plugins"></span> <?php echo esc_html__( 'Flutterwave', 'kura-ai-booking-free' ); ?></h2></div>
                        <form method="post" action="options.php">
                            <?php settings_fields( 'kab-settings-group' ); ?>
                            <div class="kab-card-body">
                            <?php $this->render_flutter_enabled_field(); ?>
                            <?php $this->render_flutter_testmode_field(); ?>
                            <?php $this->render_flutter_secret_field(); ?>
                            <?php $this->render_flutter_public_field(); ?>
                            <?php $this->render_flutter_encryption_key_field(); ?>
                            <?php $this->render_flutter_webhook_secret_field(); ?>
                            </div>
                            <div class="kab-card-footer"><button type="submit" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Save Settings', 'kura-ai-booking-free' ); ?></button></div>
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

    // Placeholder pages
    public function render_calendar_page() {
        $this->render_static_header( 'calendar' );
        $view  = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'month';
        $year  = isset( $_GET['year'] ) ? intval( $_GET['year'] ) : intval( date('Y') );
        $month = isset( $_GET['month'] ) ? intval( $_GET['month'] ) : intval( date('n') );
        $day   = isset( $_GET['day'] ) ? intval( $_GET['day'] ) : intval( date('j') );

        // Date range based on view
        if ( $view === 'week' ) {
            $ts = mktime(0,0,0,$month,$day,$year);
            $start = date('Y-m-d', strtotime('last monday', $ts));
            $end   = date('Y-m-d', strtotime('+6 days', strtotime($start)));
        } elseif ( $view === 'day' ) {
            $start = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $end   = $start;
        } else { // month
            $start = sprintf('%04d-%02d-01', $year, $month);
            $end   = date('Y-m-t', strtotime($start));
        }

        global $wpdb;
        // Fetch events in range
        $events = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}kab_events WHERE status='active' AND event_date BETWEEN %s AND %s ORDER BY event_date, event_time", $start, $end ), ARRAY_A );
        // Fetch service appointments in range
        $appointments = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}kab_bookings WHERE booking_type='service' AND booking_date BETWEEN %s AND %s ORDER BY booking_date, booking_time", $start, $end ), ARRAY_A );

        // Group by date
        $by_date = array();
        foreach ( $events as $e ) { $d = $e['event_date']; $by_date[$d]['events'][] = $e; }
        foreach ( $appointments as $a ) { $d = $a['booking_date']; $by_date[$d]['appointments'][] = $a; }

        // Header controls
        $base = admin_url('admin.php?page=kab-calendar');
        $prev = $next = array();
        if ( $view === 'month' ) {
            $prev = array('view'=>'month','year'=> $month===1 ? $year-1 : $year, 'month'=> $month===1 ? 12 : $month-1);
            $next = array('view'=>'month','year'=> $month===12? $year+1 : $year, 'month'=> $month===12? 1 : $month+1);
            $title = date_i18n('F Y', strtotime($start));
        } elseif ( $view === 'week' ) {
            $prev_ts = strtotime('-7 days', strtotime($start));
            $next_ts = strtotime('+7 days', strtotime($start));
            $prev = array('view'=>'week','year'=>date('Y',$prev_ts),'month'=>date('n',$prev_ts),'day'=>date('j',$prev_ts));
            $next = array('view'=>'week','year'=>date('Y',$next_ts),'month'=>date('n',$next_ts),'day'=>date('j',$next_ts));
            $title = date_i18n('M j, Y', strtotime($start)) . ' – ' . date_i18n('M j, Y', strtotime($end));
        } else {
            $prev_ts = strtotime('-1 day', strtotime($start));
            $next_ts = strtotime('+1 day', strtotime($start));
            $prev = array('view'=>'day','year'=>date('Y',$prev_ts),'month'=>date('n',$prev_ts),'day'=>date('j',$prev_ts));
            $next = array('view'=>'day','year'=>date('Y',$next_ts),'month'=>date('n',$next_ts),'day'=>date('j',$next_ts));
            $title = date_i18n('M j, Y', strtotime($start));
        }

        ?>
        <div class="kab-card" style="margin-top:10px;">
            <div class="kab-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <a class="kab-btn" href="<?php echo esc_url( add_query_arg($prev, $base) ); ?>">&larr; <?php esc_html_e('Prev','kura-ai-booking-free'); ?></a>
                    <a class="kab-btn" href="<?php echo esc_url( add_query_arg(array('view'=>$view,'year'=>date('Y'),'month'=>date('n'),'day'=>date('j')), $base) ); ?>"><?php esc_html_e('Today','kura-ai-booking-free'); ?></a>
                    <a class="kab-btn" href="<?php echo esc_url( add_query_arg($next, $base) ); ?>"><?php esc_html_e('Next','kura-ai-booking-free'); ?> &rarr;</a>
                </div>
                <h2 class="kab-card-title" style="margin:0;"><?php echo esc_html( $title ); ?></h2>
                <div>
                    <a class="kab-btn <?php echo $view==='month'?'kab-btn-primary':''; ?>" href="<?php echo esc_url( add_query_arg(array('view'=>'month','year'=>$year,'month'=>$month), $base) ); ?>"><?php esc_html_e('Month','kura-ai-booking-free'); ?></a>
                    <a class="kab-btn <?php echo $view==='week'?'kab-btn-primary':''; ?>" href="<?php echo esc_url( add_query_arg(array('view'=>'week','year'=>$year,'month'=>$month,'day'=>$day), $base) ); ?>"><?php esc_html_e('Week','kura-ai-booking-free'); ?></a>
                    <a class="kab-btn <?php echo $view==='day'?'kab-btn-primary':''; ?>" href="<?php echo esc_url( add_query_arg(array('view'=>'day','year'=>$year,'month'=>$month,'day'=>$day), $base) ); ?>"><?php esc_html_e('Day','kura-ai-booking-free'); ?></a>
                    <a class="kab-btn <?php echo $view==='list'?'kab-btn-primary':''; ?>" href="<?php echo esc_url( add_query_arg(array('view'=>'list','year'=>$year,'month'=>$month), $base) ); ?>"><?php esc_html_e('List','kura-ai-booking-free'); ?></a>
                </div>
            </div>
            <div class="kab-card-body">
                <?php if ( $view === 'list' ) { $this->render_calendar_list( $by_date ); } else { $this->render_calendar_grid( $view, $year, $month, $day, $by_date ); } ?>
            </div>
        </div>
        <?php
    }

    private function render_calendar_list( $by_date ) {
        ksort( $by_date );
        echo '<table class="kab-table"><thead><tr><th>'.esc_html__('Date','kura-ai-booking-free').'</th><th>'.esc_html__('Items','kura-ai-booking-free').'</th></tr></thead><tbody>';
        foreach ( $by_date as $date => $data ) {
            $items = array();
            foreach ( (array)($data['events'] ?? array()) as $e ) { $items[] = '<span class="kab-cal-tag kab-cal-event">'.esc_html($e['event_time']).' • '.esc_html($e['name']).'</span>'; }
            foreach ( (array)($data['appointments'] ?? array()) as $a ) { $items[] = '<span class="kab-cal-tag kab-cal-service">'.esc_html($a['booking_time']).' • '.esc_html__('Appointment','kura-ai-booking-free').'</span>'; }
            echo '<tr><td>'.esc_html( date_i18n( get_option('date_format'), strtotime($date) ) ).'</td><td>'.implode('<br>', $items).'</td></tr>';
        }
        echo '</tbody></table>';
    }

    private function render_calendar_grid( $view, $year, $month, $day, $by_date ) {
        if ( $view === 'week' ) {
            $start = date('Y-m-d', strtotime('last monday', mktime(0,0,0,$month,$day,$year)));
            $days = array(); for($i=0;$i<7;$i++){ $days[] = date('Y-m-d', strtotime("+{$i} days", strtotime($start))); }
            echo '<div class="kab-cal-grid kab-cal-week">';
            foreach ( $days as $d ) { $this->render_calendar_cell( $d, $by_date ); }
            echo '</div>';
        } elseif ( $view === 'day' ) {
            $d = sprintf('%04d-%02d-%02d',$year,$month,$day);
            echo '<div class="kab-cal-grid kab-cal-day">'; $this->render_calendar_cell( $d, $by_date ); echo '</div>';
        } else {
            $first = mktime(0,0,0,$month,1,$year); $start_w = date('N',$first); $days_in = date('t',$first);
            $cells = array();
            // Previous month padding
            for($i=1;$i<$start_w;$i++){ $cells[] = null; }
            for($d=1;$d<=$days_in;$d++){ $cells[] = sprintf('%04d-%02d-%02d',$year,$month,$d); }
            // Build grid
            echo '<div class="kab-cal-grid kab-cal-month">';
            foreach ( $cells as $d ) { $this->render_calendar_cell( $d, $by_date ); }
            echo '</div>';
        }
    }

    private function render_calendar_cell( $date, $by_date ) {
        $label = $date ? date('j', strtotime($date)) : '';
        echo '<div class="kab-cal-cell">';
        echo '<div class="kab-cal-date">'.esc_html($label).'</div>';
        if ( $date && isset( $by_date[$date] ) ) {
            foreach ( (array)($by_date[$date]['events'] ?? array()) as $e ) {
                echo '<div class="kab-cal-item kab-cal-event"><div class="kab-cal-item-title">'.esc_html( $e['name'] ).'</div><div class="kab-cal-item-time">'.esc_html( $e['event_time'] . ( !empty($e['event_end_time']) ? ' - '.$e['event_end_time'] : '' ) ).'</div></div>';
            }
            foreach ( (array)($by_date[$date]['appointments'] ?? array()) as $a ) {
                echo '<div class="kab-cal-item kab-cal-service"><div class="kab-cal-item-title">'.esc_html__( 'Appointment', 'kura-ai-booking-free' ).'</div><div class="kab-cal-item-time">'.esc_html( $a['booking_time'] ).'</div></div>';
            }
        }
        echo '</div>';
    }
    public function render_appointments_page() {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-bookings.php';
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-services.php';
        $this->render_static_header( 'appointments' );
        $status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $df     = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : '';
        $dt     = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : '';
        global $wpdb;
        $q = "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE booking_type='service'";
        $w = array(); if ( $status ) { $w[] = $wpdb->prepare( 'status=%s', $status ); }
        if ( $df ) { $w[] = $wpdb->prepare( 'booking_date>=%s', $df ); }
        if ( $dt ) { $w[] = $wpdb->prepare( 'booking_date<=%s', $dt ); }
        if ( $w ) { $q .= ' AND ' . implode( ' AND ', $w ); }
        $q .= ' ORDER BY booking_date DESC, booking_time DESC';
        $rows = $wpdb->get_results( $q, ARRAY_A );
        ?>
        <div class="kab-card" style="margin-top:10px;">
            <div class="kab-card-header"><h2 class="kab-card-title"><?php esc_html_e( 'Appointments', 'kura-ai-booking-free' ); ?></h2></div>
            <div class="kab-card-body">
                <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="kab-filter-row" style="margin-bottom:10px;">
                    <input type="hidden" name="page" value="kab-appointments" />
                    <div class="kab-filter-group"><label class="kab-filter-label"><?php esc_html_e('Date From','kura-ai-booking-free'); ?></label><input type="date" name="date_from" class="kab-filter-input" value="<?php echo esc_attr( $df ); ?>" /></div>
                    <div class="kab-filter-group"><label class="kab-filter-label"><?php esc_html_e('Date To','kura-ai-booking-free'); ?></label><input type="date" name="date_to" class="kab-filter-input" value="<?php echo esc_attr( $dt ); ?>" /></div>
                    <div class="kab-filter-group"><label class="kab-filter-label"><?php esc_html_e('Status','kura-ai-booking-free'); ?></label><select name="status" class="kab-filter-input"><option value=""><?php esc_html_e('All','kura-ai-booking-free'); ?></option><option value="pending" <?php selected($status,'pending'); ?>><?php esc_html_e('Pending','kura-ai-booking-free'); ?></option><option value="completed" <?php selected($status,'completed'); ?>><?php esc_html_e('Completed','kura-ai-booking-free'); ?></option><option value="cancelled" <?php selected($status,'cancelled'); ?>><?php esc_html_e('Cancelled','kura-ai-booking-free'); ?></option></select></div>
                    <div class="kab-filter-group" style="align-self:flex-end"><button class="kab-btn kab-btn-primary" type="submit"><?php esc_html_e('Filter','kura-ai-booking-free'); ?></button></div>
                </form>
                <table class="kab-table"><thead><tr><th><?php esc_html_e('Date','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Time','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Customer','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Service','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Status','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Actions','kura-ai-booking-free'); ?></th></tr></thead><tbody>
                <?php foreach ( $rows as $r ) { $u = get_user_by('id',$r['user_id']); $svc = $wpdb->get_row( $wpdb->prepare("SELECT name FROM {$wpdb->prefix}kab_services WHERE id=%d", $r['service_id']), ARRAY_A ); $cname = $u ? $u->display_name : '—'; $status_label = ucfirst($r['status']); $complete_url = wp_nonce_url( admin_url('admin-post.php?action=kab_update_booking&booking_id='.$r['id'].'&state=completed'), 'kab_update_booking_'.$r['id']); $cancel_url = wp_nonce_url( admin_url('admin-post.php?action=kab_update_booking&booking_id='.$r['id'].'&state=cancelled'), 'kab_update_booking_'.$r['id']); echo '<tr><td>'.esc_html($r['booking_date']).'</td><td>'.esc_html($r['booking_time']).'</td><td>'.esc_html($cname).'</td><td>'.esc_html($svc['name']??'').'</td><td>'.esc_html($status_label).'</td><td>'; if($r['status']!=='completed'){ echo '<a class="kab-btn kab-btn-success kab-btn-sm" href="'.esc_url($complete_url).'">'.esc_html__('Complete','kura-ai-booking-free').'</a> '; } if($r['status']!=='cancelled'){ echo '<a class="kab-btn kab-btn-danger kab-btn-sm" href="'.esc_url($cancel_url).'">'.esc_html__('Cancel','kura-ai-booking-free').'</a>'; } echo '</td></tr>'; } ?>
                </tbody></table>
            </div>
        </div>
        <?php
    }
    public function render_employees_page() {
        $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
        $employee_id = isset( $_GET['employee_id'] ) ? intval( $_GET['employee_id'] ) : 0;
        switch ( $action ) {
            case 'add': $this->render_employee_form(); break;
            case 'edit': $this->render_employee_form( $employee_id ); break;
            default:
                require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-employees.php';
                $model = new KAB_Employees();
                $view  = isset( $_GET['view'] ) && $_GET['view'] === 'grid' ? 'grid' : 'list';
                $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
                $service_id = isset( $_GET['service_id'] ) ? intval( $_GET['service_id'] ) : 0;
                $location = isset( $_GET['location'] ) ? sanitize_text_field( $_GET['location'] ) : '';
                $order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'ASC';
                $paged = isset( $_GET['paged'] ) ? max(1,intval($_GET['paged'])) : 1; $per_page = 12; $offset = ($paged-1)*$per_page;
                $args = compact('search','service_id','location','order'); $args['number']=$per_page; $args['offset']=$offset;
                $rows = $model->get_employees( $args ); $total = $model->count_employees( $args );
                $this->render_static_header( 'employees' );
                echo '<div class="kab-card"><div class="kab-card-header" style="display:flex;justify-content:space-between;align-items:center;">';
                echo '<h2 class="kab-card-title">' . esc_html__( 'Employees', 'kura-ai-booking-free' ) . ' <span style="font-weight:400;color:#6b7a5a">' . esc_html( $total ) . ' ' . esc_html__( 'Total', 'kura-ai-booking-free' ) . '</span></h2>';
                echo '<a href="' . esc_url( admin_url( 'admin.php?page=kab-employees&action=add' ) ) . '" class="kab-btn kab-btn-primary"><span class="dashicons dashicons-plus"></span> ' . esc_html__( 'Add Employee', 'kura-ai-booking-free' ) . '</a>';
                echo '</div><div class="kab-card-body">';
                echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '" class="kab-filter-row" style="margin-bottom:12px;">';
                echo '<input type="hidden" name="page" value="kab-employees" />';
                echo '<div class="kab-filter-group"><label class="kab-filter-label">' . esc_html__( 'Search Employees', 'kura-ai-booking-free' ) . '</label><input type="text" name="search" class="kab-filter-input" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'Search…', 'kura-ai-booking-free' ) . '" /></div>';
                echo '<div class="kab-filter-group"><label class="kab-filter-label">' . esc_html__( 'Services', 'kura-ai-booking-free' ) . '</label><select name="service_id" class="kab-filter-input"><option value="">' . esc_html__( 'All Services', 'kura-ai-booking-free' ) . '</option>';
                global $wpdb; $svcs = $wpdb->get_results("SELECT id,name FROM {$wpdb->prefix}kab_services WHERE status='active' ORDER BY name",ARRAY_A); foreach($svcs as $s){ echo '<option value="'.$s['id'].'" '.selected($service_id,$s['id'],false).'>' . esc_html($s['name']) . '</option>'; }
                echo '</select></div>';
                echo '<div class="kab-filter-group"><label class="kab-filter-label">' . esc_html__( 'Location', 'kura-ai-booking-free' ) . '</label><input type="text" name="location" class="kab-filter-input" value="' . esc_attr( $location ) . '" /></div>';
                echo '<div class="kab-filter-group"><label class="kab-filter-label">' . esc_html__( 'Sort', 'kura-ai-booking-free' ) . '</label><select name="order" class="kab-filter-input"><option value="ASC" '.selected($order,'ASC',false).'>' . esc_html__( 'Name Ascending', 'kura-ai-booking-free' ) . '</option><option value="DESC" '.selected($order,'DESC',false).'>' . esc_html__( 'Name Descending', 'kura-ai-booking-free' ) . '</option></select></div>';
                echo '<div class="kab-filter-group" style="align-self:flex-end"><button class="kab-btn kab-btn-primary" type="submit">' . esc_html__( 'Filter', 'kura-ai-booking-free' ) . '</button></div>';
                echo '</form>';
                echo '<div style="margin-bottom:10px;display:flex;gap:6px;">';
                $base = admin_url('admin.php?page=kab-employees');
                echo '<a class="kab-btn ' . ( $view==='list' ? 'kab-btn-primary' : '' ) . '" href="' . esc_url( add_query_arg( array_merge($_GET,['view'=>'list']), $base ) ) . '">' . esc_html__( 'List', 'kura-ai-booking-free' ) . '</a>';
                echo '<a class="kab-btn ' . ( $view==='grid' ? 'kab-btn-primary' : '' ) . '" href="' . esc_url( add_query_arg( array_merge($_GET,['view'=>'grid']), $base ) ) . '">' . esc_html__( 'Grid', 'kura-ai-booking-free' ) . '</a>';
                echo '</div>';
                if ( $view === 'grid' ) {
                    echo '<div class="kab-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">';
                    foreach($rows as $r){ $name = $r['first_name'].' '.$r['last_name']; $badge = $r['status']; echo '<div class="kab-card" style="padding:12px;">'; echo '<div style="display:flex;justify-content:space-between;align-items:center;"><div class="kab-card-title" style="margin:0;">'.esc_html($name).'</div><span class="kab-status-badge">'.esc_html(ucfirst($badge)).'</span></div>'; echo '<div style="color:#6b7a5a;margin-top:6px;">'.esc_html($r['email']).'<br>'.esc_html($r['phone']).'</div>'; echo '<div style="margin-top:10px;"><a class="kab-btn kab-btn-secondary kab-btn-sm" href="'.esc_url(admin_url('admin.php?page=kab-employees&action=edit&employee_id='.$r['id'])).'">'.esc_html__('Edit','kura-ai-booking-free').'</a> <a class="kab-btn kab-btn-danger kab-btn-sm" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=kab_delete_employee&employee_id='.$r['id']),'kab_delete_employee_'.$r['id'])).'">'.esc_html__('Delete','kura-ai-booking-free').'</a> <a class="kab-btn kab-btn-sm" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=kab_hide_employee&employee_id='.$r['id']),'kab_hide_employee_'.$r['id'])).'">'.esc_html__('Hide','kura-ai-booking-free').'</a> <a class="kab-btn kab-btn-sm" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=kab_duplicate_employee&employee_id='.$r['id']),'kab_duplicate_employee_'.$r['id'])).'">'.esc_html__('Duplicate','kura-ai-booking-free').'</a></div>'; echo '</div>'; }
                    echo '</div>';
                } else {
                    echo '<table class="kab-table"><thead><tr><th></th><th>'.esc_html__('Status','kura-ai-booking-free').'</th><th>'.esc_html__('Employee','kura-ai-booking-free').'</th><th>'.esc_html__('Email','kura-ai-booking-free').'</th><th>'.esc_html__('Phone','kura-ai-booking-free').'</th><th>'.esc_html__('Actions','kura-ai-booking-free').'</th></tr></thead><tbody>';
                    foreach($rows as $r){ $name = $r['first_name'].' '.$r['last_name']; echo '<tr><td><input type="checkbox"/></td><td><span class="kab-status-badge">'.esc_html(ucfirst($r['status'])).'</span></td><td>'.esc_html($name).'</td><td>'.esc_html($r['email']).'</td><td>'.esc_html($r['phone']).'</td><td><a class="kab-btn kab-btn-secondary kab-btn-sm" href="'.esc_url(admin_url('admin.php?page=kab-employees&action=edit&employee_id='.$r['id'])).'">'.esc_html__('Edit','kura-ai-booking-free').'</a> <a class="kab-btn kab-btn-danger kab-btn-sm" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=kab_delete_employee&employee_id='.$r['id']),'kab_delete_employee_'.$r['id'])).'">'.esc_html__('Delete','kura-ai-booking-free').'</a> <a class="kab-btn kab-btn-sm" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=kab_hide_employee&employee_id='.$r['id']),'kab_hide_employee_'.$r['id'])).'">'.esc_html__('Hide','kura-ai-booking-free').'</a> <a class="kab-btn kab-btn-sm" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=kab_duplicate_employee&employee_id='.$r['id']),'kab_duplicate_employee_'.$r['id'])).'">'.esc_html__('Duplicate','kura-ai-booking-free').'</a></td></tr>'; }
                    echo '</tbody></table>';
                }
                $pages = max(1, ceil($total/$per_page)); echo '<div class="kab-pagination"><span class="kab-page-info">'.esc_html(sprintf(__('Showing %d to %d of %d employees','kura-ai-booking-free'), $offset+1, min($offset+$per_page,$total), $total)).'</span>'; if($paged>1){ echo '<a class="kab-btn" href="'.esc_url(add_query_arg(array_merge($_GET,['paged'=>$paged-1]), $base)).'">&larr;</a>'; } if($paged<$pages){ echo ' <a class="kab-btn" href="'.esc_url(add_query_arg(array_merge($_GET,['paged'=>$paged+1]), $base)).'">&rarr;</a>'; } echo '</div>';
                echo '</div></div>';
                break;
        }
    }

    private function render_employee_form( $employee_id = 0 ) {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-employees.php'; $model = new KAB_Employees(); $emp = $employee_id ? $model->get_employee($employee_id) : null;
        $title = $employee_id ? __( 'Edit Employee', 'kura-ai-booking-free' ) : __( 'Add Employee', 'kura-ai-booking-free' );
        $action = $employee_id ? 'kab_edit_employee' : 'kab_add_employee'; $nonce = $employee_id ? 'kab_edit_employee_'.$employee_id : 'kab_add_employee';
        $this->render_static_header( 'employees' );
        echo '<div class="kab-card"><div class="kab-card-header"><h2 class="kab-card-title">'.$title.'</h2></div><div class="kab-card-body">';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">'; echo '<input type="hidden" name="action" value="'.$action.'" />'; if($employee_id){ echo '<input type="hidden" name="employee_id" value="'.esc_attr($employee_id).'" />'; }
        wp_nonce_field( $nonce );
        echo '<div style="display:flex;gap:12px;margin-bottom:12px;">';
        echo '<a class="kab-btn kab-btn-primary" href="#" onclick="return kabSwitchTab(event,\'details\')">'.esc_html__('Details','kura-ai-booking-free').'</a>';
        echo '<a class="kab-btn" href="#" onclick="return kabSwitchTab(event,\'services\')">'.esc_html__('Assigned Services','kura-ai-booking-free').'</a>';
        echo '<a class="kab-btn" href="#" onclick="return kabSwitchTab(event,\'workhours\')">'.esc_html__('Work Hours','kura-ai-booking-free').'</a>';
        echo '<a class="kab-btn" href="#" onclick="return kabSwitchTab(event,\'daysoff\')">'.esc_html__('Days Off','kura-ai-booking-free').'</a>';
        echo '<a class="kab-btn" href="#" onclick="return kabSwitchTab(event,\'special\')">'.esc_html__('Special Days','kura-ai-booking-free').'</a>';
        echo '</div>';
        echo '<div id="kab-tab-details">';
        echo '<div class="kab-form-grid"><div class="kab-col">';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('First Name','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="first_name" value="'.esc_attr($emp['first_name']??'').'" required placeholder="'.esc_attr__('First name','kura-ai-booking-free').'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Last Name','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="last_name" value="'.esc_attr($emp['last_name']??'').'" required placeholder="'.esc_attr__('Last name','kura-ai-booking-free').'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Email','kura-ai-booking-free').'</label><input class="kab-form-control" type="email" name="email" value="'.esc_attr($emp['email']??'').'" required placeholder="'.esc_attr__('email@example.com','kura-ai-booking-free').'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Phone','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="phone" value="'.esc_attr($emp['phone']??'').'" placeholder="'.esc_attr__('+123456789','kura-ai-booking-free').'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Location','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="location" value="'.esc_attr($emp['location']??'').'"/></div>';
        echo '</div><div class="kab-col">';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('WordPress User','kura-ai-booking-free').'</label><select class="kab-form-control" name="wp_user_id"><option value="">'.esc_html__('None','kura-ai-booking-free').'</option>'; $users=get_users(array('fields'=>array('ID','display_name'))); foreach($users as $u){ echo '<option value="'.$u->ID.'" '.selected(intval($emp['wp_user_id']??0),$u->ID,false).'>'.$u->display_name.'</option>'; } echo '</select></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Timezone','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="timezone" value="'.esc_attr($emp['timezone']??'').'" placeholder="'.esc_attr__('e.g. Europe/Athens','kura-ai-booking-free').'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Badge','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="badge" value="'.esc_attr($emp['badge']??'').'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Status','kura-ai-booking-free').'</label><select class="kab-form-control" name="status">'; foreach(array('available','busy','away','on_break') as $st){ echo '<option value="'.$st.'" '.selected(($emp['status']??'available'),$st,false).'>'.esc_html(ucfirst($st)).'</option>'; } echo '</select></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label"><input type="checkbox" name="show_on_site" '.checked(intval($emp['show_on_site']??0),1,false).' /> '.esc_html__('Show employee on site','kura-ai-booking-free').'</label></div>';
        // Google Calendar connect
        $gc_connected = ! empty( $emp['google_refresh_token'] );
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Google Calendar','kura-ai-booking-free').'</label>';
        if ( $employee_id ) {
            if ( $gc_connected ) {
                echo '<div>'.esc_html__('Connected','kura-ai-booking-free').'</div>';
                echo '<a class="kab-btn" href="'.esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_google_disconnect&employee_id='.$employee_id ), 'kab_google_disconnect_'.$employee_id ) ).'">'.esc_html__('Sign out from Google','kura-ai-booking-free').'</a>';
            } else {
                echo '<a class="kab-btn kab-btn-primary" href="'.esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kab_google_oauth_start&employee_id='.$employee_id ), 'kab_google_oauth_start_'.$employee_id ) ).'">'.esc_html__('Connect Google','kura-ai-booking-free').'</a>';
            }
        } else {
            echo '<div class="description">'.esc_html__('Save the employee first to connect Google Calendar.','kura-ai-booking-free').'</div>';
        }
        echo '</div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Google Calendar ID','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="google_calendar_id" value="'.esc_attr($emp['google_calendar_id']??'').'" placeholder="'.esc_attr__('primary','kura-ai-booking-free').'" /></div>';
        // Zoom user mapping
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Zoom User','kura-ai-booking-free').'</label>';
        if ( function_exists('kab_zoom_list_users') ) {
            $users = kab_zoom_list_users();
            echo '<select class="kab-form-control" name="zoom_user_id">';
            echo '<option value="">'.esc_html__('None','kura-ai-booking-free').'</option>';
            $currentZoom = $emp['zoom_user_id']??'';
            foreach ( $users as $u ) {
                $id = $u['id'] ?? ''; $name = ($u['first_name'] ?? '').' '.($u['last_name'] ?? ''); $email = $u['email'] ?? '';
                $label = trim($name) ? $name : $email;
                echo '<option value="'.esc_attr($id).'" '.selected($currentZoom,$id,false).'>'.esc_html($label).' ('.esc_html($email).')</option>';
            }
            echo '</select>';
        } else {
            echo '<div class="description">'.esc_html__('Enable Zoom in Settings to load users','kura-ai-booking-free').'</div>';
        }
        echo '</div>';
        echo '</div></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Description','kura-ai-booking-free').'</label><textarea class="kab-form-control" name="description" rows="4" placeholder="'.esc_attr__('Short bio…','kura-ai-booking-free').'">'.esc_textarea($emp['description']??'').'</textarea></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Internal Note','kura-ai-booking-free').'</label><textarea class="kab-form-control" name="internal_note" rows="3">'.esc_textarea($emp['internal_note']??'').'</textarea></div>';
        echo '</div>';

        // Assigned Services
        echo '<div id="kab-tab-services" style="display:none;">';
        global $wpdb; $services = $wpdb->get_results("SELECT id,name,price FROM {$wpdb->prefix}kab_services WHERE status='active' ORDER BY name",ARRAY_A); $assigned = $employee_id ? $model->get_employee_services($employee_id) : array(); $map = array(); foreach($assigned as $a){ $map[$a['service_id']]=$a; }
        echo '<table class="kab-table"><thead><tr><th>'.esc_html__('Assign','kura-ai-booking-free').'</th><th>'.esc_html__('Service','kura-ai-booking-free').'</th><th>'.esc_html__('Price','kura-ai-booking-free').'</th><th>'.esc_html__('Capacity','kura-ai-booking-free').'</th></tr></thead><tbody>';
        foreach($services as $s){ $chk = isset($map[$s['id']]); $price = $chk ? $map[$s['id']]['price'] : ''; $cap = $chk ? $map[$s['id']]['capacity'] : ''; echo '<tr><td><input type="checkbox" name="emp_services['.$s['id'].'][enable]" '.checked($chk,true,false).' /></td><td>'.esc_html($s['name']).'</td><td><input type="number" step="0.01" name="emp_services['.$s['id'].'][price]" value="'.esc_attr($price).'" placeholder="'.esc_attr__('inherit','kura-ai-booking-free').'" class="kab-form-control" /></td><td><input type="number" name="emp_services['.$s['id'].'][capacity]" value="'.esc_attr($cap).'" placeholder="'.esc_attr__('inherit','kura-ai-booking-free').'" class="kab-form-control" /></td></tr>'; }
        echo '</tbody></table>';
        echo '</div>';

        // Work Hours
        $work = $employee_id ? $model->get_workhours($employee_id) : array();
        echo '<div id="kab-tab-workhours" style="display:none;">';
        echo '<table class="kab-table"><thead><tr><th>'.esc_html__('Weekday','kura-ai-booking-free').'</th><th>'.esc_html__('Start','kura-ai-booking-free').'</th><th>'.esc_html__('End','kura-ai-booking-free').'</th></tr></thead><tbody id="kab-wh-rows">';
        foreach( $work as $wh ){ echo '<tr><td><select name="workhours[][weekday]" class="kab-form-control">'; for($i=1;$i<=7;$i++){ echo '<option value="'.$i.'" '.selected($wh['weekday'],$i,false).'>'.$i.'</option>'; } echo '</select></td><td><input type="time" name="workhours[][start_time]" value="'.esc_attr($wh['start_time']).'" class="kab-form-control"/></td><td><input type="time" name="workhours[][end_time]" value="'.esc_attr($wh['end_time']).'" class="kab-form-control"/></td></tr>'; }
        echo '</tbody></table><button type="button" class="kab-btn" id="kab-add-wh">+'.esc_html__('Add Row','kura-ai-booking-free').'</button>';
        echo '</div>';

        // Days Off
        $offs = $employee_id ? $model->get_daysoff($employee_id) : array();
        echo '<div id="kab-tab-daysoff" style="display:none;">';
        echo '<table class="kab-table"><thead><tr><th>'.esc_html__('Date','kura-ai-booking-free').'</th><th>'.esc_html__('Reason','kura-ai-booking-free').'</th></tr></thead><tbody id="kab-do-rows">';
        foreach($offs as $o){ echo '<tr><td><input type="date" name="daysoff[][day_off]" value="'.esc_attr($o['day_off']).'" class="kab-form-control"/></td><td><input type="text" name="daysoff[][reason]" value="'.esc_attr($o['reason']).'" class="kab-form-control"/></td></tr>'; }
        echo '</tbody></table><button type="button" class="kab-btn" id="kab-add-do">+'.esc_html__('Add Row','kura-ai-booking-free').'</button>';
        echo '</div>';

        // Special Days
        $spec = $employee_id ? $model->get_specialdays($employee_id) : array();
        echo '<div id="kab-tab-special" style="display:none;">';
        echo '<table class="kab-table"><thead><tr><th>'.esc_html__('Date','kura-ai-booking-free').'</th><th>'.esc_html__('Start','kura-ai-booking-free').'</th><th>'.esc_html__('End','kura-ai-booking-free').'</th></tr></thead><tbody id="kab-sp-rows">';
        foreach($spec as $sp){ echo '<tr><td><input type="date" name="specialdays[][special_date]" value="'.esc_attr($sp['special_date']).'" class="kab-form-control"/></td><td><input type="time" name="specialdays[][start_time]" value="'.esc_attr($sp['start_time']).'" class="kab-form-control"/></td><td><input type="time" name="specialdays[][end_time]" value="'.esc_attr($sp['end_time']).'" class="kab-form-control"/></td></tr>'; }
        echo '</tbody></table><button type="button" class="kab-btn" id="kab-add-sp">+'.esc_html__('Add Row','kura-ai-booking-free').'</button>';
        echo '</div>';

        echo '<div class="kab-form-group" style="margin-top:12px;"><button class="kab-btn kab-btn-primary" type="submit">'.esc_html__('Save','kura-ai-booking-free').'</button> <a class="kab-btn" href="'.esc_url(admin_url('admin.php?page=kab-employees')).'">'.esc_html__('Cancel','kura-ai-booking-free').'</a></div>';
        echo '</form></div></div>';
        echo '<script>(function(){window.kabSwitchTab=function(e,id){e.preventDefault();["details","services","workhours","daysoff","special"].forEach(function(k){var el=document.getElementById("kab-tab-"+k);if(el)el.style.display=(k===id?"block":"none");});};var wh=document.getElementById("kab-add-wh");if(wh){wh.addEventListener("click",function(){var tbody=document.getElementById("kab-wh-rows");var tr=document.createElement("tr");var opts="";for(var i=1;i<=7;i++){opts+=`<option value=${i}>${i}</option>`}tr.innerHTML=`<td><select name=\"workhours[][weekday]\" class=\"kab-form-control\">${opts}</select></td><td><input type=\"time\" name=\"workhours[][start_time]\" class=\"kab-form-control\"/></td><td><input type=\"time\" name=\"workhours[][end_time]\" class=\"kab-form-control\"/></td>`;tbody.appendChild(tr);});}
var add=function(btnId,tbodyId,html){var b=document.getElementById(btnId);if(b){b.addEventListener("click",function(){var tb=document.getElementById(tbodyId);var tr=document.createElement("tr");tr.innerHTML=html;tb.appendChild(tr);});}};add("kab-add-do","kab-do-rows","<td><input type=\"date\" name=\"daysoff[][day_off]\" class=\"kab-form-control\"/></td><td><input type=\"text\" name=\"daysoff[][reason]\" class=\"kab-form-control\"/></td>");add("kab-add-sp","kab-sp-rows","<td><input type=\"date\" name=\"specialdays[][special_date]\" class=\"kab-form-control\"/></td><td><input type=\"time\" name=\"specialdays[][start_time]\" class=\"kab-form-control\"/></td><td><input type=\"time\" name=\"specialdays[][end_time]\" class=\"kab-form-control\"/></td>");})();</script>';
    }
    public function render_locations_page() {
        $this->render_static_header( 'locations' );
        global $wpdb; $rows = $wpdb->get_results( "SELECT location, COUNT(*) AS cnt FROM {$wpdb->prefix}kab_events WHERE status='active' GROUP BY location ORDER BY cnt DESC", ARRAY_A );
        ?>
        <div class="kab-card"><div class="kab-card-header"><h2 class="kab-card-title"><?php esc_html_e('Locations','kura-ai-booking-free'); ?></h2></div><div class="kab-card-body"><table class="kab-table"><thead><tr><th><?php esc_html_e('Location','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Events','kura-ai-booking-free'); ?></th></tr></thead><tbody><?php foreach($rows as $r){ echo '<tr><td>'.esc_html($r['location']).'</td><td>'.esc_html($r['cnt']).'</td></tr>'; } ?></tbody></table></div></div>
        <?php
    }
    public function render_notifications_page() {
        $this->render_static_header( 'notifications' );
        $admin_email = get_option( 'admin_email' );
        $send_url = wp_nonce_url( admin_url('admin-post.php?action=kab_send_test_email'), 'kab_send_test_email' );
        ?>
        <div class="kab-card"><div class="kab-card-header"><h2 class="kab-card-title"><?php esc_html_e('Notifications','kura-ai-booking-free'); ?></h2></div><div class="kab-card-body"><p><?php esc_html_e('Send a test email to verify your mail settings.','kura-ai-booking-free'); ?></p><p><strong><?php esc_html_e('Admin Email:','kura-ai-booking-free'); ?></strong> <?php echo esc_html( $admin_email ); ?></p><a class="kab-btn kab-btn-primary" href="<?php echo esc_url($send_url); ?>"><?php esc_html_e('Send Test Email','kura-ai-booking-free'); ?></a></div></div>
        <?php
    }
    public function render_customize_page() {
        $this->render_static_header( 'customize' );
        $opts = get_option( 'kab_settings', array() );
        echo '<div class="kab-card"><div class="kab-card-header"><h2 class="kab-card-title">'.esc_html__( 'Customize', 'kura-ai-booking-free' ).'</h2></div><div class="kab-card-body">';
        echo '<form method="post" action="'.esc_url( admin_url( 'options.php' ) ).'">';
        settings_fields( 'kab-settings-group' );
        echo '<div class="kab-form-grid"><div class="kab-col">';
        $p = isset($opts['brand_primary'])?$opts['brand_primary']:'#E67E22';
        $s = isset($opts['brand_secondary'])?$opts['brand_secondary']:'#628141';
        $l = isset($opts['brand_light'])?$opts['brand_light']:'#EBD5AB';
        $a = isset($opts['brand_accent'])?$opts['brand_accent']:'#8BAE66';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Primary Color','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="kab_settings[brand_primary]" value="'.esc_attr($p).'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Secondary Color','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="kab_settings[brand_secondary]" value="'.esc_attr($s).'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Light Color','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="kab_settings[brand_light]" value="'.esc_attr($l).'"/></div>';
        echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Accent Color','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="kab_settings[brand_accent]" value="'.esc_attr($a).'"/></div>';
        echo '</div><div class="kab-col">';
        $tz = ! empty($opts['show_client_tz']);
        echo '<div class="kab-form-group"><label class="kab-form-label"><input type="checkbox" name="kab_settings[show_client_tz]" value="1" '.checked($tz,1,false).'/> '.esc_html__('Show booking slots in client time zone','kura-ai-booking-free').'</label></div>';
        echo '</div></div><div class="kab-form-group"><button class="kab-btn kab-btn-primary" type="submit">'.esc_html__('Save','kura-ai-booking-free').'</button></div>';
        echo '</form></div></div>';
    }

    public function render_custom_fields_page() {
        $action = isset($_GET['action'])?sanitize_key($_GET['action']):'list'; $field_id = isset($_GET['field_id'])?intval($_GET['field_id']):0;
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-custom-fields.php'; $model = new KAB_Custom_Fields();
        if($action==='add' || $action==='edit'){ $f = $field_id ? $model->get_field($field_id) : null; $title = $field_id?__('Edit Field','kura-ai-booking-free'):__('Add Field','kura-ai-booking-free'); $act = $field_id?'kab_update_field':'kab_create_field'; $nonce = $field_id?('kab_update_field_'.$field_id):'kab_create_field'; $this->render_static_header('custom-fields'); echo '<div class="kab-card"><div class="kab-card-header"><h2 class="kab-card-title">'.$title.'</h2></div><div class="kab-card-body">'; echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">'; echo '<input type="hidden" name="action" value="'.$act.'"/>'; if($field_id){ echo '<input type="hidden" name="field_id" value="'.esc_attr($field_id).'"/>'; } wp_nonce_field($nonce); echo '<div class="kab-form-grid"><div class="kab-col">'; echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Name','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="name" value="'.esc_attr($f['name']??'').'" '.($field_id?'readonly':'').' required/></div>'; echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Label','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="label" value="'.esc_attr($f['label']??'').'" required/></div>'; echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Type','kura-ai-booking-free').'</label><select class="kab-form-control" name="type">'; foreach(['text','number','textarea','select','checkbox','date'] as $t){ echo '<option value="'.$t.'" '.selected($f['type']??'text',$t,false).'>'.$t.'</option>'; } echo '</select></div>'; echo '<div class="kab-form-group"><label class="kab-form-label">'.esc_html__('Options (comma separated for select)','kura-ai-booking-free').'</label><input class="kab-form-control" type="text" name="options" value="'.esc_attr($f['options']??'').'"/></div>'; echo '<div class="kab-form-group"><label class="kab-form-label"><input type="checkbox" name="required" '.checked(intval($f['required']??0),1,false).'/> '.esc_html__('Required','kura-ai-booking-free').'</label></div>'; echo '</div></div><div class="kab-form-group"><button class="kab-btn kab-btn-primary" type="submit">'.esc_html__('Save','kura-ai-booking-free').'</button> <a class="kab-btn" href="'.esc_url(admin_url('admin.php?page=kab-custom-fields')).'">'.esc_html__('Cancel','kura-ai-booking-free').'</a></div>'; echo '</form></div></div>'; } else { $this->render_static_header('custom-fields'); $rows = $model->get_fields(); echo '<div class="kab-card"><div class="kab-card-header" style="display:flex;justify-content:space-between;align-items:center;"><h2 class="kab-card-title">'.esc_html__('Custom Fields','kura-ai-booking-free').'</h2><a class="kab-btn kab-btn-primary" href="'.esc_url(admin_url('admin.php?page=kab-custom-fields&action=add')).'"><span class="dashicons dashicons-plus"></span> '.esc_html__('Add Field','kura-ai-booking-free').'</a></div><div class="kab-card-body">'; echo '<table class="kab-table"><thead><tr><th>'.esc_html__('Name','kura-ai-booking-free').'</th><th>'.esc_html__('Label','kura-ai-booking-free').'</th><th>'.esc_html__('Type','kura-ai-booking-free').'</th><th>'.esc_html__('Required','kura-ai-booking-free').'</th><th>'.esc_html__('Actions','kura-ai-booking-free').'</th></tr></thead><tbody>'; foreach($rows as $r){ $del = wp_nonce_url(admin_url('admin-post.php?action=kab_delete_field&field_id='.$r['id']),'kab_delete_field_'.$r['id']); echo '<tr><td>'.esc_html($r['name']).'</td><td>'.esc_html($r['label']).'</td><td>'.esc_html($r['type']).'</td><td>'.(intval($r['required'])?esc_html__('Yes','kura-ai-booking-free'):esc_html__('No','kura-ai-booking-free')).'</td><td><a class="kab-btn kab-btn-secondary kab-btn-sm" href="'.esc_url(admin_url('admin.php?page=kab-custom-fields&action=edit&field_id='.$r['id'])).'">'.esc_html__('Edit','kura-ai-booking-free').'</a> <a class="kab-btn kab-btn-danger kab-btn-sm" href="'.esc_url($del).'">'.esc_html__('Delete','kura-ai-booking-free').'</a></td></tr>'; } echo '</tbody></table></div></div>'; }
    }

	/**
	 * Render shortcodes page
	 */
	public function render_shortcodes_page() {
		require_once KAB_FREE_PLUGIN_DIR . 'includes/admin/class-kab-shortcodes-page.php';
		KAB_Shortcodes_Page::render_page();
	}

    private function render_placeholder_page( $active, $title ) {
        ?>
        <div class="kab-admin-wrapper">
            <?php $this->render_static_header( $active ); ?>
            <div class="kab-card"><div class="kab-card-header"><h2 class="kab-card-title"><?php echo esc_html( $title ); ?></h2></div><div class="kab-card-body"><p><?php echo esc_html__( 'This section is under development.', 'kura-ai-booking-free' ); ?></p></div></div>
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
    private function render_event_attendees( $event_id ) {
        require_once KAB_FREE_PLUGIN_DIR . 'includes/class-kab-events.php';
        $events_model = new KAB_Events(); $event = $events_model->get_event( $event_id );
        if ( ! $event ) { wp_die( __( 'Event not found', 'kura-ai-booking-free' ) ); }
        global $wpdb; $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_bookings WHERE booking_type='event' AND event_id=%d ORDER BY created_at DESC", $event_id ), ARRAY_A );
        ?>
        <div class="kab-admin-wrapper">
            <?php $this->render_static_header( 'events' ); ?>
            <div class="kab-card"><div class="kab-card-header"><h2 class="kab-card-title"><?php echo esc_html( sprintf( __( 'Attendees • %s', 'kura-ai-booking-free' ), $event['name'] ) ); ?></h2><div><a href="<?php echo esc_url( admin_url( 'admin.php?page=kab-events' ) ); ?>" class="kab-btn">&larr; <?php esc_html_e('Back','kura-ai-booking-free'); ?></a></div></div><div class="kab-card-body"><table class="kab-table"><thead><tr><th><?php esc_html_e('Customer','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Date','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Ticket','kura-ai-booking-free'); ?></th><th><?php esc_html_e('Status','kura-ai-booking-free'); ?></th></tr></thead><tbody><?php foreach($rows as $r){ $u=get_user_by('id',$r['user_id']); echo '<tr><td>'.esc_html($u?$u->display_name:'—').'</td><td>'.esc_html($r['created_at']).'</td><td>'.esc_html($r['ticket_id']).'</td><td>'.esc_html(ucfirst($r['status'])).'</td></tr>'; } ?></tbody></table></div></div>
        </div>
        <?php
    }
}
        // Google Calendar
        if ( isset( $input['google_client_id'] ) ) { $sanitized['google_client_id'] = sanitize_text_field( $input['google_client_id'] ); }
        if ( isset( $input['google_client_secret'] ) ) { $sanitized['google_client_secret'] = sanitize_text_field( $input['google_client_secret'] ); }
        $sanitized['google_remove_busy'] = isset( $input['google_remove_busy'] ) ? absint( $input['google_remove_busy'] ) : 0;
