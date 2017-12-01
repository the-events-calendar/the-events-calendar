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
		if ( function_exists( 'x_force_template_override' ) ) {
			add_filter(
				'template_include', array( $this, 'filter_template_include' )
			);
		}
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
		/** @var WP_Query $wp_query */
		global $wp_query;

		if ( $wp_query->is_main_query()
		     && empty( $wp_query->tribe_is_multi_posttype )
		     && ! empty( $wp_query->tribe_is_event_query )
		) {
			remove_filter( 'template_include', 'x_force_template_override', 99 );
		}

		return $template;
	}
}
