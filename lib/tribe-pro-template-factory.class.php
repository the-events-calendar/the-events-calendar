<?php

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_PRO_Template_Factory') ) {
	class Tribe_PRO_Template_Factory extends Tribe_Template_Factory {

		protected function __construct() {
			parent::__construct();
			add_action('tribe_events_asset_package', array(__CLASS__, 'asset_package'), 10, 2);
		}

		public static function asset_package( $name, $deps = array() ){

			$tec_pro = TribeEventsPro::instance();
			$prefix = 'tribe-events-pro';

			// setup plugin resources & 3rd party vendor urls
			$resources_url = trailingslashit( $tec_pro->pluginUrl ) . 'resources/';
			$vendor_url = trailingslashit( $tec_pro->pluginUrl ) . 'vendor/';

			switch( $name ) {
				case 'ajax-weekview' :					
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'post_type' => TribeEvents::POSTTYPE );
					wp_enqueue_script('tribe-events-pro-week', $resources_url . 'tribe-events-week.js', array('jquery'), false, false);
					wp_enqueue_script( 'tribe-events-pro-slimscroll', $vendor_url . 'jquery-slimscroll/jquery.slimscroll.min.js', array('jquery-ui-draggable'), null );
					wp_localize_script( 'tribe-events-pro-week', 'TribeWeek', $ajax_data );
					break;					
				case 'ajax-photoview' :				
					$tribe_paged = ( !empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'tribe_paged' => $tribe_paged );
					wp_enqueue_script( 'tribe-events-pro-isotope', $vendor_url . 'isotope/jquery.isotope.min.js', array(), null );
					wp_enqueue_script('tribe-events-pro-photo', $resources_url . 'tribe-events-photo-view.js', array('jquery'));
					wp_localize_script( 'tribe-events-pro-photo', 'TribePhoto', $ajax_data );
					break;					
				case 'ajax-dayview':
					$ajax_data = array( "ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'post_type' => TribeEvents::POSTTYPE );
					wp_enqueue_script( 'tribe-events-pro-ajax-day', $resources_url . 'tribe-events-ajax-day.js' );
					wp_localize_script( 'tribe-events-pro-ajax-day', 'TribeCalendar', $ajax_data );
					break;

				case 'ajax-maps':
					$http = is_ssl() ? 'https' : 'http';

					wp_register_script( 'gmaps', $http . '://maps.google.com/maps/api/js?sensor=false', array( 'jquery' ) );
					wp_register_script( 'tribe-events-pro-geoloc', $resources_url . 'tribe-events-ajax-maps.js', array( 'gmaps' ) );
					wp_enqueue_script( 'tribe-events-pro-geoloc' );

					$geoloc = TribeEventsGeoLoc::instance();
					$data   = array( 'ajaxurl'  => admin_url( 'admin-ajax.php', $http ),
					                 'nonce'    => wp_create_nonce( 'geosearch' ),
					                 'center'   => $geoloc->estimate_center_point(),
					                 'map_view' => ( TribeEvents::instance()->displaying == 'map' ) ? true : false );

					wp_localize_script( 'tribe-events-pro-geoloc', 'GeoLoc', $data );

					break;
			}
			parent::asset_package( $name, $deps );
		}
	}
}