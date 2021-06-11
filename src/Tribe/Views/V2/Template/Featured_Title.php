<?php
/**
 * Handles the manipulation of the featured template title to correctly render it in the context of a Featured Views v2 request.
 *
 * @since   5.1.5
 *
 * @package Tribe\Events\Views\V2\Template
 */

namespace Tribe\Events\Views\V2\Template;

use Tribe__Context as Context;

/**
 * Class Featured_Title
 *
 * @since   5.1.5
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Featured_Title {

	/**
	 * Filter the plural events label for Featured V2 Views.
	 *
	 * @since 5.1.5
	 *
	 * @param string  $label   The plural events label as it's been generated thus far.
	 * @param Context $context The context used to build the title, it could be the global one, or one externally
	 *                         set.
	 *
	 * @return string the original label or updated label for virtual archives.
	 */
	public function filter_views_v2_wp_title_plural_events_label( $label, Context $context ) {

		$context = $context ? $context : tribe_context();

		if ( $context->is( 'featured' ) ) {
			return sprintf(
				/* translators: %s: events label plural */
				_x( 'Featured %s', 'featured events title', 'the-events-calendar' ),
				$label
			);
		}

		return $label;
	}
}
