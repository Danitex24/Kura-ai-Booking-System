<?php
/**
 * Event Categories Admin Page
 *
 * @package Kura-ai-Booking-Free
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Handle create/update
if ( isset( $_POST['kab_save_category'] ) && check_admin_referer( 'kab_save_category' ) ) {
	$category_id = ! empty( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0;
	$category_data = array(
		'name' => sanitize_text_field( $_POST['category_name'] ),
		'slug' => sanitize_title( $_POST['category_slug'] ),
		'description' => sanitize_textarea_field( $_POST['category_description'] ),
		'icon' => sanitize_text_field( $_POST['category_icon'] ),
		'color' => sanitize_hex_color( $_POST['category_color'] ),
		'status' => sanitize_text_field( $_POST['category_status'] ),
	);

	if ( $category_id ) {
		// Update
		$updated = $wpdb->update(
			$wpdb->prefix . 'kab_event_categories',
			$category_data,
			array( 'id' => $category_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
		if ( $updated !== false ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Category updated successfully!', 'kura-ai-booking-free' ) . '</p></div>';
		}
	} else {
		// Create
		$inserted = $wpdb->insert(
			$wpdb->prefix . 'kab_event_categories',
			$category_data,
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		if ( $inserted ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Category created successfully!', 'kura-ai-booking-free' ) . '</p></div>';
		}
	}
}

// Handle delete
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) && check_admin_referer( 'kab_delete_category_' . intval( $_GET['id'] ) ) ) {
	$id = intval( $_GET['id'] );
	// Delete category relations first
	$wpdb->delete( $wpdb->prefix . 'kab_event_category_relations', array( 'category_id' => $id ), array( '%d' ) );
	// Delete category
	$deleted = $wpdb->delete( $wpdb->prefix . 'kab_event_categories', array( 'id' => $id ), array( '%d' ) );
	if ( $deleted ) {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Category deleted successfully!', 'kura-ai-booking-free' ) . '</p></div>';
	}
}

// Fetch categories
$categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}kab_event_categories ORDER BY name ASC" );

// Get category for editing
$edit_category = null;
if ( isset( $_GET['edit'] ) ) {
	$edit_id = intval( $_GET['edit'] );
	$edit_category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kab_event_categories WHERE id = %d", $edit_id ), ARRAY_A );
}
?>

<div class="wrap">
	<h1><?php echo esc_html__( 'Event Categories', 'kura-ai-booking-free' ); ?></h1>

	<div class="kab-admin-container" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
		<!-- Add/Edit Form -->
		<div class="kab-card">
			<h2><?php echo $edit_category ? esc_html__( 'Edit Category', 'kura-ai-booking-free' ) : esc_html__( 'Add New Category', 'kura-ai-booking-free' ); ?></h2>

			<form method="post" action="">
				<?php wp_nonce_field( 'kab_save_category' ); ?>
				<?php if ( $edit_category ) : ?>
					<input type="hidden" name="category_id" value="<?php echo esc_attr( $edit_category['id'] ); ?>">
				<?php endif; ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="category_name"><?php echo esc_html__( 'Name', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="text" name="category_name" id="category_name" class="regular-text"
							       value="<?php echo $edit_category ? esc_attr( $edit_category['name'] ) : ''; ?>" required>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="category_slug"><?php echo esc_html__( 'Slug', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="text" name="category_slug" id="category_slug" class="regular-text"
							       value="<?php echo $edit_category ? esc_attr( $edit_category['slug'] ) : ''; ?>" required>
							<p class="description"><?php echo esc_html__( 'URL-friendly version of the name', 'kura-ai-booking-free' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="category_description"><?php echo esc_html__( 'Description', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<textarea name="category_description" id="category_description" rows="3" class="large-text"><?php echo $edit_category ? esc_textarea( $edit_category['description'] ) : ''; ?></textarea>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="category_icon"><?php echo esc_html__( 'Icon', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="text" name="category_icon" id="category_icon" class="regular-text"
							       value="<?php echo $edit_category ? esc_attr( $edit_category['icon'] ) : ''; ?>">
							<p class="description"><?php echo esc_html__( 'Dashicon class (e.g., dashicons-calendar)', 'kura-ai-booking-free' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="category_color"><?php echo esc_html__( 'Color', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<input type="color" name="category_color" id="category_color"
							       value="<?php echo $edit_category && $edit_category['color'] ? esc_attr( $edit_category['color'] ) : '#2271b1'; ?>">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="category_status"><?php echo esc_html__( 'Status', 'kura-ai-booking-free' ); ?></label>
						</th>
						<td>
							<select name="category_status" id="category_status" class="regular-text">
								<option value="active" <?php echo ( $edit_category && $edit_category['status'] === 'active' ) ? 'selected' : ''; ?>>
									<?php echo esc_html__( 'Active', 'kura-ai-booking-free' ); ?>
								</option>
								<option value="inactive" <?php echo ( $edit_category && $edit_category['status'] === 'inactive' ) ? 'selected' : ''; ?>>
									<?php echo esc_html__( 'Inactive', 'kura-ai-booking-free' ); ?>
								</option>
							</select>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="kab_save_category" class="button button-primary"
					       value="<?php echo $edit_category ? esc_attr__( 'Update Category', 'kura-ai-booking-free' ) : esc_attr__( 'Add Category', 'kura-ai-booking-free' ); ?>">
					<?php if ( $edit_category ) : ?>
						<a href="?page=kab-event-categories" class="button"><?php echo esc_html__( 'Cancel', 'kura-ai-booking-free' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>

		<!-- Categories List -->
		<div class="kab-card">
			<h2><?php echo esc_html__( 'Existing Categories', 'kura-ai-booking-free' ); ?></h2>

			<?php if ( $categories ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 40px;"></th>
							<th><?php echo esc_html__( 'Name', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Slug', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Events', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'kura-ai-booking-free' ); ?></th>
							<th><?php echo esc_html__( 'Actions', 'kura-ai-booking-free' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $categories as $category ) :
							$event_count = $wpdb->get_var( $wpdb->prepare(
								"SELECT COUNT(*) FROM {$wpdb->prefix}kab_event_category_relations WHERE category_id = %d",
								$category->id
							) );
						?>
							<tr>
								<td style="text-align: center;">
									<?php if ( $category->color ) : ?>
										<div style="width: 24px; height: 24px; background: <?php echo esc_attr( $category->color ); ?>; border-radius: 4px;"></div>
									<?php endif; ?>
								</td>
								<td>
									<strong><?php echo esc_html( $category->name ); ?></strong>
									<?php if ( $category->icon ) : ?>
										<span class="dashicons <?php echo esc_attr( $category->icon ); ?>" style="font-size: 16px;"></span>
									<?php endif; ?>
									<?php if ( $category->description ) : ?>
										<br><small style="color: #646970;"><?php echo esc_html( $category->description ); ?></small>
									<?php endif; ?>
								</td>
								<td><code><?php echo esc_html( $category->slug ); ?></code></td>
								<td><?php echo esc_html( $event_count ); ?></td>
								<td>
									<?php
									$status_colors = array(
										'active' => '#00a32a',
										'inactive' => '#646970',
									);
									$color = isset( $status_colors[ $category->status ] ) ? $status_colors[ $category->status ] : '#646970';
									?>
									<span style="color: <?php echo esc_attr( $color ); ?>; font-weight: 600;">
										<?php echo esc_html( ucfirst( $category->status ) ); ?>
									</span>
								</td>
								<td>
									<a href="?page=kab-event-categories&edit=<?php echo esc_attr( $category->id ); ?>" class="button button-small">
										<?php echo esc_html__( 'Edit', 'kura-ai-booking-free' ); ?>
									</a>
									<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $category->id ) ), 'kab_delete_category_' . $category->id ) ); ?>"
									   class="button button-small"
									   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this category?', 'kura-ai-booking-free' ) ); ?>');">
										<?php echo esc_html__( 'Delete', 'kura-ai-booking-free' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php echo esc_html__( 'No categories found. Create your first category using the form on the left.', 'kura-ai-booking-free' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Auto-generate slug from name
	$('#category_name').on('blur', function() {
		var name = $(this).val();
		var slug = $('#category_slug').val();
		if (name && !slug) {
			var generatedSlug = name.toLowerCase()
				.replace(/[^\w\s-]/g, '')
				.replace(/[\s_-]+/g, '-')
				.replace(/^-+|-+$/g, '');
			$('#category_slug').val(generatedSlug);
		}
	});
});
</script>

<style>
.kab-admin-container {
	max-width: 1400px;
}
.kab-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
	padding: 20px;
}
.kab-card h2 {
	margin-top: 0;
	padding-bottom: 10px;
	border-bottom: 1px solid #eee;
}
</style>
