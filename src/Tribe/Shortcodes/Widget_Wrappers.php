<?php

/**
 * Registers shortcodes handlers for each of the widget wrappers.
 */
class Tribe__Events__Pro__Shortcodes__Widget_Wrappers {
	public function __construct() {
		add_shortcode( 'tribe_mini_calendar',   array( $this, 'mini_calendar' ) );
		add_shortcode( 'tribe_events_list',     array( $this, 'events_list' ) );
		add_shortcode( 'tribe_featured_venue',  array( $this, 'featured_venue' ) );
		add_shortcode( 'tribe_event_countdown', array( $this, 'event_countdown' ) );
	}

	public function mini_calendar( $atts ) {
		require_once( Tribe__Events__Pro__Main::instance()->pluginPath . 'lib/Shortcodes/Filtered_Shortcode.php' );
		require_once( Tribe__Events__Pro__Main::instance()->pluginPath . 'lib/Shortcodes/Mini_Calendar.php' );
		$wrapper = new Tribe__Events__Pro__Shortcodes__Mini_Calendar( $atts );
		return $wrapper->output;
	}

	public function events_list( $atts ) {
		require_once( Tribe__Events__Pro__Main::instance()->pluginPath . 'lib/Shortcodes/Filtered_Shortcode.php' );
		require_once( Tribe__Events__Pro__Main::instance()->pluginPath . 'lib/Shortcodes/Events_List.php' );
		$wrapper = new Tribe__Events__Pro__Shortcodes__Events_List( $atts );
		return $wrapper->output;
	}

	public function featured_venue( $atts ) {
		require_once( Tribe__Events__Pro__Main::instance()->pluginPath . 'lib/Shortcodes/Featured_Venue.php' );
		$wrapper = new Tribe__Events__Pro__Shortcodes__Featured_Venue( $atts );
		return $wrapper->output;
	}

	public function event_countdown( $atts ) {
		require_once( Tribe__Events__Pro__Main::instance()->pluginPath . 'lib/Shortcodes/Event_Countdown.php' );
		$wrapper = new Tribe__Events__Pro__Shortcodes__Event_Countdown( $atts );
		return $wrapper->output;
	}
}