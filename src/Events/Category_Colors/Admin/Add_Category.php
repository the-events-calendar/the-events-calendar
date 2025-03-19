<?php
/**
 * Handles the addition of category colors in the WordPress admin.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;
use Tribe__Events__Main;

/**
 * Class Add_Category
 *
 * Provides functionality for adding category color settings in the WordPress admin.
 * Handles displaying and saving color fields when creating new event categories.
 *
 * @since TBD
 */
class Add_Category extends Abstract_Admin {

	/**
	 * Displays custom fields in the "Add New Category" form.
	 *
	 * @since TBD
	 *
	 * @param string $taxonomy The taxonomy slug.
	 * 
	 * @return string The rendered template HTML.
	 */
	public function display_category_fields( string $taxonomy ): string {
		// Get all keys from Meta_Keys.
		$meta_keys = tribe( Meta_Keys::class )->get_all_keys();

		// Prepare empty defaults for each key.
		$category_colors = array_fill_keys( array_keys( $meta_keys ), '' );

		// Set default for 'priority' explicitly as 0 (since it's numeric).
		$category_colors['priority'] = 0;

		$context = [
			'taxonomy'        => $taxonomy,
			'category_colors' => $category_colors,
		];

		return $this->get_template()->template( 'add-category-fields', $context );
	}
}
