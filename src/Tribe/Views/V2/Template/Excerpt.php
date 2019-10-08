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

		return in_array( $view, [ 'list', 'day' ] ) ? 30 : $length;
	}

	/**
	 * Filters the excerpt more button.
	 *
	 * Set the excerpt more button styles for twentyseventeen.
	 *
	 * @since TBD
	 *
	 * @param string $link The excerpt read more link.
	 *
	 * @return string The excerpt read more link modified, if necessary.
	 */
	public function maybe_filter_excerpt_more( $link ) {

		$template = strtolower( get_template() );

		// Check if theme is twentyseventeen.
		if ( ! $template || 'twentyseventeen' !== $template ) {
			return $link;
		}

		$link  = '<div class="tribe-events-c-small-cta tribe-common-b3 tribe-events-c-small-cta--readmore">';
		$link .= '<a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt">';
		$link .= esc_html__( 'Continue Reading' , 'the-events-calendar' );
		$link .= '</a>';
		$link .= '</div>';

		return $link;
	}

}
