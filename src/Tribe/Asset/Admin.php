<?php


class Tribe__Events__Asset__Admin extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge(
			$this->deps,
			array(
				'jquery',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
				'tribe-bumpdown',
				'tribe-dropdowns',
				'underscore',
				'wp-util',
				'tribe-jquery-timepicker',
			)
		);

		$no_conflict_mode = apply_filters( 'tribe_events_admin_js_noconflict_mode', false );
		$load_in_footer   = true;

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'events-admin.js' ), true );

		if ( $no_conflict_mode ) {
			wp_register_script( 'events-admin-noconflict', tribe_events_resource_url( 'events-admin-noconflict.js' ), $deps, '', false );

			$load_in_footer = false;
			$deps[]         = 'events-admin-noconflict';
		}

		wp_enqueue_script( $this->prefix . '-admin', $path, $deps, $this->filter_js_version(), $load_in_footer );
		wp_enqueue_style( 'dashicons' );
	}
}
