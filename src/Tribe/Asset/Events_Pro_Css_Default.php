<?php


class Tribe__Events__Pro__Asset__Events_Pro_Css_Default extends Tribe__Events__Asset__Abstract_Events_Css {

	public function handle( array &$stylesheets, $mobile_break ) {
		$stylesheets['tribe-events-full-pro-calendar-style'] = 'tribe-events-pro-full.css';
		$stylesheets['tribe-events-calendar-pro-style']      = 'tribe-events-pro-theme.css';
		if ( $mobile_break > 0 ) {
			$stylesheets['tribe-events-calendar-full-pro-mobile-style'] = 'tribe-events-pro-full-mobile.css';
			$stylesheets['tribe-events-calendar-pro-mobile-style']      = 'tribe-events-pro-theme-mobile.css';
		}
	}
}
