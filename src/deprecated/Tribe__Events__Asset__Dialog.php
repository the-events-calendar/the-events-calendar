<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Dialog extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		wp_enqueue_script( 'jquery-ui-dialog' );
		Tribe__Events__Template_Factory::add_vendor_script( 'jquery-ui-dialog' );
	}
}
