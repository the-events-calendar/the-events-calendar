<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

class Tribe__Events__Asset__Ajax_Dayview extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$ajax_data = array(
			'ajaxurl'   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			'post_type' => Tribe__Events__Main::POSTTYPE,
		);
		$path      = Tribe__Events__Template_Factory::getMinFile( tribe_events_resource_url( 'tribe-events-ajax-day.js' ), true );

		$handle = 'tribe-events-ajax-day';
		wp_enqueue_script( $handle, $path, array( 'tribe-events-bar' ), $this->filter_js_version(), true );
		wp_localize_script( $handle, 'TribeCalendar', $ajax_data );
	}
}
