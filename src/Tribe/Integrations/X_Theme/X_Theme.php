<?php
/**
 * Class Tribe__Events__Integrations__X_Theme__X_Theme
 *
 * Handles the integration between The Events Calendar plugin and
 * the X Theme.
 *
 * This class is meant to be an entry point hooking specialized classes and not
 * a logic hub per se.
 */
class Tribe__Events__Integrations__X_Theme__X_Theme {

	/**
	 * @var Tribe__Events__Integrations__X_Theme__X_Theme
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__X_Theme__X_Theme
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hooks the filters and actions neede for this integration to work.
	 */
	public function hook() {
		add_filter( 'template_include', array( $this, 'filter_template_include' ) );
		add_filter( 'x_get_view', array( $this, 'force_full_content' ), 10, 4 );
	}

	/**
	 * Tries to "catch" the loading of X theme content templates that render a highly-filtered
	 * excerpt view instead of full content, which often ruins the display of our Month View etc.
	 *
	 * @since 4.6.2
	 * @see x_get_view()
	 *
	 * @return array $view An array of X-theme view data with the template file and render options.
	 */
	public function force_full_content( $view, $directory, $file_base, $file_extension ) {

		// Let users disable this forceful override behavior if they'd like.
		if ( ! apply_filters( 'tribe_events_x_theme_force_full_content', true ) ) {
			return $view;
		}

		// Don't proceed if we're not on a main Tribe view or if $view isn't fully fleshed-out.
		if ( ! $this->should_run_tribe_overrides() || ! is_array( $view ) ) {
			return $view;
		}

		// Don't proceed if we're not dealing with an X-theme view that doesn't have these params.
		if ( ! isset( $view['base'] ) || ! isset( $view['extension'] ) ) {
			return $view;
		}

		// Only interrupt the normal process if we're dealing with an excerpted "content" template.
		if (
			'framework/views/global/_content' === $view['base']
			&& 'the-excerpt' === $view['extension']
		) {
			remove_filter( 'x_get_view', array( $this, 'force_full_content' ), 10, 4 );

			// Grab the global "content" template with full content.
			$view = x_get_view( 'global', '_content', 'the-content' );

			add_filter( 'x_get_view', array( $this, 'force_full_content' ), 10, 4 );
		}

		return $view;
	}

	/**
	 * Use the filter as an action to remove further filtering on X theme side
	 * if the query is for our content.
	 *
	 * @param string $template
	 *
	 * @return string $template
	 */
	public function filter_template_include( $template ) {

		if ( $this->should_run_tribe_overrides() ) {
			remove_filter( 'template_include', 'x_force_template_override', 99 );
		}

		return $template;
	}

	/**
	 * Checks if we're in a "main" calendar view, like Month View etc., where we want to apply our
	 * various integration filters and overrides.
	 *
	 * @since 4.6.2
	 *
	 * @return boolean
	 */
	public function should_run_tribe_overrides() {

		/** @var WP_Query $wp_query */
		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		return $wp_query->is_main_query()
			   && empty( $wp_query->tribe_is_multi_posttype )
			   && ! empty( $wp_query->tribe_is_event_query );
	}
}
