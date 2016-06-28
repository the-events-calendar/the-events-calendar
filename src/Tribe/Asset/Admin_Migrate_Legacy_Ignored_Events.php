<?php

class Tribe__Events__Asset__Admin_Migrate_Legacy_Ignored_Events extends Tribe__Events__Asset__Abstract_Asset {
	public function handle() {
		$deps = array_merge(
			$this->deps,
			array(
				'jquery',
			)
		);

		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'admin/migrate-legacy-ignored-events.js' ), true );

		wp_enqueue_script( $this->prefix . '-admin-migrate-legacy-ignored-events', $path, $deps, $this->filter_js_version(), true );
	}
}
