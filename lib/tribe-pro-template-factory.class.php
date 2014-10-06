<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe_PRO_Template_Factory' ) ) {
	class Tribe_PRO_Template_Factory extends Tribe_Template_Factory {

		/**
		 * The class constructor.
		 */
		public function __construct() {
			parent::__construct();
			add_action( 'tribe_events_asset_package', array( __CLASS__, 'asset_package' ), 10, 2 );
		}

		/**
		 * The asset loading function.
		 *
		 * @param string $name The name of the package reqested.
		 * @param array  $deps An array of dependencies (this should be the registered name that is registered to the wp_enqueue functions).
		 *
		 * @return void
		 */
		public static function asset_package( $name, $deps = array() ) {

			$tec_pro = TribeEventsPro::instance();
			$prefix  = 'tribe-events-pro';

			// setup plugin resources & 3rd party vendor urls
			$resources_url = trailingslashit( $tec_pro->pluginUrl ) . 'resources/';
			$vendor_url    = trailingslashit( $tec_pro->pluginUrl ) . 'vendor/';

			switch ( $name ) {
				case 'ajax-weekview' :
					$ajax_data = array(
						"ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
						'post_type' => TribeEvents::POSTTYPE
					);
					$path1     = Tribe_Template_Factory::getMinFile( $vendor_url . 'jquery-slimscroll/jquery.slimscroll.js', true );
					$path2     = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-week.js', true );
					wp_enqueue_script( 'tribe-events-pro-slimscroll', $path1, array(
							'tribe-events-pro',
							'jquery-ui-draggable'
						), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), true );
					wp_enqueue_script( 'tribe-events-pro-week', $path2, array( 'tribe-events-pro-slimscroll' ), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), true );
					wp_localize_script( 'tribe-events-pro-week', 'TribeWeek', $ajax_data );
					break;
				case 'ajax-photoview' :
					$tribe_paged = ( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data   = array(
						"ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
						'tribe_paged' => $tribe_paged
					);
					$path1       = Tribe_Template_Factory::getMinFile( $vendor_url . 'isotope/jquery.isotope.js', true );
					$path2       = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-photo-view.js', true );
					wp_enqueue_script( 'tribe-events-pro-isotope', $path1, array( 'tribe-events-pro' ), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), true );
					wp_enqueue_script( 'tribe-events-pro-photo', $path2, array( 'tribe-events-pro-isotope' ), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), true );
					wp_localize_script( 'tribe-events-pro-photo', 'TribePhoto', $ajax_data );
					break;
				case 'ajax-maps':
					$http = is_ssl() ? 'https' : 'http';
					$url  = apply_filters( 'tribe_events_pro_google_maps_api', $http . '://maps.google.com/maps/api/js?sensor=false' );
					wp_register_script( 'tribe-gmaps', $url, array( 'tribe-events-pro' ) );
					$path = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-ajax-maps.js', true );
					wp_register_script( 'tribe-events-pro-geoloc', $path, array(
							'tribe-gmaps',
							parent::get_placeholder_handle()
						), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ) );
					wp_enqueue_script( 'tribe-events-pro-geoloc' );

					$geoloc = TribeEventsGeoLoc::instance();
					$data   = array(
						'ajaxurl'  => admin_url( 'admin-ajax.php', $http ),
						'nonce'    => wp_create_nonce( 'tribe_geosearch' ),
						'map_view' => ( TribeEvents::instance()->displaying == 'map' ) ? true : false
					);

					wp_localize_script( 'tribe-events-pro-geoloc', 'GeoLoc', $data );

					break;

				case 'events-pro-css':
					$stylesheets  = array();
					$mobile_break = tribe_get_mobile_breakpoint();

					// Get the selected style option
					$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );

					// Determine the stylesheet files for the selected option
					switch ( $style_option ) {
						case 'skeleton':
							$stylesheets['tribe-events-calendar-pro-style'] = 'tribe-events-pro-skeleton.css';
							break;
						case 'full':
							$stylesheets['tribe-events-calendar-pro-style'] = 'tribe-events-pro-full.css';
							if ( $mobile_break > 0 ) {
								$stylesheets['tribe-events-calendar-pro-mobile-style'] = 'tribe-events-pro-full-mobile.css';
							}
							break;
						default: // tribe styles
							$stylesheets['tribe-events-full-pro-calendar-style'] = 'tribe-events-pro-full.css';
							$stylesheets['tribe-events-calendar-pro-style']      = 'tribe-events-pro-theme.css';
							if ( $mobile_break > 0 ) {
								$stylesheets['tribe-events-calendar-full-pro-mobile-style'] = 'tribe-events-pro-full-mobile.css';
								$stylesheets['tribe-events-calendar-pro-mobile-style']      = 'tribe-events-pro-theme-mobile.css';
							}
							break;
					}

					// put override css at the end of the array
					$stylesheets['tribe-events-calendar-pro-override-style'] = 'tribe-events/pro/tribe-events-pro.css';

					// do the enqueues
					foreach ( $stylesheets as $name => $css_file ) {
						if ( $name == 'tribe-events-calendar-pro-override-style' ) {
							$user_stylesheet_url = TribeEventsTemplates::locate_stylesheet( $css_file );
							if ( $user_stylesheet_url ) {
								wp_enqueue_style( $name, $user_stylesheet_url );
							}
						} else {

							// get full URL
							$url = tribe_events_pro_resource_url( $css_file );

							// get the minified file
							$url = self::getMinFile( $url, true );

							// apply filters
							$url = apply_filters( 'tribe_events_pro_stylesheet_url', $url, $name );

							// set the $media attribute
							if ( $name == 'tribe-events-calendar-pro-mobile-style' || $name == 'tribe-events-calendar-full-pro-mobile-style' ) {
								$media = "only screen and (max-width: {$mobile_break}px)";
								wp_enqueue_style( $name, $url, array( 'tribe-events-calendar-pro-style' ), TribeEventsPro::VERSION, $media );
							} else {
								wp_register_style( $name, $url, array(), TribeEventsPro::VERSION );
								wp_enqueue_style( $name );
							}
						}
					}

					break;
			}
			parent::asset_package( $name, $deps );
		}
	}
}