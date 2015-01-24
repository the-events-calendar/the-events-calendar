<?php


	class Tribe__Events__Asset__Admin extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$deps = array_merge( $this->deps, array( 'jquery', 'jquery-ui-datepicker' ) );
			$path = Tribe__Events__Template_Factory::getMinFile( $this->resources_url . 'events-admin.js', true );
			wp_enqueue_script( $this->prefix . '-admin', $path, $deps, apply_filters( 'tribe_events_js_version', Tribe__Events__Events::VERSION ), true );
		}
	}