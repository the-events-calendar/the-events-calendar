<?php


	class Tribe__Events__Asset__Calendar_Script extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$deps = array_merge( $this->deps, array( 'jquery' ), Tribe_Template_Factory::get_vendor_scripts() );
			$path = Tribe_Template_Factory::getMinFile( $this->resources_url . 'tribe-events.js', true );
			wp_enqueue_script( $this->prefix . '-calendar-script', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
			$js_config_array = array(
				'permalink_settings' => get_option( 'permalink_structure' ), 'events_post_type' => TribeEvents::POSTTYPE
			);
			wp_localize_script( $this->prefix . '-calendar-script', 'tribe_js_config', $js_config_array );
		}
	}