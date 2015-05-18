<?php


class Tribe__Events__Pro__Asset__Events_Pro_Css extends Tribe__Events__Asset__Events_Css {

	public function __construct() {
		$this->dir        = dirname( __FILE__ );
		$this->class_name = __CLASS__;
	}

	public function handle() {
		$stylesheets  = array();
		$mobile_break = tribe_get_mobile_breakpoint();

		// Get the selected style option
		$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );

		// from `some-style-option`
		// to `Tribe__Events__Pro__Asset__Events_Css_Some_Style_Option`
		$child_class_name = $this->get_child_class_name( $style_option );

		/**
		 * @var Tribe__Events__Asset__Abstract_Events_Css
		 */
		$child_class_instance = new $child_class_name;

		// `$stylesheets` passed by reference
		$child_class_instance->handle( $stylesheets, $mobile_break );

		// put override css at the end of the array
		$stylesheets['tribe-events-calendar-pro-override-style'] = 'tribe-events/pro/tribe-events-pro.css';

		// do the enqueues
		foreach ( $stylesheets as $name => $css_file ) {
			if ( $name == 'tribe-events-calendar-pro-override-style' ) {
				$user_stylesheet_url = Tribe__Events__Templates::locate_stylesheet( $css_file );
				if ( $user_stylesheet_url ) {
					wp_enqueue_style( $name, $user_stylesheet_url );
				}
			} else {

				// get full URL
				$url = tribe_events_pro_resource_url( $css_file );

				// get the minified file
				$url = Tribe__Events__Template_Factory::getMinFile( $url, true );

				// apply filters
				$url = apply_filters( 'tribe_events_pro_stylesheet_url', $url, $name );

				// set the $media attribute
				if ( $name == 'tribe-events-calendar-pro-mobile-style' || $name == 'tribe-events-calendar-full-pro-mobile-style' ) {
					$media = "only screen and (max-width: {$mobile_break}px)";
					wp_enqueue_style( $name, $url, array( 'tribe-events-calendar-pro-style' ), Tribe__Events__Pro__Main::VERSION, $media );
				} else {
					wp_register_style( $name, $url, array(), Tribe__Events__Pro__Main::VERSION );
					wp_enqueue_style( $name );
				}
			}
		}
	}
}
