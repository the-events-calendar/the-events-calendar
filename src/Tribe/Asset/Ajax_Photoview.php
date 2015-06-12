<?php


class Tribe__Events__Pro__Asset__Ajax_Photoview extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$tribe_paged = ( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
		$ajax_data   = array(
			'ajaxurl'     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			'tribe_paged' => $tribe_paged,
		);
		$path1       = Tribe__Events__Template_Factory::getMinFile( $this->vendor_url . 'isotope/jquery.isotope.js', true );
		$path2       = Tribe__Events__Template_Factory::getMinFile( tribe_events_pro_resource_url( 'tribe-events-photo-view.js' ), true );
		wp_enqueue_script( 'tribe-events-pro-isotope', $path1, array( 'tribe-events-pro' ), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ), true );
		wp_enqueue_script( 'tribe-events-pro-photo', $path2, array( 'tribe-events-pro-isotope' ), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ), true );
		wp_localize_script( 'tribe-events-pro-photo', 'TribePhoto', $ajax_data );
	}
}
