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
		echo '<div class="wrap"><h1>' . esc_html__( 'Settings', 'kura-ai-booking-free' ) . '</h1></div>';
	}
}
