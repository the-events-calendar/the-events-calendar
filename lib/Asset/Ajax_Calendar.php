<?php


	class Tribe__Events__Asset__Ajax_Calendar extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$deps = array_merge( $this->deps, array(
				'jquery', $this->prefix . '-bootstrap-datepicker', $this->prefix . '-calendar-script'
			) );
			$ajax_data = array( "ajaxurl" => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
			$path = Tribe_Template_Factory::getMinFile( $this->resources_url . 'tribe-events-ajax-calendar.js', true );
			wp_enqueue_script( 'tribe-events-calendar', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
			wp_localize_script( 'tribe-events-calendar', 'TribeCalendar', $ajax_data );
		}
	}