<?php
/**
 * Edit tags page for Category Colors.
 *
 * This class handles the logic for the edit tags page for Category Colors.
 *
 * @since TBD
 *
 * @package Tribe\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Events\Category_Colors\Category_Colors;
use Tribe__Events__Main as TEC;

/**
 * EditTags class.
 *
 * @since TBD
 *
 * @package Tribe\Events\Category_Colors\Admin
 */
class Edit_Tags {

	/**
	 * Add hooks for the edit tags page.
	 *
	 * @since TBD
	 */
	public function add_hooks() {
		$this->assets();

		add_filter( 'manage_edit-' . TEC::TAXONOMY . '_columns', [ $this, 'add_custom_taxonomy_columns' ] );
		add_action( 'quick_edit_custom_box', [ $this, 'add_custom_quick_edit_fields' ], 10, 3 );
		add_action( 'edited_term_taxonomy', [ $this, 'save_quick_edit_custom_fields' ], 10, 2 );
	}

	/**
	 * Remove hooks for the edit tags page.
	 *
	 * @since TBD
	 */
	public function remove_hooks() {
		remove_filter( 'manage_edit-' . TEC::TAXONOMY . '_columns', [ $this, 'add_custom_taxonomy_columns' ] );
		remove_action( 'quick_edit_custom_box', [ $this, 'add_custom_quick_edit_fields' ], 10, 3 );
		remove_action( 'edited_term_taxonomy', [ $this, 'save_quick_edit_custom_fields' ], 10, 2 );
	}

	/**
	 * Determine whether or not we are on the edit tags page for event categories.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_on_page(): bool {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-tags.php' !== $screen->base ) {
			return false;
		}

		return TEC::TAXONOMY === $screen->taxonomy;
	}

	/**
	 * Enqueue scripts for edit tags page.
	 *
	 * @since TBD
	 */
	public function assets(): void {
		$plugin = tribe( 'tec.main' );

		tribe_asset(
			$plugin,
			'tec-category-colors-admin-edit-tags-js',
			'admin/category-colors-edit-tags.js',
			[
				'jquery',
				'wp-color-picker',
			],
			'admin_enqueue_scripts',
		);

		tribe_asset(
			$plugin,
			'tec-category-colors-admin-edit-tags-css',
			'admin/category-colors-edit-tags.css',
			[
				'wp-color-picker',
			],
			'admin_enqueue_scripts',
		);
	}

	/**
	 * Adds Foreground, Background, and Text-Color columns to the taxonomy table.
	 *
	 * @since TBD
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array Modified columns.
	 */
	public function add_custom_taxonomy_columns( $columns ): array {
		$columns['tec-category-colors-priority'] = __( 'Priority', 'the-events-calendar' );
		$columns['tec-category-colors-color']    = __( 'Color', 'the-events-calendar' );

		return $columns;
	}

	/**
	 * Adds custom fields (Foreground, Background, Text-Color) to the Quick Edit form.
	 *
	 * @since TBD
	 *
	 * @param string $column_name Column name.
	 * @param string $post_type   Post type.
	 * @param string $taxonomy    Taxonomy name.
	 *
	 * @return void
	 */
	public function add_custom_quick_edit_fields( $column_name, $post_type, $taxonomy ): void {
		if ( TEC::TAXONOMY !== $taxonomy ) {
			return;
		}

		switch ( $column_name ) {
			case 'tec-category-colors-priority':
				?>
					<fieldset class="inline-edit-col-right">
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php esc_html_e( 'Priority', 'the-events-calendar' ); ?></span>
								<input type="number" name="tec-category-colors-priority" class="tec-category-color-priority" value="" />
							</label>
						</div>
					</fieldset>
				<?php
				break;
			case 'tec-category-colors-color':
				?>
				<fieldset class="inline-edit-col-right">
					<div class="inline-edit-col">
						<label>
							<div>Category Colors</div>
							<input type="hidden" name="tec-category-colors-color" value="" />
							<table>
								<tr>
									<td>
										<div>Primary Color</div>
										<input type="text" name="tec-category-color-foreground" class="tec-events-category-color-picker tec-category-color-foreground" value="" />
									</td>
									<td>
									<div>Background Color</div>
										<input type="text" name="tec-category-color-background" class="tec-events-category-color-picker tec-category-color-background" value="" />
									</td>
									<td>
									<div>Text Color</div>
										<input type="text" name="tec-category-color-text-color" class="tec-events-category-color-picker tec-category-color-text-color" value="" />
									</td>
								</tr>
							</table>
						</label>
					</div>
				</fieldset>
				<?php
				break;
		}
	}

	/**
	 * Save custom fields from Quick Edit for taxonomy terms.
	 *
	 * @param int    $term_id  The ID of the term being edited.
	 * @param string $taxonomy The taxonomy being edited.
	 */
	public function save_quick_edit_custom_fields( int $term_id, string $taxonomy ) {
		if ( TEC::TAXONOMY !== $taxonomy ) {
			return;
		}

		// Verify nonce to ensure the request is valid.
		check_ajax_referer( 'taxinlineeditnonce', '_inline_edit' );

		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) || empty( $term ) ) {
			return;
		}

		// Define the meta keys for the custom fields.
		$meta_keys = [
			'tec-category-color-foreground' => Category_Colors::$meta_foreground_slug,
			'tec-category-color-background' => Category_Colors::$meta_background_slug,
			'tec-category-color-text-color' => Category_Colors::$meta_text_color_slug,
		];

		update_term_meta( $term->term_id, Category_Colors::$meta_selected_category_slug, true );

		// Loop through the expected fields and save them if they are present.
		foreach ( $meta_keys as $field_key => $meta_key ) {
			$value = tec_get_request_var( $field_key, null );

			// If the value exists, sanitize and save it.
			if ( null !== $value ) {
				$sanitized_value = sanitize_text_field( $value );
				update_term_meta( $term_id, $meta_key, $sanitized_value );
			}
		}
	}
}
