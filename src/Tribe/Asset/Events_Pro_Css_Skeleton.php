<?php


class Tribe__Events__Pro__Asset__Events_Pro_Css_Skeleton extends Tribe__Events__Asset__Abstract_Events_Css {

	public function handle( array &$stylesheets, $mobile_break ) {
		$stylesheets['tribe-events-calendar-pro-style'] = 'tribe-events-pro-skeleton.css';
	}
}