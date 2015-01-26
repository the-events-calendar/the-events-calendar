<?php


	class Tribe__Events__Asset__Calendar_Script extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$deps = array_merge( $this->deps, array( 'jquery' ), Tribe__Events__Template_Factory::get_vendor_scripts() );
			$path = Tribe__Events__Template_Factory::getMinFile( $this->resources_url . 'tribe-events.js', true );
			$handle = $this->prefix . '-calendar-script';
			wp_enqueue_script( $handle, $path, $deps, $this->filter_js_version() );
			$js_config_array = array(
				'permalink_settings' => get_option( 'permalink_structure' ),
				'events_post_type' => Tribe__Events__Events::POSTTYPE
			);
			wp_localize_script( $handle, 'tribe_js_config', $js_config_array );
		}
	}