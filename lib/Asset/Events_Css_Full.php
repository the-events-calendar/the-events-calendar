<?php


	class Tribe__Events__Asset__Events_Css_Full extends Tribe__Events__Asset__Abstract_Events_Css {

		public function handle( array &$stylesheets, $mobile_break ) {
			$stylesheets['tribe-events-calendar-style'] = 'tribe-events-full.css';
			if ( $mobile_break > 0 ) {
				$stylesheets['tribe-events-calendar-mobile-style'] = 'tribe-events-full-mobile.css';
			}
		}
	}