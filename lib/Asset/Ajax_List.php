<?php


	class Tribe__Events__Asset__Ajax_List extends Tribe__Events__Asset__Abstract_Asset {

		public function handle() {
			$deps = array_merge( $this->deps, array( 'jquery', $this->prefix . '-calendar-script' ) );
			$tribe_paged = ( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
			$ajax_data = array(
				"ajaxurl" => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
				'tribe_paged' => $tribe_paged
			);
			$path = Tribe_Template_Factory::getMinFile( $this->resources_url . 'tribe-events-ajax-list.js', true );
			wp_enqueue_script( 'tribe-events-list', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
			wp_localize_script( 'tribe-events-list', 'TribeList', $ajax_data );
		}
	}