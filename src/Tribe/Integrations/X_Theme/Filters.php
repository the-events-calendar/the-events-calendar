<?php


/**
 * Class Tribe__Events__Integrations__X_Theme__Filters
 *
 * Filters small functionality filters to ensure compatibility with the X theme.
 */
class Tribe__Events__Integrations__X_Theme__Filters {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__X_Theme__Filters
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
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
