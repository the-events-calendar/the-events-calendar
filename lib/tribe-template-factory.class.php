<?php

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_Template_Factory') ) {
	class Tribe_Template_Factory {

		/**
		 * Asset calls for vendor packages
		 * @param  string $name
		 * @return null
		 */
		public static function asset_package( $name, $deps = array() ){

			$tec = TribeEvents::instance();
			$prefix = 'tribe-events'; // TribeEvents::POSTTYPE;

			// setup plugin resources & 3rd party vendor urls
			$resouces_url = trailingslashit( $tec->pluginUrl ) . 'resources/';
			$vendor_url = trailingslashit( $tec->pluginUrl ) . 'vendor/';

			switch( $name ) {
				case 'jquery-resize':
					wp_enqueue_script( $prefix . '-jquery-resize', $vendor_url . 'jquery-resize/jquery.ba-resize.min.js', array_merge( array( 'jquery' ), $deps ), '1.1', false );
					break;
				case 'chosen' : // Vendor: jQuery Chosen
					wp_enqueue_style( $prefix . '-chosen-style', $vendor_url . 'chosen/chosen/chosen.css' );
					wp_enqueue_script( $prefix . '-chosen-jquery', $vendor_url . 'chosen/chosen/chosen.jquery.min.js', array_merge( array( 'jquery' ), $deps ), '0.9.5', false );
					break;
				case 'smoothness' : // Vendor: jQuery Custom Styles
					wp_enqueue_style( $prefix . '-custom-jquery-styles', $vendor_url . 'jquery/smoothness/jquery-ui-1.8.23.custom.css' );
					break;
				case 'select2' : // Vendor: Select2
					wp_enqueue_style( $prefix . '-select2-css', $vendor_url . 'select2/select2.css' );
					wp_enqueue_script( $prefix . '-select2', $vendor_url . 'select2/select2.js', 'jquery', '3.2' );
					break;
				case 'calendar-script' : // Tribe Events JS
					wp_enqueue_script( $prefix . '-calendar-script', $resouces_url . 'tribe-events.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'datepicker' : // Vendor: jQuery Datepicker
					wp_enqueue_script( 'jquery-ui-datepicker' );
					wp_enqueue_style( 'jquery-ui-datepicker' );
					break;
				case 'dialog' : // Vendor: jQuery Dialog
					wp_enqueue_script( 'jquery-ui-dialog' );
					break;
				case 'admin-ui' : // Tribe Events 
					wp_enqueue_style( $prefix . '-admin-ui', $resouces_url . 'events-admin.css' );
					break;
				case 'admin' :
					wp_enqueue_script( $prefix . '-admin', $resouces_url . 'events-admin.js', array_merge( array('jquery-ui-datepicker'), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					break;
				case 'settings' :
					wp_enqueue_script( $prefix . '-settings', $resouces_url . 'tribe-settings.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					break;
				case 'ecp-plugins' : 
					wp_enqueue_script( $prefix . '-ecp-plugins', $resouces_url . 'jquery-ecp-plugins.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'tribe-events-bar' :
					wp_enqueue_script( $prefix . '-bar', $resouces_url . 'tribe-events-bar.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'jquery-placeholder' : // Vendor: jQuery Placeholder
					wp_enqueue_script( $prefix . '-jquery-placeholder', $vendor_url . 'jquery-placeholder/jquery.placeholder.min.js', array_merge( array( 'jquery' ), $deps ), '2.0.7', false );
					break;
				case 'ajax-calendar':
					$ajax_data = array( "ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
					wp_enqueue_script( 'tribe-events-calendar', $resouces_url . 'tribe-events-ajax-calendar.js', array(), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					wp_localize_script( 'tribe-events-calendar', 'TribeCalendar', $ajax_data );
					break;
				case 'ajax-list':
					$tribe_paged = ( !empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'tribe_paged' => $tribe_paged );
					wp_enqueue_script( 'tribe-events-list', $resouces_url . 'tribe-events-ajax-list.js', array(), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					wp_localize_script( 'tribe-events-list', 'TribeList', $ajax_data );
					break;
				case 'events-css':

					// Tribe Events CSS filename
					$event_file = 'tribe-events.css';
					$stylesheet_option = tribe_get_option( 'stylesheetOption' );

					// What Option was selected
					switch( $stylesheet_option ) {
						case 'skeleton':
						case 'full':
							$event_file_option = 'tribe-events-'. $stylesheet_option .'.css';
							break;
						default:
							$event_file_option = 'tribe-events-theme.css';
							break;
					}
					
					// Is there a core override file in the theme?
					$styleUrl = trailingslashit( $tec->pluginUrl ) . 'resources/' . $event_file_option;
					$styleUrl = TribeEventsTemplates::locate_stylesheet('tribe-events/'.$event_file, $styleUrl);
					$styleUrl = apply_filters( 'tribe_events_stylesheet_url', $styleUrl );

					// Load up stylesheet from theme or plugin
					if ( $styleUrl )
						wp_enqueue_style( TribeEvents::POSTTYPE . '-calendar-style', $styleUrl );
					break;
				default :
					do_action($prefix . '-' . $name);
					break;
			}
		}

		public function debug_wrapper( $html, $filter_name ){
			return self::debug($filter_name) . $html . self::debug($filter_name, false);
		}

		public static function debug( $label = null, $start = TRUE, $echo = false) {
			if( defined('WP_DEBUG') && WP_DEBUG && !empty($label) ) {
				$label = (!$start) ? '/' . $label : $label;
				$html = "\n" . '<!-- ' . $label . ' -->' . "\n";
				if( $echo ) {
					echo $html;
				} else {
					return $html;
				}
			}
		}
	}
	add_filter( 'tribe_template_factory_debug', array( 'Tribe_Template_Factory', 'debug_wrapper' ), 1, 2 );
}