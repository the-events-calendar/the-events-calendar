<?php


	class Tribe__Events__Asset__Events_Css_Skeleton extends Tribe__Events__Asset__Abstract_Events_Css {

		public function handle( array &$stylesheets, $mobile_break ) {
			$stylesheets['tribe-events-calendar-style'] = 'tribe-events-skeleton.css';
		}
	}