<?php


	class Tribe__Events__Asset__Settings extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$deps = array_merge( $this->deps, array( 'jquery' ) );
			$path = Tribe_Template_Factory::getMinFile( $this->resources_url . 'tribe-settings.js', true );
			wp_enqueue_script( $this->prefix . '-settings', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
		}
	}