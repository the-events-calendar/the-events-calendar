<?php
/**
 * Handles the manipulation of the excerpt.
 *
 * @since   4.9.10
 *
 * @package Tribe\Events\Views\V2\Template
 */
namespace Tribe\Events\Views\V2\Template;

use Tribe\Events\Views\V2\Hooks;
use Tribe__Events__Main as Plugin;
use Tribe__Template as Base_Template;

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
		$list_view_slug = \Tribe\Events\Views\V2\Views\List_View::get_view_slug();
		$context = tribe_context();
		$view = $context->get( 'event_display_mode', $list_view_slug );

		return in_array( $view, [ $list_view_slug, 'day' ] ) ? 30 : $length;
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

		// Read the method doc-block to understand why we do this here.
		$event = $this->avoiding_filter_loop( static function () {
			return tribe_get_event( get_the_ID() );
		} );

		$template = strtolower( get_template() );

		// Check if theme is twentyseventeen.
		$should_replace_read_more = $template && 'twentyseventeen' === $template;

		/**
		 * Determines if we need to replace the excerpt read more link in a given scenario or not.
		 *
		 * @since 4.9.11
		 *
		 * @param bool     $should_replace_read_more Whether we need to replace the excerpt read more link or not.
		 * @param \WP_Post $event                    Event that we are dealing with.
		 *
		 * @see tribe_get_event() for the format and the properties of the decorated post.
		 */
		$should_replace_read_more = apply_filters( 'tribe_events_views_v2_should_replace_excerpt_more_link', $should_replace_read_more, $event );

		if ( ! $should_replace_read_more ) {
			return $link;
		}

		return $this->template( 'components/read-more', [ 'event' => $event ], false );
	}

	/**
	 * Handles the infinite loop that could happen when the excerpt filtering fires as a consequence of a
	 * `Lazy_String` resolution in the `tribe_get_event` function.
	 *
	 * To correctly apply the `read-more` template, and account for possible third-parties overrides, we need the
	 * result of a call to the `tribe_get_event` function.
	 * If object caching is active that function will be fired on `shutdown` and will resolve all of its `Lazy_String`
	 * instances.
	 * One of those is the one holding the value of the filtered post excerpt.
	 * This will cause an infinite loop if not handled.
	 *
	 * @since 5.0.0
	 *
	 * @param callable $function The function that should be resolved avoiding a filter infinite loop.
	 *
	 * @return mixed The result value of the function call.
	 */
	protected function avoiding_filter_loop( callable $function ) {
		$hooks = tribe( Hooks::class );
		// As registered in `Tribe\Events\Views\V2\Hooks::action_include_filters_excerpt`.
		$priority   = 50;
		$has_filter = has_filter( 'excerpt_more', [ $hooks, 'filter_excerpt_more' ] );

		if ( $has_filter ) {
			remove_filter( 'excerpt_more', [ $hooks, 'filter_excerpt_more' ], $priority );
		}

		$result = $function();

		if ( $has_filter ) {
			add_filter( 'excerpt_more', [ $hooks, 'filter_excerpt_more' ], $priority );
		}

		return $result;
	}
}
