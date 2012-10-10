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
		public static function asset_package( $name ){

			$tec = TribeEvents::instance();
			$prefix = 'tribe-events'; // TribeEvents::POSTTYPE;

			// setup plugin resources & 3rd party vendor urls
			$resouces_url = trailingslashit( $tec->pluginUrl ) . 'resources/';
			$vendor_url = trailingslashit( $tec->pluginUrl ) . 'vendor/';

			switch( $name ) {
				case 'chosen' : // Vendor: jQuery Chosen
					wp_enqueue_style( $prefix . '-chosen-style', $vendor_url . 'chosen/chosen/chosen.css' );
					wp_enqueue_script( $prefix . '-chosen-jquery', $vendor_url . 'chosen/chosen/chosen.jquery.min.js', array('jquery'), '0.9.5', false );
					break;
				case 'pjax' : // Vendor: jQuery pjax
					wp_enqueue_script( $prefix . '-pjax', $vendor_url . 'jquery-pjax/jquery.pjax.js', array('jquery') );
					break;
				case 'smoothness' : // Vendor: jQuery Custom Styles
					wp_enqueue_style( $prefix . '-custom-jquery-styles', $vendor_url . 'jquery/smoothness/jquery-ui-1.8.23.custom.css' );
					break;
				case 'select2' : // Vendor: Select2
					wp_enqueue_style( $prefix . '-select2-css', $vendor_url . 'select2/select2.css' );
					wp_enqueue_script( $prefix . '-select2', $vendor_url . 'select2/select2.min.js', 'jquery' );
					break;
				case 'calendar-script' : // Tribe Events JS
					wp_enqueue_script( $prefix . '-calendar-script', $resouces_url . 'tribe-events.js', array('jquery', 'tribe-events-pjax') );
					break;
				case 'datepicker' : // Vendor: jQuery Datepicker
					wp_enqueue_script( 'jquery-ui-datepicker' );
					wp_enqueue_style( 'jquery-ui-datepicker' );
					break;
				case 'dialog' : // Vendor: jQuery Dialog
					wp_enqueue_script( $prefix . '-ui-dialog', $vendor_url . 'jquery/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );
					break;
				case 'admin-ui' : // Tribe Events 
					wp_enqueue_style( $prefix . '-admin-ui', $resouces_url . 'events-admin.css' );
					break;
				case 'admin' :
					wp_enqueue_script( $prefix . '-admin', $resouces_url . 'events-admin.js', array('jquery-ui-datepicker'), '', true );
					break;
				case 'settings' :
					wp_enqueue_script( $prefix . '-settings', $resouces_url . 'tribe-settings.js', array('jquery'), '', true );
					break;
				case 'ecp-plugins' : 
					wp_enqueue_script( $prefix . '-ecp-plugins', $resouces_url . 'jquery-ecp-plugins.js', array('jquery') );
					break;
				case 'tribe-events-bar' :
					wp_enqueue_script( $prefix . '-bar', $resouces_url . 'tribe-events-bar.js', array( 'jquery' ) );
					break;
				case 'jquery-placeholder' : // Vendor: jQuery Placeholder
					wp_enqueue_script( $prefix . '-jquery-placeholder', $vendor_url . 'jquery-placeholder/jquery.placeholder.min.js', array( 'jquery' ), '2.0.7', false );
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