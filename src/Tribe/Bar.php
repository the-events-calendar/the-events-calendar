<?php

/**
 *
 */
class Tribe__Events__Bar {

	private static $instance;

	// Each row should be an assosiative array with three fields: name, caption and html (html is the markup of the field)
	private $filters = array();

	// Each row should be an assosiative array with three fields: displaying, anchor and url.
	// Displaying is the value of Tribe__Events__Main->displaying
	private $views = array();

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'wp_enqueue_scripts', array( $this, 'load_script' ), 9 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'tribe_events_bar_before_template', array( $this, 'disabled_bar_before' ) );
		add_action( 'tribe_events_bar_after_template', array( $this, 'disabled_bar_after' ) );
	}

	/**
	 * Decide if the TribeBar should be shown in a particular pageview.
	 *
	 * @filter tribe-events-bar-views to get all the registred views that the Bar will show
	 * @filter tribe-events-bar-should-show to allow themers to always hide the bar if they want.
	 *
	 * To always hide the Bar, add this to your theme's functions.php:
	 *        add_filter( 'tribe-events-bar-should-show', '__return_false' );
	 *
	 * @return bool
	 *
	 */
	public function should_show() {
		global $wp_query;
		$show_bar_filter = in_array(
			get_post_type(), array(
				Tribe__Events__Main::VENUE_POST_TYPE,
				Tribe__Events__Main::ORGANIZER_POST_TYPE,
			)
		) ? false : true;
		$is_tribe_view   = ( ! empty( $wp_query->tribe_is_event_query ) && ! is_single() && $show_bar_filter );

		return apply_filters( 'tribe-events-bar-should-show', $is_tribe_view );
	}

	/**
	 * Adds a body class of tribe-bar-is-disabled when the Tribe Bar is disabled.
	 *
	 * @return array The new body class array
	 */
	public function body_class( $classes ) {
		if ( tribe_get_option( 'tribeDisableTribeBar', false ) == true ) {
			$classes[] = 'tribe-bar-is-disabled';
		}

		return $classes;
	}

	/**
	 * Returns the opening tag of the disabled bar wrapper
	 *
	 * @return string
	 */
	public function disabled_bar_before( $before ) {
		if ( tribe_get_option( 'tribeDisableTribeBar', false ) == true ) {
			$before = '<div class="tribe-bar-disabled">';
			echo $before;
		}
	}

	/**
	 * Returns the closing tag of the disabled bar wrapper
	 *
	 * @return array The new body class array
	 */
	public function disabled_bar_after( $after ) {
		if ( tribe_get_option( 'tribeDisableTribeBar', false ) == true ) {
			$after = '</div>';
			echo $after;
		}
	}

	/**
	 *    Load the CSSs and JSs only if the Bar will be shown
	 */
	public function load_script() {

		if ( $this->should_show() ) {
			Tribe__Events__Template_Factory::asset_package( 'jquery-placeholder' );
			Tribe__Events__Template_Factory::asset_package( 'bootstrap-datepicker' );
			Tribe__Events__Template_Factory::asset_package( 'tribe-events-bar' );

			do_action( 'tribe-events-bar-enqueue-scripts' );
		}
	}

	/**
	 * @static
	 * @return Tribe__Events__Bar
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}

}
