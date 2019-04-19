<?php
/**
 * The interface all Views should implement.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

/**
 * Interface View_Interface
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
interface View_Interface {

	/**
	 * Returns a View HTML code.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_html(  );

	/**
	 * Returns the view slug.
	 *
	 * The slug should be the one that will allow the view to be built by the View class by slug.
	 *
	 * @since TBD
	 *
	 * @return string The view slug.
	 */
	public function get_slug(  );
}