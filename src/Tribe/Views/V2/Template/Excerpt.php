<?php
/**
 * Handles the manipulation of the excerpt.
 *
 * @since   4.9.10
 *
 * @package Tribe\Events\Views\V2\Template
 */
namespace Tribe\Events\Views\V2\Template;

use Tribe__Template as Base_Template;
use Tribe__Events__Main as Plugin;
use Tribe\Events\Views\V2\Hooks;

/**
 * Class Excerpt
 *
 * @since   4.9.10
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Excerpt extends Base_Template {

	/**
	 * Excerpt constructor.
	 *
	 * @since 4.9.10
	 */
	public function __construct() {
		$this->set_template_origin( Plugin::instance() );
		$this->set_template_folder( 'src/views/v2' );
		$this->set_template_context_extract( true );
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Filters the excerpt length.
	 *
	 * Set the excerpt length for list and day view.
	 *
	 * @since 4.9.10
	 *
	 * @param int $length The excerpt length.
	 *
	 * @return int The excerpt length modified, if necessary.
	 */
	public function maybe_filter_excerpt_length( $length ) {
		$context = tribe_context();
		$view = $context->get( 'event_display_mode', 'list' );

		return in_array( $view, [ 'list', 'day' ] ) ? 30 : $length;
	}

	/**
	 * Filters the excerpt more button.
	 *
	 * Set the excerpt more button styles for twentyseventeen.
	 *
	 * @since 4.9.10
	 *
	 * @param string $link The excerpt read more link.
	 *
	 * @return string The excerpt read more link modified, if necessary.
	 */
	public function maybe_filter_excerpt_more( $link ) {
		if ( is_admin() ) {
			return $link;
		}

		$event = tribe_get_event( get_the_ID() );

		$template = strtolower( get_template() );

		// Check if theme is twentyseventeen.
		$should_replace_read_more = $template && 'twentyseventeen' === $template;

		/**
		 * Detemines the require
		 *
		 * @since 4.9.11
		 *
		 * @param bool    $should_replace_read_more Determines if we need to replace the excerpt read more link
		 *                                          in a given scenario.
		 * @param WP_Post $event                    Event that we are dealing with.
		 */
		$should_replace_read_more = apply_filters( 'tribe_events_views_v2_should_replace_excerpt_more_link', $should_replace_read_more, $event );

		// If shouldn't replace we bail.
		if ( ! $should_replace_read_more ) {
			return $link;
		}

		return $this->template( 'components/read-more', [ 'event' => $event ], false );
	}

}
