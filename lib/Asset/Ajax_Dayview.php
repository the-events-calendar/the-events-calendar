<?php


	class Tribe__Events__Asset__Ajax_Dayview extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$ajax_data = array(
				"ajaxurl" => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
				'post_type' => Tribe__Events__Events::POSTTYPE
			);
			$path = Tribe__Events__Template_Factory::getMinFile( $this->resources_url . 'tribe-events-ajax-day.js', true );
			wp_enqueue_script( 'tribe-events-ajax-day', $path, array( 'tribe-events-bar' ), apply_filters( 'tribe_events_js_version', Tribe__Events__Events::VERSION ), true );
			wp_localize_script( 'tribe-events-ajax-day', 'TribeCalendar', $ajax_data );
		}
	}