Save Changes"></p></form>';
			}
		} else {
			// Add Event Form
			echo '<h2>' . esc_html__( 'Add New Event', 'kura-ai-booking-free' ) . '</h2>';
			echo '<form method="post">
				<input type="hidden" name="kab_add_event_nonce" value="' . wp_create_nonce( 'kab_add_event' ) . '" />
				<p><label>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="name" required></p>';
				<p><label>' . esc_html__( 'Description', 'kura-ai-booking-free' ) . '</label><br><textarea name="description" required></textarea></p>';
				<p><label>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</label><br><input type="date" name="event_date" required></p>';
				<p><label>' . esc_html__( 'Time', 'kura-ai-booking-free' ) . '</label><br><input type="time" name="event_time" required></p>';
				<p><label>' . esc_html__( 'Location', 'kura-ai-booking-free' ) . '</label><br><input type="text" name="location" required></p>';
				<p><label>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</label><br><input type="number" step="0.01" name="price" required></p>';
				<p><label>' . esc_html__( 'Capacity', 'kura-ai-booking-free' ) . '</label><br><input type="number" name="capacity" required></p>';
				<p><input type="submit" class="button-primary" value="Add Event"></p></form>';
			}

		// Events Table
		$events = $events_model->get_events();
		echo '<h2>' . esc_html__( 'Events List', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="wp-list-table widefat fixed striped">
			<thead><tr>
				<th>' . esc_html__( 'Name', 'kura-ai-booking-free' ) . '</th>
				<th>' . esc_html__( 'Date', 'kura-ai-booking-free' ) . '</th>
				<th>' . esc_html__( 'Time', 'kura-ai-booking-free' ) . '</th>
				<th>' . esc_html__( 'Location', 'kura-ai-booking-free' ) . '</th>
				<th>' . esc_html__( 'Price', 'kura-ai-booking-free' ) . '</th>
				<th>' . esc_html__( 'Capacity', 'kura-ai-booking-free' ) . '</th>
				<th>' . esc_html__( 'Actions', 'kura-ai-booking-free' ) . '</th>
			</tr></thead><tbody>';
		if ( $events ) {
			foreach ( $events as $event ) {
				$edit_url = add_query_arg( array( 'action' => 'edit', 'event_id' => $event['id'] ), menu_page_url( 'kab-events', false ) );
				$delete_url = wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'event_id' => $event['id'] ), menu_page_url( 'kab-events', false ) ), 'kab_delete_event_' . $event['id'] );
				echo '<tr>
					<td>' . esc_html( $event['name'] ) . '</td>
					<td>' . esc_html( $event['event_date'] ) . '</td>
					<td>' . esc_html( $event['event_time'] ) . '</td>
					<td>' . esc_html( $event['location'] ) . '</td>
					<td>' . esc_html( number_format( $event['price'], 2 ) ) . '</td>
					<td>' . esc_html( $event['capacity'] ) . '</td>
					<td>
						<a href="' . esc_url( $edit_url ) . '" class="button">' . esc_html__( 'Edit', 'kura-ai-booking-free' ) . '</a>
						<a href="' . esc_url( $delete_url ) . '" class="button kab-delete-event" data-event-name="' . esc_attr( $event['name'] ) . '">' . esc_html__( 'Delete', 'kura-ai-booking-free' ) . '</a>
					</td>
				</tr>';
			}
		} else {
			echo '<tr><td colspan="7">' . esc_html__( 'No events found.', 'kura-ai-booking-free' ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	public function render_customers_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Customers', 'kura-ai-booking-free' ) . '</h1></div>';
	}
	public function render_settings_page() {
		// Handle form submission
		if ( isset( $_POST['kab_settings_nonce'] ) && wp_verify_nonce( $_POST['kab_settings_nonce'], 'kab_save_settings' ) ) {
			$settings = array(
				'company_name' => sanitize_text_field( $_POST['company_name'] ),
				'company_logo' => esc_url_raw( $_POST['company_logo'] ),
				'support_email' => sanitize_email( $_POST['support_email'] ),
				'enable_tickets' => isset( $_POST['enable_tickets'] ) ? 'yes' : 'no',
				'email_from_name' => sanitize_text_field( $_POST['email_from_name'] ),
				'email_from_email' => sanitize_email( $_POST['email_from_email'] ),
			);
			update_option( 'kab_settings', $settings );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully.', 'kura-ai-booking-free' ) . '</p></div>';
		}

		// Get current settings
		$settings = get_option( 'kab_settings', array(
			'company_name' => get_bloginfo( 'name' ),
			'company_logo' => '',
			'support_email' => get_option( 'admin_email' ),
			'enable_tickets' => 'yes',
			'email_from_name' => get_bloginfo( 'name' ),
			'email_from_email' => get_option( 'admin_email' ),
		) );

		echo '<div class="wrap"><h1>' . esc_html__( 'Kura-ai Booking Settings', 'kura-ai-booking-free' ) . '</h1>';
		echo '<form method="post">';
		wp_nonce_field( 'kab_save_settings', 'kab_settings_nonce' );

		// Company Information
		echo '<h2>' . esc_html__( 'Company Information', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="company_name">' . esc_html__( 'Company Name', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="text" name="company_name" id="company_name" value="' . esc_attr( $settings['company_name'] ) . '" class="regular-text"></td></tr>';
		echo '<tr><th scope="row"><label for="company_logo">' . esc_html__( 'Company Logo URL', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="url" name="company_logo" id="company_logo" value="' . esc_attr( $settings['company_logo'] ) . '" class="regular-text"><p class="description">' . esc_html__( 'Full URL to your company logo image', 'kura-ai-booking-free' ) . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="support_email">' . esc_html__( 'Support Email', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="email" name="support_email" id="support_email" value="' . esc_attr( $settings['support_email'] ) . '" class="regular-text"></td></tr>';
		echo '</table>';

		// Email Settings
		echo '<h2>' . esc_html__( 'Email Settings', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="email_from_name">' . esc_html__( 'Email From Name', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="text" name="email_from_name" id="email_from_name" value="' . esc_attr( $settings['email_from_name'] ) . '" class="regular-text"></td></tr>';
		echo '<tr><th scope="row"><label for="email_from_email">' . esc_html__( 'Email From Address', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="email" name="email_from_email" id="email_from_email" value="' . esc_attr( $settings['email_from_email'] ) . '" class="regular-text"></td></tr>';
		echo '</table>';

		// Ticket Settings
		echo '<h2>' . esc_html__( 'Ticket Settings', 'kura-ai-booking-free' ) . '</h2>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="enable_tickets">' . esc_html__( 'Enable E-Tickets', 'kura-ai-booking-free' ) . '</label></th>';
		echo '<td><input type="checkbox" name="enable_tickets" id="enable_tickets" value="1" ' . checked( $settings['enable_tickets'], 'yes', false ) . '> ' . esc_html__( 'Enable QR code e-ticket generation', 'kura-ai-booking-free' ) . '</td></tr>';
		echo '</table>';

		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . esc_attr__( 'Save Settings', 'kura-ai-booking-free' ) . '"></p>';
		echo '</form></div>';
	}
}
