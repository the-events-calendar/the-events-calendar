<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Ecp_Plugins extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$deps = array_merge( $this->deps, [ 'jquery' ] );
		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'jquery-ecp-plugins.js' ), true );
		wp_enqueue_script( $this->prefix . '-ecp-plugins', $path, $deps, $this->filter_js_version() );
	}
}
