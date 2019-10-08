<?php
/**
 * Handles the manipulation of the excerpt.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Template
 */

namespace Tribe\Events\Views\V2\Template;
use Tribe__Template as Base_Template;
use Tribe__Events__Main as Plugin;

/**
 * Class Excerpt
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Excerpt extends Base_Template {

	/**
	 * Excerpt constructor.
	 *
	 * @since TBD
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

		if ( is_admin() ) {
			return $link;
		}

		$template = strtolower( get_template() );

		// Check if theme is twentyseventeen.
		if ( ! $template || 'twentyseventeen' !== $template ) {
			return $link;
		}

		return $this->template( 'components/read-more', [], false );
	}

}
