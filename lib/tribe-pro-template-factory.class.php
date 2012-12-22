<?php

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_PRO_Template_Factory') ) {
	class Tribe_PRO_Template_Factory extends Tribe_Template_Factory {

		public static function asset_package( $name ){

			$tec_pro = TribeEventsPro::instance();
			$prefix = 'tribe-events-pro';

			// setup plugin resources & 3rd party vendor urls
			$resouces_url = trailingslashit( $tec_pro->pluginUrl ) . 'resources/';
			$vendor_url = trailingslashit( $tec_pro->pluginUrl ) . 'vendor/';

			switch( $name ) {
				case 'ajax-weekview' :					
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'post_type' => TribeEvents::POSTTYPE );					
					wp_enqueue_script('tribe-events-pro-week', $resouces_url . 'tribe-events-week.js', array('jquery'), false, false);
					wp_localize_script( 'tribe-events-pro-week', 'TribeWeek', $ajax_data );
					break;					
				case 'ajax-photoview' :				
					$tribe_paged = ( !empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'tribe_paged' => $tribe_paged );
					wp_enqueue_script( 'tribe-events-pro-isotope', $vendor_url . 'isotope/jquery.isotope.min.js', array(), NULL );	
					wp_enqueue_script('tribe-events-pro-photo', $resouces_url . 'tribe-events-photo-view.js', array('jquery'));
					wp_localize_script( 'tribe-events-pro-photo', 'TribePhoto', $ajax_data );
					break;					
				case 'ajax-dayview':
					$ajax_data = array( "ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'post_type' => TribeEvents::POSTTYPE );
					wp_enqueue_script( 'tribe-events-pro-ajax-day', $resouces_url . 'tribe-events-ajax-day.js' );
					wp_localize_script( 'tribe-events-pro-ajax-day', 'TribeCalendar', $ajax_data );
					break;
			}

		}
		
	}
}