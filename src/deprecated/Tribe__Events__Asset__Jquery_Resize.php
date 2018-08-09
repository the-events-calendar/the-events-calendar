<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Jquery_Resize extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$path = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'jquery-resize/jquery.ba-resize.js', true );
		$deps = array_merge( $this->deps, array( 'jquery' ) );

		$handle = $this->prefix . '-jquery-resize';
		wp_enqueue_script( $handle, $path, $deps, '1.1', false );
		Tribe__Events__Template_Factory::add_vendor_script( $handle );
	}
}