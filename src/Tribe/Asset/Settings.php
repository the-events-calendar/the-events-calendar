<?php


class Tribe__Events__Asset__Settings extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge( $this->deps, array( 'jquery' ) );
		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'tribe-settings.js' ), true );
		wp_enqueue_script( $this->prefix . '-settings', $path, $deps, $this->filter_js_version(), true );
	}
}