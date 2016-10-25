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
class Tribe__Events__Integrations__XTheme__XTheme {

	/**
	 * @var Tribe__Events__Integrations__XTheme__XTheme
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__XTheme__XTheme
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
				'template_include', array( Tribe__Events__Integrations__X_Theme__Filters::instance(), 'filter_template_include' )
			);
		}
	}
}
