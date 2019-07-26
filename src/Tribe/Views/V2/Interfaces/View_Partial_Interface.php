<?php
/**
 * Models the API of a Views v2 partial component.
 *
 * @since   4.9.5
 * @package Tribe\Events\Views\V2\Interfaces
 */

namespace Tribe\Events\Views\V2\Interfaces;

/**
 * Interface View_Partial_Interface
 *
 * @since   4.9.5
 * @package Tribe\Events\Views\V2\Interfaces
 */
interface View_Partial_Interface {
	/**
	 * Renders the partials and returns its HTML code.
	 *
	 * @since 4.9.5
	 *
	 * @param \Tribe__Template $template The template instance currently rendering.
	 *
	 * @return string
	 */
	public function render( \Tribe__Template $template );
}