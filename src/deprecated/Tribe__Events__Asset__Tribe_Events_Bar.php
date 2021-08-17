<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Tribe_Events_Bar extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge( $this->deps, [
			'jquery',
			$this->prefix . '-calendar-script',
			$this->prefix . '-bootstrap-datepicker',
			$this->prefix . '-jquery-resize',
			Tribe__Events__Template_Factory::get_placeholder_handle(),
		] );
		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'tribe-events-bar.js' ), true );
		wp_enqueue_script( $this->prefix . '-bar', $path, $deps, $this->filter_js_version() );
	}
}
