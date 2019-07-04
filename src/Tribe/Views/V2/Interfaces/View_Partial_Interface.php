<?php
/**
 * Models the API of a Views v2 partial component.
 *
 * @since   TBD
 * @package Tribe\Events\Views\V2\Interfaces
 */

namespace Tribe\Events\Views\V2\Interfaces;

/**
 * Interface View_Partial_Interface
 *
 * @since   TBD
 * @package Tribe\Events\Views\V2\Interfaces
 */
interface View_Partial_Interface {
	/**
	 * Renders the partials and returns its HTML code.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Template $template The template instance currently rendering.
	 *
	 * @return string
	 */
	public function render( \Tribe__Template $template );
}