<?php
/**
 * Handles the addition of category colors in the WordPress admin.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe__Template;

/**
 * Class Add_Category
 *
 * Provides functionality for adding category color settings in the WordPress admin.
 * Handles displaying and saving color fields when creating new event categories.
 *
 * @since TBD
 */
class Add_Category extends Abstract_Admin {
	use Meta_Keys_Trait;

	/**
	 * Constructor for the Add_Category class.
	 *
	 * @since TBD
	 *
	 * @param Tribe__Template|null $template The template instance to use for rendering.
	 */
	public function __construct( ?Tribe__Template $template = null ) {
		parent::__construct( $template );
	}

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
		$meta_keys = $this->get_all_keys();

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
