<?php

namespace TEC\Events\Category_Colors\Admin;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;
use Tribe__Events__Main;
use WP_Term;

class Edit_Category extends Abstract_Admin {

	/**
	 * Displays custom fields in the "Edit Category" form.
	 *
	 * @since TBD
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
