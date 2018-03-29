<?php


class Tribe__Events__Asset__Admin_Menu extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'admin-menu.css' ), true );
		wp_enqueue_style( $this->prefix . '-admin-menu', $path, array(), Tribe__Events__Main::VERSION );
	}
}
