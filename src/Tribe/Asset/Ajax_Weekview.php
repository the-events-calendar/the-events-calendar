<?php


class Tribe__Events__Pro__Asset__Ajax_Weekview extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$ajax_data = array(
			'ajaxurl'   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			'post_type' => Tribe__Events__Main::POSTTYPE,
		);

		$path1 = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'nanoscroller/jquery.nanoscroller.js', true );
		wp_enqueue_script( 'tribe-events-pro-slimscroll', $path1, array(
			'tribe-events-pro',
			'jquery-ui-draggable',
		), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ), true );

		$path2 = Tribe__Events__Template_Factory::getMinFile( tribe_events_pro_resource_url( 'tribe-events-week.js' ), true );
		wp_enqueue_script( 'tribe-events-pro-week', $path2, array(
			'tribe-events-pro-slimscroll',
		), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ), true );

		wp_localize_script( 'tribe-events-pro-week', 'TribeWeek', $ajax_data );
	}
}
