<?php


class Tribe__Events__Pro__Asset__Ajax_Maps extends Tribe__Events__Asset__Abstract_Asset {

	public function handle() {
		$http = is_ssl() ? 'https' : 'http';
		$url  = apply_filters( 'tribe_events_pro_google_maps_api', $http . '://maps.google.com/maps/api/js?sensor=false' );
		wp_register_script( 'tribe-gmaps', $url, array( 'tribe-events-pro' ) );
		$path = Tribe__Events__Template_Factory::getMinFile( tribe_events_pro_resource_url( 'tribe-events-ajax-maps.js' ), true );
		wp_register_script( 'tribe-events-pro-geoloc', $path, array(
			'tribe-gmaps',
			Tribe__Events__Template_Factory::get_placeholder_handle(),
		), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ) );
		wp_enqueue_script( 'tribe-events-pro-geoloc' );

		$geoloc = Tribe__Events__Pro__Geo_Loc::instance();
		$data   = array(
			'ajaxurl'  => admin_url( 'admin-ajax.php', $http ),
			'nonce'    => wp_create_nonce( 'tribe_geosearch' ),
			'map_view' => 'map' == Tribe__Events__Main::instance()->displaying ? true : false,
		);

		wp_localize_script( 'tribe-events-pro-geoloc', 'GeoLoc', $data );
	}
}
