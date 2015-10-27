<?php

class Tribe__Events__Asset__Admin extends Tribe__Events__Asset__Abstract_Asset {
	public function handle() {
		$deps = array_merge(
			$this->deps,
			array(
				'jquery',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
				'tribe-inline-bumpdown',
				'wp-util',
			)
		);

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'events-admin.js' ), true );

		wp_enqueue_script( $this->prefix . '-admin', $path, $deps, $this->filter_js_version(), true );
	}
}
