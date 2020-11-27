<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Admin_Menu extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'admin-menu.css' ), true );
		wp_enqueue_style( $this->prefix . '-admin-menu', $path, [], Tribe__Events__Main::VERSION );
	}
}
