<?php
/**
 * A View that will reflect back the view context for debugging purposes.
 *
 * @since   4.9.3
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;

/**
 * Class Reflector_View
 *
 * @since   4.9.3
 * @package Tribe\Events\Views\V2\Views
 */
class Reflector_View extends View {

	/**
	 * Slug for this view.
	 *
	 * @since 4.9.4
	 *
	 * @var string
	 */
	protected $slug = 'reflector';

	/**
	 * Visibility for this view.
	 *
	 * @since 4.9.4
	 *
	 * @var bool
	 */
	protected $publicly_visible = false;

	/**
	 * Overrides the base HTML method to return the JSON representation of the view context.
	 *
	 * @since 4.9.3
	 *
	 * @return false|string The result of the `json_encode` called on the current view context.
	 */
	public function get_html() {
		return wp_json_encode( $this->context->to_array(), JSON_PRETTY_PRINT );
	}
}
