<?php
/**
 * Handles the manipulation of the excerpt.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Template
 */

namespace Tribe\Events\Views\V2\Template;

/**
 * Class Excerpt
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Excerpt {

	/**
	 * Excerpt constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {}

	/**
	 * Filters the excerpt length.
	 *
	 * Set the excerpt length for list and day view.
	 *
	 * @since TBD
	 *
	 * @param int $length The excerpt length.
	 *
	 * @return int The excerpt length modified, if necessary.
	 */
	public function maybe_filter_excerpt_length( $length ) {
		$context = tribe_context();
		$view = $context->get( 'event_display_mode', 'list' );

		if ( 'list' === $view || 'day' === $view ) {
			$length = 30;
		}

		return $length;
	}

}
