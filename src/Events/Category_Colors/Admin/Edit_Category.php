<?php
/**
 * Handles the editing of category colors in the WordPress admin.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use WP_Term;

/**
 * Class Edit_Category
 *
 * Provides functionality for editing category color settings in the WordPress admin.
 * Handles displaying and saving color fields when editing existing event categories.
 *
 * @since 6.14.0
 */
class Edit_Category extends Abstract_Admin {
	/**
	 * Displays custom fields in the "Edit Category" form.
	 *
	 * @since 6.14.0
	 *
	 * @param WP_Term $tag      The term object.
	 * @param string  $taxonomy The taxonomy slug.
	 *
	 * @return string The rendered template HTML.
	 */
	public function display_category_fields( WP_Term $tag, string $taxonomy ): string {
		if ( empty( $tag->term_id ) ) {
			return '';
		}

		$category_colors = $this->get_category_colors( $tag->term_id );

		$context = [
			'taxonomy'        => $taxonomy,
			'category_colors' => $category_colors,
		];

		return $this->get_template()->template( 'edit-category-fields-table', $context );
	}
}
